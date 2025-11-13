<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InventoryService;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Get stock level for a product
     * 
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStock($productId)
    {
        try {
            $stock = $this->inventoryService->getStock($productId);
            
            return response()->json([
                'success' => true,
                'data' => $stock
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get stock levels for multiple products
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBulkStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $stock = $this->inventoryService->getBulkStock($request->product_ids);
            
            return response()->json([
                'success' => true,
                'data' => $stock
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve stock levels',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Adjust stock quantity
     * 
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function adjustStock(Request $request, $productId)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer',
            'reason' => 'nullable|string|max:255',
            'reference_type' => 'nullable|string|max:50',
            'reference_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->inventoryService->adjustStock(
                $productId,
                $request->quantity,
                $request->reason ?? 'Manual adjustment',
                $request->reference_type,
                $request->reference_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Stock adjusted successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Set stock to a specific quantity
     * 
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function setStock(Request $request, $productId)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->inventoryService->setStock(
                $productId,
                $request->quantity,
                $request->reason ?? 'Stock set manually'
            );

            return response()->json([
                'success' => true,
                'message' => 'Stock set successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Reserve stock for an order
     * 
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function reserveStock(Request $request, $productId)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->inventoryService->reserveStock(
                $productId,
                $request->quantity,
                $request->order_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Stock reserved successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Release reserved stock
     * 
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function releaseStock(Request $request, $productId)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->inventoryService->releaseStock(
                $productId,
                $request->quantity,
                $request->order_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Stock released successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get low stock products
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLowStock(Request $request)
    {
        $threshold = $request->input('threshold', 10);
        
        try {
            $products = $this->inventoryService->getLowStockProducts($threshold);
            
            return response()->json([
                'success' => true,
                'data' => $products,
                'threshold' => $threshold
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve low stock products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get out of stock products
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOutOfStock()
    {
        try {
            $products = $this->inventoryService->getOutOfStockProducts();
            
            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve out of stock products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk stock adjustment
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkAdjustStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'adjustments' => 'required|array|min:1',
            'adjustments.*.product_id' => 'required|integer|exists:products,id',
            'adjustments.*.quantity' => 'required|integer',
            'adjustments.*.reason' => 'nullable|string|max:255',
            'adjustments.*.reference_type' => 'nullable|string|max:50',
            'adjustments.*.reference_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->inventoryService->bulkAdjustStock($request->adjustments);
            
            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk adjustment failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get inventory history for a product
     * 
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistory(Request $request, $productId)
    {
        $limit = $request->input('limit', 50);
        
        try {
            $history = $this->inventoryService->getHistory($productId, $limit);
            
            return response()->json([
                'success' => true,
                'data' => $history
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve inventory history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if product has sufficient stock
     * 
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStock(Request $request, $productId)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $hasStock = $this->inventoryService->hasSufficientStock($productId, $request->quantity);
            $stock = $this->inventoryService->getStock($productId);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'has_sufficient_stock' => $hasStock,
                    'requested_quantity' => $request->quantity,
                    'available_quantity' => $stock['stock_quantity'],
                    'product_id' => $productId,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}

