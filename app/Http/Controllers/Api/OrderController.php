<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    /**
     * Create order from cart
     * POST /api/customer/orders/create
     *
     * Supports both local and dropship products
     * Transaction number and payment receipt are optional
     */
    public function createFromCart(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'shipping_address' => 'required',
                'shipping_method' => 'nullable|in:air,ship',
                'payment_method' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'transaction_number' => 'nullable|string|max:255',
                'payment_receipt' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $customer = auth('sanctum')->user();

            // Get customer's cart
            $cart = Cart::with('items.product')
                ->where('customer_id', $customer->id)
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart is empty'
                ], 400);
            }

            // Verify local products are still available and in stock
            // Skip validation for dropship products (product_id is null)
            foreach ($cart->items as $item) {
                // Only validate local products
                if ($item->product_id) {
                    $product = $item->product;

                    if (!$product || !$product->is_active) {
                        return response()->json([
                            'success' => false,
                            'message' => "Product '{$item->product_name}' is no longer available"
                        ], 400);
                    }

                    if ($product->stock_quantity < $item->quantity) {
                        return response()->json([
                            'success' => false,
                            'message' => "Insufficient stock for '{$item->product_name}'. Available: {$product->stock_quantity}"
                        ], 400);
                    }
                }
            }

            DB::beginTransaction();

            // Check if manual payment with transaction number - don't save amounts, let admin update
            $paymentMethod = $request->payment_method ?? 'manual';
            $isManualPaymentWithTransaction = ($paymentMethod === 'manual') && !empty($request->transaction_number);
            
            // Get site settings for shipping and tax
            $settings = SiteSetting::getInstance();
            
            // Calculate totals
            $subtotal = $cart->total;
            $shippingCost = $settings->shipping_cost ?? 0;
            
            // Apply free shipping threshold if set
            if ($settings->free_shipping_threshold && $subtotal >= $settings->free_shipping_threshold) {
                $shippingCost = 0;
            }

            // Calculate tax
            $taxRate = $settings->tax_rate ?? 0;
            $taxInclusive = $settings->tax_inclusive ?? false;
            
            if ($taxInclusive) {
                // Tax is already included in product prices
                $taxAmount = ($subtotal / (100 + $taxRate)) * $taxRate;
            } else {
                // Tax needs to be added
                $taxAmount = ($subtotal * $taxRate) / 100;
            }

            $totalAmount = $subtotal + $shippingCost;
            if (!$taxInclusive) {
                $totalAmount += $taxAmount;
            }
            
            // If manual payment with transaction number, set amounts to 0/null for admin to update
            if ($isManualPaymentWithTransaction) {
                $subtotal = 0;
                $shippingCost = 0;
                $taxAmount = 0;
                $totalAmount = 0;
            }

            // Handle payment receipt upload
            $paymentReceiptPath = null;
            if ($request->hasFile('payment_receipt')) {
                $receipt = $request->file('payment_receipt');
                $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $receipt->getClientOriginalExtension();
                $paymentReceiptPath = $receipt->storeAs('payment_receipts', $filename, 'public');
            }

            // Parse shipping address (can be JSON string or array)
            $shippingAddress = $request->shipping_address;
            if (is_string($shippingAddress)) {
                $decoded = json_decode($shippingAddress, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $shippingAddress = $decoded;
                }
            }
            // Store as JSON string in database
            $shippingAddressJson = is_array($shippingAddress) ? json_encode($shippingAddress) : $shippingAddress;

            // Generate unique order number
            $orderNumber = 'ORD-' . strtoupper(uniqid()) . '-' . time();

            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'shipping_cost' => $shippingCost,
                'shipping_method' => $request->shipping_method ?? 'air',
                'tax_amount' => $taxAmount,
                'tax_rate' => $isManualPaymentWithTransaction ? 0 : $taxRate,
                'tax_inclusive' => $taxInclusive,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_method' => $request->payment_method ?? 'manual',
                'payment_status' => 'pending',
                'transaction_number' => $request->transaction_number,
                'payment_receipt_image' => $paymentReceiptPath ? Storage::url($paymentReceiptPath) : null,
                'shipping_address' => $shippingAddressJson,
                'notes' => $request->notes,
            ]);

            // Create order items from cart items
            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_code' => $cartItem->product_code,
                    'product_name' => $cartItem->product_name,
                    'product_image' => $cartItem->product_image,
                    'product_sku' => $cartItem->product_sku,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product_price,
                    'total' => $cartItem->subtotal,
                    'variations' => $cartItem->variations,
                ]);

                // Update product stock (only for local products)
                if ($cartItem->product_id && $cartItem->product) {
                    $product = $cartItem->product;
                    $product->stock_quantity -= $cartItem->quantity;
                    $product->save();
                }
            }

            // Clear cart after order creation
            $cart->items()->delete();
            
            // Record initial status in history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'old_status' => null,
                'new_status' => 'pending',
                'changed_by_type' => 'customer',
                'changed_by_id' => $customer->id,
                'notes' => 'Order created',
            ]);

            DB::commit();

            // Load order with items and status history
            $order->load('orderItems.product', 'statusHistory');

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'subtotal' => $order->subtotal,
                    'shipping_cost' => $order->shipping_cost,
                    'shipping_method' => $order->shipping_method,
                    'tax_amount' => $order->tax_amount,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'transaction_number' => $order->transaction_number,
                    'payment_receipt_url' => $order->payment_receipt_url,
                    'shipping_address' => is_string($order->shipping_address) ? json_decode($order->shipping_address, true) ?? $order->shipping_address : $order->shipping_address,
                    'notes' => $order->notes,
                    'items' => $order->orderItems->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'product_code' => $item->product_code,
                            'product_name' => $item->product_name ?? ($item->product->name ?? 'N/A'),
                            'product_image_url' => $item->product_image_url,
                            'product_sku' => $item->product_sku,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'total' => $item->total,
                            'variations' => $item->variations,
                        ];
                    }),
                    'status_history' => $order->statusHistory->map(function ($history) {
                        return [
                            'id' => $history->id,
                            'old_status' => $history->old_status,
                            'new_status' => $history->new_status,
                            'changed_by_type' => $history->changed_by_type,
                            'changed_by_id' => $history->changed_by_id,
                            'notes' => $history->notes,
                            'created_at' => $history->created_at,
                        ];
                    }),
                    'created_at' => $order->created_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up uploaded receipt on error
            if (isset($paymentReceiptPath) && $paymentReceiptPath) {
                Storage::disk('public')->delete($paymentReceiptPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer's orders
     * GET /api/customer/orders
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $customer = auth('sanctum')->user();

            $query = Order::with('orderItems.product')
                ->where('customer_id', $customer->id);

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by payment status if provided
            if ($request->has('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            $orders = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'total_amount' => $order->total_amount,
                        'status' => $order->status,
                        'payment_method' => $order->payment_method,
                        'payment_status' => $order->payment_status,
                        'items_count' => $order->orderItems->count(),
                        'created_at' => $order->created_at,
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order details
     * GET /api/customer/orders/{orderId}
     */
    public function show($orderId): JsonResponse
    {
        try {
            $customer = auth('sanctum')->user();

            $order = Order::with('orderItems.product', 'statusHistory')
                ->where('customer_id', $customer->id)
                ->findOrFail($orderId);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'subtotal' => $order->subtotal,
                    'discount_amount' => $order->discount_amount,
                    'shipping_cost' => $order->shipping_cost,
                    'shipping_method' => $order->shipping_method,
                    'tax_amount' => $order->tax_amount,
                    'tax_rate' => $order->tax_rate,
                    'tax_inclusive' => $order->tax_inclusive,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'transaction_number' => $order->transaction_number,
                    'payment_receipt_url' => $order->payment_receipt_url,
                    'paid_at' => $order->paid_at,
                    'shipping_address' => is_string($order->shipping_address) ? json_decode($order->shipping_address, true) ?? $order->shipping_address : $order->shipping_address,
                    'notes' => $order->notes,
                    'items' => $order->orderItems->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'product_code' => $item->product_code,
                            'product_name' => $item->product_name ?? ($item->product->name ?? 'N/A'),
                            'product_image_url' => $item->product_image_url,
                            'product_sku' => $item->product_sku,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'total' => $item->total,
                            'variations' => $item->variations,
                        ];
                    }),
                    'status_history' => $order->statusHistory->map(function ($history) {
                        return [
                            'id' => $history->id,
                            'old_status' => $history->old_status,
                            'new_status' => $history->new_status,
                            'changed_by_type' => $history->changed_by_type,
                            'changed_by_id' => $history->changed_by_id,
                            'notes' => $history->notes,
                            'created_at' => $history->created_at,
                        ];
                    }),
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

