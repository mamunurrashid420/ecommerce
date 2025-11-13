<?php

namespace App\Services;

use App\Models\Product;
use App\Models\InventoryHistory;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AdminPurchaseService
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Record a product purchase from supplier (increases inventory)
     * 
     * @param array $data Purchase data
     * @param int $adminId Admin user ID who made the purchase
     * @return array
     * @throws Exception
     */
    public function recordPurchase(array $data, int $adminId): array
    {
        DB::beginTransaction();
        
        try {
            // Validate admin user
            $admin = User::findOrFail($adminId);
            if (!$admin->isAdmin()) {
                throw new Exception('Only admin users can record purchases');
            }

            // Validate product exists
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);
            
            $oldQuantity = $product->stock_quantity;
            $purchaseQuantity = $data['quantity'];
            $newQuantity = $oldQuantity + $purchaseQuantity;
            
            // Validate purchase quantity
            if ($purchaseQuantity <= 0) {
                throw new Exception("Purchase quantity must be greater than 0");
            }
            
            // Update product stock
            $product->stock_quantity = $newQuantity;
            
            // Update product price if provided
            if (isset($data['purchase_price']) && $data['purchase_price'] > 0) {
                // Optionally update product cost or keep purchase price separate
                // For now, we'll just record it in history
            }
            
            // Update product if inactive and being restocked
            if (!$product->is_active && $purchaseQuantity > 0) {
                // Optionally reactivate product, but we'll leave it as is for safety
            }
            
            $product->save();
            
            // Record inventory history
            $reason = $data['reason'] ?? "Product purchase from supplier";
            if (isset($data['supplier_name'])) {
                $reason .= " - Supplier: {$data['supplier_name']}";
            }
            if (isset($data['purchase_order_number'])) {
                $reason .= " - PO#: {$data['purchase_order_number']}";
            }
            
            $this->recordPurchaseHistory([
                'product_id' => $product->id,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'adjustment' => $purchaseQuantity,
                'reason' => $reason,
                'purchase_price' => $data['purchase_price'] ?? null,
                'supplier_name' => $data['supplier_name'] ?? null,
                'purchase_order_number' => $data['purchase_order_number'] ?? null,
                'created_by' => $adminId,
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Product purchase recorded successfully',
                'purchase' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'purchase_quantity' => $purchaseQuantity,
                    'old_stock' => $oldQuantity,
                    'new_stock' => $newQuantity,
                    'purchase_price' => $data['purchase_price'] ?? null,
                    'supplier_name' => $data['supplier_name'] ?? null,
                    'purchase_order_number' => $data['purchase_order_number'] ?? null,
                ],
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'stock_quantity' => $newQuantity,
                    'is_active' => $product->is_active,
                ],
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Admin purchase recording failed', [
                'admin_id' => $adminId,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Record bulk product purchases
     * 
     * @param array $purchases Array of purchase data
     * @param int $adminId Admin user ID
     * @return array
     * @throws Exception
     */
    public function recordBulkPurchases(array $purchases, int $adminId): array
    {
        $results = [];
        $errors = [];
        
        DB::beginTransaction();
        
        try {
            // Validate admin user
            $admin = User::findOrFail($adminId);
            if (!$admin->isAdmin()) {
                throw new Exception('Only admin users can record purchases');
            }

            foreach ($purchases as $index => $purchaseData) {
                try {
                    $result = $this->recordPurchase($purchaseData, $adminId);
                    $results[] = [
                        'index' => $index,
                        'success' => true,
                        'purchase' => $result['purchase'],
                    ];
                } catch (Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'product_id' => $purchaseData['product_id'] ?? null,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            if (!empty($errors)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Some purchases failed',
                    'results' => $results,
                    'errors' => $errors
                ];
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'All purchases recorded successfully',
                'total_purchases' => count($results),
                'results' => $results
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Bulk purchase recording failed', [
                'admin_id' => $adminId,
                'purchases' => $purchases,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get purchase history for a product
     * 
     * @param int $productId
     * @param int $limit
     * @return array
     */
    public function getPurchaseHistory(int $productId, int $limit = 50): array
    {
        $history = InventoryHistory::where('product_id', $productId)
            ->where('reference_type', 'admin_purchase')
            ->orWhere(function($query) {
                $query->where('reason', 'like', '%Product purchase%')
                      ->orWhere('reason', 'like', '%supplier%');
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->with('creator')
            ->get();
        
        return [
            'success' => true,
            'product_id' => $productId,
            'history' => $history,
            'total_records' => $history->count(),
        ];
    }

    /**
     * Get all purchase history with filters
     * 
     * @param array $filters
     * @param int $perPage
     * @return array
     */
    public function getAllPurchaseHistory(array $filters = [], int $perPage = 15): array
    {
        $query = InventoryHistory::where(function($q) {
                $q->where('reference_type', 'admin_purchase')
                  ->orWhere('reason', 'like', '%Product purchase%')
                  ->orWhere('reason', 'like', '%supplier%');
            })
            ->with(['product', 'creator'])
            ->orderBy('created_at', 'desc');
        
        // Filter by product
        if (isset($filters['product_id']) && $filters['product_id']) {
            $query->where('product_id', $filters['product_id']);
        }
        
        // Filter by date range
        if (isset($filters['date_from']) && $filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to']) && $filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        
        // Filter by admin user
        if (isset($filters['admin_id']) && $filters['admin_id']) {
            $query->where('created_by', $filters['admin_id']);
        }
        
        $history = $query->paginate($perPage);
        
        return [
            'success' => true,
            'data' => $history->items(),
            'pagination' => [
                'current_page' => $history->currentPage(),
                'last_page' => $history->lastPage(),
                'per_page' => $history->perPage(),
                'total' => $history->total(),
            ],
        ];
    }

    /**
     * Record purchase history
     * 
     * @param array $data
     * @return InventoryHistory
     */
    private function recordPurchaseHistory(array $data): InventoryHistory
    {
        return InventoryHistory::create([
            'product_id' => $data['product_id'],
            'old_quantity' => $data['old_quantity'],
            'new_quantity' => $data['new_quantity'],
            'adjustment' => $data['adjustment'],
            'reason' => $data['reason'],
            'reference_type' => 'admin_purchase',
            'reference_id' => null,
            'created_by' => $data['created_by'],
        ]);
    }

    /**
     * Get purchase statistics
     * 
     * @param array $filters
     * @return array
     */
    public function getPurchaseStats(array $filters = []): array
    {
        $query = InventoryHistory::where(function($q) {
                $q->where('reference_type', 'admin_purchase')
                  ->orWhere('reason', 'like', '%Product purchase%')
                  ->orWhere('reason', 'like', '%supplier%');
            });
        
        // Apply date filters
        if (isset($filters['date_from']) && $filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to']) && $filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        
        $totalPurchases = $query->count();
        $totalQuantity = $query->sum('adjustment');
        
        // Get top purchased products
        $topProducts = InventoryHistory::where(function($q) {
                $q->where('reference_type', 'admin_purchase')
                  ->orWhere('reason', 'like', '%Product purchase%')
                  ->orWhere('reason', 'like', '%supplier%');
            })
            ->select('product_id', DB::raw('sum(adjustment) as total_purchased'))
            ->groupBy('product_id')
            ->orderBy('total_purchased', 'desc')
            ->limit(10)
            ->with('product')
            ->get();
        
        return [
            'success' => true,
            'stats' => [
                'total_purchases' => $totalPurchases,
                'total_quantity_purchased' => $totalQuantity,
                'top_purchased_products' => $topProducts,
            ],
        ];
    }
}

