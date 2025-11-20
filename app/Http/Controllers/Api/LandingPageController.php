<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\Product;
use App\Models\Category;
use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LandingPageController extends Controller
{
    /**
     * Get all landing page data
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Get site settings
            $settings = SiteSetting::getInstance();
            
            // Get featured categories (limit to 8)
            $featuredCategories = Category::active()
                ->featured()
                ->with(['children' => function ($query) {
                    $query->active()->orderBy('sort_order')->limit(5);
                }])
                ->withCount(['products as active_products_count' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->orderBy('sort_order')
                ->limit(8)
                ->get();

            // Get latest products (limit to 12)
            $latestProducts = Product::with(['category', 'media'])
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->limit(12)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'description' => $product->description,
                        'price' => $product->price,
                        'stock_quantity' => $product->stock_quantity,
                        'sku' => $product->sku,
                        'image_url' => $product->image_url,
                        'category' => $product->category ? [
                            'id' => $product->category->id,
                            'name' => $product->category->name,
                            'slug' => $product->category->slug,
                        ] : null,
                        'media' => $product->media->map(function ($media) {
                            return [
                                'id' => $media->id,
                                'url' => $media->url,
                                'type' => $media->type,
                                'is_thumbnail' => $media->is_thumbnail,
                            ];
                        }),
                        'in_stock' => $product->stock_quantity > 0,
                    ];
                });

            // Get featured deals (limit to 6)
            $featuredDeals = Deal::valid()
                ->where('is_active', true)
                ->where('is_featured', true)
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get()
                ->map(function ($deal) {
                    return [
                        'id' => $deal->id,
                        'title' => $deal->title,
                        'slug' => $deal->slug,
                        'short_description' => $deal->short_description,
                        'description' => $deal->description,
                        'type' => $deal->type,
                        'discount_type' => $deal->discount_type,
                        'discount_value' => $deal->discount_value,
                        'discount_percentage' => $deal->discount_percentage,
                        'original_price' => $deal->original_price,
                        'deal_price' => $deal->deal_price,
                        'image_url' => $deal->image_url,
                        'banner_image_url' => $deal->banner_image_url,
                        'start_date' => $deal->start_date,
                        'end_date' => $deal->end_date,
                        'time_remaining' => $deal->time_remaining,
                        'is_valid' => $deal->is_valid,
                    ];
                });

            // Get flash deals (limit to 4)
            $flashDeals = Deal::valid()
                ->where('is_active', true)
                ->where('type', 'flash')
                ->whereNotNull('end_date')
                ->where('end_date', '>', now())
                ->orderBy('end_date', 'asc')
                ->limit(4)
                ->get()
                ->map(function ($deal) {
                    return [
                        'id' => $deal->id,
                        'title' => $deal->title,
                        'slug' => $deal->slug,
                        'short_description' => $deal->short_description,
                        'discount_type' => $deal->discount_type,
                        'discount_value' => $deal->discount_value,
                        'discount_percentage' => $deal->discount_percentage,
                        'original_price' => $deal->original_price,
                        'deal_price' => $deal->deal_price,
                        'image_url' => $deal->image_url,
                        'banner_image_url' => $deal->banner_image_url,
                        'end_date' => $deal->end_date,
                        'time_remaining' => $deal->time_remaining,
                    ];
                });

            // Get top selling products (based on order items, limit to 8)
            $topSellingProducts = Product::with(['category', 'media'])
                ->where('is_active', true)
                ->withCount(['orderItems as total_sold' => function ($query) {
                    $query->select(DB::raw('COALESCE(SUM(quantity), 0)'));
                }])
                ->orderBy('total_sold', 'desc')
                ->limit(8)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'description' => $product->description,
                        'price' => $product->price,
                        'stock_quantity' => $product->stock_quantity,
                        'sku' => $product->sku,
                        'image_url' => $product->image_url,
                        'total_sold' => $product->total_sold ?? 0,
                        'category' => $product->category ? [
                            'id' => $product->category->id,
                            'name' => $product->category->name,
                            'slug' => $product->category->slug,
                        ] : null,
                        'media' => $product->media->map(function ($media) {
                            return [
                                'id' => $media->id,
                                'url' => $media->url,
                                'type' => $media->type,
                                'is_thumbnail' => $media->is_thumbnail,
                            ];
                        }),
                        'in_stock' => $product->stock_quantity > 0,
                    ];
                });

            // Get hero section data from site settings
            $heroSection = [
                'title' => $settings->title,
                'tagline' => $settings->tagline,
                'description' => $settings->description,
                'slider_images' => $settings->slider_images_urls ?? [],
            ];

            // Get site information
            $siteInfo = [
                'store_enabled' => $settings->store_enabled,
                'store_mode' => $settings->store_mode,
                'currency' => $settings->currency,
                'currency_symbol' => $settings->currency_symbol,
                'formatted_currency' => $settings->formatted_currency,
                'free_shipping_threshold' => $settings->free_shipping_threshold,
                'shipping_cost' => $settings->shipping_cost,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Landing page data retrieved successfully',
                'data' => [
                    'hero_section' => $heroSection,
                    'site_info' => $siteInfo,
                    'featured_categories' => $featuredCategories,
                    'latest_products' => $latestProducts,
                    'top_selling_products' => $topSellingProducts,
                    'featured_deals' => $featuredDeals,
                    'flash_deals' => $flashDeals,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve landing page data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get hero section data only
     * 
     * @return JsonResponse
     */
    public function hero(): JsonResponse
    {
        try {
            $settings = SiteSetting::getInstance();

            return response()->json([
                'success' => true,
                'data' => [
                    'title' => $settings->title,
                    'tagline' => $settings->tagline,
                    'description' => $settings->description,
                    'slider_images' => $settings->slider_images_urls ?? [],
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve hero section data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get featured products for landing page
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function featuredProducts(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 12);

            $products = Product::with(['category', 'media'])
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'description' => $product->description,
                        'price' => $product->price,
                        'stock_quantity' => $product->stock_quantity,
                        'sku' => $product->sku,
                        'image_url' => $product->image_url,
                        'category' => $product->category ? [
                            'id' => $product->category->id,
                            'name' => $product->category->name,
                            'slug' => $product->category->slug,
                        ] : null,
                        'media' => $product->media->map(function ($media) {
                            return [
                                'id' => $media->id,
                                'url' => $media->url,
                                'type' => $media->type,
                                'is_thumbnail' => $media->is_thumbnail,
                            ];
                        }),
                        'in_stock' => $product->stock_quantity > 0,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $products
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve featured products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top selling products
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function topSellingProducts(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 8);

            $products = Product::with(['category', 'media'])
                ->where('is_active', true)
                ->withCount(['orderItems as total_sold' => function ($query) {
                    $query->select(DB::raw('COALESCE(SUM(quantity), 0)'));
                }])
                ->orderBy('total_sold', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'description' => $product->description,
                        'price' => $product->price,
                        'stock_quantity' => $product->stock_quantity,
                        'sku' => $product->sku,
                        'image_url' => $product->image_url,
                        'total_sold' => $product->total_sold ?? 0,
                        'category' => $product->category ? [
                            'id' => $product->category->id,
                            'name' => $product->category->name,
                            'slug' => $product->category->slug,
                        ] : null,
                        'media' => $product->media->map(function ($media) {
                            return [
                                'id' => $media->id,
                                'url' => $media->url,
                                'type' => $media->type,
                                'is_thumbnail' => $media->is_thumbnail,
                            ];
                        }),
                        'in_stock' => $product->stock_quantity > 0,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $products
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve top selling products',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
