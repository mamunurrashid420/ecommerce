<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Deal;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Get comprehensive dashboard statistics
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $dateFrom = $request->get('date_from') 
                ? Carbon::parse($request->get('date_from')) 
                : Carbon::now()->subDays(30);
            $dateTo = $request->get('date_to') 
                ? Carbon::parse($request->get('date_to')) 
                : Carbon::now();

            // Overall Statistics
            $totalOrders = Order::whereBetween('created_at', [$dateFrom, $dateTo])->count();
            $totalRevenue = Order::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount');
            $totalCustomers = Customer::whereBetween('created_at', [$dateFrom, $dateTo])->count();
            $totalProducts = Product::count();
            $activeProducts = Product::where('is_active', true)->count();
            
            // Order Status Breakdown
            $orderStatusBreakdown = Order::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // Revenue by Status
            $revenueByStatus = Order::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('status', DB::raw('sum(total_amount) as revenue'))
                ->groupBy('status')
                ->pluck('revenue', 'status')
                ->toArray();

            // Average Order Value
            $averageOrderValue = $totalOrders > 0 
                ? $totalRevenue / $totalOrders 
                : 0;

            // Pending Cancellations
            $pendingCancellations = Order::whereNotNull('cancellation_requested_at')
                ->where('status', '!=', 'cancelled')
                ->count();

            // Low Stock Products
            $lowStockThreshold = 10; // Configurable
            $lowStockProducts = Product::where('stock_quantity', '<=', $lowStockThreshold)
                ->where('is_active', true)
                ->count();

            // Out of Stock Products
            $outOfStockProducts = Product::where('stock_quantity', '<=', 0)
                ->where('is_active', true)
                ->count();

            // Active Coupons
            $activeCoupons = Coupon::where('is_active', true)
                ->where(function($query) {
                    $query->whereNull('valid_until')
                        ->orWhere('valid_until', '>=', Carbon::now());
                })
                ->count();

            // Active Deals
            $activeDeals = Deal::where('is_active', true)
                ->where(function($query) {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', Carbon::now());
                })
                ->count();

            // Open Support Tickets
            $openSupportTickets = SupportTicket::whereIn('status', ['open', 'in_progress'])
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'overview' => [
                        'total_orders' => $totalOrders,
                        'total_revenue' => round($totalRevenue, 2),
                        'total_customers' => $totalCustomers,
                        'total_products' => $totalProducts,
                        'active_products' => $activeProducts,
                        'average_order_value' => round($averageOrderValue, 2),
                    ],
                    'orders' => [
                        'status_breakdown' => $orderStatusBreakdown,
                        'revenue_by_status' => array_map(function($value) {
                            return round($value, 2);
                        }, $revenueByStatus),
                        'pending_cancellations' => $pendingCancellations,
                    ],
                    'inventory' => [
                        'low_stock_products' => $lowStockProducts,
                        'out_of_stock_products' => $outOfStockProducts,
                    ],
                    'promotions' => [
                        'active_coupons' => $activeCoupons,
                        'active_deals' => $activeDeals,
                    ],
                    'support' => [
                        'open_tickets' => $openSupportTickets,
                    ],
                    'date_range' => [
                        'from' => $dateFrom->toDateString(),
                        'to' => $dateTo->toDateString(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales trends (daily, weekly, monthly)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function salesTrends(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', 'daily'); // daily, weekly, monthly
            $days = $request->get('days', 30);
            $dateFrom = Carbon::now()->subDays($days);
            $dateTo = Carbon::now();

            $query = Order::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', '!=', 'cancelled');

            $trends = [];

            switch ($period) {
                case 'daily':
                    $trends = $query->select(
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('COUNT(*) as orders'),
                        DB::raw('SUM(total_amount) as revenue')
                    )
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->map(function($item) {
                        return [
                            'date' => $item->date,
                            'orders' => $item->orders,
                            'revenue' => round($item->revenue ?? 0, 2),
                        ];
                    });
                    break;

                case 'weekly':
                    $trends = $query->select(
                        DB::raw('YEAR(created_at) as year'),
                        DB::raw('WEEK(created_at) as week'),
                        DB::raw('COUNT(*) as orders'),
                        DB::raw('SUM(total_amount) as revenue')
                    )
                    ->groupBy('year', 'week')
                    ->orderBy('year')
                    ->orderBy('week')
                    ->get()
                    ->map(function($item) {
                        return [
                            'year' => $item->year,
                            'week' => $item->week,
                            'label' => "Week {$item->week}, {$item->year}",
                            'orders' => $item->orders,
                            'revenue' => round($item->revenue ?? 0, 2),
                        ];
                    });
                    break;

                case 'monthly':
                    $trends = $query->select(
                        DB::raw('YEAR(created_at) as year'),
                        DB::raw('MONTH(created_at) as month'),
                        DB::raw('COUNT(*) as orders'),
                        DB::raw('SUM(total_amount) as revenue')
                    )
                    ->groupBy('year', 'month')
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get()
                    ->map(function($item) {
                        return [
                            'year' => $item->year,
                            'month' => $item->month,
                            'label' => Carbon::create($item->year, $item->month)->format('M Y'),
                            'orders' => $item->orders,
                            'revenue' => round($item->revenue ?? 0, 2),
                        ];
                    });
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => $period,
                    'trends' => $trends,
                    'date_range' => [
                        'from' => $dateFrom->toDateString(),
                        'to' => $dateTo->toDateString(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve sales trends',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top products by sales
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function topProducts(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $dateFrom = $request->get('date_from') 
                ? Carbon::parse($request->get('date_from')) 
                : Carbon::now()->subDays(30);
            $dateTo = $request->get('date_to') 
                ? Carbon::parse($request->get('date_to')) 
                : Carbon::now();

            $topProducts = OrderItem::whereHas('order', function($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('created_at', [$dateFrom, $dateTo])
                        ->where('status', '!=', 'cancelled');
                })
                ->select(
                    'product_id',
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('SUM(price * quantity) as total_revenue')
                )
                ->groupBy('product_id')
                ->orderBy('total_quantity', 'desc')
                ->limit($limit)
                ->get()
                ->map(function($item) {
                    $product = Product::find($item->product_id);
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $product ? $product->name : 'Unknown',
                        'product_sku' => $product ? $product->sku : null,
                        'total_quantity_sold' => $item->total_quantity,
                        'total_revenue' => round($item->total_revenue ?? 0, 2),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $topProducts,
                    'date_range' => [
                        'from' => $dateFrom->toDateString(),
                        'to' => $dateTo->toDateString(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve top products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top customers by orders/revenue
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function topCustomers(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $sortBy = $request->get('sort_by', 'revenue'); // revenue or orders
            $dateFrom = $request->get('date_from') 
                ? Carbon::parse($request->get('date_from')) 
                : Carbon::now()->subDays(30);
            $dateTo = $request->get('date_to') 
                ? Carbon::parse($request->get('date_to')) 
                : Carbon::now();

            $query = Order::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', '!=', 'cancelled')
                ->select(
                    'customer_id',
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(total_amount) as total_revenue')
                )
                ->groupBy('customer_id');

            $orderBy = $sortBy === 'orders' ? 'total_orders' : 'total_revenue';
            $topCustomers = $query->orderBy($orderBy, 'desc')
                ->limit($limit)
                ->get()
                ->map(function($item) {
                    $customer = Customer::find($item->customer_id);
                    return [
                        'customer_id' => $item->customer_id,
                        'customer_name' => $customer ? $customer->name : 'Unknown',
                        'customer_email' => $customer ? $customer->email : null,
                        'customer_phone' => $customer ? $customer->phone : null,
                        'total_orders' => $item->total_orders,
                        'total_revenue' => round($item->total_revenue ?? 0, 2),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'customers' => $topCustomers,
                    'sort_by' => $sortBy,
                    'date_range' => [
                        'from' => $dateFrom->toDateString(),
                        'to' => $dateTo->toDateString(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve top customers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent orders
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function recentOrders(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);

            $recentOrders = Order::with(['customer:id,name,email,phone', 'orderItems.product:id,name,sku'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'customer_name' => $order->customer ? $order->customer->name : 'Unknown',
                        'customer_email' => $order->customer ? $order->customer->email : null,
                        'status' => $order->status,
                        'total_amount' => round($order->total_amount, 2),
                        'items_count' => $order->orderItems->count(),
                        'created_at' => $order->created_at->toDateTimeString(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'orders' => $recentOrders,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve recent orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low stock alerts
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function lowStockAlerts(Request $request): JsonResponse
    {
        try {
            $threshold = $request->get('threshold', 10);
            $limit = $request->get('limit', 50);

            $lowStockProducts = Product::where('stock_quantity', '<=', $threshold)
                ->where('is_active', true)
                ->with('category:id,name')
                ->orderBy('stock_quantity', 'asc')
                ->limit($limit)
                ->get()
                ->map(function($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'stock_quantity' => $product->stock_quantity,
                        'price' => round($product->price, 2),
                        'category' => $product->category ? $product->category->name : null,
                        'is_out_of_stock' => $product->stock_quantity <= 0,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $lowStockProducts,
                    'threshold' => $threshold,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve low stock alerts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get category sales breakdown
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function categorySales(Request $request): JsonResponse
    {
        try {
            $dateFrom = $request->get('date_from') 
                ? Carbon::parse($request->get('date_from')) 
                : Carbon::now()->subDays(30);
            $dateTo = $request->get('date_to') 
                ? Carbon::parse($request->get('date_to')) 
                : Carbon::now();

            $categorySales = OrderItem::whereHas('order', function($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('created_at', [$dateFrom, $dateTo])
                        ->where('status', '!=', 'cancelled');
                })
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->select(
                    'categories.id',
                    'categories.name',
                    DB::raw('SUM(order_items.quantity) as total_quantity'),
                    DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue'),
                    DB::raw('COUNT(DISTINCT order_items.order_id) as order_count')
                )
                ->groupBy('categories.id', 'categories.name')
                ->orderBy('total_revenue', 'desc')
                ->get()
                ->map(function($item) {
                    return [
                        'category_id' => $item->id,
                        'category_name' => $item->name,
                        'total_quantity_sold' => $item->total_quantity,
                        'total_revenue' => round($item->total_revenue ?? 0, 2),
                        'order_count' => $item->order_count,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'categories' => $categorySales,
                    'date_range' => [
                        'from' => $dateFrom->toDateString(),
                        'to' => $dateTo->toDateString(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve category sales',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

