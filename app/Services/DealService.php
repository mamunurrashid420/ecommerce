<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use App\Models\DealUsage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class DealService
{
    /**
     * Validate and apply deal to order items
     * 
     * @param int $dealId
     * @param array $items Array of order items with product_id and quantity
     * @param int|null $customerId
     * @return array
     * @throws Exception
     */
    public function validateAndCalculateDiscount(int $dealId, array $items, ?int $customerId = null): array
    {
        // Find deal
        $deal = Deal::find($dealId);
        
        if (!$deal) {
            throw new Exception('Deal not found');
        }

        // Check if deal is valid
        if (!$deal->is_valid) {
            throw new Exception('Deal is not valid or has expired');
        }

        // Check customer eligibility if customer ID is provided
        if ($customerId) {
            if (!$deal->canBeUsedBy($customerId)) {
                throw new Exception('Deal cannot be used by this customer');
            }
        }

        // Calculate subtotal from items
        $subtotal = 0;
        $applicableItems = [];
        $productsApplied = [];

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            
            if (!$product) {
                continue;
            }

            // Check if deal applies to this product
            if (!$deal->appliesToProduct($product->id)) {
                continue;
            }

            $itemTotal = $product->price * $item['quantity'];
            $subtotal += $itemTotal;
            
            $applicableItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'total' => $itemTotal,
            ];

            $productsApplied[] = $product->id;
        }

        // Check minimum purchase requirement
        if ($deal->minimum_purchase_amount && $subtotal < $deal->minimum_purchase_amount) {
            throw new Exception("Minimum purchase amount of {$deal->minimum_purchase_amount} required for this deal");
        }

        // Calculate discount based on deal type
        $discountAmount = 0;
        
        if ($deal->type === 'buy_x_get_y') {
            // Handle buy X get Y deals
            $discountAmount = $this->calculateBuyXGetYDiscount($deal, $applicableItems);
        } else {
            // Regular discount calculation
            $discountAmount = $deal->calculateDiscount($subtotal);
        }

        $totalAfterDiscount = $subtotal - $discountAmount;

        return [
            'success' => true,
            'deal' => $deal,
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'total_after_discount' => round($totalAfterDiscount, 2),
            'applicable_items' => $applicableItems,
            'products_applied' => $productsApplied,
        ];
    }

    /**
     * Calculate discount for buy X get Y deals
     */
    protected function calculateBuyXGetYDiscount(Deal $deal, array $items): float
    {
        if (!$deal->buy_quantity || !$deal->get_quantity || !$deal->get_product_id) {
            return 0;
        }

        $getProduct = Product::find($deal->get_product_id);
        if (!$getProduct) {
            return 0;
        }

        // Calculate how many sets of "buy X" we have
        $totalQuantity = array_sum(array_column($items, 'quantity'));
        $sets = floor($totalQuantity / $deal->buy_quantity);
        
        // Calculate discount: number of free items * price of free product
        $freeItems = $sets * $deal->get_quantity;
        $discount = $freeItems * $getProduct->price;

        return $discount;
    }

    /**
     * Validate deal without calculating discount
     * 
     * @param int $dealId
     * @param int|null $customerId
     * @return array
     * @throws Exception
     */
    public function validateDeal(int $dealId, ?int $customerId = null): array
    {
        $deal = Deal::find($dealId);
        
        if (!$deal) {
            throw new Exception('Deal not found');
        }

        if (!$deal->is_valid) {
            throw new Exception('Deal is not valid or has expired');
        }

        if ($customerId && !$deal->canBeUsedBy($customerId)) {
            throw new Exception('Deal cannot be used by this customer');
        }

        return [
            'success' => true,
            'deal' => [
                'id' => $deal->id,
                'title' => $deal->title,
                'slug' => $deal->slug,
                'description' => $deal->description,
                'type' => $deal->type,
                'discount_type' => $deal->discount_type,
                'discount_value' => $deal->discount_value,
                'minimum_purchase_amount' => $deal->minimum_purchase_amount,
                'maximum_discount' => $deal->maximum_discount,
                'time_remaining' => $deal->time_remaining,
            ],
        ];
    }

    /**
     * Record deal usage for an order
     * 
     * @param int $dealId
     * @param int $orderId
     * @param int $customerId
     * @param float $discountAmount
     * @param float $orderTotalBeforeDiscount
     * @param float $orderTotalAfterDiscount
     * @param array $productsApplied
     * @return \App\Models\DealUsage
     */
    public function recordUsage(
        int $dealId,
        int $orderId,
        int $customerId,
        float $discountAmount,
        float $orderTotalBeforeDiscount,
        float $orderTotalAfterDiscount,
        array $productsApplied = []
    ) {
        $deal = Deal::findOrFail($dealId);
        
        // Increment deal usage count
        $deal->incrementUsage();

        // Record usage
        return DealUsage::create([
            'deal_id' => $dealId,
            'order_id' => $orderId,
            'customer_id' => $customerId,
            'discount_amount' => $discountAmount,
            'order_total_before_discount' => $orderTotalBeforeDiscount,
            'order_total_after_discount' => $orderTotalAfterDiscount,
            'products_applied' => $productsApplied,
        ]);
    }

    /**
     * Get deal statistics
     * 
     * @param int|null $dealId
     * @return array
     */
    public function getDealStats(?int $dealId = null): array
    {
        if ($dealId) {
            $deal = Deal::findOrFail($dealId);
            
            $totalUsages = DealUsage::where('deal_id', $dealId)->count();
            $totalDiscountGiven = DealUsage::where('deal_id', $dealId)
                ->sum('discount_amount');
            $totalOrders = Order::whereHas('dealUsages', function($q) use ($dealId) {
                $q->where('deal_id', $dealId);
            })->count();
            
            return [
                'deal_id' => $deal->id,
                'deal_title' => $deal->title,
                'deal_slug' => $deal->slug,
                'total_usages' => $totalUsages,
                'total_discount_given' => round($totalDiscountGiven, 2),
                'total_orders' => $totalOrders,
                'usage_limit' => $deal->usage_limit,
                'usage_count' => $deal->usage_count,
                'remaining_uses' => $deal->usage_limit ? ($deal->usage_limit - $deal->usage_count) : null,
            ];
        }

        // Overall statistics
        $totalDeals = Deal::count();
        $activeDeals = Deal::where('is_active', true)->count();
        $validDeals = Deal::valid()->count();
        $totalUsages = DealUsage::count();
        $totalDiscountGiven = DealUsage::sum('discount_amount');
        $totalOrdersWithDeals = Order::whereHas('dealUsages')->count();

        return [
            'total_deals' => $totalDeals,
            'active_deals' => $activeDeals,
            'valid_deals' => $validDeals,
            'total_usages' => $totalUsages,
            'total_discount_given' => round($totalDiscountGiven, 2),
            'total_orders_with_deals' => $totalOrdersWithDeals,
        ];
    }

    /**
     * Get products with active deals
     * 
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProductsWithDeals(array $filters = [])
    {
        // Get all valid deals
        $validDeals = Deal::valid()->get();
        
        // Extract product IDs from deals
        $productIds = [];
        foreach ($validDeals as $deal) {
            if ($deal->type === 'product' && $deal->applicable_products) {
                $productIds = array_merge($productIds, $deal->applicable_products);
            } elseif ($deal->type === 'category' && $deal->applicable_categories) {
                // Get products from categories
                $categoryProducts = Product::whereIn('category_id', $deal->applicable_categories)
                    ->pluck('id')
                    ->toArray();
                $productIds = array_merge($productIds, $categoryProducts);
            }
        }
        
        $productIds = array_unique($productIds);
        
        $query = Product::with(['category', 'media'])
            ->whereIn('id', $productIds)
            ->where('is_active', true);

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }
}

