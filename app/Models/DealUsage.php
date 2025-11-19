<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealUsage extends Model
{
    protected $fillable = [
        'deal_id',
        'order_id',
        'customer_id',
        'discount_amount',
        'order_total_before_discount',
        'order_total_after_discount',
        'products_applied',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'order_total_before_discount' => 'decimal:2',
        'order_total_after_discount' => 'decimal:2',
        'products_applied' => 'array',
    ];

    /**
     * Get the deal that was used
     */
    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    /**
     * Get the order that used this deal
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the customer who used this deal
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}

