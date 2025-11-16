<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\CouponUsage;
use App\Models\Order;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'discount_value',
        'minimum_purchase',
        'maximum_discount',
        'usage_limit',
        'usage_count',
        'usage_limit_per_customer',
        'valid_from',
        'valid_until',
        'is_active',
        'applicable_categories',
        'applicable_products',
        'first_order_only',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'minimum_purchase' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'usage_limit_per_customer' => 'integer',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
        'applicable_categories' => 'array',
        'applicable_products' => 'array',
        'first_order_only' => 'boolean',
    ];

    /**
     * Get all orders that used this coupon
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get all coupon usages
     */
    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Check if coupon is currently valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Check if coupon can be used by a specific customer
     */
    public function canBeUsedBy(int $customerId): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        if ($this->usage_limit_per_customer) {
            $usageCount = CouponUsage::where('coupon_id', $this->id)
                ->where('customer_id', $customerId)
                ->count();

            if ($usageCount >= $this->usage_limit_per_customer) {
                return false;
            }
        }

        if ($this->first_order_only) {
            $hasPreviousOrder = Order::where('customer_id', $customerId)
                ->exists();

            if ($hasPreviousOrder) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if coupon applies to a product
     */
    public function appliesToProduct(int $productId): bool
    {
        if ($this->applicable_products === null) {
            return true; // No restriction, applies to all products
        }

        return in_array($productId, $this->applicable_products);
    }

    /**
     * Check if coupon applies to a category
     */
    public function appliesToCategory(int $categoryId): bool
    {
        if ($this->applicable_categories === null) {
            return true; // No restriction, applies to all categories
        }

        return in_array($categoryId, $this->applicable_categories);
    }

    /**
     * Calculate discount amount for a given order total
     */
    public function calculateDiscount(float $orderTotal): float
    {
        if ($this->type === 'percentage') {
            $discount = ($orderTotal * $this->discount_value) / 100;
            
            if ($this->maximum_discount && $discount > $this->maximum_discount) {
                $discount = $this->maximum_discount;
            }
        } else {
            $discount = $this->discount_value;
        }

        // Discount cannot exceed order total
        return min($discount, $orderTotal);
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
