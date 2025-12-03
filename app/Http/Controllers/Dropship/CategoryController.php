<?php

namespace App\Http\Controllers\Dropship;

use App\Http\Controllers\Controller;
use App\Services\DropshipService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    protected DropshipService $dropshipService;

    public function __construct(DropshipService $dropshipService)
    {
        $this->dropshipService = $dropshipService;
    }

    /**
     * Get category information
     * GET /api/dropship/categories/{catId}
     */
    public function show(Request $request, string $catId): JsonResponse
    {
        $platform = $request->input('platform', 'taobao');

        $result = $this->dropshipService->getCategoryInfo($platform, $catId);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category information fetched successfully',
            'data' => $result['data'],
        ]);
    }

    /**
     * Get category products
     * GET /api/dropship/categories/{catId}/products
     */
    public function products(Request $request, string $catId): JsonResponse
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

        $result = $this->dropshipService->getCategoryProducts($platform, $catId, $page, $pageSize);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category products fetched successfully',
            'data' => $result['data'],
        ]);
    }
}

