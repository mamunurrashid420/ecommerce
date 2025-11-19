<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_id',
        'coupon_id',
        'coupon_code',
        'subtotal',
        'discount_amount',
        'shipping_cost',
        'tax_amount',
        'tax_rate',
        'tax_inclusive',
        'total_amount',
        'status',
        'shipping_address',
        'notes',
        'cancellation_requested_at',
        'cancellation_reason',
        'cancellation_requested_by',
        'cancelled_at',
        'cancelled_by'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_inclusive' => 'boolean',
        'total_amount' => 'decimal:2',
        'cancellation_requested_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function couponUsage()
    {
        return $this->hasOne(CouponUsage::class);
    }

    public function dealUsages()
    {
        return $this->hasMany(DealUsage::class);
    }

    /**
     * Check if order has a pending cancellation request
     * 
     * @return bool
     */
    public function hasPendingCancellationRequest(): bool
    {
        return $this->cancellation_requested_at !== null 
            && $this->status !== 'cancelled';
    }

    /**
     * Check if order can be cancelled
     * 
     * @return bool
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Check if order can request cancellation
     * 
     * @return bool
     */
    public function canRequestCancellation(): bool
    {
        return $this->status === 'pending' && !$this->hasPendingCancellationRequest();
    }

    /**
     * Check if order is cancelled
     * 
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
