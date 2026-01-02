<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Services\DropshipService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    protected DropshipService $dropshipService;

    public function __construct(DropshipService $dropshipService)
    {
        $this->dropshipService = $dropshipService;
    }
    /**
     * Get customer's cart
     * GET /api/customer/cart
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $customer = auth('sanctum')->user();
            
            $cart = Cart::with(['items.product'])
                ->where('customer_id', $customer->id)
                ->first();

            if (!$cart) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'items' => [],
                        'total_items' => 0,
                        'subtotal' => 0,
                        'total' => 0,
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'cart_id' => $cart->id,
                    'items' => $cart->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product_code' => $item->product_code,
                            'product_name' => $item->product_name,
                            'product_price' => $item->product_price,
                            'product_image' => $item->product_image,
                            'product_image_url' => $item->product_image_url,
                            'product_sku' => $item->product_sku,
                            'quantity' => $item->quantity,
                            'subtotal' => $item->subtotal,
                            'variations' => $item->variations,
                        ];
                    }),
                    'total_items' => $cart->total_items,
                    'subtotal' => $cart->total,
                    'total' => $cart->total,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add product with variations to cart
     * POST /api/customer/cart
     *
     * Supports both local products and dropship products (1688, Taobao, Tmall)
     *
     * Payload for local product:
     * {
     *   "product": "product_id",
     *   "quantity": 6,
     *   "variations": [
     *     {"id": "variation_id_1", "quantity": 3},
     *     {"id": "variation_id_2", "quantity": 3}
     *   ]
     * }
     *
     * Payload for dropship product:
     * {
     *   "product": "product_code_or_id",
     *   "quantity": 16,
     *   "variations": [
     *     {"id": "variation_id_1", "quantity": 4},
     *     {"id": "variation_id_2", "quantity": 4}
     *   ],
     *   "product_code": "product_code_from_1688", // optional, uses product if not provided
     *   "product_name": "Product Name", // optional for dropship
     *   "product_price": 29.99, // optional for dropship
     *   "product_image": "https://example.com/image.jpg", // optional
     *   "product_sku": "SKU123" // optional
     * }
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'product' => 'required',
                'quantity' => 'required|integer|min:1',
                'variations' => 'required|array|min:1',
                'variations.*.id' => 'required|string',
                'variations.*.quantity' => 'required|integer|min:1',
                'product_code' => 'nullable|string',
                'product_name' => 'nullable|string',
                'product_price' => 'nullable|numeric|min:0',
                'product_image' => 'nullable|string',
                'product_sku' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validate that total variation quantities match the main quantity
            $totalVariationQuantity = array_sum(array_column($request->variations, 'quantity'));
            if ($totalVariationQuantity !== $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total variation quantities must match the main quantity',
                    'errors' => [
                        'quantity' => ['Total variation quantities (' . $totalVariationQuantity . ') does not match main quantity (' . $request->quantity . ')']
                    ]
                ], 422);
            }

            $customer = auth('sanctum')->user();
            DB::beginTransaction();

            // Try to find product in local database
            $product = Product::find($request->product);
            $isDropshipProduct = !$product;

            if ($isDropshipProduct) {
                // Handle dropship product - fetch variant prices from product details API
                $productCode = $request->product_code ?? (string)$request->product;

                // Fetch product details to get variant prices and product info
                $variantPrices = [];
                $variantDetails = []; // Store complete variant information
                $productName = 'Dropship Product'; // Default fallback
                $productPrice = 0; // Default fallback
                $productImage = null; // Default fallback
                $productSku = null; // Default fallback

                try {
                    // Use DropshipService to get product details
                    $platform = '1688';
                    $lang = 'en';

                    $result = $this->dropshipService->getProduct($platform, $productCode, false, $lang, true);

                    if ($result['success'] && isset($result['data'])) {
                        // Transform the product details
                        $productData = $this->transformProductDetails($result['data']);

                        // Extract product name from title
                        if (isset($productData['title']) && !empty($productData['title'])) {
                            $productName = $productData['title'];
                        }

                        // Extract product image - prefer first image from images array, fallback to thumbnail
                        if (isset($productData['images']) && is_array($productData['images']) && !empty($productData['images'][0])) {
                            $productImage = $productData['images'][0];
                        } elseif (isset($productData['thumbnail']['large']) && !empty($productData['thumbnail']['large'])) {
                            $productImage = $productData['thumbnail']['large'];
                        } elseif (isset($productData['thumbnail']['medium']) && !empty($productData['thumbnail']['medium'])) {
                            $productImage = $productData['thumbnail']['medium'];
                        }

                        // Extract base product price
                        // The transformed data returns prices in cents (already converted to site currency)
                        // Use sale_price if available, otherwise use regular_price
                        if (isset($productData['sale_price']) && $productData['sale_price'] > 0) {
                            $productPrice = (float)$productData['sale_price'] / 100;
                        } elseif (isset($productData['regular_price'])) {
                            $productPrice = (float)$productData['regular_price'] / 100;
                        } elseif (isset($productData['price_min'])) {
                            $productPrice = (float)$productData['price_min'] / 100;
                        }

                        // Build variant details map (sku_id -> complete variant info)
                        // Variants array contains prices in cents (already converted to site currency)
                        if (isset($productData['variants']) && is_array($productData['variants'])) {
                            foreach ($productData['variants'] as $variant) {
                                if (isset($variant['sku_id'])) {
                                    $skuId = (string)$variant['sku_id'];

                                    // Store price for quick lookup
                                    if (isset($variant['price'])) {
                                        $variantPrices[$skuId] = (float)$variant['price'] / 100;
                                    }

                                    // Store complete variant details
                                    $variantDetails[$skuId] = [
                                        'sku_id' => $skuId,
                                        'spec_id' => $variant['spec_id'] ?? '',
                                        'price' => isset($variant['price']) ? (float)$variant['price'] / 100 : 0,
                                        'original_price' => isset($variant['original_price']) ? (float)$variant['original_price'] / 100 : 0,
                                        'stock' => $variant['stock'] ?? 0,
                                        'props_names' => $variant['props_names'] ?? '',
                                    ];
                                }
                            }
                        }

                        Log::info('Extracted product details from DropshipService', [
                            'product_code' => $productCode,
                            'product_name' => $productName,
                            'product_price' => $productPrice,
                            'has_image' => !empty($productImage),
                            'variant_count' => count($variantPrices),
                            'variant_prices' => $variantPrices
                        ]);
                    } else {
                        Log::warning('Failed to fetch product from DropshipService', [
                            'product_code' => $productCode,
                            'error' => $result['message'] ?? 'Unknown error'
                        ]);
                    }
                } catch (\Exception $e) {
                    // Log error but continue - use fallback values
                    Log::error('Failed to fetch product details from DropshipService', [
                        'product_code' => $productCode,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } else {
                // Handle local product
                // Check if product is active
                if (!$product->is_active) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Product is not available'
                    ], 400);
                }

                // Check stock
                if ($product->stock_quantity < $request->quantity) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock. Available: ' . $product->stock_quantity
                    ], 400);
                }

                $productCode = null;
                $productName = $product->name;
                $productPrice = $product->price;
                $productImage = $product->image_url;
                $productSku = $product->sku;
            }

            $cart = Cart::firstOrCreate(['customer_id' => $customer->id]);
            $addedItems = [];

            // Create a cart item for each variation
            foreach ($request->variations as $variation) {
                // Get variant-specific price and details for dropship products
                $variantPrice = $productPrice; // Default to product price
                $variantInfo = null; // Complete variant information

                if ($isDropshipProduct && !empty($variantPrices)) {
                    $variationId = (string)$variation['id'];
                    if (isset($variantPrices[$variationId])) {
                        $variantPrice = $variantPrices[$variationId];
                    }
                    if (isset($variantDetails[$variationId])) {
                        $variantInfo = $variantDetails[$variationId];
                    }
                }

                // Check if cart item with this product and variation already exists
                if ($isDropshipProduct) {
                    $cartItem = CartItem::where('cart_id', $cart->id)
                        ->where('product_code', $productCode)
                        ->whereNull('product_id')
                        ->whereJsonContains('variations->id', $variation['id'])
                        ->first();
                } else {
                    $cartItem = CartItem::where('cart_id', $cart->id)
                        ->where('product_id', $product->id)
                        ->whereJsonContains('variations->id', $variation['id'])
                        ->first();
                }

                if ($cartItem) {
                    // Update existing cart item
                    $newQuantity = $cartItem->quantity + $variation['quantity'];

                    // Only check stock for local products
                    if (!$isDropshipProduct && $product->stock_quantity < $newQuantity) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Cannot add more items. Maximum available: ' . $product->stock_quantity
                        ], 400);
                    }

                    // Update price if variant price is available (for dropship products)
                    if ($isDropshipProduct && $variantPrice != $cartItem->product_price) {
                        $cartItem->product_price = $variantPrice;
                    }

                    $cartItem->quantity = $newQuantity;
                    $cartItem->subtotal = $newQuantity * $cartItem->product_price;

                    // Update variation data in stored JSON
                    $variations = $cartItem->variations ?? [];
                    if (isset($variations['id']) && $variations['id'] === $variation['id']) {
                        $variations['quantity'] = $newQuantity;
                        // Update variant details if available
                        if ($variantInfo) {
                            $variations = array_merge($variations, $variantInfo);
                            $variations['quantity'] = $newQuantity; // Ensure quantity is preserved
                        }
                        $cartItem->variations = $variations;
                    }
                    $cartItem->save();
                } else {
                    // Prepare variation data to save
                    $variationData = [
                        'id' => $variation['id'],
                        'quantity' => $variation['quantity']
                    ];

                    // Add complete variant details if available (for dropship products)
                    if ($variantInfo) {
                        $variationData = array_merge($variationData, $variantInfo);
                        $variationData['quantity'] = $variation['quantity']; // Ensure quantity is preserved
                    }

                    // Create new cart item for this variation
                    $cartItem = CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $isDropshipProduct ? null : $product->id,
                        'product_code' => $productCode,
                        'product_name' => $productName,
                        'product_price' => $variantPrice, // Use variant-specific price
                        'product_image' => $productImage,
                        'product_sku' => $productSku,
                        'quantity' => $variation['quantity'],
                        'subtotal' => $variation['quantity'] * $variantPrice,
                        'variations' => $variationData,
                    ]);
                }

                $addedItems[] = [
                    'id' => $cartItem->id,
                    'product_id' => $cartItem->product_id,
                    'product_code' => $cartItem->product_code,
                    'product_name' => $cartItem->product_name,
                    'product_price' => $cartItem->product_price,
                    'product_image_url' => $cartItem->product_image_url,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $cartItem->subtotal,
                    'variations' => $cartItem->variations,
                ];
            }

            DB::commit();

            // Reload cart with items
            $cart->load('items');

            return response()->json([
                'success' => true,
                'message' => 'Items added to cart successfully',
                'data' => [
                    'cart_id' => $cart->id,
                    'items' => $addedItems,
                    'total_items' => $cart->total_items,
                    'subtotal' => $cart->total,
                    'total' => $cart->total,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add items to cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add item to cart
     * POST /api/customer/cart/add
     *
     * Supports both local products and dropship products
     */
    public function addItem(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'nullable|exists:products,id',
                'product_code' => 'nullable|string', // item_id from 1688/TMAPI
                'product_name' => 'required_without:product_id|string',
                'product_price' => 'required_without:product_id|numeric|min:0',
                'product_image' => 'nullable|string',
                'product_sku' => 'nullable|string',
                'quantity' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Must provide either product_id or product_code
            if (!$request->product_id && !$request->product_code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Either product_id or product_code is required'
                ], 422);
            }

            $customer = auth('sanctum')->user();
            DB::beginTransaction();

            $cart = Cart::firstOrCreate(['customer_id' => $customer->id]);

            // Handle local product
            if ($request->product_id) {
                $product = Product::findOrFail($request->product_id);

                // Check if product is active
                if (!$product->is_active) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Product is not available'
                    ], 400);
                }

                // Check stock
                if ($product->stock_quantity < $request->quantity) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock. Available: ' . $product->stock_quantity
                    ], 400);
                }

                // Check if item already exists in cart
                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('product_id', $product->id)
                    ->first();

                if ($cartItem) {
                    $newQuantity = $cartItem->quantity + $request->quantity;

                    if ($product->stock_quantity < $newQuantity) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Cannot add more items. Maximum available: ' . $product->stock_quantity
                        ], 400);
                    }

                    $cartItem->quantity = $newQuantity;
                    $cartItem->subtotal = $newQuantity * $cartItem->product_price;
                    $cartItem->save();
                } else {
                    $cartItem = CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $product->id,
                        'product_code' => null,
                        'product_name' => $product->name,
                        'product_price' => $product->price,
                        'product_image' => $product->image_url,
                        'product_sku' => $product->sku,
                        'quantity' => $request->quantity,
                        'subtotal' => $request->quantity * $product->price,
                    ]);
                }
            }
            // Handle dropship product
            else {
                // Check if item already exists in cart
                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('product_code', $request->product_code)
                    ->first();

                if ($cartItem) {
                    $newQuantity = $cartItem->quantity + $request->quantity;
                    $cartItem->quantity = $newQuantity;
                    $cartItem->subtotal = $newQuantity * $cartItem->product_price;
                    $cartItem->save();
                } else {
                    $cartItem = CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => null,
                        'product_code' => $request->product_code,
                        'product_name' => $request->product_name,
                        'product_price' => $request->product_price,
                        'product_image' => $request->product_image,
                        'product_sku' => $request->product_sku,
                        'quantity' => $request->quantity,
                        'subtotal' => $request->quantity * $request->product_price,
                    ]);
                }
            }

            DB::commit();

            // Reload cart with items
            $cart->load('items');

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully',
                'data' => [
                    'cart_id' => $cart->id,
                    'item' => [
                        'id' => $cartItem->id,
                        'product_id' => $cartItem->product_id,
                        'product_code' => $cartItem->product_code,
                        'product_name' => $cartItem->product_name,
                        'product_price' => $cartItem->product_price,
                        'product_image_url' => $cartItem->product_image_url,
                        'quantity' => $cartItem->quantity,
                        'subtotal' => $cartItem->subtotal,
                    ],
                    'total_items' => $cart->total_items,
                    'total' => $cart->total,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     * PUT /api/customer/cart/items/{cartItemId}
     */
    public function updateItem(Request $request, $cartItemId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $customer = auth('sanctum')->user();

            // Get cart item and verify ownership
            $cartItem = CartItem::whereHas('cart', function ($query) use ($customer) {
                $query->where('customer_id', $customer->id);
            })->findOrFail($cartItemId);

            // Only check stock for local products
            if ($cartItem->product_id) {
                $product = Product::findOrFail($cartItem->product_id);

                // Check stock
                if ($product->stock_quantity < $request->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock. Available: ' . $product->stock_quantity
                    ], 400);
                }
            }

            // Update quantity and subtotal
            $cartItem->quantity = $request->quantity;
            $cartItem->subtotal = $request->quantity * $cartItem->product_price;
            $cartItem->save();

            // Reload cart
            $cart = $cartItem->cart;
            $cart->load('items');

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully',
                'data' => [
                    'item' => [
                        'id' => $cartItem->id,
                        'product_id' => $cartItem->product_id,
                        'product_code' => $cartItem->product_code,
                        'product_name' => $cartItem->product_name,
                        'quantity' => $cartItem->quantity,
                        'subtotal' => $cartItem->subtotal,
                    ],
                    'total_items' => $cart->total_items,
                    'total' => $cart->total,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove item from cart
     * DELETE /api/customer/cart/items/{cartItemId}
     */
    public function removeItem($cartItemId): JsonResponse
    {
        try {
            $customer = auth('sanctum')->user();

            // Get cart item and verify ownership
            $cartItem = CartItem::whereHas('cart', function ($query) use ($customer) {
                $query->where('customer_id', $customer->id);
            })->findOrFail($cartItemId);

            $cart = $cartItem->cart;
            $cartItem->delete();

            // Reload cart
            $cart->load('items');

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart successfully',
                'data' => [
                    'total_items' => $cart->total_items,
                    'total' => $cart->total,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item from cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear cart
     * DELETE /api/customer/cart/clear
     */
    public function clearCart(): JsonResponse
    {
        try {
            $customer = auth('sanctum')->user();

            $cart = Cart::where('customer_id', $customer->id)->first();

            if ($cart) {
                $cart->items()->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart',
                'error' => $e->getMessage()
            ], 500);
        }
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

        return round($finalPrice, 2);
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
     * Transform API product to detail format
     * (Simplified version from ProductController for cart usage)
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

        // Extract price info (in CNY cents)
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

        // Convert prices from CNY to site currency
        $regularPrice = (int) round($this->convertPrice($regularPrice / 100) * 100);
        $salePrice = $salePrice ? (int) round($this->convertPrice($salePrice / 100) * 100) : null;
        $priceMin = (int) round($this->convertPrice($priceMin / 100) * 100);
        $priceMax = (int) round($this->convertPrice($priceMax / 100) * 100);

        // Extract SKUs/variants
        $variants = [];
        if (isset($item['skus']) && is_array($item['skus'])) {
            foreach ($item['skus'] as $sku) {
                $skuPrice = (float) ($sku['sale_price'] ?? $sku['price'] ?? 0);
                $skuOriginalPrice = (float) ($sku['origin_price'] ?? $sku['sale_price'] ?? 0);

                // Convert variant prices
                $convertedPrice = (int) round($this->convertPrice($skuPrice) * 100);
                $convertedOriginalPrice = (int) round($this->convertPrice($skuOriginalPrice) * 100);

                $variants[] = [
                    'sku_id' => $sku['skuid'] ?? $sku['sku_id'] ?? '',
                    'spec_id' => $sku['specid'] ?? '',
                    'price' => $convertedPrice,
                    'original_price' => $convertedOriginalPrice,
                    'stock' => (int) ($sku['stock'] ?? 0),
                    'props_names' => $sku['props_names'] ?? '',
                ];
            }
        }

        // Build transformed structure
        return [
            'id' => 'e3pro-' . ($item['item_id'] ?? ''),
            'item_id' => $item['item_id'] ?? '',
            'title' => $item['title'] ?? '',
            'description' => $item['desc'] ?? '',
            'regular_price' => $regularPrice,
            'sale_price' => $salePrice,
            'price_min' => $priceMin,
            'price_max' => $priceMax,
            'currency' => $siteCurrency,
            'stock' => (int) ($item['stock'] ?? 0),
            'is_sold_out' => $item['is_sold_out'] ?? false,
            'images' => $images,
            'thumbnail' => [
                'large' => !empty($images) ? $this->resizeImageUrl($images[0], '600x600') : '',
                'medium' => !empty($images) ? $this->resizeImageUrl($images[0], '310x310') : '',
                'small' => !empty($images) ? $this->resizeImageUrl($images[0], '100x100') : '',
            ],
            'variants' => $variants,
        ];
    }
}

