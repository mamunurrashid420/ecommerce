<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['customer', 'orderItems.product'])->latest()->paginate(10);
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'shipping_address' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            $totalAmount = 0;
            $orderNumber = 'ORD-' . time() . '-' . rand(1000, 9999);

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $request->customer_id,
                'total_amount' => 0,
                'shipping_address' => $request->shipping_address,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $price = $product->price;
                $total = $price * $item['quantity'];
                $totalAmount += $total;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'total' => $total,
                ]);

                // Update stock
                $product->decrement('stock_quantity', $item['quantity']);
            }

            $order->update(['total_amount' => $totalAmount]);
            return response()->json($order->load(['customer', 'orderItems.product']), 201);
        });
    }

    public function show(Order $order)
    {
        return response()->json($order->load(['customer', 'orderItems.product']));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order->update(['status' => $request->status]);
        return response()->json($order->load(['customer', 'orderItems.product']));
    }
}
