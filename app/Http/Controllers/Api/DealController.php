<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Customer;
use App\Models\Product;
use App\Services\DealService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Exception;

class DealController extends Controller
{
    protected $dealService;

    public function __construct(DealService $dealService)
    {
        $this->dealService = $dealService;
    }

    /**
     * Get all available deals (Public/Customer endpoint)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Deal::valid()
                ->where('is_active', true);

            // Filter by type
            if ($request->has('type')) {
                $query->where('type', $request->get('type'));
            }

            // Filter by featured
            if ($request->has('featured') && $request->boolean('featured')) {
                $query->where('is_featured', true);
            }

            // Filter by product
            if ($request->has('product_id')) {
                $productId = $request->get('product_id');
                $query->where(function($q) use ($productId) {
                    $q->where('type', 'product')
                      ->whereJsonContains('applicable_products', $productId)
                      ->orWhere(function($q2) use ($productId) {
                          $product = Product::find($productId);
                          if ($product && $product->category_id) {
                              $q2->where('type', 'category')
                                ->whereJsonContains('applicable_categories', $product->category_id);
                          }
                      });
                });
            }

            // Filter by category
            if ($request->has('category_id')) {
                $query->where('type', 'category')
                      ->whereJsonContains('applicable_categories', $request->get('category_id'));
            }

            // Sort
            $sortBy = $request->get('sort_by', 'priority');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder)->orderBy('created_at', 'desc');

            $perPage = $request->get('per_page', 12);
            $deals = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $deals->items(),
                'pagination' => [
                    'current_page' => $deals->currentPage(),
                    'last_page' => $deals->lastPage(),
                    'per_page' => $deals->perPage(),
                    'total' => $deals->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve deals',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single deal (Public/Customer endpoint)
     * 
     * @param string $identifier Deal ID or slug
     * @return JsonResponse
     */
    public function show($identifier): JsonResponse
    {
        try {
            // Try to find by ID first (if numeric), then by slug
            if (is_numeric($identifier)) {
                $deal = Deal::valid()->find($identifier);
            } else {
                $deal = Deal::valid()->where('slug', $identifier)->first();
            }

            if (!$deal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deal not found or expired'
                ], 404);
            }

            // Load related products/categories if applicable
            if ($deal->applicable_products) {
                $deal->load(['products' => function($query) use ($deal) {
                    $query->whereIn('id', $deal->applicable_products)
                          ->with(['category', 'media']);
                }]);
            }

            if ($deal->applicable_categories) {
                $deal->load(['categories' => function($query) use ($deal) {
                    $query->whereIn('id', $deal->applicable_categories);
                }]);
            }

            return response()->json([
                'success' => true,
                'data' => $deal
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve deal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get featured deals (Public/Customer endpoint)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function featured(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 6);
            
            $deals = Deal::valid()
                ->where('is_active', true)
                ->where('is_featured', true)
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $deals
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve featured deals',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get flash deals (time-limited deals) (Public/Customer endpoint)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function flashDeals(Request $request): JsonResponse
    {
        try {
            $query = Deal::valid()
                ->where('is_active', true)
                ->where('type', 'flash')
                ->whereNotNull('end_date')
                ->where('end_date', '>', now())
                ->orderBy('end_date', 'asc');

            $perPage = $request->get('per_page', 12);
            $deals = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $deals->items(),
                'pagination' => [
                    'current_page' => $deals->currentPage(),
                    'last_page' => $deals->lastPage(),
                    'per_page' => $deals->perPage(),
                    'total' => $deals->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve flash deals',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate and calculate discount for a deal (Customer endpoint)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function validate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'deal_id' => 'required|integer|exists:deals,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            $customer = $this->getAuthenticatedCustomer();
            
            $result = $this->dealService->validateAndCalculateDiscount(
                $request->deal_id,
                $request->items,
                $customer->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Deal is valid',
                'data' => [
                    'deal' => [
                        'id' => $result['deal']->id,
                        'title' => $result['deal']->title,
                        'slug' => $result['deal']->slug,
                        'description' => $result['deal']->description,
                        'type' => $result['deal']->type,
                        'discount_type' => $result['deal']->discount_type,
                        'discount_value' => $result['deal']->discount_value,
                        'time_remaining' => $result['deal']->time_remaining,
                    ],
                    'subtotal' => $result['subtotal'],
                    'discount_amount' => $result['discount_amount'],
                    'total_after_discount' => $result['total_after_discount'],
                    'applicable_items' => $result['applicable_items'],
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
     * Get deals for a specific product (Public/Customer endpoint)
     * 
     * @param int $productId
     * @return JsonResponse
     */
    public function forProduct($productId): JsonResponse
    {
        try {
            $product = Product::findOrFail($productId);

            $deals = Deal::valid()
                ->where('is_active', true)
                ->where(function($query) use ($product) {
                    $query->where(function($q) use ($product) {
                        $q->where('type', 'product')
                          ->whereJsonContains('applicable_products', $product->id);
                    })->orWhere(function($q) use ($product) {
                        if ($product->category_id) {
                            $q->where('type', 'category')
                              ->whereJsonContains('applicable_categories', $product->category_id);
                        }
                    });
                })
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $deals
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve deals for product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get deals for a specific category (Public/Customer endpoint)
     * 
     * @param int $categoryId
     * @return JsonResponse
     */
    public function forCategory($categoryId): JsonResponse
    {
        try {
            $deals = Deal::valid()
                ->where('is_active', true)
                ->where('type', 'category')
                ->whereJsonContains('applicable_categories', $categoryId)
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $deals
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve deals for category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated customer
     * 
     * @return Customer
     * @throws Exception
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

