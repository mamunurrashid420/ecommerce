<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Get all orders (Admin only)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $filters = [
                'status' => $request->get('status'),
                'customer_id' => $request->get('customer_id'),
                'search' => $request->get('search'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
                'sort_by' => $request->get('sort_by', 'created_at'),
                'sort_order' => $request->get('sort_order', 'desc'),
            ];

            $perPage = $request->get('per_page', 15);
            $result = $this->orderService->getOrders($filters, $perPage);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer's orders
     * If admin accesses this route, they get all orders (same as index)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function customerOrders(Request $request)
    {
        try {
            // If admin, return all orders (same as index method)
            if ($this->isAdmin()) {
                return $this->index($request);
            }
            
            // For customers, return only their orders
            $customer = $this->getAuthenticatedCustomer();
            
            $filters = [
                'status' => $request->get('status'),
                'search' => $request->get('search'),
                'sort_by' => $request->get('sort_by', 'created_at'),
                'sort_order' => $request->get('sort_order', 'desc'),
            ];

            $perPage = $request->get('per_page', 15);
            $result = $this->orderService->getCustomerOrders($customer->id, $filters, $perPage);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new order
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'shipping_address' => 'required|string|max:500',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'notes' => 'nullable|string|max:1000',
                'coupon_code' => 'nullable|string|max:50',
            ]);

            $customer = $this->getAuthenticatedCustomer();

            $data = [
                'shipping_address' => $request->shipping_address,
                'items' => $request->items,
                'notes' => $request->notes,
                'coupon_code' => $request->coupon_code,
            ];

            $result = $this->orderService->createOrder($data, $customer->id);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $result['order']
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get a single order
     * 
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Order $order)
    {
        try {
            // If admin, allow access to any order
            if ($this->isAdmin()) {
                $result = $this->orderService->getOrder($order->id);
            } else {
                // For customers, verify ownership
                $customer = $this->getAuthenticatedCustomer();
                $result = $this->orderService->getOrder($order->id, $customer->id);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getMessage() === 'Unauthorized access to this order' ? 403 : 404);
        }
    }

    /**
     * Update order status (Admin only)
     * 
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Order $order)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            ]);

            $result = $this->orderService->updateOrderStatus($order->id, $request->status);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $result['order']
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete an order (Admin only)
     * 
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Order $order)
    {
        try {
            $result = $this->orderService->deleteOrder($order->id);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getMessage() === 'Cannot delete delivered orders. Consider cancelling instead.' ? 409 : 500);
        }
    }

    /**
     * Get order statistics (Admin only)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats(Request $request)
    {
        try {
            $filters = [
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
            ];

            $result = $this->orderService->getOrderStats($filters);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated customer
     * 
     * @return Customer
     * @throws \Exception
     */
    protected function getAuthenticatedCustomer()
    {
        $user = auth()->user();
        
        if (!$user) {
            throw new \Exception('Unauthenticated');
        }

        // Check if authenticated user is a Customer model
        if ($user instanceof Customer) {
            return $user;
        }

        // If it's a User model (admin), we can't create orders as admin
        // This should not happen in customer routes, but handle it gracefully
        throw new \Exception('Customer authentication required');
    }

    /**
     * Check if authenticated user is admin
     * 
     * @return bool
     */
    protected function isAdmin()
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Check if it's a User model with admin role
        if ($user instanceof \App\Models\User) {
            return $user->isAdmin();
        }

        return false;
    }
}
