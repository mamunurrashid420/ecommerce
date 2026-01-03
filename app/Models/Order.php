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
        'shipping_method',
        'tax_amount',
        'tax_rate',
        'tax_inclusive',
        'total_amount',
        'status',
        'payment_method',
        'payment_status',
        'transaction_number',
        'payment_receipt_image',
        'paid_at',
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
        'paid_at' => 'datetime',
        'cancellation_requested_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $appends = ['payment_receipt_url'];

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

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc');
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

    /**
     * Get full payment receipt URL
     */
    public function getPaymentReceiptUrlAttribute()
    {
        if (empty($this->payment_receipt_image)) {
            return null;
        }

        // If URL already starts with http/https, return as is
        if (str_starts_with($this->payment_receipt_image, 'http://') || str_starts_with($this->payment_receipt_image, 'https://')) {
            return $this->payment_receipt_image;
        }

        // If URL starts with /storage, prepend the app URL
        if (str_starts_with($this->payment_receipt_image, '/storage')) {
            return config('app.url') . $this->payment_receipt_image;
        }

        // If URL doesn't start with /, add it and prepend app URL
        return config('app.url') . '/' . ltrim($this->payment_receipt_image, '/');
    }
}
