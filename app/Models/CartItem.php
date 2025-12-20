<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'product_code',
        'product_name',
        'product_price',
        'product_image',
        'product_sku',
        'quantity',
        'subtotal',
    ];

    protected $casts = [
        'product_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantity' => 'integer',
    ];

    protected $appends = ['product_image_url'];

    /**
     * Relationships
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get full product image URL
     */
    public function getProductImageUrlAttribute()
    {
        if (empty($this->product_image)) {
            return null;
        }

        // If URL already starts with http/https, return as is
        if (str_starts_with($this->product_image, 'http://') || str_starts_with($this->product_image, 'https://')) {
            return $this->product_image;
        }

        // If URL starts with /storage, prepend the app URL
        if (str_starts_with($this->product_image, '/storage')) {
            return config('app.url') . $this->product_image;
        }

        // If URL doesn't start with /, add it and prepend app URL
        return config('app.url') . '/' . ltrim($this->product_image, '/');
    }
}
