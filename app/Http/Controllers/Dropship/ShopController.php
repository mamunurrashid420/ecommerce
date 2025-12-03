<?php

namespace App\Http\Controllers\Dropship;

use App\Http\Controllers\Controller;
use App\Services\DropshipService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    protected DropshipService $dropshipService;

    public function __construct(DropshipService $dropshipService)
    {
        $this->dropshipService = $dropshipService;
    }

    /**
     * Get shop information
     * GET /api/dropship/shops/{sellerId}
     */
    public function show(Request $request, string $sellerId): JsonResponse
    {
        $platform = $request->input('platform', 'taobao');

        $result = $this->dropshipService->getShopInfo($platform, $sellerId);

        if (!$result['success']) {
            return response()->json($result, 400);
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

        $result = $this->dropshipService->getShopProducts($platform, $sellerId, $page, $pageSize);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Shop products fetched successfully',
            'data' => $result['data'],
        ]);
    }
}

