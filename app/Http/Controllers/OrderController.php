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
                'payment_status' => $request->get('payment_status'),
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
                'payment_status' => $request->get('payment_status'),
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
     * Get valid next statuses for an order
     * GET /api/orders/{order}/next-statuses
     * 
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNextStatuses(Order $order)
    {
        try {
            $nextStatuses = $this->orderService->getValidNextStatuses($order->status);

            return response()->json([
                'success' => true,
                'data' => [
                    'current_status' => $order->status,
                    'next_statuses' => $nextStatuses,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get all order status transitions
     * GET /api/orders/status-transitions
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatusTransitions()
    {
        try {
            // Get all statuses and their transitions
            $allStatuses = [
                'pending',
                'confirm',
                'cancelled',
                'delivered',
            ];

            $transitions = [];
            foreach ($allStatuses as $status) {
                $transitions[$status] = $this->orderService->getValidNextStatuses($status);
            }

            return response()->json([
                'success' => true,
                'data' => $transitions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
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
                'status' => [
                    'required',
                    'in:pending,confirm,purchasing,purchase_completed,shipped_from_supplier,received_in_china_warehouse,on_the_way_to_china_airport,received_in_china_airport,on_the_way_to_bd_airport,received_in_bd_airport,on_the_way_to_bd_warehouse,received_in_bd_warehouse,processing_for_delivery,on_the_way_to_delivery,delivered,completed,processing_for_refund,refunded,cancelled'
                ],
                'notes' => 'nullable|string|max:1000',
            ]);

            // Get the authenticated admin user
            $user = auth()->user();
            $changedByType = null;
            $changedById = null;

            if ($user instanceof \App\Models\User) {
                $changedByType = 'admin';
                $changedById = $user->id;
            }

            $result = $this->orderService->updateOrderStatus(
                $order->id,
                $request->status,
                $changedByType,
                $changedById,
                $request->notes
            );

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
                'message' => 'Failed to update status',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update order payment status (Admin)
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updatePaymentStatus(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'payment_status' => [
                    'required',
                    'in:pending,partially_paid,paid,refunded'
                ],
                'notes' => 'nullable|string|max:1000',
            ]);

            $result = $this->orderService->updatePaymentStatus(
                $id,
                $request->payment_status,
                'admin',
                auth()->id(),
                $request->notes
            );

            return response()->json($result);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment status',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update order amounts (Admin only)
     * 
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAmounts(Request $request, Order $order)
    {
        try {
            $request->validate([
                'subtotal' => 'nullable|numeric|min:0',
                'discount_amount' => 'nullable|numeric|min:0',
                'shipping_cost' => 'nullable|numeric|min:0',
                'tax_amount' => 'nullable|numeric|min:0',
                'tax_rate' => 'nullable|numeric|min:0|max:100',
                'tax_inclusive' => 'nullable|boolean',
            ]);

            $amounts = $request->only([
                'subtotal',
                'discount_amount',
                'shipping_cost',
                'tax_amount',
                'tax_rate',
                'tax_inclusive'
            ]);

            // Remove null values
            $amounts = array_filter($amounts, function ($value) {
                return $value !== null;
            });

            if (empty($amounts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least one amount field must be provided'
                ], 422);
            }

            $result = $this->orderService->updateOrderAmounts($order->id, $amounts);

            return response()->json($result);
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
     * Request order cancellation (Customer only)
     * 
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestCancellation(Request $request, Order $order)
    {
        try {
            $request->validate([
                'reason' => 'nullable|string|max:1000',
            ]);

            $customer = $this->getAuthenticatedCustomer();

            $result = $this->orderService->requestCancellation(
                $order->id,
                $customer->id,
                $request->reason
            );

            return response()->json($result);
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
     * Approve cancellation request (Admin only)
     * 
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function approveCancellation(Order $order)
    {
        try {
            $result = $this->orderService->approveCancellation($order->id);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Reject cancellation request (Admin only)
     * 
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectCancellation(Request $request, Order $order)
    {
        try {
            $request->validate([
                'admin_note' => 'nullable|string|max:1000',
            ]);

            $result = $this->orderService->rejectCancellation(
                $order->id,
                $request->admin_note
            );

            return response()->json($result);
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
     * Cancel order directly (Admin or Customer for pending orders)
     * 
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelOrder(Request $request, Order $order)
    {
        try {
            $request->validate([
                'reason' => 'nullable|string|max:1000',
            ]);

            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            // Determine who is cancelling
            $cancelledBy = $this->isAdmin() ? 'admin' : 'customer';

            // If customer, verify ownership
            if ($cancelledBy === 'customer') {
                if (!($user instanceof \App\Models\Customer)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Customer authentication required for this action'
                    ], 403);
                }

                if ($order->customer_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access to this order'
                    ], 403);
                }
            }

            $result = $this->orderService->cancelOrder(
                $order->id,
                $cancelledBy,
                $request->reason
            );

            return response()->json($result);
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
     * Get pending cancellation requests (Admin only)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pendingCancellations(Request $request)
    {
        try {
            $filters = [
                'customer_id' => $request->get('customer_id'),
                'search' => $request->get('search'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
                'sort_by' => $request->get('sort_by', 'cancellation_requested_at'),
                'sort_order' => $request->get('sort_order', 'desc'),
            ];

            $perPage = $request->get('per_page', 15);
            $result = $this->orderService->getPendingCancellationRequests($filters, $perPage);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pending cancellation requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update partial payment (Admin only)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpdatePartialPayment(Request $request)
    {
        try {
            $request->validate([
                'order_ids' => 'required|array|min:1',
                'order_ids.*' => 'required|integer|exists:orders,id',
            ]);

            $result = $this->orderService->bulkUpdatePartialPayment($request->order_ids);

            return response()->json($result);
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
     * Bulk make paid payment status (Admin only)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkMakePaid(Request $request)
    {
        try {
            $request->validate([
                'order_ids' => 'required|array|min:1',
                'order_ids.*' => 'required|integer|exists:orders,id',
            ]);

            $result = $this->orderService->bulkMakePaid($request->order_ids);

            return response()->json($result);
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
     * View invoice for an order (opens in browser)
     * 
     * @param Request $request
     * @param mixed $order
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function viewInvoice(Request $request, $order)
    {
        try {
            // Check authentication - support both Bearer token and token query parameter
            $token = $request->bearerToken() ?? $request->query('token');

            if ($token) {
                // Validate token from query parameter
                $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
                if (!$accessToken) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized'
                    ], 401);
                }
                // Set the authenticated user
                auth()->setUser($accessToken->tokenable);
            } else {
                // If no token provided, check if user is authenticated via session/middleware
                if (!auth()->check()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized'
                    ], 401);
                }
            }

            // Find order by ID or order number
            $orderModel = Order::where('id', $order)
                ->orWhere('order_number', $order)
                ->first();

            if (!$orderModel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Check if invoice exists
            if (empty($orderModel->invoice_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found for this order'
                ], 404);
            }

            // Check if file exists
            if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($orderModel->invoice_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice file not found'
                ], 404);
            }

            // Get file path
            $filePath = \Illuminate\Support\Facades\Storage::disk('public')->path($orderModel->invoice_path);

            // Return file for viewing (inline) instead of download
            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="invoice_' . $orderModel->order_number . '.pdf"',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to view invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download invoice for an order
     * 
     * @param Request $request
     * @param mixed $order
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function downloadInvoice(Request $request, $order)
    {
        try {
            // Find order by ID or order number
            $orderModel = Order::where('id', $order)
                ->orWhere('order_number', $order)
                ->first();

            if (!$orderModel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Check if invoice exists
            if (empty($orderModel->invoice_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found for this order'
                ], 404);
            }

            // Check if file exists
            if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($orderModel->invoice_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice file not found'
                ], 404);
            }

            // Get file path
            $filePath = \Illuminate\Support\Facades\Storage::disk('public')->path($orderModel->invoice_path);

            // Return file download response
            return response()->download($filePath, 'invoice_' . $orderModel->order_number . '.pdf', [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download invoice: ' . $e->getMessage()
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
