<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_code',
        'product_name',
        'product_image',
        'product_sku',
        'quantity',
        'price',
        'total',
        'variations'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'variations' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get product image URL
     */
    public function getProductImageUrlAttribute()
    {
        if ($this->product_image) {
            // If it's already a full URL (dropship product)
            if (filter_var($this->product_image, FILTER_VALIDATE_URL)) {
                return $this->product_image;
            }
            // If it's a local path
            return url($this->product_image);
        }

        // Fallback to product relationship if available
        if ($this->product) {
            return $this->product->image_url;
        }

        return null;
    }
}
