<?php

namespace App\Http\Controllers;

use App\Services\PurchaseService;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    protected $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    /**
     * Check product and inventory availability before purchase
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAvailability(Request $request)
    {
        try {
            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            $result = $this->purchaseService->checkAvailability($request->items);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 400);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check availability',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get purchase summary before checkout
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSummary(Request $request)
    {
        try {
            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            $customerId = null;
            if (auth()->check() && auth()->user() instanceof Customer) {
                $customerId = auth()->id();
            }

            $result = $this->purchaseService->getPurchaseSummary($request->items, $customerId);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 400);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get purchase summary',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Validate purchase items (for authenticated customers)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateItems(Request $request)
    {
        try {
            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            $customer = $this->getAuthenticatedCustomer();
            
            // Validate customer
            $this->purchaseService->validateCustomer($customer->id);

            // Validate items
            $result = $this->purchaseService->validatePurchaseItems($request->items);

            return response()->json([
                'success' => true,
                'message' => 'Purchase items are valid',
                'data' => [
                    'total_items' => $result['total_items'],
                    'total_quantity' => $result['total_quantity'],
                    'total_amount' => $result['total_amount'],
                    'items' => array_map(function ($item) {
                        return [
                            'product_id' => $item['product_id'],
                            'product_name' => $item['product']->name,
                            'product_sku' => $item['product']->sku,
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['price'],
                            'item_total' => $item['total'],
                            'available_stock' => $item['available_stock'],
                        ];
                    }, $result['validated_items']),
                ],
                'warnings' => $result['warnings'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase validation failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get authenticated customer
     * 
     * @return Customer
     * @throws \Exception
     */
    protected function getAuthenticatedCustomer()
    {
        $user = auth()->user();
        
        if (!$user) {
            throw new \Exception('Unauthenticated');
        }

        // Check if authenticated user is a Customer model
        if ($user instanceof Customer) {
            return $user;
        }

        throw new \Exception('Customer authentication required');
    }
}

