<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    protected $fillable = [
        'coupon_id',
        'order_id',
        'customer_id',
        'discount_amount',
        'order_total_before_discount',
        'order_total_after_discount',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'order_total_before_discount' => 'decimal:2',
        'order_total_after_discount' => 'decimal:2',
    ];

    /**
     * Get the coupon that was used
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the order that used the coupon
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the customer who used the coupon
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
