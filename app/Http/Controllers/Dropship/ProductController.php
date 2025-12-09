<?php

namespace App\Http\Controllers\Dropship;

use App\Http\Controllers\Controller;
use App\Services\DropshipService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    protected DropshipService $dropshipService;

    public function __construct(DropshipService $dropshipService)
    {
        $this->dropshipService = $dropshipService;
    }

    /**
     * Get product details by ID
     * GET /api/dropship/products/{numIid}
     */
    public function show(Request $request, string $numIid): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'platform' => 'nullable|string|in:taobao,1688,tmall',
            'lang' => 'nullable|string|max:10',
            'is_promotion' => 'nullable|boolean',
            'cache' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $platform = $request->input('platform', '1688');
        $lang = $request->input('lang', 'en');  // Default to English
        $isPromotion = $request->boolean('is_promotion', true);
        $useCache = $request->boolean('cache', true);

        $result = $this->dropshipService->getProduct($platform, $numIid, $isPromotion, $lang, $useCache);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        $responseData = [
            'success' => true,
            'message' => 'Product fetched successfully',
            'data' => $result['data'],
        ];

        // Only transform if data exists
        if (!empty($result['data'])) {
            $responseData['transformed'] = $this->dropshipService->transformToLocalProduct($result['data']);
        }

        return response()->json($responseData);
    }
    /**
     * Search products by keyword
     * GET /api/dropship/products?search=keyword
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search' => 'required|string|max:255',
            // 'platform' => 'nullable|string|in:taobao,1688,tmall',
            'page' => 'nullable|integer|min:1',
            'page_size' => 'nullable|integer|min:1|max:100',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'sort' => 'nullable|string|in:price_asc,price_desc,sale_desc,credit_desc',
            'lang' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $platform = '1688'; // $request->input('platform', '1688');
        $keyword = $request->input('search');
        $page = $request->integer('page', 1);
        $pageSize = $request->integer('page_size', 20);

        $options = [
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
            'sort' => $request->input('sort'),
            'lang' => $request->input('lang', 'en'),  // Default to English
        ];

        $result = $this->dropshipService->searchProducts($platform, $keyword, $page, $pageSize, $options);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        // Transform response to match chinasource API format
        $products = $result['data']; // $this->transformProductsForListing($result['data']);

        return response()->json([
            'result' => [
                'page' => $page,
                'per_page' => $pageSize,
                'total_found' => $result['data']['total_results'] ?? count($products),
                'products' => $products,
                'keywords' => [],
                'time' => now()->toIso8601String(),
            ],
            'image' => null,
        ]);
    }

    /**
     * Get product details by item ID (Public endpoint)
     * GET /api/product-details/{itemId}
     */
    public function productDetails(Request $request, string $itemId): JsonResponse
    {
        $lang = $request->input('lang', 'en');
        $platform = '1688';

        $result = $this->dropshipService->getProduct($platform, $itemId, false, $lang, true);

        if (!$result['success']) {
            return response()->json([
                'result' => null,
                'error' => $result['message'] ?? 'Product not found',
            ], 400);
        }

        // Transform to the expected format
        $product = $this->transformProductDetails($result['data']);

        return response()->json([
            'result' => [
                'product' => $product,
                'time' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Transform API product to detail format
     */
    private function transformProductDetails(array $item): array
    {
        // Extract images
        $images = [];
        if (isset($item['main_imgs']) && is_array($item['main_imgs'])) {
            foreach ($item['main_imgs'] as $url) {
                if (is_string($url) && !empty($url)) {
                    $images[] = $this->normalizeImageUrl($url);
                }
            }
        }

        // Extract price info
        $regularPrice = 0;
        $salePrice = null;
        $priceMin = 0;
        $priceMax = 0;

        if (isset($item['price_info'])) {
            $priceInfo = $item['price_info'];
            $regularPrice = (int) round((float) ($priceInfo['price'] ?? $priceInfo['price_min'] ?? 0) * 100);
            $priceMin = (int) round((float) ($priceInfo['price_min'] ?? 0) * 100);
            $priceMax = (int) round((float) ($priceInfo['price_max'] ?? 0) * 100);
            if (isset($priceInfo['origin_price_min']) && $priceInfo['origin_price_min'] > ($priceInfo['price_min'] ?? 0)) {
                $salePrice = $regularPrice;
                $regularPrice = (int) round((float) $priceInfo['origin_price_min'] * 100);
            }
        } elseif (isset($item['sku_price_range'])) {
            $priceRange = $item['sku_price_range'];
            $priceMin = (int) round((float) ($priceRange[0] ?? 0) * 100);
            $priceMax = (int) round((float) ($priceRange[1] ?? $priceRange[0] ?? 0) * 100);
            $regularPrice = $priceMin;
        }

        // Extract sale count
        $totalSold = 0;
        if (isset($item['sale_count'])) {
            $totalSold = (int) $item['sale_count'];
        } elseif (isset($item['sale_info']['sale_count'])) {
            $totalSold = (int) $item['sale_info']['sale_count'];
        }

        // Extract SKUs/variants
        $variants = [];
        if (isset($item['skus']) && is_array($item['skus'])) {
            foreach ($item['skus'] as $sku) {
                $variants[] = [
                    'sku_id' => $sku['skuid'] ?? $sku['sku_id'] ?? '',
                    'spec_id' => $sku['specid'] ?? '',
                    'price' => (int) round((float) ($sku['sale_price'] ?? $sku['price'] ?? 0) * 100),
                    'original_price' => (int) round((float) ($sku['origin_price'] ?? $sku['sale_price'] ?? 0) * 100),
                    'stock' => (int) ($sku['stock'] ?? 0),
                    'props_names' => $sku['props_names'] ?? '',
                ];
            }
        }

        // Extract shop info
        $shopInfo = $item['shop_info'] ?? [];

        // Extract props
        $props = [];
        if (isset($item['product_props']) && is_array($item['product_props'])) {
            foreach ($item['product_props'] as $prop) {
                if (is_array($prop)) {
                    foreach ($prop as $key => $value) {
                        $props[] = ['name' => $key, 'value' => $value];
                    }
                }
            }
        }

        return [
            'id' => 'e3pro-' . ($item['item_id'] ?? ''),
            'item_id' => $item['item_id'] ?? '',
            'title' => $item['title'] ?? '',
            'description' => $item['desc'] ?? '',
            'regular_price' => $regularPrice,
            'sale_price' => $salePrice,
            'price_min' => $priceMin,
            'price_max' => $priceMax,
            'currency' => $item['currency'] ?? 'CNY',
            'stock' => (int) ($item['stock'] ?? 0),
            'is_sold_out' => $item['is_sold_out'] ?? false,
            'images' => $images,
            'thumbnail' => [
                'large' => !empty($images) ? $this->resizeImageUrl($images[0], '600x600') : '',
                'medium' => !empty($images) ? $this->resizeImageUrl($images[0], '310x310') : '',
                'small' => !empty($images) ? $this->resizeImageUrl($images[0], '100x100') : '',
            ],
            'video_url' => $item['video_url'] ?? null,
            'variants' => $variants,
            'props' => $props,
            'shop' => [
                'name' => $shopInfo['shop_name'] ?? $shopInfo['seller_nick'] ?? '',
                'seller_id' => $shopInfo['seller_id'] ?? '',
                'location' => $shopInfo['location'] ?? '',
            ],
            'meta' => [
                'total_sold' => $totalSold,
                'category_id' => $item['category_id'] ?? null,
                'product_url' => $item['product_url'] ?? '',
            ],
        ];
    }

    /**
     * Transform API products to the listing format matching chinasource API
     */
    private function transformProductsForListing(array $data): array
    {
        $products = [];
        $items = $data['items'] ?? $data['products'] ?? $data ?? [];

        foreach ($items as $item) {
            // Extract main image URL from various possible field names
            $mainImage = $this->extractMainImage($item);

            // Generate different thumbnail sizes (1688 CDN supports size parameters)
            $thumbnail = [
                'large' => $this->resizeImageUrl($mainImage, '600x600'),
                'medium' => $this->resizeImageUrl($mainImage, '310x310'),
                'small' => $this->resizeImageUrl($mainImage, '100x100'),
            ];

            // Extract price - handle both single price and price range
            $regularPrice = 0;
            if (isset($item['price'])) {
                $regularPrice = (int) round((float) $item['price'] * 100); // Convert to cents/smallest unit
            } elseif (isset($item['sale_price'])) {
                $regularPrice = (int) round((float) $item['sale_price'] * 100);
            } elseif (isset($item['price_range'])) {
                $regularPrice = (int) round((float) ($item['price_range'][0] ?? 0) * 100);
            }

            // Extract sale price if available
            $salePrice = null;
            if (isset($item['original_price']) && isset($item['sale_price']) && $item['original_price'] > $item['sale_price']) {
                $salePrice = (int) round((float) $item['sale_price'] * 100);
                $regularPrice = (int) round((float) $item['original_price'] * 100);
            }

            // Extract total sold count
            $totalSold = 0;
            if (isset($item['sale_count'])) {
                $totalSold = (int) $item['sale_count'];
            } elseif (isset($item['sold'])) {
                $totalSold = (int) $item['sold'];
            } elseif (isset($item['sales'])) {
                $totalSold = (int) $item['sales'];
            }

            $products[] = [
                'id' => 'e3pro-' . ($item['item_id'] ?? $item['num_iid'] ?? uniqid()),
                'title' => $item['title'] ?? $item['name'] ?? '',
                'regular_price' => $regularPrice,
                'sale_price' => $salePrice,
                'thumbnail' => $thumbnail,
                'meta' => [
                    'total_sold' => $totalSold,
                ],
            ];
        }

        return $products;
    }

    /**
     * Extract main image URL from item data - handles various API response formats
     */
    private function extractMainImage(array $item): string
    {
        // Try various possible field names for the image URL
        $possibleFields = [
            'pic_url',           // Common in search results
            'img_url',           // Alternative name
            'image_url',         // Alternative name
            'image',             // Simple field name
            'thumb_url',         // Thumbnail URL
            'thumbnail',         // Thumbnail
            'cover',             // Cover image
            'cover_url',         // Cover URL
            'product_image',     // Product image
        ];

        foreach ($possibleFields as $field) {
            if (isset($item[$field]) && is_string($item[$field]) && !empty($item[$field])) {
                return $this->normalizeImageUrl($item[$field]);
            }
        }

        // Try array fields (first element)
        $arrayFields = [
            'main_imgs',         // Array of main images
            'item_imgs',         // Array of item images
            'images',            // Array of images
            'pics',              // Array of pics
            'product_images',    // Array of product images
        ];

        foreach ($arrayFields as $field) {
            if (isset($item[$field]) && is_array($item[$field]) && !empty($item[$field])) {
                $firstImage = $item[$field][0];
                if (is_string($firstImage)) {
                    return $this->normalizeImageUrl($firstImage);
                } elseif (is_array($firstImage) && isset($firstImage['url'])) {
                    return $this->normalizeImageUrl($firstImage['url']);
                }
            }
        }

        return '';
    }

    /**
     * Normalize image URL - ensure it has proper protocol
     */
    private function normalizeImageUrl(string $url): string
    {
        if (empty($url)) {
            return '';
        }

        // Add https: if URL starts with //
        if (strpos($url, '//') === 0) {
            return 'https:' . $url;
        }

        return $url;
    }

    /**
     * Resize image URL for different thumbnail sizes
     */
    private function resizeImageUrl(string $url, string $size): string
    {
        if (empty($url)) {
            return '';
        }

        // If the URL already contains size info, try to replace it
        // Common pattern for 1688/taobao CDN: replace dimensions in URL
        if (strpos($url, '.jpg') !== false || strpos($url, '.png') !== false || strpos($url, '.webp') !== false) {
            // For 1688 CDN URLs, append the size suffix if not present
            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (!empty($extension)) {
                $urlWithoutExt = preg_replace('/\.' . $extension . '.*$/', '', $url);
                return $urlWithoutExt . '.' . $size . '.' . $extension;
            }
        }

        return $url;
    }
    /**
     * Search products by keyword
     * GET /api/dropship/products/search
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|max:255',
            'platform' => 'nullable|string|in:taobao,1688,tmall',
            'page' => 'nullable|integer|min:1',
            'page_size' => 'nullable|integer|min:1|max:100',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'sort' => 'nullable|string|in:price_asc,price_desc,sale_desc,credit_desc',
            'lang' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $platform = $request->input('platform', '1688');
        $keyword = $request->input('q');
        $page = $request->integer('page', 1);
        $pageSize = $request->integer('page_size', 20);

        $options = [
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
            'sort' => $request->input('sort'),
            'lang' => $request->input('lang', 'en'),  // Default to English
        ];

        $result = $this->dropshipService->searchProducts($platform, $keyword, $page, $pageSize, $options);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Products searched successfully',
            'data' => $result['data'],
        ]);
    }

    /**
     * Search products by image
     * POST /api/dropship/products/search-by-image
     */
    public function searchByImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image_url' => 'required|url',
            'platform' => 'nullable|string|in:taobao,1688,tmall',
            'page' => 'nullable|integer|min:1',
            'page_size' => 'nullable|integer|min:1|max:20',
            'lang' => 'nullable|string|in:en,zh',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $platform = $request->input('platform', '1688');
        $imageUrl = $request->input('image_url');
        $page = $request->integer('page', 1);
        $pageSize = $request->integer('page_size', 20);
        $lang = $request->input('lang', 'en');

        $result = $this->dropshipService->searchByImage($platform, $imageUrl, $page, $pageSize, $lang);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Image search completed successfully',
            'data' => $result['data'],
        ]);
    }

    /**
     * Get product description/images
     * GET /api/dropship/products/{numIid}/description
     */
    public function description(Request $request, string $numIid): JsonResponse
    {
        $platform = $request->input('platform', 'taobao');

        $result = $this->dropshipService->getProductDescription($platform, $numIid);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product description fetched successfully',
            'data' => $result['data'],
        ]);
    }

    /**
     * Get product reviews
     * GET /api/dropship/products/{numIid}/reviews
     */
    public function reviews(Request $request, string $numIid): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'platform' => 'nullable|string|in:taobao,1688,tmall',
            'page' => 'nullable|integer|min:1',
            'page_size' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $platform = $request->input('platform', 'taobao');
        $page = $request->integer('page', 1);
        $pageSize = $request->integer('page_size', 20);

        $result = $this->dropshipService->getProductReviews($platform, $numIid, $page, $pageSize);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product reviews fetched successfully',
            'data' => $result['data'],
        ]);
    }

    /**
     * Get shipping fee for a product
     * GET /api/dropship/products/{numIid}/shipping
     */
    public function shipping(Request $request, string $numIid): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'platform' => 'nullable|string|in:taobao,1688,tmall',
            'area_id' => 'required|string',
            'quantity' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $platform = $request->input('platform', 'taobao');
        $areaId = $request->input('area_id');
        $quantity = $request->integer('quantity', 1);

        $result = $this->dropshipService->getShippingFee($platform, $numIid, $areaId, $quantity);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Shipping fee fetched successfully',
            'data' => $result['data'],
        ]);
    }

    /**
     * Import product to local store
     * POST /api/dropship/products/import
     *
     * Accepts either:
     * 1. num_iid/source_id to fetch from API and import
     * 2. Full product data (name, description, price, images, variants) to import directly
     */
    public function import(Request $request): JsonResponse
    {
        // Check if full product data is provided or just source_id/num_iid
        $hasFullData = $request->has('name') && $request->has('price');

        if ($hasFullData) {
            // Validate full product data import
            $validator = Validator::make($request->all(), [
                'source_id' => 'required|string',
                'platform' => 'nullable|string|in:taobao,1688,tmall',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'cost_price' => 'nullable|numeric|min:0',
                'category_id' => 'required|exists:categories,id',
                'images' => 'nullable|array',
                'images.*' => 'url',
                'variants' => 'nullable|array',
                'variants.*.sku_id' => 'required|string',
                'variants.*.price' => 'required|numeric|min:0',
                'variants.*.cost' => 'nullable|numeric|min:0',
                'is_active' => 'nullable|boolean',
            ]);
        } else {
            // Validate source ID import (fetch from API)
            $validator = Validator::make($request->all(), [
                'num_iid' => 'required_without:source_id|string',
                'source_id' => 'required_without:num_iid|string',
                'platform' => 'nullable|string|in:taobao,1688,tmall',
                'category_id' => 'required|exists:categories,id',
                'markup_percentage' => 'nullable|numeric|min:0|max:500',
                'is_active' => 'nullable|boolean',
            ]);
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $platform = $request->input('platform', '1688');
        $numIid = $request->input('num_iid') ?? $request->input('source_id');

        if ($hasFullData) {
            // Use provided product data directly
            $productData = [
                'name' => $request->input('name'),
                'description' => $request->input('description', ''),
                'price' => $request->input('price'),
                'cost_price' => $request->input('cost_price', 0),
                'stock_quantity' => 100,  // Default stock
                'images' => $request->input('images', []),
                'brand' => null,
                'weight' => null,
                'sku' => 'DS-' . $platform . '-' . $numIid,
                'source_item_id' => $numIid,
                'source_platform' => $platform,
                'variants' => $request->input('variants', []),
            ];
        } else {
            // Fetch product from dropship API
            $result = $this->dropshipService->getProduct($platform, $numIid);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch product from source',
                    'error' => $result['message'],
                ], 400);
            }

            // Transform to local format
            $productData = $this->dropshipService->transformToLocalProduct($result['data']);

            // Apply markup
            $markupPercentage = $request->input('markup_percentage', 30);
            $productData['price'] = $productData['price'] * (1 + $markupPercentage / 100);
            $productData['sku'] = 'DS-' . $platform . '-' . $numIid;
        }

        // Set category and status
        $productData['category_id'] = $request->input('category_id');
        $productData['is_active'] = $request->boolean('is_active', true);

        // Generate SKU
        $sku = $productData['sku'] ?? 'DS-' . $platform . '-' . $numIid;

        // Check if product already exists
        $existingProduct = \App\Models\Product::where('sku', $sku)->first();
        if ($existingProduct) {
            return response()->json([
                'success' => false,
                'message' => 'Product already imported',
                'error' => 'A product with SKU "' . $sku . '" already exists',
                'existing_product' => [
                    'id' => $existingProduct->id,
                    'name' => $existingProduct->name,
                    'sku' => $existingProduct->sku,
                    'price' => $existingProduct->price,
                ],
            ], 409);  // 409 Conflict
        }

        // Create product using existing Product model
        try {
            // Build tags to store dropship source info (for order sourcing later)
            $dropshipTags = json_encode([
                'dropship' => true,
                'source_platform' => $platform,
                'source_id' => $numIid,
                'cost_price' => $productData['cost_price'] ?? 0,
                'variants' => $productData['variants'] ?? [],
            ]);

            $product = \App\Models\Product::create([
                'name' => $productData['name'],
                'slug' => \Illuminate\Support\Str::slug($productData['name']) . '-' . $numIid,
                'description' => $productData['description'] ?? '',
                'price' => round($productData['price'], 2),
                'stock_quantity' => $productData['stock_quantity'] ?? 100,
                'sku' => $sku,
                'brand' => $productData['brand'] ?? null,
                'weight' => $productData['weight'] ?? null,
                'category_id' => $productData['category_id'],
                'is_active' => $productData['is_active'],
                'meta_title' => $productData['name'],
                'meta_description' => substr($productData['description'] ?? '', 0, 160),
                'tags' => $dropshipTags,
            ]);

            // Add images as media
            if (!empty($productData['images'])) {
                foreach ($productData['images'] as $index => $imageUrl) {
                    \App\Models\ProductMedia::create([
                        'product_id' => $product->id,
                        'type' => 'image',
                        'url' => $imageUrl,
                        'alt_text' => $productData['name'],
                        'is_thumbnail' => $index === 0,
                        'sort_order' => $index + 1,
                    ]);
                }
            }

            // Add video if exists
            if (!empty($productData['video'])) {
                \App\Models\ProductMedia::create([
                    'product_id' => $product->id,
                    'type' => 'video',
                    'url' => $productData['video'],
                    'alt_text' => $productData['name'] . ' video',
                    'sort_order' => 999,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product imported successfully',
                'data' => $product->load(['category', 'media']),
                'dropship_info' => [
                    'source_platform' => $platform,
                    'source_id' => $numIid,
                    'cost_price' => $productData['cost_price'] ?? 0,
                    'variants_count' => count($productData['variants'] ?? []),
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

