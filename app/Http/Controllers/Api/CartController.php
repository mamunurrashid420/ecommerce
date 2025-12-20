<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
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
}

