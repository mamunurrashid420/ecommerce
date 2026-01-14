<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderCancellationRequestNotification;
use App\Notifications\OrderCancellationApprovedNotification;
use App\Notifications\OrderCancellationRejectedNotification;
use App\Notifications\OrderCancelledNotification;
use App\Services\InventoryService;
use App\Services\PurchaseService;
use App\Services\CouponService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class OrderService
{
    protected $inventoryService;
    protected $purchaseService;
    protected $couponService;

    public function __construct(
        InventoryService $inventoryService,
        PurchaseService $purchaseService,
        CouponService $couponService
    ) {
        $this->inventoryService = $inventoryService;
        $this->purchaseService = $purchaseService;
        $this->couponService = $couponService;
    }

    /**
     * Create a new order
     * 
     * @param array $data
     * @param int $customerId
     * @return array
     * @throws Exception
     */
    public function createOrder(array $data, int $customerId): array
    {
        DB::beginTransaction();
        
        try {
            // Validate customer exists
            $customer = Customer::findOrFail($customerId);
            
            // Check if customer is banned or suspended
            if ($customer->isBanned()) {
                throw new Exception("Your account has been banned. Reason: " . ($customer->ban_reason ?? 'No reason provided'));
            }
            
            if ($customer->isSuspended()) {
                throw new Exception("Your account has been suspended. Reason: " . ($customer->suspend_reason ?? 'No reason provided'));
            }
            
            // Validate customer can make purchase
            $this->purchaseService->validateCustomer($customerId);
            
            // Validate purchase items with database locks to prevent race conditions
            $items = $data['items'];
            $validation = $this->purchaseService->validatePurchaseItemsWithLock($items);
            $validatedItems = $validation['validated_items'];
            
            // Generate unique order number
            $orderNumber = $this->generateOrderNumber();
            
            // Get site settings for shipping and tax calculations
            $siteSettings = \App\Models\SiteSetting::getInstance();
            
            // Calculate subtotal from validated items
            $subtotal = $validation['total_amount'];
            $discountAmount = 0;
            $afterDiscountAmount = $subtotal;
            $couponId = null;
            $couponCode = null;
            
            // Apply coupon if provided
            if (!empty($data['coupon_code'])) {
                try {
                    $couponResult = $this->couponService->validateAndCalculateDiscount(
                        $data['coupon_code'],
                        $items,
                        $customerId
                    );
                    
                    $subtotal = $couponResult['subtotal'];
                    $discountAmount = $couponResult['discount_amount'];
                    $afterDiscountAmount = $couponResult['total_after_discount'];
                    $couponId = $couponResult['coupon']->id;
                    $couponCode = $couponResult['coupon']->code;
                } catch (Exception $e) {
                    // Log coupon error but don't fail order creation
                    Log::warning('Coupon application failed during order creation', [
                        'coupon_code' => $data['coupon_code'],
                        'customer_id' => $customerId,
                        'error' => $e->getMessage()
                    ]);
                    // Continue without coupon
                }
            }
            
            // Calculate shipping cost
            $shippingCost = 0;
            if ($siteSettings->free_shipping_threshold && $afterDiscountAmount >= $siteSettings->free_shipping_threshold) {
                $shippingCost = 0; // Free shipping
            } else {
                $shippingCost = $siteSettings->shipping_cost ?? 0;
            }
            
            // Calculate tax
            $taxRate = $siteSettings->tax_rate ?? 0;
            $taxInclusive = $siteSettings->tax_inclusive ?? false;
            $taxAmount = 0;
            
            if ($taxRate > 0) {
                if ($taxInclusive) {
                    // Tax is already included in product prices
                    // Calculate the tax amount that's already included
                    $taxAmount = $afterDiscountAmount * ($taxRate / (100 + $taxRate));
                } else {
                    // Tax is added on top
                    $taxAmount = $afterDiscountAmount * ($taxRate / 100);
                }
            }
            
            // Calculate final total
            $totalAmount = $afterDiscountAmount + $shippingCost;
            if (!$taxInclusive && $taxAmount > 0) {
                $totalAmount += $taxAmount;
            }
            
            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customerId,
                'coupon_id' => $couponId,
                'coupon_code' => $couponCode,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'shipping_cost' => $shippingCost,
                'tax_amount' => $taxAmount,
                'tax_rate' => $taxRate,
                'tax_inclusive' => $taxInclusive,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'shipping_address' => $data['shipping_address'],
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Record initial status in history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'old_status' => null,
                'new_status' => 'pending',
                'changed_by_type' => 'customer',
                'changed_by_id' => $customerId,
                'notes' => 'Order created',
            ]);
            
            // Create order items and reserve stock
            foreach ($validatedItems as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                    'total' => $itemData['total'],
                ]);
                
                // Reserve stock using InventoryService
                $this->inventoryService->reserveStock(
                    $itemData['product_id'],
                    $itemData['quantity'],
                    $order->id
                );
            }
            
            // Record coupon usage if coupon was applied
            if ($couponId) {
                $this->couponService->recordUsage(
                    $couponId,
                    $order->id,
                    $customerId,
                    $discountAmount,
                    $subtotal,
                    $totalAmount
                );
            }
            
            DB::commit();
            
            // Load customer relationship for notifications
            $order->load('customer');
            
            // Send notifications to all admin users
            try {
                $adminUsers = User::where('role', 'admin')->get();
                foreach ($adminUsers as $admin) {
                    $admin->notify(new NewOrderNotification($order));
                }
            } catch (Exception $e) {
                // Log notification error but don't fail the order creation
                Log::warning('Failed to send order notification to admin users', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Load full order relationships for response
            $order->load(['customer', 'orderItems.product']);
            
            $response = [
                'success' => true,
                'order' => $order,
            ];
            
            // Include warnings if any
            if (!empty($validation['warnings'])) {
                $response['warnings'] = $validation['warnings'];
            }
            
            return $response;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Get orders with filtering and pagination (Admin)
     * 
     * @param array $filters
     * @param int $perPage
     * @return array
     */
    public function getOrders(array $filters = [], int $perPage = 15): array
    {
        $query = Order::with(['customer', 'orderItems.product', 'coupon']);
        
        // Filter by status
        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }
        
        // Filter by customer
        if (isset($filters['customer_id']) && $filters['customer_id']) {
            $query->where('customer_id', $filters['customer_id']);
        }
        
        // Search by order number
        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Date range filter
        if (isset($filters['date_from']) && $filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to']) && $filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        
        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);
        
        $orders = $query->paginate($perPage);
        
        return [
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ];
    }

    /**
     * Get orders for a specific customer
     * 
     * @param int $customerId
     * @param array $filters
     * @param int $perPage
     * @return array
     */
    public function getCustomerOrders(int $customerId, array $filters = [], int $perPage = 15): array
    {
        $query = Order::with(['orderItems.product', 'coupon'])
            ->where('customer_id', $customerId);
        
        // Filter by status
        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }
        
        // Search by order number
        if (isset($filters['search']) && $filters['search']) {
            $query->where('order_number', 'like', "%{$filters['search']}%");
        }
        
        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);
        
        $orders = $query->paginate($perPage);
        
        return [
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ];
    }

    /**
     * Get a single order by ID
     * 
     * @param int $orderId
     * @param int|null $customerId Optional customer ID for authorization check
     * @return array
     * @throws Exception
     */
    public function getOrder(int $orderId, ?int $customerId = null): array
    {
        $order = Order::with(['customer', 'orderItems.product', 'coupon', 'couponUsage', 'statusHistory'])->findOrFail($orderId);
        
        // If customer ID is provided, verify ownership
        if ($customerId !== null && $order->customer_id !== $customerId) {
            throw new Exception('Unauthorized access to this order');
        }
        
        return [
            'success' => true,
            'order' => $order,
        ];
    }

    /**
     * Update order status
     * 
     * @param int $orderId
     * @param string $status
     * @param string|null $changedByType
     * @param int|null $changedById
     * @param string|null $notes
     * @return array
     * @throws Exception
     */
    public function updateOrderStatus(int $orderId, string $status, ?string $changedByType = null, ?int $changedById = null, ?string $notes = null): array
    {
        $validStatuses = [
            'cancelled',
            'pending_payment',
            'pending_payment_verification',
            'partially_paid',
            'purchasing',
            'purchase_completed',
            'shipped_from_supplier',
            'received_in_china_warehouse',
            'on_the_way_to_china_airport',
            'received_in_china_airport',
            'on_the_way_to_bd_airport',
            'received_in_bd_airport',
            'on_the_way_to_bd_warehouse',
            'received_in_bd_warehouse',
            'processing_for_delivery',
            'on_the_way_to_delivery',
            'completed',
            'processing_for_refund',
            'refunded',
            // Legacy statuses for backward compatibility
            'pending',
            'processing',
            'shipped',
            'delivered',
        ];
        
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Invalid status. Must be one of: " . implode(', ', $validStatuses));
        }
        
        DB::beginTransaction();
        
        try {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            $oldStatus = $order->status;
            
            // Skip if status hasn't changed
            if ($oldStatus === $status) {
                DB::commit();
                return [
                    'success' => true,
                    'order' => $order->load(['customer', 'orderItems.product', 'statusHistory']),
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'message' => 'Status unchanged'
                ];
            }
            
            // Validate status transition
            $this->validateStatusTransition($oldStatus, $status);
            
            $order->status = $status;
            $order->save();
            
            // Record status change in history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'changed_by_type' => $changedByType ?? 'system',
                'changed_by_id' => $changedById,
                'notes' => $notes,
            ]);
            
            // Handle cancellation - stock release removed
            // No need to release stock on cancellation
            
            DB::commit();
            
            return [
                'success' => true,
                'order' => $order->load(['customer', 'orderItems.product', 'statusHistory']),
                'old_status' => $oldStatus,
                'new_status' => $status,
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Order status update failed', [
                'order_id' => $orderId,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update order amounts (Admin only)
     * 
     * @param int $orderId
     * @param array $amounts
     * @return array
     * @throws Exception
     */
    public function updateOrderAmounts(int $orderId, array $amounts): array
    {
        DB::beginTransaction();
        
        try {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            
            // Validate amounts
            if (isset($amounts['subtotal']) && (!is_numeric($amounts['subtotal']) || $amounts['subtotal'] < 0)) {
                throw new Exception('Subtotal must be a non-negative number');
            }
            
            if (isset($amounts['discount_amount']) && (!is_numeric($amounts['discount_amount']) || $amounts['discount_amount'] < 0)) {
                throw new Exception('Discount amount must be a non-negative number');
            }
            
            if (isset($amounts['shipping_cost']) && (!is_numeric($amounts['shipping_cost']) || $amounts['shipping_cost'] < 0)) {
                throw new Exception('Shipping cost must be a non-negative number');
            }
            
            if (isset($amounts['tax_amount']) && (!is_numeric($amounts['tax_amount']) || $amounts['tax_amount'] < 0)) {
                throw new Exception('Tax amount must be a non-negative number');
            }
            
            if (isset($amounts['tax_rate']) && (!is_numeric($amounts['tax_rate']) || $amounts['tax_rate'] < 0)) {
                throw new Exception('Tax rate must be a non-negative number');
            }
            
            // Update amounts
            if (isset($amounts['subtotal'])) {
                $order->subtotal = $amounts['subtotal'];
            }
            
            if (isset($amounts['discount_amount'])) {
                $order->discount_amount = $amounts['discount_amount'];
            }
            
            if (isset($amounts['shipping_cost'])) {
                $order->shipping_cost = $amounts['shipping_cost'];
            }
            
            if (isset($amounts['tax_amount'])) {
                $order->tax_amount = $amounts['tax_amount'];
            }
            
            if (isset($amounts['tax_rate'])) {
                $order->tax_rate = $amounts['tax_rate'];
            }
            
            if (isset($amounts['tax_inclusive'])) {
                $order->tax_inclusive = (bool) $amounts['tax_inclusive'];
            }
            
            // Recalculate total amount
            $subtotal = $order->subtotal ?? 0;
            $discountAmount = $order->discount_amount ?? 0;
            $shippingCost = $order->shipping_cost ?? 0;
            $taxAmount = $order->tax_amount ?? 0;
            
            $totalAmount = $subtotal - $discountAmount + $shippingCost;
            
            // Add tax if not inclusive
            if (!$order->tax_inclusive) {
                $totalAmount += $taxAmount;
            }
            
            $order->total_amount = $totalAmount;
            
            // Update payment status to 'paid' if it was 'pending' and set paid_at timestamp
            if ($order->payment_status === 'pending') {
                $order->payment_status = 'paid';
                $order->paid_at = now();
            }
            
            $order->save();
            
            DB::commit();
            
            Log::info('Order amounts updated', [
                'order_id' => $orderId,
                'amounts' => $amounts,
                'new_total' => $totalAmount,
                'payment_status_updated' => $order->payment_status === 'paid'
            ]);
            
            return [
                'success' => true,
                'message' => 'Order amounts updated successfully',
                'order' => $order->load(['customer', 'orderItems.product']),
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Order amounts update failed', [
                'order_id' => $orderId,
                'amounts' => $amounts,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete an order
     * 
     * @param int $orderId
     * @return array
     * @throws Exception
     */
    public function deleteOrder(int $orderId): array
    {
        DB::beginTransaction();
        
        try {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            
            // Prevent deletion of delivered orders
            if ($order->status === 'delivered') {
                throw new Exception('Cannot delete delivered orders. Consider cancelling instead.');
            }
            
            // Stock release removed - no need to release stock on deletion
            
            $orderData = [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
            ];
            
            // Delete order items
            $order->orderItems()->delete();
            
            // Delete order
            $order->delete();
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Order deleted successfully',
                'order' => $orderData,
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Order deletion failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get order statistics
     * 
     * @param array $filters
     * @return array
     */
    public function getOrderStats(array $filters = []): array
    {
        $baseQuery = Order::query();
        
        // Apply date filters if provided
        if (isset($filters['date_from']) && $filters['date_from']) {
            $baseQuery->whereDate('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to']) && $filters['date_to']) {
            $baseQuery->whereDate('created_at', '<=', $filters['date_to']);
        }
        
        $totalOrders = (clone $baseQuery)->count();
        $totalRevenue = (clone $baseQuery)->sum('total_amount');
        
        $statusCounts = (clone $baseQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        return [
            'success' => true,
            'stats' => [
                'total_orders' => $totalOrders,
                'total_revenue' => round($totalRevenue, 2),
                'status_breakdown' => [
                    'pending' => $statusCounts['pending'] ?? 0,
                    'processing' => $statusCounts['processing'] ?? 0,
                    'shipped' => $statusCounts['shipped'] ?? 0,
                    'delivered' => $statusCounts['delivered'] ?? 0,
                    'cancelled' => $statusCounts['cancelled'] ?? 0,
                ],
            ],
        ];
    }

    /**
     * Get valid next statuses for a given order status
     * 
     * @param string $currentStatus
     * @return array
     */
    public function getValidNextStatuses(string $currentStatus): array
    {
        $validTransitions = $this->getStatusTransitions();
        
        // Always allow cancellation from any status (except already cancelled)
        $nextStatuses = $validTransitions[$currentStatus] ?? [];
        
        // Add cancelled if not already in the list and current status is not cancelled
        if ($currentStatus !== 'cancelled' && !in_array('cancelled', $nextStatuses)) {
            $nextStatuses[] = 'cancelled';
        }
        
        return $nextStatuses;
    }

    /**
     * Get all status transitions
     * 
     * @return array
     */
    private function getStatusTransitions(): array
    {
        return [
            'cancelled' => [],
            'pending_payment' => ['pending_payment_verification', 'cancelled'],
            'pending_payment_verification' => ['partially_paid', 'purchasing', 'cancelled'],
            'partially_paid' => ['pending_payment_verification', 'purchasing', 'cancelled'],
            'purchasing' => ['purchase_completed', 'cancelled'],
            'purchase_completed' => ['shipped_from_supplier', 'cancelled'],
            'shipped_from_supplier' => ['received_in_china_warehouse', 'cancelled'],
            'received_in_china_warehouse' => ['on_the_way_to_china_airport', 'cancelled'],
            'on_the_way_to_china_airport' => ['received_in_china_airport', 'cancelled'],
            'received_in_china_airport' => ['on_the_way_to_bd_airport', 'cancelled'],
            'on_the_way_to_bd_airport' => ['received_in_bd_airport', 'cancelled'],
            'received_in_bd_airport' => ['on_the_way_to_bd_warehouse', 'cancelled'],
            'on_the_way_to_bd_warehouse' => ['received_in_bd_warehouse', 'cancelled'],
            'received_in_bd_warehouse' => ['processing_for_delivery', 'cancelled'],
            'processing_for_delivery' => ['on_the_way_to_delivery', 'cancelled'],
            'on_the_way_to_delivery' => ['completed', 'cancelled'],
            'completed' => [], // Final state
            'processing_for_refund' => ['refunded', 'cancelled'],
            'refunded' => [], // Final state
            // Legacy statuses for backward compatibility
            'pending' => ['pending_payment_verification', 'processing', 'cancelled'],
            'processing' => ['purchasing', 'shipped', 'cancelled'],
            'shipped' => ['on_the_way_to_delivery', 'delivered', 'cancelled'],
            'delivered' => ['completed'], // Can transition to completed
        ];
    }

    /**
     * Validate status transition
     * 
     * @param string $oldStatus
     * @param string $newStatus
     * @return void
     * @throws Exception
     */
    private function validateStatusTransition(string $oldStatus, string $newStatus): void
    {
        $validTransitions = $this->getStatusTransitions();
        
        // Allow transition to cancelled from any status (except already cancelled)
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            return; // Allow cancellation from any status
        }
        
        if (!isset($validTransitions[$oldStatus])) {
            throw new Exception("Invalid current status: {$oldStatus}");
        }
        
        if (!in_array($newStatus, $validTransitions[$oldStatus])) {
            throw new Exception("Cannot transition from '{$oldStatus}' to '{$newStatus}'");
        }
    }

    /**
     * Request order cancellation (Customer)
     * 
     * @param int $orderId
     * @param int $customerId
     * @param string|null $reason
     * @return array
     * @throws Exception
     */
    public function requestCancellation(int $orderId, int $customerId, ?string $reason = null): array
    {
        DB::beginTransaction();
        
        try {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            
            // Verify ownership
            if ($order->customer_id !== $customerId) {
                throw new Exception('Unauthorized access to this order');
            }
            
            // Check if order can request cancellation
            if (!$order->canRequestCancellation()) {
                throw new Exception('Order cannot be cancelled. Only pending orders without existing cancellation requests can be cancelled.');
            }
            
            // Set cancellation request
            $order->cancellation_requested_at = now();
            $order->cancellation_reason = $reason;
            $order->cancellation_requested_by = 'customer';
            $order->save();
            
            DB::commit();
            
            // Load customer relationship for notifications
            $order->load('customer');
            
            // Send notifications to all admin users
            try {
                $adminUsers = User::where('role', 'admin')->get();
                foreach ($adminUsers as $admin) {
                    $admin->notify(new OrderCancellationRequestNotification($order));
                }
            } catch (Exception $e) {
                // Log notification error but don't fail the cancellation request
                Log::warning('Failed to send cancellation request notification to admin users', [
                    'order_id' => $orderId,
                    'error' => $e->getMessage()
                ]);
            }
            
            Log::info('Order cancellation requested', [
                'order_id' => $orderId,
                'customer_id' => $customerId,
                'reason' => $reason
            ]);
            
            return [
                'success' => true,
                'message' => 'Cancellation request submitted successfully. Waiting for admin approval.',
                'order' => $order->load(['customer', 'orderItems.product']),
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation request failed', [
                'order_id' => $orderId,
                'customer_id' => $customerId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Approve cancellation request (Admin)
     * 
     * @param int $orderId
     * @return array
     * @throws Exception
     */
    public function approveCancellation(int $orderId): array
    {
        DB::beginTransaction();
        
        try {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            
            // Check if there's a pending cancellation request
            if (!$order->hasPendingCancellationRequest()) {
                throw new Exception('No pending cancellation request found for this order');
            }
            
            // Check if order can still be cancelled
            if (!$order->canBeCancelled()) {
                throw new Exception("Order cannot be cancelled. Current status: {$order->status}");
            }
            
            $oldStatus = $order->status;
            
            // Cancel the order
            $order->status = 'cancelled';
            $order->cancelled_at = now();
            $order->cancelled_by = 'admin';
            $order->save();
            
            // Stock release removed - no need to release stock on cancellation
            
            DB::commit();
            
            // Load customer relationship for notifications
            $order->load('customer');
            
            // Send notification to customer
            try {
                $order->customer->notify(new OrderCancellationApprovedNotification($order));
            } catch (Exception $e) {
                // Log notification error but don't fail the cancellation approval
                Log::warning('Failed to send cancellation approval notification to customer', [
                    'order_id' => $orderId,
                    'customer_id' => $order->customer_id,
                    'error' => $e->getMessage()
                ]);
            }
            
            Log::info('Order cancellation approved', [
                'order_id' => $orderId,
                'old_status' => $oldStatus
            ]);
            
            return [
                'success' => true,
                'message' => 'Order cancellation approved successfully',
                'order' => $order->load(['customer', 'orderItems.product']),
                'old_status' => $oldStatus,
                'new_status' => 'cancelled',
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation approval failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Reject cancellation request (Admin)
     * 
     * @param int $orderId
     * @param string|null $adminNote
     * @return array
     * @throws Exception
     */
    public function rejectCancellation(int $orderId, ?string $adminNote = null): array
    {
        DB::beginTransaction();
        
        try {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            
            // Check if there's a pending cancellation request
            if (!$order->hasPendingCancellationRequest()) {
                throw new Exception('No pending cancellation request found for this order');
            }
            
            // Clear cancellation request
            $order->cancellation_requested_at = null;
            $order->cancellation_reason = $adminNote ? 
                ($order->cancellation_reason . "\n\nAdmin Note: " . $adminNote) : 
                $order->cancellation_reason;
            $order->cancellation_requested_by = null;
            $order->save();
            
            DB::commit();
            
            // Load customer relationship for notifications
            $order->load('customer');
            
            // Send notification to customer
            try {
                $order->customer->notify(new OrderCancellationRejectedNotification($order, $adminNote));
            } catch (Exception $e) {
                // Log notification error but don't fail the cancellation rejection
                Log::warning('Failed to send cancellation rejection notification to customer', [
                    'order_id' => $orderId,
                    'customer_id' => $order->customer_id,
                    'error' => $e->getMessage()
                ]);
            }
            
            Log::info('Order cancellation rejected', [
                'order_id' => $orderId,
                'admin_note' => $adminNote
            ]);
            
            return [
                'success' => true,
                'message' => 'Cancellation request rejected',
                'order' => $order->load(['customer', 'orderItems.product']),
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation rejection failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Cancel order directly (Admin or Customer for pending orders)
     * 
     * @param int $orderId
     * @param string $cancelledBy 'customer' or 'admin'
     * @param string|null $reason
     * @return array
     * @throws Exception
     */
    public function cancelOrder(int $orderId, string $cancelledBy, ?string $reason = null): array
    {
        DB::beginTransaction();
        
        try {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            
            // Validate cancelled_by value
            if (!in_array($cancelledBy, ['customer', 'admin'])) {
                throw new Exception("Invalid cancelled_by value. Must be 'customer' or 'admin'");
            }
            
            // Check if order can be cancelled
            if (!$order->canBeCancelled()) {
                throw new Exception("Order cannot be cancelled. Current status: {$order->status}");
            }
            
            // If customer is cancelling, verify ownership
            if ($cancelledBy === 'customer') {
                // This will be checked in the controller
            }
            
            $oldStatus = $order->status;
            
            // Cancel the order
            $order->status = 'cancelled';
            $order->cancelled_at = now();
            $order->cancelled_by = $cancelledBy;
            $order->cancellation_reason = $reason;
            // Clear any pending cancellation request
            $order->cancellation_requested_at = null;
            $order->cancellation_requested_by = null;
            $order->save();
            
            // Stock release removed - no need to release stock on cancellation
            
            DB::commit();
            
            // Load customer relationship for notifications
            $order->load('customer');
            
            // Send notification to customer
            try {
                $order->customer->notify(new OrderCancelledNotification($order));
            } catch (Exception $e) {
                // Log notification error but don't fail the cancellation
                Log::warning('Failed to send cancellation notification to customer', [
                    'order_id' => $orderId,
                    'customer_id' => $order->customer_id,
                    'error' => $e->getMessage()
                ]);
            }
            
            Log::info('Order cancelled directly', [
                'order_id' => $orderId,
                'cancelled_by' => $cancelledBy,
                'old_status' => $oldStatus,
                'reason' => $reason
            ]);
            
            return [
                'success' => true,
                'message' => 'Order cancelled successfully',
                'order' => $order->load(['customer', 'orderItems.product']),
                'old_status' => $oldStatus,
                'new_status' => 'cancelled',
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation failed', [
                'order_id' => $orderId,
                'cancelled_by' => $cancelledBy,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get orders with pending cancellation requests (Admin)
     * 
     * @param array $filters
     * @param int $perPage
     * @return array
     */
    public function getPendingCancellationRequests(array $filters = [], int $perPage = 15): array
    {
        $query = Order::with(['customer', 'orderItems.product', 'coupon'])
            ->whereNotNull('cancellation_requested_at')
            ->where('status', '!=', 'cancelled');
        
        // Filter by customer
        if (isset($filters['customer_id']) && $filters['customer_id']) {
            $query->where('customer_id', $filters['customer_id']);
        }
        
        // Search by order number
        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Date range filter
        if (isset($filters['date_from']) && $filters['date_from']) {
            $query->whereDate('cancellation_requested_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to']) && $filters['date_to']) {
            $query->whereDate('cancellation_requested_at', '<=', $filters['date_to']);
        }
        
        // Sort
        $sortBy = $filters['sort_by'] ?? 'cancellation_requested_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);
        
        $orders = $query->paginate($perPage);
        
        return [
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ];
    }

    /**
     * Generate unique order number
     * 
     * @return string
     */
    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (Order::where('order_number', $orderNumber)->exists());
        
        return $orderNumber;
    }
}

