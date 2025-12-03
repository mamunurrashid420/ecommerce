<?php

namespace App\Http\Controllers\Dropship;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\DropshipService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected DropshipService $dropshipService;

    public function __construct(DropshipService $dropshipService)
    {
        $this->dropshipService = $dropshipService;
    }

    /**
     * Get order sourcing details - find source products for order items
     * GET /api/dropship/orders/{order}/source
     */
    public function source(Request $request, Order $order): JsonResponse
    {
        $sourcingResults = [];

        foreach ($order->items as $item) {
            $product = $item->product;
            
            // Check if product has dropship source info
            if ($product && str_starts_with($product->sku, 'DS-')) {
                // Extract platform and source ID from SKU (format: DS-platform-numIid)
                $skuParts = explode('-', $product->sku, 3);
                $platform = $skuParts[1] ?? 'taobao';
                $numIid = $skuParts[2] ?? null;

                if ($numIid) {
                    $result = $this->dropshipService->getProduct($platform, $numIid);
                    
                    $sourcingResults[] = [
                        'order_item_id' => $item->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $item->quantity,
                        'platform' => $platform,
                        'source_id' => $numIid,
                        'source_available' => $result['success'],
                        'source_data' => $result['success'] ? [
                            'title' => $result['data']['item']['title'] ?? '',
                            'price' => $result['data']['item']['price'] ?? 0,
                            'stock' => $result['data']['item']['num'] ?? 0,
                            'seller' => $result['data']['item']['seller_info'] ?? null,
                            'detail_url' => $result['data']['item']['detail_url'] ?? '',
                        ] : null,
                        'error' => $result['success'] ? null : $result['message'],
                    ];
                } else {
                    $sourcingResults[] = [
                        'order_item_id' => $item->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $item->quantity,
                        'platform' => null,
                        'source_id' => null,
                        'source_available' => false,
                        'source_data' => null,
                        'error' => 'Invalid dropship SKU format',
                    ];
                }
            } else {
                $sourcingResults[] = [
                    'order_item_id' => $item->id,
                    'product_id' => $product?->id,
                    'product_name' => $product?->name ?? $item->product_name,
                    'quantity' => $item->quantity,
                    'platform' => null,
                    'source_id' => null,
                    'source_available' => false,
                    'source_data' => null,
                    'error' => 'Not a dropship product',
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Order sourcing information retrieved',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'items_count' => count($sourcingResults),
                'dropship_items' => collect($sourcingResults)->where('platform', '!=', null)->count(),
                'sourcing_results' => $sourcingResults,
            ],
        ]);
    }

    /**
     * Bulk check sourcing availability for multiple orders
     * POST /api/dropship/orders/bulk-source-check
     */
    public function bulkSourceCheck(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_ids' => 'required|array|min:1|max:50',
            'order_ids.*' => 'integer|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $orders = Order::with('items.product')->whereIn('id', $request->input('order_ids'))->get();
        $results = [];

        foreach ($orders as $order) {
            $dropshipItems = 0;
            $availableItems = 0;

            foreach ($order->items as $item) {
                if ($item->product && str_starts_with($item->product->sku, 'DS-')) {
                    $dropshipItems++;
                    
                    $skuParts = explode('-', $item->product->sku, 3);
                    $platform = $skuParts[1] ?? 'taobao';
                    $numIid = $skuParts[2] ?? null;

                    if ($numIid) {
                        $result = $this->dropshipService->getProduct($platform, $numIid);
                        if ($result['success']) {
                            $stock = (int) ($result['data']['item']['num'] ?? 0);
                            if ($stock >= $item->quantity) {
                                $availableItems++;
                            }
                        }
                    }
                }
            }

            $results[] = [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_items' => $order->items->count(),
                'dropship_items' => $dropshipItems,
                'available_items' => $availableItems,
                'fully_sourceable' => $dropshipItems > 0 && $availableItems === $dropshipItems,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Bulk source check completed',
            'data' => $results,
        ]);
    }

    /**
     * Get source product price comparison
     * GET /api/dropship/orders/{order}/price-comparison
     */
    public function priceComparison(Request $request, Order $order): JsonResponse
    {
        $comparison = [];
        $totalLocalCost = 0;
        $totalSourceCost = 0;

        foreach ($order->items as $item) {
            $product = $item->product;
            $localPrice = (float) $item->unit_price;
            $sourcePrice = null;
            $platform = null;
            $numIid = null;

            if ($product && str_starts_with($product->sku, 'DS-')) {
                $skuParts = explode('-', $product->sku, 3);
                $platform = $skuParts[1] ?? 'taobao';
                $numIid = $skuParts[2] ?? null;

                if ($numIid) {
                    $result = $this->dropshipService->getProduct($platform, $numIid);
                    if ($result['success']) {
                        $sourcePrice = (float) ($result['data']['item']['price'] ?? 0);
                    }
                }
            }

            $itemLocalTotal = $localPrice * $item->quantity;
            $itemSourceTotal = $sourcePrice ? $sourcePrice * $item->quantity : null;

            $totalLocalCost += $itemLocalTotal;
            if ($itemSourceTotal) {
                $totalSourceCost += $itemSourceTotal;
            }

            $comparison[] = [
                'order_item_id' => $item->id,
                'product_name' => $product?->name ?? $item->product_name,
                'quantity' => $item->quantity,
                'local_unit_price' => $localPrice,
                'source_unit_price' => $sourcePrice,
                'local_total' => $itemLocalTotal,
                'source_total' => $itemSourceTotal,
                'profit_margin' => $sourcePrice ? round((($localPrice - $sourcePrice) / $sourcePrice) * 100, 2) : null,
                'platform' => $platform,
                'source_id' => $numIid,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Price comparison completed',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_local_cost' => round($totalLocalCost, 2),
                'total_source_cost' => round($totalSourceCost, 2),
                'estimated_profit' => round($totalLocalCost - $totalSourceCost, 2),
                'profit_margin_percentage' => $totalSourceCost > 0
                    ? round((($totalLocalCost - $totalSourceCost) / $totalSourceCost) * 100, 2)
                    : null,
                'items' => $comparison,
            ],
        ]);
    }

    /**
     * Mark order items as sourced
     * POST /api/dropship/orders/{order}/mark-sourced
     */
    public function markSourced(Request $request, Order $order): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|integer|exists:order_items,id',
            'items.*.source_order_id' => 'nullable|string|max:255',
            'items.*.source_tracking' => 'nullable|string|max:255',
            'items.*.source_cost' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $updatedItems = [];

            foreach ($request->input('items') as $itemData) {
                $orderItem = OrderItem::where('id', $itemData['order_item_id'])
                    ->where('order_id', $order->id)
                    ->first();

                if ($orderItem) {
                    // Store sourcing info in metadata or custom fields
                    $sourcingInfo = [
                        'sourced_at' => now()->toIso8601String(),
                        'source_order_id' => $itemData['source_order_id'] ?? null,
                        'source_tracking' => $itemData['source_tracking'] ?? null,
                        'source_cost' => $itemData['source_cost'] ?? null,
                        'notes' => $itemData['notes'] ?? null,
                    ];

                    // Update the order item with sourcing metadata
                    $currentMeta = $orderItem->metadata ?? [];
                    $currentMeta['dropship_sourcing'] = $sourcingInfo;
                    $orderItem->metadata = $currentMeta;
                    $orderItem->save();

                    $updatedItems[] = [
                        'order_item_id' => $orderItem->id,
                        'sourcing_info' => $sourcingInfo,
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Items marked as sourced',
                'data' => [
                    'order_id' => $order->id,
                    'updated_items' => $updatedItems,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark items as sourced',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

