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

        $platform = $request->input('platform', 'taobao');
        $lang = $request->input('lang', 'zh-CN');
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

        $platform = $request->input('platform', 'taobao');
        $keyword = $request->input('q');
        $page = $request->integer('page', 1);
        $pageSize = $request->integer('page_size', 40);

        $options = [
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
            'sort' => $request->input('sort'),
            'lang' => $request->input('lang', 'zh-CN'),
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
            'page_size' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $platform = $request->input('platform', 'taobao');
        $imageUrl = $request->input('image_url');
        $page = $request->integer('page', 1);
        $pageSize = $request->integer('page_size', 40);

        $result = $this->dropshipService->searchByImage($platform, $imageUrl, $page, $pageSize);

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
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'num_iid' => 'required|string',
            'platform' => 'nullable|string|in:taobao,1688,tmall',
            'category_id' => 'required|exists:categories,id',
            'markup_percentage' => 'nullable|numeric|min:0|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $platform = $request->input('platform', 'taobao');
        $numIid = $request->input('num_iid');

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

        // Set category and status
        $productData['category_id'] = $request->input('category_id');
        $productData['is_active'] = $request->boolean('is_active', true);

        // Generate unique SKU
        $productData['sku'] = 'DS-' . $platform . '-' . $numIid;

        // Create product using existing Product model
        try {
            $product = \App\Models\Product::create([
                'name' => $productData['name'],
                'slug' => \Illuminate\Support\Str::slug($productData['name']) . '-' . $numIid,
                'description' => $productData['description'],
                'price' => round($productData['price'], 2),
                'stock_quantity' => $productData['stock_quantity'],
                'sku' => $productData['sku'],
                'brand' => $productData['brand'],
                'weight' => $productData['weight'],
                'category_id' => $productData['category_id'],
                'is_active' => $productData['is_active'],
                'meta_title' => $productData['name'],
                'meta_description' => substr($productData['description'], 0, 160),
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

