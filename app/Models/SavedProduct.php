<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedProduct extends Model
{
    protected $fillable = [
        'customer_id',
        'product_id',
        'product_code',
        'product_name',
        'product_price',
        'product_image',
        'product_sku',
        'product_slug',
        'product_category',
    ];

    protected $appends = ['product_image_url'];

    /**
     * Relationships
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
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

