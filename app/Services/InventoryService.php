<?php

namespace App\Services;

use App\Models\Product;
use App\Models\InventoryHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class InventoryService
{
    /**
     * Adjust stock quantity for a product
     * 
     * @param int $productId
     * @param int $quantity (positive to increase, negative to decrease)
     * @param string $reason
     * @param string|null $referenceType (e.g., 'order', 'adjustment', 'return')
     * @param int|null $referenceId
     * @return array
     * @throws Exception
     */
    public function adjustStock(
        int $productId,
        int $quantity,
        string $reason = 'Manual adjustment',
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array {
        DB::beginTransaction();
        
        try {
            $product = Product::lockForUpdate()->findOrFail($productId);
            
            $oldQuantity = $product->stock_quantity;
            $newQuantity = $oldQuantity + $quantity;
            
            // Prevent negative stock if quantity is negative
            if ($newQuantity < 0) {
                throw new Exception("Insufficient stock. Available: {$oldQuantity}, Requested: " . abs($quantity));
            }
            
            $product->stock_quantity = $newQuantity;
            $product->save();
            
            // Record inventory history
            $this->recordHistory([
                'product_id' => $productId,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'adjustment' => $quantity,
                'reason' => $reason,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'product_id' => $productId,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'adjustment' => $quantity,
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Inventory adjustment failed', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Reserve stock for an order
     * 
     * @param int $productId
     * @param int $quantity
     * @param int $orderId
     * @return array
     * @throws Exception
     */
    public function reserveStock(int $productId, int $quantity, int $orderId): array
    {
        return $this->adjustStock(
            $productId,
            -$quantity,
            "Stock reserved for order #{$orderId}",
            'order',
            $orderId
        );
    }
    
    /**
     * Release reserved stock (e.g., order cancelled)
     * 
     * @param int $productId
     * @param int $quantity
     * @param int $orderId
     * @return array
     * @throws Exception
     */
    public function releaseStock(int $productId, int $quantity, int $orderId): array
    {
        return $this->adjustStock(
            $productId,
            $quantity,
            "Stock released from cancelled order #{$orderId}",
            'order',
            $orderId
        );
    }
    
    /**
     * Set stock to a specific quantity
     * 
     * @param int $productId
     * @param int $quantity
     * @param string $reason
     * @return array
     * @throws Exception
     */
    public function setStock(int $productId, int $quantity, string $reason = 'Stock set manually'): array
    {
        if ($quantity < 0) {
            throw new Exception("Stock quantity cannot be negative");
        }
        
        DB::beginTransaction();
        
        try {
            $product = Product::lockForUpdate()->findOrFail($productId);
            
            $oldQuantity = $product->stock_quantity;
            $adjustment = $quantity - $oldQuantity;
            
            $product->stock_quantity = $quantity;
            $product->save();
            
            // Record inventory history
            $this->recordHistory([
                'product_id' => $productId,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $quantity,
                'adjustment' => $adjustment,
                'reason' => $reason,
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'product_id' => $productId,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $quantity,
                'adjustment' => $adjustment,
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Set stock failed', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Get current stock level for a product
     * 
     * @param int $productId
     * @return array
     */
    public function getStock(int $productId): array
    {
        $product = Product::findOrFail($productId);
        
        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'stock_quantity' => $product->stock_quantity,
            'is_low_stock' => $this->isLowStock($product),
            'is_out_of_stock' => $product->stock_quantity <= 0,
        ];
    }
    
    /**
     * Get stock levels for multiple products
     * 
     * @param array $productIds
     * @return array
     */
    public function getBulkStock(array $productIds): array
    {
        $products = Product::whereIn('id', $productIds)->get();
        
        return $products->map(function ($product) {
            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'stock_quantity' => $product->stock_quantity,
                'is_low_stock' => $this->isLowStock($product),
                'is_out_of_stock' => $product->stock_quantity <= 0,
            ];
        })->toArray();
    }
    
    /**
     * Get products with low stock
     * 
     * @param int $threshold
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowStockProducts(int $threshold = 10)
    {
        return Product::where('stock_quantity', '<=', $threshold)
            ->where('stock_quantity', '>', 0)
            ->where('is_active', true)
            ->get();
    }
    
    /**
     * Get out of stock products
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOutOfStockProducts()
    {
        return Product::where('stock_quantity', '<=', 0)
            ->where('is_active', true)
            ->get();
    }
    
    /**
     * Bulk stock adjustment
     * 
     * @param array $adjustments [['product_id' => int, 'quantity' => int, 'reason' => string], ...]
     * @return array
     */
    public function bulkAdjustStock(array $adjustments): array
    {
        $results = [];
        $errors = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($adjustments as $index => $adjustment) {
                try {
                    $result = $this->adjustStock(
                        $adjustment['product_id'],
                        $adjustment['quantity'],
                        $adjustment['reason'] ?? 'Bulk adjustment',
                        $adjustment['reference_type'] ?? null,
                        $adjustment['reference_id'] ?? null
                    );
                    $results[] = $result;
                } catch (Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'product_id' => $adjustment['product_id'] ?? null,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            if (!empty($errors)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Some adjustments failed',
                    'results' => $results,
                    'errors' => $errors
                ];
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'All adjustments completed successfully',
                'results' => $results
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Get inventory history for a product
     * 
     * @param int $productId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHistory(int $productId, int $limit = 50)
    {
        return InventoryHistory::where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Check if product has sufficient stock
     * 
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function hasSufficientStock(int $productId, int $quantity): bool
    {
        $product = Product::findOrFail($productId);
        return $product->stock_quantity >= $quantity;
    }
    
    /**
     * Check if product is low stock
     * 
     * @param Product $product
     * @param int $threshold
     * @return bool
     */
    private function isLowStock(Product $product, int $threshold = 10): bool
    {
        return $product->stock_quantity > 0 && $product->stock_quantity <= $threshold;
    }
    
    /**
     * Record inventory history
     * 
     * @param array $data
     * @return InventoryHistory
     */
    private function recordHistory(array $data): InventoryHistory
    {
        $createdBy = null;
        
        // Only set created_by if user is authenticated and exists in database
        if (auth()->check()) {
            $userId = auth()->id();
            if ($userId && User::where('id', $userId)->exists()) {
                $createdBy = $userId;
            }
        }
        
        return InventoryHistory::create([
            'product_id' => $data['product_id'],
            'old_quantity' => $data['old_quantity'],
            'new_quantity' => $data['new_quantity'],
            'adjustment' => $data['adjustment'],
            'reason' => $data['reason'],
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'created_by' => $createdBy,
        ]);
    }
}

