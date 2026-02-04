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
        'original_price',
        'paid_amount',
        'due_amount',
        'discount_amount',
        'shipping_cost',
        'shipping_method',
        'tax_amount',
        'tax_rate',
        'tax_inclusive',
        'total_amount',
        'status',
        'payment_method',
        'payment_method_id',
        'payment_status',
        'transaction_number',
        'payment_receipt_image',
        'invoice_path',
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
        'original_price' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
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

    protected $appends = ['payment_receipt_url', 'invoice_url'];

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

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
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

    /**
     * Get full invoice URL
     */
    public function getInvoiceUrlAttribute()
    {
        if (empty($this->invoice_path)) {
            return null;
        }

        // If URL already starts with http/https, return as is
        if (str_starts_with($this->invoice_path, 'http://') || str_starts_with($this->invoice_path, 'https://')) {
            return $this->invoice_path;
        }

        // Return storage URL
        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->invoice_path);
    }
}
