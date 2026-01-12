<?php

namespace App\Http\Controllers\Dropship;

use App\Http\Controllers\Controller;
use App\Services\DropshipService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

use App\Models\SiteSetting;
use Carbon\Carbon;

class ShopController extends Controller
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
     * Convert prices and currency for product list
     */
    private function convertProductListPrices(array $productsData): array
    {
        $siteCurrency = $this->getSiteCurrency();

        // Handle nested structure which might come from shop items
        // shop/items usually returns a list directly or in 'items' depending on wrapper
        // IF it's a list (indexed array), wrap it to process loop
        if (array_is_list($productsData) && !empty($productsData)) {
            foreach ($productsData as &$item) {
                if (is_array($item)) {
                    $item = $this->convertProductItemPrices($item, $siteCurrency);
                }
            }
            return $productsData;
        }

        if (isset($productsData['items']) && is_array($productsData['items'])) {
            foreach ($productsData['items'] as &$item) {
                $item = $this->convertProductItemPrices($item, $siteCurrency);
            }
        } elseif (isset($productsData['products']) && is_array($productsData['products'])) {
            foreach ($productsData['products'] as &$item) {
                $item = $this->convertProductItemPrices($item, $siteCurrency);
            }
        }

        return $productsData;
    }

    /**
     * Convert prices for a single product item
     */
    private function convertProductItemPrices(array $item, string $siteCurrency): array
    {
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
     * Get shop information
     * GET /api/dropship/shops/{sellerId}
     */
    public function show(Request $request, string $sellerId): JsonResponse
    {
        $platform = $request->input('platform', 'taobao');
        $lang = $request->input('lang', 'en');

        $result = $this->dropshipService->getShopInfo($platform, $sellerId, $lang);

        // If shop info fails (common for 1688 with some providers), return a graceful fallback
        if (!$result['success']) {
            // Check if it's 1688, we might just return basic info we have
            return response()->json([
                'success' => true,
                'message' => 'Shop Info unavailable, returning basic details',
                'data' => [
                    'shop_name' => 'Store #' . $sellerId,
                    'seller_id' => $sellerId,
                    'seller_nick' => $sellerId,
                    'platform' => $platform
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Shop information fetched successfully',
            'data' => $result['data'],
        ]);
    }

    /**
     * Get shop products
     * GET /api/dropship/shops/{sellerId}/products
     */
    public function products(Request $request, string $sellerId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'platform' => 'nullable|string|in:taobao,1688,tmall',
            'page' => 'nullable|integer|min:1',
            'page_size' => 'nullable|integer|min:1|max:100',
            'sort' => 'nullable|string',
            'lang' => 'nullable|string|in:en,zh',
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
        $pageSize = $request->integer('page_size', 40);
        $sort = $request->input('sort', 'default');
        $lang = $request->input('lang', 'en');

        $options = [
            'sort' => $sort,
            'lang' => $lang
        ];

        $result = $this->dropshipService->getShopProducts($platform, $sellerId, $page, $pageSize, $options);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        // Transform response variables
        $products = $result['data']; // The raw list or array

        // Convert prices and currency for all items
        $products = $this->convertProductListPrices($products);

        return response()->json([
            'result' => [
                'page' => $page,
                'per_page' => $pageSize,
                'products' => $products,
                'time' => now()->toIso8601String(),
            ],
            'image' => null,
        ]);
    }
}

