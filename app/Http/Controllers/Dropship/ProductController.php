<?php

namespace App\Http\Controllers\Dropship;

use App\Http\Controllers\Controller;
use App\Services\DropshipService;
use App\Models\SiteSetting;
use App\Helpers\ChineseTranslationHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class ProductController extends Controller
{
    protected DropshipService $dropshipService;

    public function __construct(DropshipService $dropshipService)
    {
        $this->dropshipService = $dropshipService;
    }

    /**
     * Convert CNY price to site currency with margin
     */
    private function convertPrice(float $cnyPrice): float
    {
        $settings = SiteSetting::getInstance();
        $currencyRate = $settings->currency_rate ?? 1;
        $priceMargin = $settings->price_margin ?? 0;

        // Convert CNY to target currency
        $convertedPrice = $cnyPrice * $currencyRate;

        // Apply price margin (percentage)
        $finalPrice = $convertedPrice * (1 + ($priceMargin / 100));

        // Round up to the nearest whole dollar
        return ceil($finalPrice);
    }

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
     */
    private function calculateDiscountPrice(float $originalPrice): array
    {
        $offer = $this->getCurrentOffer();
        
        // Default result with no discount
        $result = [
            'discount_percentage' => 0,
            'discount_price' => $originalPrice,
        ];
        
        // If no active offer, return default (no discount)
        if (!$offer) {
            return $result;
        }
        
        // Calculate discount
        $discountPercentage = (float) $offer['amount'];
        $discountPrice = $originalPrice * (1 - ($discountPercentage / 100));
        
        // Round down to the nearest whole dollar for discount price
        return [
            'discount_percentage' => $discountPercentage,
            'discount_price' => floor($discountPrice),
        ];
    }

    /**
     * Get site currency code
     */
    private function getSiteCurrency(): string
    {
        $settings = SiteSetting::getInstance();
        return $settings->currency ?? 'USD';
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
        $products = $result['data'];

        // Convert prices and currency for all items
        $products = $this->convertProductListPrices($products);

        return response()->json([
            'result' => [
                'page' => $page,
                'per_page' => $pageSize,
                'products' => $products,
                'keywords' => [],
                'time' => now()->toIso8601String(),
                '_debug_discount_implementation' => 'v2.0',
            ],
            'image' => null,
        ]);
    }

    /**
     * Search products by uploading an image (Public endpoint)
     * POST /api/product-search-by-image
     *
     * Note: Accepts both image file upload and image_url parameter
     */
    
    public function searchProductsByImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'page' => 'nullable|integer|min:1',
            'page_size' => 'nullable|integer|min:1|max:100',
            'sort' => 'nullable|string|in:default,sales,price_up,price_down',
            'price_start' => 'nullable|numeric|min:0',
            'price_end' => 'nullable|numeric|min:0',
            'support_dropshipping' => 'nullable|boolean',
            'is_factory' => 'nullable|boolean',
            'verified_supplier' => 'nullable|boolean',
            'free_shipping' => 'nullable|boolean',
            'new_arrival' => 'nullable|boolean',
            'lang' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            $this->logImageSearchFailure([
                'type' => 'validation_error',
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->except(['image']), // Exclude image file from log
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $uploadedImagePath = null;
            $absoluteFilePath = null;
            $searchImageUrl = null;

            if ($request->hasFile('image')) {
                $imageFile = $request->file('image');

                $filename = 'search_' . time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();

                // Save to storage/app/public/search-images
                $uploadedImagePath = $imageFile->storeAs(
                    'search-images',
                    $filename,
                    'public'
                );

                $absoluteFilePath = Storage::disk('public')->path($uploadedImagePath);

                $searchImageUrl =  Storage::disk('public')->url($uploadedImagePath);

            }
            // return response()->json([
            //     'success' => true,
            //     'message' => 'Checking url ',
            //     'data' => $searchImageUrl,
            // ], 200);

            $page = $request->integer('page', 1);
            $pageSize = $request->integer('page_size', 20);
            $lang = $request->input('lang', 'en');

            // Convert image URL for search - REQUIRED, local URLs won't work without conversion
            $conversionResult = $this->dropshipService->convertImageUrlForSearch(
                $searchImageUrl,
                '/global/search/image/v2'
            );
            
            // return $conversionResult['data'];
            // Check if conversion was successful
            if (empty($conversionResult['data']['image_url'])) {
                $this->logImageSearchFailure([
                    'type' => 'image_conversion_failed',
                    'original_url' => $searchImageUrl,
                    'conversion_result' => $conversionResult,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to convert image URL for search. Local image URLs must be converted before use.',
                    'error' => $conversionResult['message'] ?? 'Image conversion failed',
                ], 400);
            }
            
            // Use the converted URL (required for API to work)
            $convertedImageUrl = $conversionResult['data']['image_url'];

            $result = $this->dropshipService->searchByImage(
                '1688',
                $convertedImageUrl,
                $page,
                $pageSize,
                $lang,
                []
            );

            Log::info('Search by image result', [
                'success' => $result['success'] ?? false,
                'data_keys' => isset($result['data']) ? array_keys($result['data']) : [],
                'total_count' => $result['data']['total_count'] ?? 0,
                'items_count' => isset($result['data']['items']) ? count($result['data']['items']) : 0,
            ]);

            if (!$result['success']) {
                $this->logImageSearchFailure([
                    'type' => 'search_api_error',
                    'error_message' => $result['message'] ?? 'Unknown error',
                    'error_data' => $result,
                    'image_url' => $convertedImageUrl ?? null,
                    'page' => $page,
                    'page_size' => $pageSize,
                    'lang' => $lang,
                ]);
                
                return response()->json($result, 400);
            }

            // Transform response to match searchProducts API format
            $products = $result['data'];

            // Convert prices and currency for all items
            $products = $this->convertProductListPrices($products);

            return response()->json([
                'result' => [
                    'page' => $page,
                    'per_page' => $pageSize,
                    'products' => $products,
                    'keywords' => [],
                    'time' => now()->toIso8601String(),
                    '_debug_discount_implementation' => 'v2.0',
                ],
                'image' => $convertedImageUrl,
            ]);

        } catch (\Throwable $e) {
            Log::error('Image upload error', [
                'error' => $e->getMessage(),
            ]);

            $this->logImageSearchFailure([
                'type' => 'exception',
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    

    /**
     * Convert prices and currency for product list
     */
    private function convertProductListPrices(array $productsData): array
    {
        $siteCurrency = $this->getSiteCurrency();

        // Add debug information
        $productsData['_debug_structure'] = array_keys($productsData);

        // Handle nested structure
        if (isset($productsData['items']) && is_array($productsData['items'])) {
            $productsData['_debug_found'] = 'items';
            foreach ($productsData['items'] as &$item) {
                $item = $this->convertProductItemPrices($item, $siteCurrency);
            }
        } elseif (isset($productsData['products']) && is_array($productsData['products'])) {
            $productsData['_debug_found'] = 'products';
            foreach ($productsData['products'] as &$item) {
                $item = $this->convertProductItemPrices($item, $siteCurrency);
            }
        } else {
            $productsData['_debug_found'] = 'none';
        }

        return $productsData;
    }

    /**
     * Convert prices for a single product item
     */
    private function convertProductItemPrices(array $item, string $siteCurrency): array
    {
        // Add debug flag to verify method is being called
        $item['_debug_discount_processed'] = true;
        
        // Convert main price
        if (isset($item['price'])) {
            $originalPrice = $this->convertPrice((float) $item['price']);
            $discountInfo = $this->calculateDiscountPrice($originalPrice);
            
            $item['price'] = number_format($originalPrice, 2, '.', '');
            $item['discount_percentage'] = $discountInfo['discount_percentage'];
            $item['discount_price'] = number_format($discountInfo['discount_price'], 2, '.', '');
        }

        // Convert price_info if exists
        if (isset($item['price_info'])) {
            if (isset($item['price_info']['price'])) {
                $originalPrice = $this->convertPrice((float) $item['price_info']['price']);
                $discountInfo = $this->calculateDiscountPrice($originalPrice);
                
                $item['price_info']['price'] = number_format($originalPrice, 2, '.', '');
                $item['price_info']['discount_percentage'] = $discountInfo['discount_percentage'];
                $item['price_info']['discount_price'] = number_format($discountInfo['discount_price'], 2, '.', '');
            }
            if (isset($item['price_info']['price_min'])) {
                $originalPrice = $this->convertPrice((float) $item['price_info']['price_min']);
                $discountInfo = $this->calculateDiscountPrice($originalPrice);
                
                $item['price_info']['price_min'] = number_format($originalPrice, 2, '.', '');
                $item['price_info']['price_min_discount'] = number_format($discountInfo['discount_price'], 2, '.', '');
            }
            if (isset($item['price_info']['price_max'])) {
                $originalPrice = $this->convertPrice((float) $item['price_info']['price_max']);
                $discountInfo = $this->calculateDiscountPrice($originalPrice);
                
                $item['price_info']['price_max'] = number_format($originalPrice, 2, '.', '');
                $item['price_info']['price_max_discount'] = number_format($discountInfo['discount_price'], 2, '.', '');
            }
        }

        // Update currency
        $item['currency'] = $siteCurrency;

        return $item;
    }

    /**
     * Get product details by item ID (Public endpoint)
     * GET /api/product-details/{itemId}
     */
    public function productDetails(Request $request, string $itemId)
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

        // Transform to the expected format and include all original data
        $product = $this->transformProductDetails($result['data']);

        // Check if product is saved (if user is authenticated)
        $isSaved = false;
        $savedProductId = null;
        
        // Check authentication using Sanctum (token can be passed as Bearer token or via cookie)
        if (auth('sanctum')->check()) {
            $customer = auth('sanctum')->user();
            // Product ID format in saved_products table is "e3pro-{item_id}"
            $productId = $product['id'] ?? 'e3pro-' . $itemId;
            
            // Also try checking with just item_id in case it was saved with different format
            $savedProduct = \App\Models\SavedProduct::where('customer_id', $customer->id)
                ->where(function($query) use ($productId, $itemId) {
                    $query->where('product_id', $productId)
                          ->orWhere('product_id', 'e3pro-' . $itemId)
                          ->orWhere('product_slug', $itemId)
                          ->orWhere('product_slug', $productId);
                })
                ->first();
            
            if ($savedProduct) {
                $isSaved = true;
                $savedProductId = $savedProduct->id;
            }
        }

        // Add saved status to product data
        $product['is_saved'] = $isSaved;
        $product['saved_product_id'] = $savedProductId;

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
        $siteCurrency = $this->getSiteCurrency();

        // Extract images
        $images = [];
        if (isset($item['main_imgs']) && is_array($item['main_imgs'])) {
            foreach ($item['main_imgs'] as $url) {
                if (is_string($url) && !empty($url)) {
                    $images[] = $this->normalizeImageUrl($url);
                }
            }
        }

        // Extract price info (in CNY decimal format)
        $regularPrice = 0.0;
        $salePrice = null;
        $priceMin = 0.0;
        $priceMax = 0.0;

        if (isset($item['price_info'])) {
            $priceInfo = $item['price_info'];
            // Extract prices as floats (API returns decimal prices, not cents)
            $regularPrice = (float) ($priceInfo['price'] ?? $priceInfo['price_min'] ?? 0);
            $priceMin = (float) ($priceInfo['price_min'] ?? 0);
            $priceMax = (float) ($priceInfo['price_max'] ?? 0);
            if (isset($priceInfo['origin_price_min']) && $priceInfo['origin_price_min'] > ($priceInfo['price_min'] ?? 0)) {
                $salePrice = $regularPrice;
                $regularPrice = (float) $priceInfo['origin_price_min'];
            }
        } elseif (isset($item['sku_price_range'])) {
            $priceRange = $item['sku_price_range'];
            $priceMin = (float) ($priceRange[0] ?? 0);
            $priceMax = (float) ($priceRange[1] ?? $priceRange[0] ?? 0);
            $regularPrice = $priceMin;
        }

        // Convert prices from CNY to site currency (ceil is applied in convertPrice)
        $regularPriceFloat = $this->convertPrice($regularPrice);
        $salePriceFloat = $salePrice ? $this->convertPrice($salePrice) : null;
        $priceMinFloat = $this->convertPrice($priceMin);
        $priceMaxFloat = $this->convertPrice($priceMax);

        // Calculate discount prices
        $regularDiscountInfo = $this->calculateDiscountPrice($regularPriceFloat);
        $priceMinDiscountInfo = $this->calculateDiscountPrice($priceMinFloat);
        $priceMaxDiscountInfo = $this->calculateDiscountPrice($priceMaxFloat);

        // Format prices as decimal strings with 2 decimal places (already using ceil in convertPrice and calculateDiscountPrice)
        $regularPrice = number_format($regularPriceFloat, 2, '.', '');
        $salePrice = $salePriceFloat ? number_format($salePriceFloat, 2, '.', '') : null;
        $priceMin = number_format($priceMinFloat, 2, '.', '');
        $priceMax = number_format($priceMaxFloat, 2, '.', '');

        // Discount prices formatted with 2 decimal places
        $regularDiscountPrice = number_format($regularDiscountInfo['discount_price'], 2, '.', '');
        $priceMinDiscountPrice = number_format($priceMinDiscountInfo['discount_price'], 2, '.', '');
        $priceMaxDiscountPrice = number_format($priceMaxDiscountInfo['discount_price'], 2, '.', '');

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
                $skuPrice = (float) ($sku['sale_price'] ?? $sku['price'] ?? 0);
                $skuOriginalPrice = (float) ($sku['origin_price'] ?? $sku['sale_price'] ?? 0);

                // Convert variant prices
                $convertedPriceFloat = $this->convertPrice($skuPrice);
                $convertedOriginalPriceFloat = $this->convertPrice($skuOriginalPrice);
                
                // Calculate discount for variant
                $variantDiscountInfo = $this->calculateDiscountPrice($convertedPriceFloat);
                
                // Format prices as decimal strings with 2 decimal places (already using ceil in convertPrice and calculateDiscountPrice)
                $convertedPrice = number_format($convertedPriceFloat, 2, '.', '');
                $convertedOriginalPrice = number_format($convertedOriginalPriceFloat, 2, '.', '');
                $convertedDiscountPrice = number_format($variantDiscountInfo['discount_price'], 2, '.', '');

                // Translate props_names (e.g., "Color:黑色" -> "Color:Black")
                $propsNames = $sku['props_names'] ?? '';
                $translatedPropsNames = ChineseTranslationHelper::translatePropsNames($propsNames);

                $variants[] = [
                    'sku_id' => $sku['skuid'] ?? $sku['sku_id'] ?? '',
                    'spec_id' => $sku['specid'] ?? '',
                    'price' => $convertedPrice,
                    'original_price' => $convertedOriginalPrice,
                    'discount_percentage' => $variantDiscountInfo['discount_percentage'],
                    'discount_price' => $convertedDiscountPrice,
                    'stock' => (int) ($sku['stock'] ?? 0),
                    'props_names' => $translatedPropsNames,
                ];
            }
        }

        // Extract shop info
        $shopInfo = $item['shop_info'] ?? [];

        // Extract props and translate
        $props = [];
        if (isset($item['product_props']) && is_array($item['product_props'])) {
            foreach ($item['product_props'] as $prop) {
                if (is_array($prop)) {
                    foreach ($prop as $key => $value) {
                        // Translate both property name and value
                        $translatedName = ChineseTranslationHelper::translate($key);
                        $translatedValue = is_string($value) ? ChineseTranslationHelper::translate($value) : $value;
                        $props[] = ['name' => $translatedName, 'value' => $translatedValue];
                    }
                }
            }
        }

        // Build transformed structure
        $transformed = [
            'id' => 'e3pro-' . ($item['item_id'] ?? ''),
            'item_id' => $item['item_id'] ?? '',
            'title' => $item['title'] ?? '',
            'description' => $item['desc'] ?? '',
            'regular_price' => $regularPrice,
            'sale_price' => $salePrice,
            'price_min' => $priceMin,
            'price_max' => $priceMax,
            'discount_percentage' => $regularDiscountInfo['discount_percentage'],
            'discount_price' => $regularDiscountPrice,
            'discount_price_min' => $priceMinDiscountPrice,
            'discount_price_max' => $priceMaxDiscountPrice,
            'currency' => $siteCurrency,
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
                'name' => ChineseTranslationHelper::translate($shopInfo['shop_name'] ?? $shopInfo['seller_nick'] ?? ''),
                'seller_id' => $shopInfo['seller_id'] ?? '',
                'location' => ChineseTranslationHelper::translate($shopInfo['location'] ?? ''),
            ],
            'meta' => [
                'total_sold' => $totalSold,
                'category_id' => $item['category_id'] ?? null,
                'product_url' => $item['product_url'] ?? '',
            ],
        ];

        // Add all original fields to ensure no data is missing
        // This preserves the original API response structure alongside the transformed fields
        $originalFields = [
            'product_url',
            'category_id',
            'category_name',
            'root_category_id',
            'currency',
            'main_imgs',
            'detail_url',
            'offer_unit',
            'product_props',
            'sale_count',
            'price_info',
            'tiered_price_info',
            'mixed_batch',
            'sale_info',
            'support_drop_shipping',
            'support_cross_border',
            'shop_info',
            'delivery_info',
            'service_tags',
            'sku_props',
            'skus',
            'promotions',
            'extra',
        ];

        // Add original fields (they will override any existing transformed fields with the same name)
        // This ensures all original data is preserved
        foreach ($originalFields as $field) {
            if (isset($item[$field])) {
                $transformed[$field] = $item[$field];
            }
        }

        return $transformed;
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

            // Calculate discount prices
            $regularPriceFloat = $regularPrice / 100;
            $discountInfo = $this->calculateDiscountPrice($regularPriceFloat);
            $discountPrice = (int) round($discountInfo['discount_price'] * 100);

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
                'discount_percentage' => $discountInfo['discount_percentage'],
                'discount_price' => $discountPrice,
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
     * Get product description details
     * GET /api/product-description/{itemId}
     * 
     * Public endpoint to get detailed product description with images
     */
    public function productDescription(Request $request, string $itemId): JsonResponse
    {
        $platform = $request->input('platform', '1688');

        $result = $this->dropshipService->getProductDescriptionDetails($platform, $itemId);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to fetch product description',
                'error' => $result['message'] ?? 'Product description not found',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product description fetched successfully',
            'data' => $result['data'] ?? $result,
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

    /**
     * Log image search failure to file
     */
    private function logImageSearchFailure(array $data): void
    {
        try {
            $logData = [
                'timestamp' => now()->toIso8601String(),
                'date' => now()->format('Y-m-d H:i:s'),
                'data' => $data,
            ];

            $logContent = json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n" . str_repeat('-', 80) . "\n";

            // Create logs directory if it doesn't exist
            $logDirectory = storage_path('logs/image-search-failures');
            if (!is_dir($logDirectory)) {
                mkdir($logDirectory, 0755, true);
            }

            // Log to daily file
            $logFile = $logDirectory . '/image-search-failures-' . now()->format('Y-m-d') . '.log';
            
            // Append to log file
            file_put_contents($logFile, $logContent, FILE_APPEND | LOCK_EX);
            
            // Also log to Laravel log for monitoring
            Log::error('Image search failed', $logData);
        } catch (\Throwable $e) {
            // If file logging fails, at least log to Laravel log
            Log::error('Failed to write image search failure log', [
                'error' => $e->getMessage(),
                'original_data' => $data,
            ]);
        }
    }
}

