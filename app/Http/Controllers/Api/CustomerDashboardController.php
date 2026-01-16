<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CustomerDashboardController extends Controller
{
    /**
     * Get customer dashboard statistics
     */
    public function stats(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get order statistics
        $totalOrders = $customer->orders()->count();
        $pendingOrders = $customer->orders()->where('status', 'pending')->count();
        $completedOrders = $customer->orders()->where('status', 'delivered')->count();
        
        // Calculate total spent
        $totalSpent = $customer->orders()
            ->whereIn('status', ['delivered', 'processing', 'shipped'])
            ->sum('total_amount');

        return response()->json([
            'success' => true,
            'data' => [
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'completed_orders' => $completedOrders,
                'total_spent' => number_format($totalSpent, 2)
            ]
        ]);
    }

    /**
     * Get recent activity for customer
     */
    public function recentActivity(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $limit = $request->get('limit', 10);

        // Get recent orders with basic info
        $recentOrders = $customer->orders()
            ->with(['orderItems.product:id,name,thumbnail'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'created_at' => $order->created_at->format('M d, Y'),
                    'items_count' => $order->orderItems->count(),
                    'first_product' => $order->orderItems->first() ? [
                        'name' => $order->orderItems->first()->product->name ?? 'Product',
                        'thumbnail' => $order->orderItems->first()->product->thumbnail ?? null
                    ] : null
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'recent_orders' => $recentOrders,
                'has_activity' => $recentOrders->count() > 0
            ]
        ]);
    }

    /**
     * Get customer profile summary
     */
    public function profile(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'profile_picture_url' => $customer->profile_picture_url,
                'member_since' => $customer->created_at->format('M Y'),
                'total_orders' => $customer->orders()->count(),
                'can_make_purchases' => $customer->canMakePurchases()
            ]
        ]);
    }

    /**
     * Get order status breakdown
     */
    public function orderStatusBreakdown(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $statusBreakdown = $customer->orders()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all statuses are represented
        $allStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $breakdown = [];
        
        foreach ($allStatuses as $status) {
            $breakdown[$status] = $statusBreakdown[$status] ?? 0;
        }

        return response()->json([
            'success' => true,
            'data' => $breakdown
        ]);
    }

    /**
     * Get monthly spending trend (last 6 months)
     */
    public function spendingTrend(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $months = [];
        $spending = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();
            
            $monthSpending = $customer->orders()
                ->whereIn('status', ['delivered', 'processing', 'shipped'])
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('total_amount');
            
            $months[] = $date->format('M Y');
            $spending[] = (float) $monthSpending;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'months' => $months,
                'spending' => $spending
            ]
        ]);
    }
}