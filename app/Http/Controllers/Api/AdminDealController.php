<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Product;
use App\Models\Category;
use App\Services\DealService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class AdminDealController extends Controller
{
    protected $dealService;

    public function __construct(DealService $dealService)
    {
        $this->dealService = $dealService;
    }

    /**
     * List all deals (Admin only)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Deal::with(['creator', 'updater']);

            // Filter by active status
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Filter by featured
            if ($request->has('is_featured')) {
                $query->where('is_featured', $request->boolean('is_featured'));
            }

            // Filter by type
            if ($request->has('type')) {
                $query->where('type', $request->get('type'));
            }

            // Filter by valid deals
            if ($request->has('valid_only') && $request->boolean('valid_only')) {
                $query->valid();
            }

            // Search by title or description
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 15);
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
     * Create a new deal (Admin only)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:deals,slug',
                'description' => 'nullable|string',
                'short_description' => 'nullable|string|max:500',
                'type' => 'required|in:product,category,flash,buy_x_get_y,minimum_purchase',
                'discount_type' => 'required|in:percentage,fixed',
                'discount_value' => 'required|numeric|min:0',
                'original_price' => 'nullable|numeric|min:0',
                'deal_price' => 'nullable|numeric|min:0',
                'minimum_purchase_amount' => 'nullable|numeric|min:0',
                'maximum_discount' => 'nullable|numeric|min:0',
                'applicable_products' => 'nullable|array',
                'applicable_products.*' => 'integer|exists:products,id',
                'applicable_categories' => 'nullable|array',
                'applicable_categories.*' => 'integer|exists:categories,id',
                'buy_quantity' => 'nullable|integer|min:1',
                'get_quantity' => 'nullable|integer|min:1',
                'get_product_id' => 'nullable|integer|exists:products,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'is_active' => 'sometimes|boolean',
                'is_featured' => 'sometimes|boolean',
                'priority' => 'nullable|integer|min:0',
                'image_url' => 'nullable|url',
                'banner_image_url' => 'nullable|url',
                'usage_limit' => 'nullable|integer|min:1',
                'usage_limit_per_customer' => 'nullable|integer|min:1',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:500',
            ]);

            // Additional validation for percentage type
            if ($validated['discount_type'] === 'percentage' && $validated['discount_value'] > 100) {
                throw ValidationException::withMessages([
                    'discount_value' => ['Percentage discount cannot exceed 100%.']
                ]);
            }

            // Validate type-specific requirements
            if ($validated['type'] === 'product' && empty($validated['applicable_products'])) {
                throw ValidationException::withMessages([
                    'applicable_products' => ['Products are required for product type deals.']
                ]);
            }

            if ($validated['type'] === 'category' && empty($validated['applicable_categories'])) {
                throw ValidationException::withMessages([
                    'applicable_categories' => ['Categories are required for category type deals.']
                ]);
            }

            if ($validated['type'] === 'buy_x_get_y') {
                if (empty($validated['buy_quantity']) || empty($validated['get_quantity']) || empty($validated['get_product_id'])) {
                    throw ValidationException::withMessages([
                        'buy_quantity' => ['Buy quantity, get quantity, and get product are required for buy X get Y deals.'],
                        'get_quantity' => ['Buy quantity, get quantity, and get product are required for buy X get Y deals.'],
                        'get_product_id' => ['Buy quantity, get quantity, and get product are required for buy X get Y deals.'],
                    ]);
                }
            }

            // Generate slug if not provided
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['title']);
                
                // Ensure slug uniqueness
                $originalSlug = $validated['slug'];
                $counter = 1;
                while (Deal::where('slug', $validated['slug'])->exists()) {
                    $validated['slug'] = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }

            // Set created_by and updated_by
            if (auth()->check()) {
                $validated['created_by'] = auth()->id();
                $validated['updated_by'] = auth()->id();
            }

            DB::beginTransaction();
            
            $deal = Deal::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Deal created successfully',
                'data' => $deal->load(['creator', 'updater'])
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create deal',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get a single deal (Admin only)
     * 
     * @param Deal $deal
     * @return JsonResponse
     */
    public function show(Deal $deal): JsonResponse
    {
        try {
            $deal->load(['creator', 'updater']);
            
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
     * Update a deal (Admin only)
     * 
     * @param Request $request
     * @param Deal $deal
     * @return JsonResponse
     */
    public function update(Request $request, Deal $deal): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'slug' => 'sometimes|string|max:255|unique:deals,slug,' . $deal->id,
                'description' => 'nullable|string',
                'short_description' => 'nullable|string|max:500',
                'type' => 'sometimes|in:product,category,flash,buy_x_get_y,minimum_purchase',
                'discount_type' => 'sometimes|in:percentage,fixed',
                'discount_value' => 'sometimes|numeric|min:0',
                'original_price' => 'nullable|numeric|min:0',
                'deal_price' => 'nullable|numeric|min:0',
                'minimum_purchase_amount' => 'nullable|numeric|min:0',
                'maximum_discount' => 'nullable|numeric|min:0',
                'applicable_products' => 'nullable|array',
                'applicable_products.*' => 'integer|exists:products,id',
                'applicable_categories' => 'nullable|array',
                'applicable_categories.*' => 'integer|exists:categories,id',
                'buy_quantity' => 'nullable|integer|min:1',
                'get_quantity' => 'nullable|integer|min:1',
                'get_product_id' => 'nullable|integer|exists:products,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'is_active' => 'sometimes|boolean',
                'is_featured' => 'sometimes|boolean',
                'priority' => 'nullable|integer|min:0',
                'image_url' => 'nullable|url',
                'banner_image_url' => 'nullable|url',
                'usage_limit' => 'nullable|integer|min:1',
                'usage_limit_per_customer' => 'nullable|integer|min:1',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:500',
            ]);

            // Additional validation for percentage type
            if (isset($validated['discount_type']) && $validated['discount_type'] === 'percentage') {
                $discountValue = $validated['discount_value'] ?? $deal->discount_value;
                if ($discountValue > 100) {
                    throw ValidationException::withMessages([
                        'discount_value' => ['Percentage discount cannot exceed 100%.']
                    ]);
                }
            }

            // Set updated_by
            if (auth()->check()) {
                $validated['updated_by'] = auth()->id();
            }

            DB::beginTransaction();
            
            $deal->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Deal updated successfully',
                'data' => $deal->fresh()->load(['creator', 'updater'])
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update deal',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete a deal (Admin only)
     * 
     * @param Deal $deal
     * @return JsonResponse
     */
    public function destroy(Deal $deal): JsonResponse
    {
        try {
            // Check if deal has been used
            if ($deal->usage_count > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete deal that has been used. Consider deactivating it instead.'
                ], 409);
            }

            $deal->delete();

            return response()->json([
                'success' => true,
                'message' => 'Deal deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete deal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle deal active status (Admin only)
     * 
     * @param Deal $deal
     * @return JsonResponse
     */
    public function toggleActive(Deal $deal): JsonResponse
    {
        try {
            $deal->is_active = !$deal->is_active;
            $deal->save();

            return response()->json([
                'success' => true,
                'message' => 'Deal status updated successfully',
                'data' => $deal
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update deal status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle deal featured status (Admin only)
     * 
     * @param Deal $deal
     * @return JsonResponse
     */
    public function toggleFeatured(Deal $deal): JsonResponse
    {
        try {
            $deal->is_featured = !$deal->is_featured;
            $deal->save();

            return response()->json([
                'success' => true,
                'message' => 'Deal featured status updated successfully',
                'data' => $deal
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update deal featured status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get deal statistics (Admin only)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $dealId = $request->get('deal_id');
            $stats = $this->dealService->getDealStats($dealId);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve deal statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

