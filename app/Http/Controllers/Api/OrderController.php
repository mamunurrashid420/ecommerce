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
     * Creates separate orders for each item with 70% payable price
     */
    public function createFromCart(Request $request): JsonResponse
    {
        try {
            // Parse items if it's a JSON string
            $items = $request->items;
            if (is_string($items)) {
                $decodedItems = json_decode($items, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedItems)) {
                    $items = $decodedItems;
                    // Replace in request for validation
                    $request->merge(['items' => $items]);
                }
            }

            // Ensure items is an array
            if (!is_array($items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Items must be a valid array',
                    'errors' => ['items' => ['The items field must be an array.']]
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'shipping_address' => 'required',
                'shipping_method' => 'nullable|in:air,ship',
                'payment_method' => 'nullable|string|max:255',
                'payment_method_id' => 'nullable|exists:payment_methods,id',
                'notes' => 'nullable|string',
                'transaction_number' => 'nullable|string|max:255',
                'payment_receipt' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|integer',
                'items.*.quantity' => 'nullable|integer|min:1',
                'items.*.product_id' => 'nullable|integer',
                'items.*.product_code' => 'nullable|string',
                'items.*.product_name' => 'nullable|string',
                'items.*.product_price' => 'nullable|numeric',
                'items.*.product_image' => 'nullable|string',
                'items.*.product_image_url' => 'nullable|string',
                'items.*.product_sku' => 'nullable|string',
                'items.*.subtotal' => 'nullable|numeric',
                'items.*.variations' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $customer = auth('sanctum')->user();

            // Validate items array
            if (empty($items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Items array is empty'
                ], 400);
            }

            DB::beginTransaction();

            // Handle payment receipt upload (shared across all orders)
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

            // Get site settings for shipping and tax
            $settings = SiteSetting::getInstance();
            $taxRate = $settings->tax_rate ?? 0;
            $taxInclusive = $settings->tax_inclusive ?? false;

            // Array to store all created orders
            $createdOrders = [];

            // Create one order for each item (with all its variations as order items)
            foreach ($items as $item) {
                // Get variations data - can be array or string
                $variations = $item['variations'] ?? null;
                
                // Parse variations if it's a string
                if (is_string($variations)) {
                    $decodedVariations = json_decode($variations, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $variations = $decodedVariations;
                    }
                }

                // Create one order for this item with all its variations
                $order = $this->createOrderForItem(
                    $customer,
                    $item,
                    $variations,
                    $request,
                    $settings,
                    $taxRate,
                    $taxInclusive,
                    $shippingAddressJson,
                    $paymentReceiptPath
                );

                if ($order) {
                    $createdOrders[] = $order;
                }
            }

            // Remove ordered items and variants from cart
            $cart = Cart::where('customer_id', $customer->id)->first();
            if ($cart) {
                foreach ($items as $orderedItem) {
                    $cartItemId = $orderedItem['id'] ?? null;
                    if (!$cartItemId) {
                        continue;
                    }

                    $cartItem = $cart->items()->find($cartItemId);
                    if (!$cartItem) {
                        continue;
                    }

                    // Get ordered variations
                    $orderedVariations = $orderedItem['variations'] ?? null;
                    if (is_string($orderedVariations)) {
                        $decoded = json_decode($orderedVariations, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $orderedVariations = $decoded;
                        }
                    }

                    // Get cart item variations
                    $cartVariations = $cartItem->variations;
                    if (is_string($cartVariations)) {
                        $decoded = json_decode($cartVariations, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $cartVariations = $decoded;
                        }
                    }

                    // If cart item has multiple variations (array of variations)
                    if (is_array($cartVariations) && isset($cartVariations[0]) && is_array($cartVariations[0])) {
                        // Get ordered variation IDs/SKUs
                        $orderedVariationIds = [];
                        if (is_array($orderedVariations) && isset($orderedVariations[0])) {
                            // Multiple ordered variations
                            foreach ($orderedVariations as $orderedVar) {
                                if (isset($orderedVar['id'])) {
                                    $orderedVariationIds[] = $orderedVar['id'];
                                } elseif (isset($orderedVar['sku_id'])) {
                                    $orderedVariationIds[] = $orderedVar['sku_id'];
                                }
                            }
                        } elseif (is_array($orderedVariations)) {
                            // Single ordered variation
                            if (isset($orderedVariations['id'])) {
                                $orderedVariationIds[] = $orderedVariations['id'];
                            } elseif (isset($orderedVariations['sku_id'])) {
                                $orderedVariationIds[] = $orderedVariations['sku_id'];
                            }
                        }

                        // Remove ordered variations from cart item
                        $remainingVariations = [];
                        $remainingQuantity = 0;
                        $remainingSubtotal = 0;

                        foreach ($cartVariations as $cartVar) {
                            $cartVarId = $cartVar['id'] ?? $cartVar['sku_id'] ?? null;
                            
                            // Check if this variation was ordered
                            $wasOrdered = false;
                            if ($cartVarId && in_array($cartVarId, $orderedVariationIds)) {
                                // Check if the ordered quantity matches or exceeds cart quantity
                                foreach ($orderedVariations as $orderedVar) {
                                    $orderedVarId = $orderedVar['id'] ?? $orderedVar['sku_id'] ?? null;
                                    if ($cartVarId === $orderedVarId) {
                                        $orderedQty = intval($orderedVar['quantity'] ?? 0);
                                        $cartVarQty = intval($cartVar['quantity'] ?? 0);
                                        
                                        if ($orderedQty >= $cartVarQty) {
                                            // All of this variation was ordered, remove it
                                            $wasOrdered = true;
                                        } else {
                                            // Partial quantity ordered, reduce the quantity
                                            $cartVar['quantity'] = $cartVarQty - $orderedQty;
                                            $cartVarPrice = floatval($cartVar['price'] ?? $cartVar['original_price'] ?? 0);
                                            $cartVar['subtotal'] = $cartVar['quantity'] * $cartVarPrice;
                                            $remainingVariations[] = $cartVar;
                                            $remainingQuantity += $cartVar['quantity'];
                                            $remainingSubtotal += $cartVar['subtotal'];
                                        }
                                        break;
                                    }
                                }
                            }

                            if (!$wasOrdered) {
                                // This variation was not ordered, keep it
                                $remainingVariations[] = $cartVar;
                                $remainingQuantity += intval($cartVar['quantity'] ?? 0);
                                $cartVarPrice = floatval($cartVar['price'] ?? $cartVar['original_price'] ?? 0);
                                $remainingSubtotal += (intval($cartVar['quantity'] ?? 0) * $cartVarPrice);
                            }
                        }

                        // Update or delete cart item
                        if (empty($remainingVariations)) {
                            // All variations were ordered, delete the cart item
                            $cartItem->delete();
                        } else {
                            // Update cart item with remaining variations
                            $cartItem->variations = $remainingVariations;
                            $cartItem->quantity = $remainingQuantity;
                            $cartItem->subtotal = $remainingSubtotal;
                            $cartItem->save();
                        }
                    } else {
                        // Cart item has single variation or no variations structure
                        // If the entire item was ordered, delete it
                        $cartItem->delete();
                    }
                }

                // Cart total is calculated automatically via accessor
                // No need to save it as it's computed from items
            }

            DB::commit();

            // Format response with all created orders
            return response()->json([
                'success' => true,
                'message' => count($createdOrders) . ' order(s) created successfully',
                'data' => [
                    'orders' => array_map(function ($order) {
                        return [
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                            'subtotal' => $order->subtotal,
                            'original_price' => $order->original_price,
                            'paid_amount' => $order->paid_amount,
                            'due_amount' => $order->due_amount,
                            'shipping_cost' => $order->shipping_cost,
                            'shipping_method' => $order->shipping_method,
                            'tax_amount' => $order->tax_amount,
                            'total_amount' => $order->total_amount,
                            'status' => $order->status,
                            'payment_method' => $order->payment_method,
                            'payment_method_id' => $order->payment_method_id ?? null,
                            'payment_status' => $order->payment_status,
                            'transaction_number' => $order->transaction_number,
                            'payment_receipt_url' => $order->payment_receipt_url,
                            'shipping_address' => is_string($order->shipping_address) ? json_decode($order->shipping_address, true) ?? $order->shipping_address : $order->shipping_address,
                            'notes' => $order->notes,
                            'items' => $order->orderItems->map(function ($item) {
                                return [
                                    'product_id' => $item->product_id,
                                    'product_code' => $item->product_code,
                                    'product_name' => $item->product_name,
                                    'product_image_url' => $item->product_image_url,
                                    'product_sku' => $item->product_sku,
                                    'quantity' => $item->quantity,
                                    'price' => $item->price,
                                    'total' => $item->total,
                                    'variations' => is_string($item->variations) ? json_decode($item->variations, true) : $item->variations,
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
                        ];
                    }, $createdOrders),
                    'total_orders' => count($createdOrders),
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
                    'original_price' => $order->original_price,
                    'paid_amount' => $order->paid_amount,
                    'due_amount' => $order->due_amount,
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

    /**
     * Helper method to create an order for an item with all its variations
     */
    private function createOrderForItem(
        $customer,
        $item,
        $variations,
        $request,
        $settings,
        $taxRate,
        $taxInclusive,
        $shippingAddressJson,
        $paymentReceiptPath
    ) {
        try {
            $orderItemsData = [];
            $totalSubtotal = 0;
            $totalOriginalPrice = 0; // Track original price before 70% discount

            // Get product image
            $productImage = null;
            if (isset($item['product_image'])) {
                $productImage = $item['product_image'];
            } elseif (isset($item['product_image_url'])) {
                $productImage = $item['product_image_url'];
            }

            // Process variations - if it's an array of variations, loop through each
            if (is_array($variations) && isset($variations[0]) && is_array($variations[0])) {
                // Multiple variations - create order item for each variation
                foreach ($variations as $variation) {
                    if (!is_array($variation)) {
                        continue;
                    }

                    // Get price from variation
                    $itemPrice = 0;
                    if (isset($variation['price'])) {
                        $itemPrice = floatval($variation['price']);
                    } elseif (isset($variation['original_price'])) {
                        $itemPrice = floatval($variation['original_price']);
                    } elseif (isset($item['product_price'])) {
                        $itemPrice = floatval($item['product_price']);
                    }

                    // Get quantity from variation
                    $quantity = isset($variation['quantity']) ? intval($variation['quantity']) : intval($item['quantity'] ?? 1);
                    
                    if ($quantity <= 0 || $itemPrice <= 0) {
                        continue; // Skip invalid variations
                    }

                    // Calculate original price total (before discount)
                    $originalItemTotal = $itemPrice * $quantity;
                    $totalOriginalPrice += $originalItemTotal;

                    // Calculate 70% of the price (payable amount)
                    $payablePrice = $itemPrice * 0.70;
                    $itemSubtotal = $payablePrice * $quantity;
                    $totalSubtotal += $itemSubtotal;

                    // Build product name
                    $productName = $item['product_name'] ?? 'Product';
                    if (isset($variation['props_names'])) {
                        $productName .= ' - ' . $variation['props_names'];
                    }
                    if (isset($variation['sku_id'])) {
                        $productName .= ' (SKU: ' . $variation['sku_id'] . ')';
                    }

                    // Get product code and SKU
                    $productCode = isset($variation['sku_id']) ? $variation['sku_id'] : ($item['product_code'] ?? null);
                    $productSku = isset($variation['sku_id']) ? $variation['sku_id'] : ($item['product_sku'] ?? null);

                    // Store order item data
                    $orderItemsData[] = [
                        'product_id' => $item['product_id'] ?? null,
                        'product_code' => $productCode,
                        'product_name' => $productName,
                        'product_image' => $productImage,
                        'product_sku' => $productSku,
                        'quantity' => $quantity,
                        'price' => $payablePrice,
                        'total' => $itemSubtotal,
                        'variations' => json_encode($variation),
                    ];
                }
            } else {
                // Single variation object or no variations - create one order item
                $itemPrice = 0;
                if (is_array($variations) && isset($variations['price'])) {
                    $itemPrice = floatval($variations['price']);
                } elseif (is_array($variations) && isset($variations['original_price'])) {
                    $itemPrice = floatval($variations['original_price']);
                } elseif (isset($item['product_price'])) {
                    $itemPrice = floatval($item['product_price']);
                }

                $quantity = intval($item['quantity'] ?? 1);
                
                if ($quantity > 0 && $itemPrice > 0) {
                    // Calculate original price total (before discount)
                    $totalOriginalPrice = $itemPrice * $quantity;

                    // Calculate 70% of the price (payable amount)
                    $payablePrice = $itemPrice * 0.70;
                    $itemSubtotal = $payablePrice * $quantity;
                    $totalSubtotal = $itemSubtotal;

                    // Build product name
                    $productName = $item['product_name'] ?? 'Product';
                    if (is_array($variations) && isset($variations['props_names'])) {
                        $productName .= ' - ' . $variations['props_names'];
                    }
                    if (is_array($variations) && isset($variations['sku_id'])) {
                        $productName .= ' (SKU: ' . $variations['sku_id'] . ')';
                    }

                    // Get product code and SKU
                    $productCode = null;
                    if (is_array($variations) && isset($variations['sku_id'])) {
                        $productCode = $variations['sku_id'];
                    } elseif (isset($item['product_code'])) {
                        $productCode = $item['product_code'];
                    }

                    $productSku = null;
                    if (is_array($variations) && isset($variations['sku_id'])) {
                        $productSku = $variations['sku_id'];
                    } elseif (isset($item['product_sku'])) {
                        $productSku = $item['product_sku'];
                    }

                    // Store order item data
                    $orderItemsData[] = [
                        'product_id' => $item['product_id'] ?? null,
                        'product_code' => $productCode,
                        'product_name' => $productName,
                        'product_image' => $productImage,
                        'product_sku' => $productSku,
                        'quantity' => $quantity,
                        'price' => $payablePrice,
                        'total' => $itemSubtotal,
                        'variations' => is_array($variations) ? json_encode($variations) : (is_string($variations) ? $variations : null),
                    ];
                }
            }

            // Skip if no valid order items
            if (empty($orderItemsData) || $totalSubtotal <= 0) {
                return null;
            }

            // Calculate shipping cost (per order)
            $shippingCost = $settings->shipping_cost ?? 0;
            
            // Calculate tax
            if ($taxInclusive) {
                // Tax is already included in product prices
                $taxAmount = ($totalSubtotal / (100 + $taxRate)) * $taxRate;
            } else {
                // Tax needs to be added
                $taxAmount = ($totalSubtotal * $taxRate) / 100;
            }

            $totalAmount = $totalSubtotal + $shippingCost;
            if (!$taxInclusive) {
                $totalAmount += $taxAmount;
            }

            // Calculate price breakdown
            // original_price = total original price before 70% discount
            // paid_amount = 70% of original (which is the subtotal)
            // due_amount = 30% of original (remaining amount)
            $paidAmount = $totalSubtotal; // 70% of original
            $dueAmount = $totalOriginalPrice - $paidAmount; // 30% of original

            // Generate unique order number
            $orderNumber = 'ORD-' . strtoupper(uniqid()) . '-' . time();

            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'subtotal' => $totalSubtotal,
                'original_price' => $totalOriginalPrice,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'discount_amount' => 0,
                'shipping_cost' => $shippingCost,
                'shipping_method' => $request->shipping_method ?? 'ship',
                'tax_amount' => $taxAmount,
                'tax_rate' => $taxRate,
                'tax_inclusive' => $taxInclusive,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_method' => $request->payment_method ?? 'manual',
                'payment_method_id' => $request->payment_method_id ?? null,
                'payment_status' => 'pending',
                'transaction_number' => $request->transaction_number,
                'payment_receipt_image' => $paymentReceiptPath ? Storage::url($paymentReceiptPath) : null,
                'shipping_address' => $shippingAddressJson,
                'notes' => $request->notes,
            ]);

            // Create order items
            foreach ($orderItemsData as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'product_code' => $itemData['product_code'],
                    'product_name' => $itemData['product_name'],
                    'product_image' => $itemData['product_image'],
                    'product_sku' => $itemData['product_sku'],
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'], // 70% price
                    'total' => $itemData['total'],
                    'variations' => $itemData['variations'],
                ]);
            }

            // Record initial status in history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'old_status' => null,
                'new_status' => 'pending',
                'changed_by_type' => 'customer',
                'changed_by_id' => $customer->id,
                'notes' => 'Order created',
            ]);

            // Load order with items and status history
            $order->load('orderItems', 'statusHistory');
            
            return $order;
        } catch (\Exception $e) {
            // Log error but continue with other orders
            \Log::error('Failed to create order for item: ' . $e->getMessage());
            return null;
        }
    }
}

