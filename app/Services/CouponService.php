<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CouponService
{
    /**
     * Validate and apply coupon to order items
     * 
     * @param string $couponCode
     * @param array $items Array of order items with product_id and quantity
     * @param int|null $customerId
     * @return array
     * @throws Exception
     */
    public function validateAndCalculateDiscount(string $couponCode, array $items, ?int $customerId = null): array
    {
        // Find coupon
        $coupon = Coupon::where('code', $couponCode)->first();
        
        if (!$coupon) {
            throw new Exception('Coupon not found');
        }

        // Check if coupon is valid
        if (!$coupon->isValid()) {
            throw new Exception('Coupon is not valid or has expired');
        }

        // Check customer eligibility if customer ID is provided
        if ($customerId) {
            if (!$coupon->canBeUsedBy($customerId)) {
                throw new Exception('Coupon cannot be used by this customer');
            }
        }

        // Calculate subtotal from items
        $subtotal = 0;
        $applicableItems = [];

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            
            if (!$product) {
                continue;
            }

            // Check if coupon applies to this product
            if (!$coupon->appliesToProduct($product->id)) {
                continue;
            }

            // Check if coupon applies to product's category
            if (!$coupon->appliesToCategory($product->category_id)) {
                continue;
            }

            $itemTotal = $product->price * $item['quantity'];
            $subtotal += $itemTotal;
            
            $applicableItems[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'total' => $itemTotal,
            ];
        }

        // Check minimum purchase requirement
        if ($coupon->minimum_purchase && $subtotal < $coupon->minimum_purchase) {
            throw new Exception("Minimum purchase amount of {$coupon->minimum_purchase} required for this coupon");
        }

        // Calculate discount
        $discountAmount = $coupon->calculateDiscount($subtotal);
        $totalAfterDiscount = $subtotal - $discountAmount;

        return [
            'success' => true,
            'coupon' => $coupon,
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'total_after_discount' => round($totalAfterDiscount, 2),
            'applicable_items' => $applicableItems,
        ];
    }

    /**
     * Validate coupon code without calculating discount
     * 
     * @param string $couponCode
     * @param int|null $customerId
     * @return array
     * @throws Exception
     */
    public function validateCoupon(string $couponCode, ?int $customerId = null): array
    {
        $coupon = Coupon::where('code', $couponCode)->first();
        
        if (!$coupon) {
            throw new Exception('Coupon not found');
        }

        if (!$coupon->isValid()) {
            throw new Exception('Coupon is not valid or has expired');
        }

        if ($customerId && !$coupon->canBeUsedBy($customerId)) {
            throw new Exception('Coupon cannot be used by this customer');
        }

        return [
            'success' => true,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'name' => $coupon->name,
                'description' => $coupon->description,
                'type' => $coupon->type,
                'discount_value' => $coupon->discount_value,
                'minimum_purchase' => $coupon->minimum_purchase,
                'maximum_discount' => $coupon->maximum_discount,
            ],
        ];
    }

    /**
     * Record coupon usage for an order
     * 
     * @param int $couponId
     * @param int $orderId
     * @param int $customerId
     * @param float $discountAmount
     * @param float $orderTotalBeforeDiscount
     * @param float $orderTotalAfterDiscount
     * @return \App\Models\CouponUsage
     */
    public function recordUsage(
        int $couponId,
        int $orderId,
        int $customerId,
        float $discountAmount,
        float $orderTotalBeforeDiscount,
        float $orderTotalAfterDiscount
    ) {
        $coupon = Coupon::findOrFail($couponId);
        
        // Increment coupon usage count
        $coupon->incrementUsage();

        // Record usage
        return \App\Models\CouponUsage::create([
            'coupon_id' => $couponId,
            'order_id' => $orderId,
            'customer_id' => $customerId,
            'discount_amount' => $discountAmount,
            'order_total_before_discount' => $orderTotalBeforeDiscount,
            'order_total_after_discount' => $orderTotalAfterDiscount,
        ]);
    }

    /**
     * Get coupon statistics
     * 
     * @param int|null $couponId
     * @return array
     */
    public function getCouponStats(?int $couponId = null): array
    {
        if ($couponId) {
            $coupon = Coupon::findOrFail($couponId);
            
            $totalUsages = \App\Models\CouponUsage::where('coupon_id', $couponId)->count();
            $totalDiscountGiven = \App\Models\CouponUsage::where('coupon_id', $couponId)
                ->sum('discount_amount');
            $totalOrders = Order::where('coupon_id', $couponId)->count();
            
            return [
                'coupon_id' => $coupon->id,
                'coupon_code' => $coupon->code,
                'coupon_name' => $coupon->name,
                'total_usages' => $totalUsages,
                'total_discount_given' => round($totalDiscountGiven, 2),
                'total_orders' => $totalOrders,
                'usage_limit' => $coupon->usage_limit,
                'usage_count' => $coupon->usage_count,
                'remaining_uses' => $coupon->usage_limit ? ($coupon->usage_limit - $coupon->usage_count) : null,
            ];
        }

        // Overall statistics
        $totalCoupons = Coupon::count();
        $activeCoupons = Coupon::where('is_active', true)->count();
        $totalUsages = \App\Models\CouponUsage::count();
        $totalDiscountGiven = \App\Models\CouponUsage::sum('discount_amount');
        $totalOrdersWithCoupons = Order::whereNotNull('coupon_id')->count();

        return [
            'total_coupons' => $totalCoupons,
            'active_coupons' => $activeCoupons,
            'total_usages' => $totalUsages,
            'total_discount_given' => round($totalDiscountGiven, 2),
            'total_orders_with_coupons' => $totalOrdersWithCoupons,
        ];
    }
}

