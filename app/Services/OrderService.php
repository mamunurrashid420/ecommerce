<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\NewOrderNotification;
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
            
            // Calculate subtotal from validated items
            $subtotal = $validation['total_amount'];
            $discountAmount = 0;
            $totalAmount = $subtotal;
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
                    $totalAmount = $couponResult['total_after_discount'];
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
            
            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customerId,
                'coupon_id' => $couponId,
                'coupon_code' => $couponCode,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'shipping_address' => $data['shipping_address'],
                'notes' => $data['notes'] ?? null,
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
        $order = Order::with(['customer', 'orderItems.product', 'coupon', 'couponUsage'])->findOrFail($orderId);
        
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
     * @return array
     * @throws Exception
     */
    public function updateOrderStatus(int $orderId, string $status): array
    {
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Invalid status. Must be one of: " . implode(', ', $validStatuses));
        }
        
        DB::beginTransaction();
        
        try {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            $oldStatus = $order->status;
            
            // Validate status transition
            $this->validateStatusTransition($oldStatus, $status);
            
            $order->status = $status;
            $order->save();
            
            // Handle cancellation - release stock
            if ($status === 'cancelled' && $oldStatus !== 'cancelled') {
                foreach ($order->orderItems as $item) {
                    $this->inventoryService->releaseStock(
                        $item->product_id,
                        $item->quantity,
                        $order->id
                    );
                }
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'order' => $order->load(['customer', 'orderItems.product']),
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
            
            // Release stock if order is not cancelled
            if ($order->status !== 'cancelled') {
                foreach ($order->orderItems as $item) {
                    $this->inventoryService->releaseStock(
                        $item->product_id,
                        $item->quantity,
                        $order->id
                    );
                }
            }
            
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
     * Validate status transition
     * 
     * @param string $oldStatus
     * @param string $newStatus
     * @return void
     * @throws Exception
     */
    private function validateStatusTransition(string $oldStatus, string $newStatus): void
    {
        $validTransitions = [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered', 'cancelled'],
            'delivered' => [], // Final state
            'cancelled' => [], // Final state
        ];
        
        if (!isset($validTransitions[$oldStatus])) {
            throw new Exception("Invalid current status: {$oldStatus}");
        }
        
        if (!in_array($newStatus, $validTransitions[$oldStatus])) {
            throw new Exception("Cannot transition from '{$oldStatus}' to '{$newStatus}'");
        }
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

