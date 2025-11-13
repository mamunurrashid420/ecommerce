<?php

namespace App\Http\Controllers;

use App\Services\AdminPurchaseService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminPurchaseController extends Controller
{
    protected $purchaseService;

    public function __construct(AdminPurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    /**
     * Record a product purchase from supplier
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recordPurchase(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'purchase_price' => 'nullable|numeric|min:0',
                'supplier_name' => 'nullable|string|max:255',
                'purchase_order_number' => 'nullable|string|max:100',
                'reason' => 'nullable|string|max:500',
            ]);

            $admin = auth()->user();
            if (!$admin || !$admin->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }

            $data = [
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'purchase_price' => $request->purchase_price,
                'supplier_name' => $request->supplier_name,
                'purchase_order_number' => $request->purchase_order_number,
                'reason' => $request->reason,
            ];

            $result = $this->purchaseService->recordPurchase($data, $admin->id);

            return response()->json($result, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record purchase',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Record bulk product purchases
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recordBulkPurchases(Request $request)
    {
        try {
            $request->validate([
                'purchases' => 'required|array|min:1',
                'purchases.*.product_id' => 'required|integer|exists:products,id',
                'purchases.*.quantity' => 'required|integer|min:1',
                'purchases.*.purchase_price' => 'nullable|numeric|min:0',
                'purchases.*.supplier_name' => 'nullable|string|max:255',
                'purchases.*.purchase_order_number' => 'nullable|string|max:100',
                'purchases.*.reason' => 'nullable|string|max:500',
            ]);

            $admin = auth()->user();
            if (!$admin || !$admin->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }

            $result = $this->purchaseService->recordBulkPurchases($request->purchases, $admin->id);

            return response()->json($result, $result['success'] ? 201 : 400);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record bulk purchases',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get purchase history for a product
     * 
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductPurchaseHistory(Request $request, $productId)
    {
        try {
            $limit = $request->input('limit', 50);
            $result = $this->purchaseService->getPurchaseHistory($productId, $limit);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve purchase history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all purchase history with filters
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllPurchaseHistory(Request $request)
    {
        try {
            $filters = [
                'product_id' => $request->get('product_id'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
                'admin_id' => $request->get('admin_id'),
            ];

            $perPage = $request->get('per_page', 15);
            $result = $this->purchaseService->getAllPurchaseHistory($filters, $perPage);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve purchase history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get purchase statistics
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPurchaseStats(Request $request)
    {
        try {
            $filters = [
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
            ];

            $result = $this->purchaseService->getPurchaseStats($filters);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve purchase statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

