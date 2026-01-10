<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavedProduct;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SavedProductController extends Controller
{
    /**
     * Get current offer details
     */
    private function getCurrentOffer(): ?array
    {
        $settings = SiteSetting::getInstance();
        $offer = $settings->offer_with_url;
        
        if (!$offer || !isset($offer['start_date']) || !isset($offer['end_date'])) {
            return null;
        }
        
        // Validate offer amount exists and is greater than 0
        if (!isset($offer['amount']) || $offer['amount'] <= 0) {
            return null;
        }
        
        $now = now();
        $startDate = Carbon::parse($offer['start_date'])->startOfDay();
        $endDate = Carbon::parse($offer['end_date'])->endOfDay();
        
        // Check if offer is currently active
        if ($now->between($startDate, $endDate)) {
            return $offer;
        }
        
        return null; // Return null if offer is not active
    }

    /**
     * Calculate discount price based on current offer
     * Price is expected to be in cents (as stored in database)
     */
    private function calculateDiscountPrice(string $priceInCents): array
    {
        $offer = $this->getCurrentOffer();
        
        // Convert price from cents to decimal
        $originalPrice = (float) $priceInCents / 100;
        
        // Default result with no discount
        $result = [
            'discount_percentage' => 0,
            'discount_price' => (int) $priceInCents, // Return in cents
            'old_price' => (int) $priceInCents, // Original price in cents
        ];
        
        // If no active offer, return default (no discount)
        if (!$offer) {
            return $result;
        }
        
        // Calculate discount
        $discountPercentage = (float) $offer['amount'];
        $discountPriceFloat = $originalPrice * (1 - ($discountPercentage / 100));
        
        // Round down to the nearest whole dollar for discount price, then convert back to cents
        $discountPriceCents = (int) floor($discountPriceFloat * 100);
        
        return [
            'discount_percentage' => $discountPercentage,
            'discount_price' => $discountPriceCents,
            'old_price' => (int) $priceInCents, // Original price in cents
        ];
    }

    /**
     * Get all saved products for the authenticated customer
     * GET /api/customer/saved-products
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $customer = auth('sanctum')->user();
            
            $savedProducts = SavedProduct::where('customer_id', $customer->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $savedProducts->map(function ($item) {
                    // Calculate discount information
                    $discountInfo = $this->calculateDiscountPrice($item->product_price);
                    
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_code' => $item->product_code,
                        'product_name' => $item->product_name,
                        'product_price' => $item->product_price,
                        'old_price' => $discountInfo['old_price'],
                        'discount_percentage' => $discountInfo['discount_percentage'],
                        'discount_price' => $discountInfo['discount_price'],
                        'product_image' => $item->product_image,
                        'product_image_url' => $item->product_image_url,
                        'product_sku' => $item->product_sku,
                        'product_slug' => $item->product_slug,
                        'product_category' => $item->product_category,
                        'saved_at' => $item->created_at,
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve saved products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save a product
     * POST /api/customer/saved-products
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|string',
                'product_code' => 'nullable|string',
                'product_name' => 'required|string',
                'product_price' => 'required|string',
                'product_image' => 'nullable|string',
                'product_sku' => 'nullable|string',
                'product_slug' => 'nullable|string',
                'product_category' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $customer = auth('sanctum')->user();

            // Check if product is already saved
            $existing = SavedProduct::where('customer_id', $customer->id)
                ->where('product_id', $request->product_id)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product is already saved'
                ], 409);
            }

            $savedProduct = SavedProduct::create([
                'customer_id' => $customer->id,
                'product_id' => $request->product_id,
                'product_code' => $request->product_code,
                'product_name' => $request->product_name,
                'product_price' => $request->product_price,
                'product_image' => $request->product_image,
                'product_sku' => $request->product_sku,
                'product_slug' => $request->product_slug ?? $request->product_id,
                'product_category' => $request->product_category,
            ]);

            // Calculate discount information for saved product
            $discountInfo = $this->calculateDiscountPrice($savedProduct->product_price);

            return response()->json([
                'success' => true,
                'message' => 'Product saved successfully',
                'data' => [
                    'id' => $savedProduct->id,
                    'product_id' => $savedProduct->product_id,
                    'product_code' => $savedProduct->product_code,
                    'product_name' => $savedProduct->product_name,
                    'product_price' => $savedProduct->product_price,
                    'old_price' => $discountInfo['old_price'],
                    'discount_percentage' => $discountInfo['discount_percentage'],
                    'discount_price' => $discountInfo['discount_price'],
                    'product_image' => $savedProduct->product_image,
                    'product_image_url' => $savedProduct->product_image_url,
                    'product_sku' => $savedProduct->product_sku,
                    'product_slug' => $savedProduct->product_slug,
                    'product_category' => $savedProduct->product_category,
                    'saved_at' => $savedProduct->created_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a saved product
     * DELETE /api/customer/saved-products/{id}
     */
    public function destroy($id): JsonResponse
    {
        try {
            $customer = auth('sanctum')->user();

            $savedProduct = SavedProduct::where('customer_id', $customer->id)
                ->where('id', $id)
                ->first();

            if (!$savedProduct) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saved product not found'
                ], 404);
            }

            $savedProduct->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product removed from saved list'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove saved product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if a product is saved
     * GET /api/customer/saved-products/check/{productId}
     */
    public function check($productId): JsonResponse
    {
        try {
            $customer = auth('sanctum')->user();

            $savedProduct = SavedProduct::where('customer_id', $customer->id)
                ->where('product_id', $productId)
                ->first();

            return response()->json([
                'success' => true,
                'is_saved' => $savedProduct !== null,
                'saved_product_id' => $savedProduct?->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check saved product status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle save status of a product (save if not saved, remove if saved)
     * POST /api/customer/saved-products/toggle
     */
    public function toggle(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|string',
                'product_code' => 'nullable|string',
                'product_name' => 'required|string',
                'product_price' => 'required|string',
                'product_image' => 'nullable|string',
                'product_sku' => 'nullable|string',
                'product_slug' => 'nullable|string',
                'product_category' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $customer = auth('sanctum')->user();

            $existing = SavedProduct::where('customer_id', $customer->id)
                ->where('product_id', $request->product_id)
                ->first();

            if ($existing) {
                // Update category if provided (in case category changed)
                if ($request->has('product_category')) {
                    $existing->product_category = $request->product_category;
                    $existing->save();
                }
                
                // Remove if exists
                $existing->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Product removed from saved list',
                    'is_saved' => false,
                ]);
            } else {
                // Save if not exists
                $savedProduct = SavedProduct::create([
                    'customer_id' => $customer->id,
                    'product_id' => $request->product_id,
                    'product_code' => $request->product_code,
                    'product_name' => $request->product_name,
                    'product_price' => $request->product_price,
                    'product_image' => $request->product_image,
                    'product_sku' => $request->product_sku,
                    'product_slug' => $request->product_slug ?? $request->product_id,
                    'product_category' => $request->product_category,
                ]);

                // Calculate discount information for saved product
                $discountInfo = $this->calculateDiscountPrice($savedProduct->product_price);

                return response()->json([
                    'success' => true,
                    'message' => 'Product saved successfully',
                    'is_saved' => true,
                    'data' => [
                        'id' => $savedProduct->id,
                        'product_id' => $savedProduct->product_id,
                        'product_image_url' => $savedProduct->product_image_url,
                        'old_price' => $discountInfo['old_price'],
                        'discount_percentage' => $discountInfo['discount_percentage'],
                        'discount_price' => $discountInfo['discount_price'],
                        'product_category' => $savedProduct->product_category,
                        'saved_at' => $savedProduct->created_at,
                    ]
                ], 201);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle saved product',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
