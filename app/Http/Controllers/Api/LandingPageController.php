<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\Product;
use App\Models\Category;
use App\Models\Deal;
use App\Models\Offer;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class LandingPageController extends Controller
{
    use ApiResponseTrait;

    /**
     * Cache duration in minutes
     */
    private const CACHE_DURATION = 15; // 15 minutes

    /**
     * Get all landing page data
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Use cache to store the entire landing page data
            $cacheKey = 'landing_page_data';

            $cachedData = Cache::remember($cacheKey, now()->addMinutes(self::CACHE_DURATION), function () {
                // Get site settings
                $settings = SiteSetting::getInstance();

                // Get featured categories (limit to 8) - optimized with eager loading
                $featuredCategories = Category::active()
                    ->select('id', 'name', 'description', 'slug')
                    ->featured()
                    // ->with(['children' => function ($query) {
                    //     $query->active()->orderBy('sort_order')->limit(5);
                    // }])
                    ->withCount([
                        'products as active_products_count' => function ($query) {
                            $query->where('is_active', true);
                        }
                    ])
                    ->orderBy('sort_order')
                    ->limit(8)
                    ->get();

                // Get latest products (limit to 12) - optimized with eager loading and select specific columns
                $latestProducts = Product::select([
                    'id',
                    'name',
                    'slug',
                    'description',
                    'price',
                    'stock_quantity',
                    'sku',
                    'image_url',
                    'category_id',
                    'created_at'
                ])
                    ->with([
                        'category:id,name,slug',
                        'media:id,product_id,url,type,is_thumbnail,sort_order'
                    ])
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
                            'media' => $product->media->take(1)->map(function ($media) {
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

                // Get featured deals (limit to 6) - optimized query
                $featuredDeals = Deal::select([
                    'id',
                    'title',
                    'slug',
                    'short_description',
                    'description',
                    'type',
                    'discount_type',
                    'discount_value',
                    'original_price',
                    'deal_price',
                    'image_url',
                    'banner_image_url',
                    'start_date',
                    'end_date'
                ])
                    ->valid()
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

                // Get flash deals (limit to 4) - optimized query
                $flashDeals = Deal::select([
                    'id',
                    'title',
                    'slug',
                    'short_description',
                    'discount_type',
                    'discount_value',
                    'original_price',
                    'deal_price',
                    'image_url',
                    'banner_image_url',
                    'end_date'
                ])
                    ->valid()
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

                // Get top selling products (based on order items, limit to 8) - optimized with subquery
                $topSellingProducts = Product::select([
                    'products.id',
                    'products.name',
                    'products.slug',
                    'products.description',
                    'products.price',
                    'products.stock_quantity',
                    'products.sku',
                    'products.image_url',
                    'products.category_id',
                    DB::raw('COALESCE((
                        SELECT SUM(quantity) 
                        FROM order_items 
                        WHERE order_items.product_id = products.id
                    ), 0) as total_sold')
                ])
                    ->with([
                        'category:id,name,slug',
                        'media:id,product_id,url,type,is_thumbnail,sort_order'
                    ])
                    ->where('products.is_active', true)
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
                            'total_sold' => (int) $product->total_sold,
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

                return [
                    'hero_section' => $heroSection,
                    'site_info' => $siteInfo,
                    'featured_categories' => $featuredCategories,
                    'latest_products' => $latestProducts,
                    'top_selling_products' => $topSellingProducts,
                    'featured_deals' => $featuredDeals,
                    'flash_deals' => $flashDeals,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Landing page data retrieved successfully',
                'data' => $cachedData
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
            $cacheKey = 'landing_page_hero';

            $data = Cache::remember($cacheKey, now()->addMinutes(self::CACHE_DURATION), function () {
                $settings = SiteSetting::getInstance();

                return [
                    'title' => $settings->title,
                    'tagline' => $settings->tagline,
                    'description' => $settings->description,
                    'slider_images' => $settings->slider_images_urls ?? [],
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
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
            $limit = (int) $request->get('limit', 12);
            $cacheKey = "landing_page_featured_products_{$limit}";

            $products = Cache::remember($cacheKey, now()->addMinutes(self::CACHE_DURATION), function () use ($limit) {
                return Product::select([
                    'id',
                    'name',
                    'slug',
                    'description',
                    'price',
                    'stock_quantity',
                    'sku',
                    'image_url',
                    'category_id',
                    'created_at'
                ])
                    ->with([
                        'category:id,name,slug',
                        'media:id,product_id,url,type,is_thumbnail,sort_order'
                    ])
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
            $limit = (int) $request->get('limit', 8);
            $cacheKey = "landing_page_top_selling_products_{$limit}";

            $products = Cache::remember($cacheKey, now()->addMinutes(self::CACHE_DURATION), function () use ($limit) {
                return Product::select([
                    'products.id',
                    'products.name',
                    'products.slug',
                    'products.description',
                    'products.price',
                    'products.stock_quantity',
                    'products.sku',
                    'products.image_url',
                    'products.category_id',
                    DB::raw('COALESCE((
                        SELECT SUM(quantity) 
                        FROM order_items 
                        WHERE order_items.product_id = products.id
                    ), 0) as total_sold')
                ])
                    ->with([
                        'category:id,name,slug',
                        'media:id,product_id,url,type,is_thumbnail,sort_order'
                    ])
                    ->where('products.is_active', true)
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
                            'total_sold' => (int) $product->total_sold,
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

    public function combinedData(Request $request): JsonResponse
    {
        try {
            $cacheKey = 'landing_page_combined_data';

            $data = Cache::remember($cacheKey, now()->addMinutes(self::CACHE_DURATION), function () {
                // 1. Site Settings Public + Featured Categories (from site-settings/public)
                $settings = SiteSetting::getInstance();

                $featuredCategoriesSetting = Category::active()
                    ->featured()
                    ->select('id', 'name', 'description', 'slug', 'image_url', 'icon', 'sort_order')
                    ->withCount([
                        'products as active_products_count' => function ($query) {
                            $query->where('is_active', true);
                        }
                    ])
                    ->orderBy('sort_order')
                    ->limit(12)
                    ->get()
                    ->map(function ($category) {
                        return [
                            'id' => $category->id,
                            'name' => $category->name,
                            'description' => $category->description,
                            'slug' => $category->slug,
                            'image_url' => $category->full_image_url,
                            'icon' => $category->icon,
                            'sort_order' => $category->sort_order,
                            'active_products_count' => $category->active_products_count,
                        ];
                    });

                $siteSettings = [
                    'title' => $settings->title,
                    'tagline' => $settings->tagline,
                    'description' => $settings->description,
                    'contact_number' => $settings->contact_number,
                    'email' => $settings->email,
                    'address' => $settings->address,
                    'secondary_address' => $settings->secondary_address,
                    'map_url' => $settings->map_url,
                    'business_name' => $settings->business_name,
                    'header_logo' => $settings->header_logo_url,
                    'footer_logo' => $settings->footer_logo_url,
                    'favicon' => $settings->favicon_url,
                    'slider_images' => $settings->slider_images_urls,
                    'offer' => $settings->offer_with_url,
                    'promotional_items' => $settings->promotional_items_with_urls,
                    'social_links' => $settings->social_links_with_defaults,
                    'meta_title' => $settings->meta_title,
                    'meta_description' => $settings->meta_description,
                    'meta_keywords' => $settings->meta_keywords,
                    'currency' => $settings->currency,
                    'currency_symbol' => $settings->currency_symbol,
                    'currency_rate' => $settings->currency_rate,
                    'currency_position' => $settings->currency_position,
                    'formatted_currency' => $settings->formatted_currency,
                    'min_product_quantity' => $settings->min_product_quantity,
                    'min_item_number_discount' => $settings->min_item_number_discount,
                    'discount_percentage_on_item' => $settings->discount_percentage_on_item,
                    'min_order_amount' => $settings->min_order_amount,
                    'shipping_cost' => $settings->shipping_cost,
                    'shipping_cost_by_ship' => $settings->shipping_cost_by_ship,
                    'shipping_duration_by_ship' => $settings->shipping_duration_by_ship,
                    'shipping_cost_by_air' => $settings->shipping_cost_by_air,
                    'shipping_duration_by_air' => $settings->shipping_duration_by_air,
                    'free_shipping_threshold' => $settings->free_shipping_threshold,
                    'tax_rate' => $settings->tax_rate,
                    'tax_inclusive' => $settings->tax_inclusive,
                    'store_enabled' => $settings->store_enabled,
                    'store_mode' => $settings->store_mode,
                    'maintenance_message' => $settings->maintenance_message,
                    'google_analytics_id' => $settings->google_analytics_id,
                    'facebook_pixel_id' => $settings->facebook_pixel_id,
                    'price_margin' => $settings->price_margin,
                    'featured_categories' => $featuredCategoriesSetting,
                ];

                // 2. Categories list (based on customerIndex method logic)
                $categories = Category::active()
                    ->select('id', 'name', 'icon', 'slug', 'image_url', 'meta_title', 'meta_description', 'meta_keywords', 'parent_id')
                    ->parent()
                    ->orderBy('name', 'asc')
                    ->with([
                        'children' => function ($query) {
                            $query->active()
                                ->select('id', 'name', 'icon', 'slug', 'image_url', 'meta_title', 'meta_description', 'meta_keywords', 'parent_id')
                                ->orderBy('sort_order');
                        }
                    ])
                    ->get()
                    ->map(function ($category) {
                        $data = [
                            'id' => $category->id,
                            'name' => $category->name,
                            'slug' => $category->slug,
                            'full_image_url' => $category->icon ? url($category->icon) : ($category->image_url ? url($category->image_url) : null),
                            'meta_title' => $category->meta_title,
                            'meta_description' => $category->meta_description,
                            'meta_keywords' => $category->meta_keywords,
                        ];

                        if ($category->children && $category->children->count() > 0) {
                            $data['children'] = $category->children->map(function ($child) {
                                return [
                                    'id' => $child->id,
                                    'name' => $child->name,
                                    'slug' => $child->slug,
                                    'full_image_url' => $child->icon ? url($child->icon) : ($child->image_url ? url($child->image_url) : null),
                                    'meta_title' => $child->meta_title,
                                    'meta_description' => $child->meta_description,
                                    'meta_keywords' => $child->meta_keywords,
                                ];
                            });
                        }

                        return $data;
                    });

                // 3. Featured Categories list
                $featuredCategories = Category::active()
                    ->featured()
                    ->with([
                        'children' => function ($query) {
                            $query->active()->orderBy('sort_order');
                        }
                    ])
                    ->withCount([
                        'products as active_products_count' => function ($query) {
                            $query->where('is_active', true);
                        }
                    ])
                    ->orderBy('sort_order')
                    ->get()
                    ->map(function ($category) {
                        $data = [
                            'id' => $category->id,
                            'name' => $category->name,
                            'slug' => $category->slug,
                            'full_image_url' => $category->icon ? url($category->icon) : ($category->image_url ? url($category->image_url) : null),
                            'active_products_count' => $category->active_products_count,
                        ];

                        if ($category->children && $category->children->count() > 0) {
                            $data['children'] = $category->children->map(function ($child) {
                                return [
                                    'id' => $child->id,
                                    'name' => $child->name,
                                    'slug' => $child->slug,
                                    'full_image_url' => $child->icon ? url($child->icon) : ($child->image_url ? url($child->image_url) : null),
                                ];
                            });
                        }

                        return $data;
                    });

                // 4. Featured Offers
                $featuredOffers = Offer::active()
                    ->available()
                    ->featured()
                    ->ordered()
                    ->get()
                    ->map(function ($offer) {
                        return [
                            'id' => $offer->id,
                            'title' => $offer->title,
                            'image' => $offer->image_url,
                            'url' => $offer->url,
                            'description' => $offer->description,
                            'time_remaining' => $offer->time_remaining,
                            'is_available' => $offer->is_available,
                        ];
                    });

                // 5. Non-featured Offers
                $nonFeaturedOffers = Offer::active()
                    ->available()
                    ->where('is_featured', false)
                    ->ordered()
                    ->get()
                    ->map(function ($offer) {
                        return [
                            'id' => $offer->id,
                            'title' => $offer->title,
                            'image' => $offer->image_url,
                            'url' => $offer->url,
                            'description' => $offer->description,
                            'time_remaining' => $offer->time_remaining,
                            'is_available' => $offer->is_available,
                        ];
                    });

                return [
                    'site_settings' => $siteSettings,
                    'categories' => $categories,
                    'featured_categories' => $featuredCategories,
                    'featured_offers' => $featuredOffers,
                    'non_featured_offers' => $nonFeaturedOffers,
                ];
            });

            return $this->successMessage($data, 'Landing page data retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorMessage('Failed to retrieve landing page data', $e->getMessage(), 500);
        }
    }
}
