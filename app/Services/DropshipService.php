<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DropshipService
{
    protected string $baseUrl;
    protected string $apiToken;
    protected int $cacheTimeout;

    public function __construct()
    {
        $this->baseUrl = config('dropship.api_url', 'http://api.tmapi.top');
        $this->apiToken = config('dropship.api_token', '');
        $this->cacheTimeout = config('dropship.cache_timeout', 3600);
    }

    /**
     * Get product details by ID from Taobao
     */
    public function getTaobaoProduct(string $numIid, bool $isPromotion = true, string $lang = 'zh-CN'): array
    {
        return $this->getProduct('taobao', $numIid, $isPromotion, $lang);
    }

    /**
     * Get product details by ID from 1688
     */
    public function get1688Product(string $numIid, bool $cache = true, string $lang = 'zh-CN'): array
    {
        return $this->getProduct('1688', $numIid, false, $lang, $cache);
    }

    /**
     * Generic get product method for any platform
     */
    public function getProduct(string $platform, string $numIid, bool $isPromotion = false, string $lang = 'en', bool $useCache = true): array
    {
        $cacheKey = "dropship_{$platform}_product_{$numIid}_{$lang}";

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $params = [
            'apiToken' => $this->apiToken,
            'item_id' => $numIid,
        ];

        // Use multi-language endpoint for non-Chinese languages
        $endpoint = $lang === 'en' ? 'en/item_detail' : 'item_detail';
        $response = $this->makeRequest($platform, $endpoint, $params);

        if ($response['success'] && $useCache) {
            Cache::put($cacheKey, $response, $this->cacheTimeout);
        }

        return $response;
    }

    /**
     * Search products by keyword
     */
    public function searchProducts(string $platform, string $keyword, int $page = 1, int $pageSize = 20, array $options = []): array
    {
        $params = [
            'apiToken' => $this->apiToken,
            'keyword' => $keyword,
            'page' => $page,
            'page_size' => min($pageSize, 20), // tmapi.top max is 20
        ];

        if (!empty($options['min_price'])) {
            $params['price_start'] = $options['min_price'];
        }
        if (!empty($options['max_price'])) {
            $params['price_end'] = $options['max_price'];
        }
        if (!empty($options['sort'])) {
            // tmapi.top supports: default, sales, price_up, price_down
            $params['sort'] = $options['sort'];
        }

        // Use multi-language endpoint - default to English
        $lang = $options['lang'] ?? 'en';
        $endpoint = $lang === 'en' ? 'en/search/items' : 'search/items';

        return $this->makeRequest($platform, $endpoint, $params);
    }

    /**
     * Search products by image URL
     */
    public function searchByImage(string $platform, string $imageUrl, int $page = 1, int $pageSize = 20, string $lang = 'en', array $options = []): array
    {
        $params = [
            'apiToken' => $this->apiToken,
            'img_url' => $imageUrl,
            'page' => $page,
            'page_size' => min($pageSize, 20), // tmapi.top max is 20
        ];

        // Add optional filters from the API documentation
        if (!empty($options['sort'])) {
            $params['sort'] = $options['sort']; // default, sales, price_up, price_down
        }
        if (!empty($options['price_start'])) {
            $params['price_start'] = $options['price_start'];
        }
        if (!empty($options['price_end'])) {
            $params['price_end'] = $options['price_end'];
        }
        if (isset($options['support_dropshipping'])) {
            $params['support_dropshipping'] = $options['support_dropshipping'];
        }
        if (isset($options['is_factory'])) {
            $params['is_factory'] = $options['is_factory'];
        }
        if (isset($options['verified_supplier'])) {
            $params['verified_supplier'] = $options['verified_supplier'];
        }
        if (isset($options['free_shipping'])) {
            $params['free_shipping'] = $options['free_shipping'];
        }
        if (isset($options['new_arrival'])) {
            $params['new_arrival'] = $options['new_arrival'];
        }

        // Use multi-language endpoint for non-Chinese languages
        $endpoint = $lang === 'en' ? 'en/search/items_by_img' : 'search/items_by_img';

        return $this->makeRequest($platform, $endpoint, $params);
    }

    /**
     * Get product description images
     */
    public function getProductDescription(string $platform, string $numIid): array
    {
        // For tmapi.top, description is included in item_detail
        return $this->getProduct($platform, $numIid);
    }

    /**
     * Get product description from item_desc endpoint
     * This endpoint specifically returns detailed product description with images
     */
    public function getProductDescriptionDetails(string $platform, string $numIid): array
    {
        $cacheKey = "dropship_{$platform}_product_desc_{$numIid}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $params = [
            'apiToken' => $this->apiToken,
            'item_id' => $numIid,
        ];

        $response = $this->makeRequest($platform, 'item_desc', $params);

        if ($response['success']) {
            Cache::put($cacheKey, $response, $this->cacheTimeout);
        }

        return $response;
    }

    /**
     * Get product reviews
     */
    public function getProductReviews(string $platform, string $numIid, int $page = 1, int $pageSize = 20): array
    {
        $params = [
            'apiToken' => $this->apiToken,
            'item_id' => $numIid,
            'page' => $page,
            'page_size' => $pageSize,
        ];

        return $this->makeRequest($platform, 'item_reviews', $params);
    }

    /**
     * Get shipping fee for a product
     */
    public function getShippingFee(string $platform, string $numIid, string $areaId, int $quantity = 1): array
    {
        $params = [
            'apiToken' => $this->apiToken,
            'item_id' => $numIid,
            'area_id' => $areaId,
            'quantity' => $quantity,
        ];

        return $this->makeRequest($platform, 'item_shipping_fee', $params);
    }

    /**
     * Get shop information
     */
    public function getShopInfo(string $platform, string $sellerId, string $lang = 'en'): array
    {
        $params = [
            'apiToken' => $this->apiToken,
        ];

        if ($platform === '1688') {
            $params['member_id'] = $sellerId;
        } else {
            $params['seller_id'] = $sellerId;
        }

        return $this->makeRequest($platform, 'shop/info', $params);
    }

    /**
     * Get shop products list
     */
    public function getShopProducts(string $platform, string $sellerId, int $page = 1, int $pageSize = 20, array $options = []): array
    {
        $params = [
            'apiToken' => $this->apiToken,
            'page' => $page,
            'page_size' => min($pageSize, 20),
        ];

        if ($platform === '1688') {
            $params['member_id'] = $sellerId;
        } else {
            $params['seller_id'] = $sellerId;
        }

        if (!empty($options['sort'])) {
            $params['sort'] = $options['sort'];
        }

        // Add language parameter to request params
        $lang = $options['lang'] ?? 'en';
        if ($lang) {
            $params['lang'] = $lang;
            $params['language'] = $lang;
        }

        return $this->makeRequest($platform, 'shop/items', $params);
    }

    /**
     * Get category information
     */
    public function getCategoryInfo(string $platform, string $catId): array
    {
        $params = [
            'apiToken' => $this->apiToken,
            'cat_id' => $catId,
        ];

        return $this->makeRequest($platform, 'category/info', $params);
    }

    /**
     * Get category products
     */
    public function getCategoryProducts(string $platform, string $catId, int $page = 1, int $pageSize = 20): array
    {
        $params = [
            'apiToken' => $this->apiToken,
            'cat_id' => $catId,
            'page' => $page,
            'page_size' => min($pageSize, 20),
        ];

        return $this->makeRequest($platform, 'category/items', $params);
    }

    /**
     * Translate text
     */
    public function translate(string $text, string $fromLang = 'zh', string $toLang = 'en'): array
    {
        $params = [
            'apiToken' => $this->apiToken,
            'text' => $text,
            'from' => $fromLang,
            'to' => $toLang,
        ];

        return $this->makeRequest('translate', 'text', $params);
    }

    /**
     * Convert image URL to accessible URL (proxy image)
     */
    public function convertImageUrl(string $imageUrl): array
    {
        // Return the image URL directly - tmapi.top images are already accessible
        return [
            'success' => true,
            'data' => ['url' => $imageUrl],
        ];
    }

    /**
     * Convert non-Alibaba image URL to Alibaba-compatible URL for search
     * This uses TMAPI's image URL conversion endpoint
     */
    public function convertImageUrlForSearch(string $imageUrl): array
    {
        $params = [
            'apiToken' => $this->apiToken,
            'img_url' => $imageUrl,
        ];

        // Use the image conversion endpoint
        // Note: This endpoint may not exist in TMAPI, so we'll handle gracefully
        try {
            $response = $this->makeRequest('1688', 'tools/convert_image', $params);
            return $response;
        } catch (\Exception $e) {
            // If conversion fails, return the original URL
            return [
                'success' => true,
                'data' => ['url' => $imageUrl],
                'message' => 'Image conversion not available, using original URL',
            ];
        }
    }

    /**
     * Make HTTP request to the tmapi.top API
     */
    protected function makeRequest(string $platform, string $endpoint, array $params): array
    {
        try {
            $url = "{$this->baseUrl}/{$platform}/{$endpoint}";

            $response = Http::timeout(30)
                ->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();

                // Check for API-level errors (tmapi.top uses code != 200 for errors)
                if (isset($data['code']) && $data['code'] !== 200) {
                    return [
                        'success' => false,
                        'error_code' => $data['code'],
                        'message' => $data['msg'] ?? 'API error',
                        'data' => null,
                    ];
                }

                return [
                    'success' => true,
                    'error_code' => 200,
                    'message' => $data['msg'] ?? 'Success',
                    'data' => $data['data'] ?? $data,
                ];
            }

            return [
                'success' => false,
                'error_code' => $response->status(),
                'message' => 'HTTP request failed',
                'data' => null,
            ];

        } catch (\Exception $e) {
            Log::error('Dropship API Error', [
                'platform' => $platform,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error_code' => 'EXCEPTION',
                'message' => $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Transform raw API product data to local product format (tmapi.top format)
     */
    public function transformToLocalProduct(?array $apiProduct): array
    {
        if (empty($apiProduct)) {
            return [];
        }

        // tmapi.top returns data directly, not wrapped in 'item'
        $item = $apiProduct;

        // Extract price from price_info (tmapi.top format)
        $price = 0;
        $originalPrice = 0;
        $priceMin = 0;
        $priceMax = 0;

        if (isset($item['price_info'])) {
            $priceInfo = $item['price_info'];
            $price = (float) ($priceInfo['price'] ?? $priceInfo['price_min'] ?? 0);
            $originalPrice = (float) ($priceInfo['origin_price_min'] ?? $price);
            $priceMin = (float) ($priceInfo['price_min'] ?? $price);
            $priceMax = (float) ($priceInfo['price_max'] ?? $price);
        } elseif (isset($item['sku_price_range'])) {
            $priceRange = $item['sku_price_range'];
            $priceMin = (float) ($priceRange[0] ?? 0);
            $priceMax = (float) ($priceRange[1] ?? $priceMin);
            $price = $priceMin;
            $originalPrice = $priceMax;
        }

        // Extract properties as key-value pairs
        $props = [];
        if (isset($item['product_props']) && is_array($item['product_props'])) {
            foreach ($item['product_props'] as $prop) {
                if (is_array($prop)) {
                    foreach ($prop as $key => $value) {
                        $props[$key] = $value;
                    }
                }
            }
        }

        // Extract shop info
        $shopInfo = $item['shop_info'] ?? [];
        $shopName = $shopInfo['shop_name'] ?? $shopInfo['seller_nick'] ?? '';
        $sellerId = $shopInfo['seller_id'] ?? '';

        // Extract stock
        $stock = 0;
        if (isset($item['stock'])) {
            $stock = (int) $item['stock'];
        }

        // Extract sale count
        $saleCount = 0;
        if (isset($item['sale_count'])) {
            $saleCount = (int) $item['sale_count'];
        } elseif (isset($item['sale_info']['sale_count'])) {
            $saleCount = (int) $item['sale_info']['sale_count'];
        }

        return [
            'name' => $item['title'] ?? '',
            'description' => $item['desc'] ?? '',
            'price' => $price,
            'original_price' => $originalPrice,
            'price_min' => $priceMin,
            'price_max' => $priceMax,
            'sku' => (string) ($item['item_id'] ?? ''),
            'stock_quantity' => $stock,
            'is_sold_out' => $item['is_sold_out'] ?? false,
            'brand' => $props['品牌'] ?? $props['brand'] ?? null,
            'weight' => $this->extractWeight($props),
            'source_platform' => 'dropship',
            'source_id' => (string) ($item['item_id'] ?? ''),
            'source_url' => $item['product_url'] ?? '',
            'seller_id' => $sellerId,
            'shop_name' => $shopName,
            'shop_info' => $shopInfo,
            'location' => $shopInfo['location'] ?? '',
            'total_sold' => $saleCount,
            'images' => $this->extractImages($item),
            'video' => $item['video_url'] ?? null,
            'skus' => $this->extractSkus($item),
            'sku_props' => $item['sku_props'] ?? [],
            'props' => $props,
            'category_id' => $item['category_id'] ?? null,
            'root_category_id' => $item['root_category_id'] ?? null,
            'currency' => $item['currency'] ?? 'CNY',
            'offer_unit' => $item['offer_unit'] ?? '',
            'detail_url' => $item['detail_url'] ?? null,
            'delivery_info' => $item['delivery_info'] ?? null,
            'service_tags' => $item['service_tags'] ?? [],
            'promotions' => $item['promotions'] ?? [],
            'tiered_price_info' => $item['tiered_price_info'] ?? [],
        ];
    }

    /**
     * Extract weight from properties
     */
    protected function extractWeight(array $props): float
    {
        $weightStr = $props['重量'] ?? $props['weight'] ?? '0';
        // Remove unit suffix like "克", "kg", etc.
        $weight = preg_replace('/[^0-9.]/', '', $weightStr);
        return (float) $weight;
    }

    /**
     * Extract images from API product data (tmapi.top format)
     */
    protected function extractImages(array $item): array
    {
        $images = [];

        // Main images from tmapi.top format
        if (isset($item['main_imgs']) && is_array($item['main_imgs'])) {
            foreach ($item['main_imgs'] as $url) {
                if (is_string($url) && $url) {
                    $images[] = $url;
                }
            }
        }

        // Fallback to item_imgs format
        if (empty($images) && isset($item['item_imgs']) && is_array($item['item_imgs'])) {
            foreach ($item['item_imgs'] as $img) {
                $url = is_string($img) ? $img : ($img['url'] ?? '');
                if ($url && strpos($url, '//') === 0) {
                    $url = 'https:' . $url;
                }
                if ($url) {
                    $images[] = $url;
                }
            }
        }

        // Fallback to pic_url
        if (empty($images) && isset($item['pic_url'])) {
            $url = $item['pic_url'];
            if (strpos($url, '//') === 0) {
                $url = 'https:' . $url;
            }
            $images[] = $url;
        }

        return $images;
    }

    /**
     * Extract SKUs from API product data (tmapi.top format)
     */
    protected function extractSkus(array $item): array
    {
        $skus = [];

        // tmapi.top uses 'skus' array
        if (isset($item['skus']) && is_array($item['skus'])) {
            foreach ($item['skus'] as $sku) {
                $skus[] = [
                    'sku_id' => $sku['skuid'] ?? $sku['sku_id'] ?? '',
                    'spec_id' => $sku['specid'] ?? '',
                    'price' => (float) ($sku['sale_price'] ?? $sku['price'] ?? 0),
                    'original_price' => (float) ($sku['origin_price'] ?? $sku['sale_price'] ?? 0),
                    'quantity' => (int) ($sku['stock'] ?? 0),
                    'sale_count' => (int) ($sku['sale_count'] ?? 0),
                    'props_ids' => $sku['props_ids'] ?? '',
                    'props_names' => $sku['props_names'] ?? '',
                    'package_info' => $sku['package_info'] ?? null,
                ];
            }
        }

        // Fallback to sku_list format
        if (empty($skus) && isset($item['sku_list']) && is_array($item['sku_list'])) {
            foreach ($item['sku_list'] as $sku) {
                $skus[] = [
                    'sku_id' => $sku['sku_id'] ?? '',
                    'price' => (float) ($sku['price'] ?? 0),
                    'original_price' => (float) ($sku['original_price'] ?? $sku['price'] ?? 0),
                    'quantity' => (int) ($sku['stock'] ?? $sku['quantity'] ?? 0),
                    'props_ids' => $sku['props'] ?? $sku['properties'] ?? '',
                    'props_names' => $sku['props_name'] ?? $sku['properties_name'] ?? '',
                    'image' => $sku['sku_img'] ?? null,
                ];
            }
        }

        return $skus;
    }
}

