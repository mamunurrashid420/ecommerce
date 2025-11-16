<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Customer;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Exception;

class CouponController extends Controller
{
    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * List all coupons (Admin only)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Coupon::query();

            // Filter by active status
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Search by code or name
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
                });
            }

            // Filter by type
            if ($request->has('type')) {
                $query->where('type', $request->get('type'));
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 15);
            $coupons = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $coupons->items(),
                'pagination' => [
                    'current_page' => $coupons->currentPage(),
                    'last_page' => $coupons->lastPage(),
                    'per_page' => $coupons->perPage(),
                    'total' => $coupons->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve coupons',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new coupon (Admin only)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:coupons,code',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'required|in:percentage,fixed',
                'discount_value' => 'required|numeric|min:0',
                'minimum_purchase' => 'nullable|numeric|min:0',
                'maximum_discount' => 'nullable|numeric|min:0',
                'usage_limit' => 'nullable|integer|min:1',
                'usage_limit_per_customer' => 'nullable|integer|min:1',
                'valid_from' => 'nullable|date',
                'valid_until' => 'nullable|date|after_or_equal:valid_from',
                'is_active' => 'sometimes|boolean',
                'applicable_categories' => 'nullable|array',
                'applicable_categories.*' => 'integer|exists:categories,id',
                'applicable_products' => 'nullable|array',
                'applicable_products.*' => 'integer|exists:products,id',
                'first_order_only' => 'sometimes|boolean',
            ]);

            // Additional validation for percentage type
            if ($validated['type'] === 'percentage' && $validated['discount_value'] > 100) {
                throw ValidationException::withMessages([
                    'discount_value' => ['Percentage discount cannot exceed 100%.']
                ]);
            }

            $coupon = Coupon::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Coupon created successfully',
                'data' => $coupon
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create coupon',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get a single coupon (Admin only)
     */
    public function show(Coupon $coupon): JsonResponse
    {
        try {
            $coupon->load('usages.order', 'usages.customer');
            
            return response()->json([
                'success' => true,
                'data' => $coupon
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve coupon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a coupon (Admin only)
     */
    public function update(Request $request, Coupon $coupon): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'sometimes|string|max:50|unique:coupons,code,' . $coupon->id,
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'type' => 'sometimes|in:percentage,fixed',
                'discount_value' => 'sometimes|numeric|min:0',
                'minimum_purchase' => 'nullable|numeric|min:0',
                'maximum_discount' => 'nullable|numeric|min:0',
                'usage_limit' => 'nullable|integer|min:1',
                'usage_limit_per_customer' => 'nullable|integer|min:1',
                'valid_from' => 'nullable|date',
                'valid_until' => 'nullable|date|after_or_equal:valid_from',
                'is_active' => 'sometimes|boolean',
                'applicable_categories' => 'nullable|array',
                'applicable_categories.*' => 'integer|exists:categories,id',
                'applicable_products' => 'nullable|array',
                'applicable_products.*' => 'integer|exists:products,id',
                'first_order_only' => 'sometimes|boolean',
            ]);

            // Additional validation for percentage type
            if (isset($validated['type']) && $validated['type'] === 'percentage') {
                $discountValue = $validated['discount_value'] ?? $coupon->discount_value;
                if ($discountValue > 100) {
                    throw ValidationException::withMessages([
                        'discount_value' => ['Percentage discount cannot exceed 100%.']
                    ]);
                }
            }

            $coupon->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Coupon updated successfully',
                'data' => $coupon->fresh()
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
                'message' => 'Failed to update coupon',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete a coupon (Admin only)
     */
    public function destroy(Coupon $coupon): JsonResponse
    {
        try {
            // Check if coupon has been used
            if ($coupon->usage_count > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete coupon that has been used. Consider deactivating it instead.'
                ], 409);
            }

            $coupon->delete();

            return response()->json([
                'success' => true,
                'message' => 'Coupon deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete coupon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle coupon active status (Admin only)
     */
    public function toggleActive(Coupon $coupon): JsonResponse
    {
        try {
            $coupon->is_active = !$coupon->is_active;
            $coupon->save();

            return response()->json([
                'success' => true,
                'message' => 'Coupon status updated successfully',
                'data' => $coupon
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update coupon status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get coupon statistics (Admin only)
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $couponId = $request->get('coupon_id');
            $stats = $this->couponService->getCouponStats($couponId);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve coupon statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate coupon code (Customer endpoint)
     */
    public function validate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'code' => 'required|string|max:50',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            $customer = $this->getAuthenticatedCustomer();
            
            $result = $this->couponService->validateAndCalculateDiscount(
                $request->code,
                $request->items,
                $customer->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Coupon is valid',
                'data' => [
                    'coupon' => [
                        'id' => $result['coupon']->id,
                        'code' => $result['coupon']->code,
                        'name' => $result['coupon']->name,
                        'description' => $result['coupon']->description,
                        'type' => $result['coupon']->type,
                        'discount_value' => $result['coupon']->discount_value,
                    ],
                    'subtotal' => $result['subtotal'],
                    'discount_amount' => $result['discount_amount'],
                    'total_after_discount' => $result['total_after_discount'],
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get available coupons for customer (Public/Customer endpoint)
     */
    public function available(Request $request): JsonResponse
    {
        try {
            $query = Coupon::where('is_active', true)
                ->where(function($q) {
                    $q->whereNull('valid_from')
                      ->orWhere('valid_from', '<=', now());
                })
                ->where(function($q) {
                    $q->whereNull('valid_until')
                      ->orWhere('valid_until', '>=', now());
                })
                ->where(function($q) {
                    $q->whereNull('usage_limit')
                      ->orWhereColumn('usage_count', '<', 'usage_limit');
                });

            // Filter by customer eligibility if authenticated
            if ($request->user() instanceof Customer) {
                $customerId = $request->user()->id;
                
                // Filter out coupons that customer has already used up
                $query->where(function($q) use ($customerId) {
                    $q->whereNull('usage_limit_per_customer')
                      ->orWhereRaw('(SELECT COUNT(*) FROM coupon_usages WHERE coupon_id = coupons.id AND customer_id = ?) < COALESCE(coupons.usage_limit_per_customer, 999999)', [$customerId]);
                });
            }

            $coupons = $query->orderBy('created_at', 'desc')
                ->get()
                ->map(function($coupon) {
                    return [
                        'id' => $coupon->id,
                        'code' => $coupon->code,
                        'name' => $coupon->name,
                        'description' => $coupon->description,
                        'type' => $coupon->type,
                        'discount_value' => $coupon->discount_value,
                        'minimum_purchase' => $coupon->minimum_purchase,
                        'maximum_discount' => $coupon->maximum_discount,
                        'valid_until' => $coupon->valid_until,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $coupons
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve available coupons',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated customer
     */
    protected function getAuthenticatedCustomer()
    {
        $user = auth()->user();
        
        if (!$user) {
            throw new Exception('Unauthenticated');
        }

        if ($user instanceof Customer) {
            return $user;
        }

        throw new Exception('Customer authentication required');
    }
}
