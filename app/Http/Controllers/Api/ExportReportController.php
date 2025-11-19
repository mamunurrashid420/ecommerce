<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class ExportReportController extends Controller
{
    /**
     * Export orders to CSV
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportOrders(Request $request)
    {
        try {
            $format = $request->get('format', 'csv'); // csv, excel (if available)
            $dateFrom = $request->get('date_from') 
                ? Carbon::parse($request->get('date_from')) 
                : null;
            $dateTo = $request->get('date_to') 
                ? Carbon::parse($request->get('date_to')) 
                : null;
            $status = $request->get('status');

            $query = Order::with(['customer', 'orderItems.product']);

            if ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->where('created_at', '<=', $dateTo->endOfDay());
            }
            if ($status) {
                $query->where('status', $status);
            }

            $orders = $query->orderBy('created_at', 'desc')->get();

            $filename = 'orders_export_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function() use ($orders) {
                $file = fopen('php://output', 'w');

                // CSV Headers
                fputcsv($file, [
                    'Order Number',
                    'Order Date',
                    'Customer Name',
                    'Customer Email',
                    'Customer Phone',
                    'Status',
                    'Subtotal',
                    'Discount Amount',
                    'Shipping Cost',
                    'Tax Amount',
                    'Total Amount',
                    'Shipping Address',
                    'Items Count',
                    'Items Details',
                    'Coupon Code',
                    'Notes',
                ]);

                // CSV Data
                foreach ($orders as $order) {
                    $itemsDetails = $order->orderItems->map(function($item) {
                        $productName = $item->product ? $item->product->name : 'Unknown';
                        return "{$productName} (Qty: {$item->quantity}, Price: {$item->price})";
                    })->implode('; ');

                    fputcsv($file, [
                        $order->order_number,
                        $order->created_at->format('Y-m-d H:i:s'),
                        $order->customer ? $order->customer->name : 'Unknown',
                        $order->customer ? $order->customer->email : '',
                        $order->customer ? $order->customer->phone : '',
                        $order->status,
                        number_format($order->subtotal, 2),
                        number_format($order->discount_amount, 2),
                        number_format($order->shipping_cost, 2),
                        number_format($order->tax_amount, 2),
                        number_format($order->total_amount, 2),
                        $order->shipping_address,
                        $order->orderItems->count(),
                        $itemsDetails,
                        $order->coupon_code ?? '',
                        $order->notes ?? '',
                    ]);
                }

                fclose($file);
            };

            return Response::stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export products to CSV
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportProducts(Request $request)
    {
        try {
            $format = $request->get('format', 'csv');
            $categoryId = $request->get('category_id');
            $isActive = $request->get('is_active');

            $query = Product::with('category');

            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }
            if ($isActive !== null) {
                $query->where('is_active', $isActive);
            }

            $products = $query->orderBy('created_at', 'desc')->get();

            $filename = 'products_export_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function() use ($products) {
                $file = fopen('php://output', 'w');

                // CSV Headers
                fputcsv($file, [
                    'ID',
                    'Name',
                    'SKU',
                    'Category',
                    'Price',
                    'Stock Quantity',
                    'Description',
                    'Is Active',
                    'Brand',
                    'Model',
                    'Weight',
                    'Created At',
                    'Updated At',
                ]);

                // CSV Data
                foreach ($products as $product) {
                    fputcsv($file, [
                        $product->id,
                        $product->name,
                        $product->sku ?? '',
                        $product->category ? $product->category->name : '',
                        number_format($product->price, 2),
                        $product->stock_quantity,
                        strip_tags($product->description ?? ''),
                        $product->is_active ? 'Yes' : 'No',
                        $product->brand ?? '',
                        $product->model ?? '',
                        $product->weight ?? '',
                        $product->created_at->format('Y-m-d H:i:s'),
                        $product->updated_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($file);
            };

            return Response::stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export customers to CSV
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportCustomers(Request $request)
    {
        try {
            $format = $request->get('format', 'csv');
            $dateFrom = $request->get('date_from') 
                ? Carbon::parse($request->get('date_from')) 
                : null;
            $dateTo = $request->get('date_to') 
                ? Carbon::parse($request->get('date_to')) 
                : null;
            $isBanned = $request->get('is_banned');
            $isSuspended = $request->get('is_suspended');

            $query = Customer::withCount('orders');

            if ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->where('created_at', '<=', $dateTo->endOfDay());
            }
            if ($isBanned !== null) {
                $query->where('is_banned', $isBanned);
            }
            if ($isSuspended !== null) {
                $query->where('is_suspended', $isSuspended);
            }

            $customers = $query->orderBy('created_at', 'desc')->get();

            $filename = 'customers_export_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function() use ($customers) {
                $file = fopen('php://output', 'w');

                // CSV Headers
                fputcsv($file, [
                    'ID',
                    'Name',
                    'Email',
                    'Phone',
                    'Address',
                    'Total Orders',
                    'Is Banned',
                    'Is Suspended',
                    'Banned At',
                    'Suspended At',
                    'Created At',
                    'Updated At',
                ]);

                // CSV Data
                foreach ($customers as $customer) {
                    fputcsv($file, [
                        $customer->id,
                        $customer->name,
                        $customer->email,
                        $customer->phone ?? '',
                        $customer->address ?? '',
                        $customer->orders_count,
                        $customer->is_banned ? 'Yes' : 'No',
                        $customer->is_suspended ? 'Yes' : 'No',
                        $customer->banned_at ? $customer->banned_at->format('Y-m-d H:i:s') : '',
                        $customer->suspended_at ? $customer->suspended_at->format('Y-m-d H:i:s') : '',
                        $customer->created_at->format('Y-m-d H:i:s'),
                        $customer->updated_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($file);
            };

            return Response::stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export customers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export sales report to CSV
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportSalesReport(Request $request)
    {
        try {
            $dateFrom = $request->get('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
            $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
            $groupBy = $request->get('group_by', 'day'); // day, week, month

            $dateFromCarbon = Carbon::parse($dateFrom);
            $dateToCarbon = Carbon::parse($dateTo)->endOfDay();

            $query = Order::whereBetween('created_at', [$dateFromCarbon, $dateToCarbon])
                ->where('status', '!=', 'cancelled')
                ->with('orderItems.product');

            $filename = 'sales_report_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function() use ($query, $groupBy, $dateFromCarbon, $dateToCarbon) {
                $file = fopen('php://output', 'w');

                // CSV Headers
                fputcsv($file, [
                    'Period',
                    'Total Orders',
                    'Total Revenue',
                    'Average Order Value',
                    'Total Items Sold',
                    'Total Discount',
                    'Total Shipping',
                    'Total Tax',
                ]);

                // Group data based on group_by parameter
                $orders = $query->get();
                $groupedData = [];

                foreach ($orders as $order) {
                    $key = '';
                    switch ($groupBy) {
                        case 'day':
                            $key = $order->created_at->format('Y-m-d');
                            break;
                        case 'week':
                            $key = $order->created_at->format('Y-W');
                            break;
                        case 'month':
                            $key = $order->created_at->format('Y-m');
                            break;
                    }

                    if (!isset($groupedData[$key])) {
                        $groupedData[$key] = [
                            'period' => $key,
                            'orders' => 0,
                            'revenue' => 0,
                            'items' => 0,
                            'discount' => 0,
                            'shipping' => 0,
                            'tax' => 0,
                        ];
                    }

                    $groupedData[$key]['orders']++;
                    $groupedData[$key]['revenue'] += $order->total_amount;
                    $groupedData[$key]['items'] += $order->orderItems->sum('quantity');
                    $groupedData[$key]['discount'] += $order->discount_amount;
                    $groupedData[$key]['shipping'] += $order->shipping_cost;
                    $groupedData[$key]['tax'] += $order->tax_amount;
                }

                // Write grouped data
                foreach ($groupedData as $data) {
                    $avgOrderValue = $data['orders'] > 0 
                        ? $data['revenue'] / $data['orders'] 
                        : 0;

                    fputcsv($file, [
                        $data['period'],
                        $data['orders'],
                        number_format($data['revenue'], 2),
                        number_format($avgOrderValue, 2),
                        $data['items'],
                        number_format($data['discount'], 2),
                        number_format($data['shipping'], 2),
                        number_format($data['tax'], 2),
                    ]);
                }

                fclose($file);
            };

            return Response::stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export sales report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export product sales report to CSV
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportProductSalesReport(Request $request)
    {
        try {
            $dateFrom = $request->get('date_from') 
                ? Carbon::parse($request->get('date_from')) 
                : Carbon::now()->subDays(30);
            $dateTo = $request->get('date_to') 
                ? Carbon::parse($request->get('date_to')) 
                : Carbon::now();

            $productSales = OrderItem::whereHas('order', function($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('created_at', [$dateFrom, $dateTo])
                        ->where('status', '!=', 'cancelled');
                })
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->select(
                    'products.id',
                    'products.name',
                    'products.sku',
                    'categories.name as category_name',
                    \Illuminate\Support\Facades\DB::raw('SUM(order_items.quantity) as total_quantity'),
                    \Illuminate\Support\Facades\DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue'),
                    \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT order_items.order_id) as order_count'),
                    \Illuminate\Support\Facades\DB::raw('AVG(order_items.price) as average_price')
                )
                ->groupBy('products.id', 'products.name', 'products.sku', 'categories.name')
                ->orderBy('total_revenue', 'desc')
                ->get();

            $filename = 'product_sales_report_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function() use ($productSales) {
                $file = fopen('php://output', 'w');

                // CSV Headers
                fputcsv($file, [
                    'Product ID',
                    'Product Name',
                    'SKU',
                    'Category',
                    'Total Quantity Sold',
                    'Total Revenue',
                    'Order Count',
                    'Average Price',
                ]);

                // CSV Data
                foreach ($productSales as $item) {
                    fputcsv($file, [
                        $item->id,
                        $item->name,
                        $item->sku ?? '',
                        $item->category_name ?? '',
                        $item->total_quantity,
                        number_format($item->total_revenue, 2),
                        $item->order_count,
                        number_format($item->average_price, 2),
                    ]);
                }

                fclose($file);
            };

            return Response::stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export product sales report',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

