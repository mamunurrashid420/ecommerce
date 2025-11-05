<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductMedia extends Model
{
    protected $fillable = [
        'product_id', 'type', 'url', 'alt_text', 'title', 'is_thumbnail', 'sort_order', 'file_size', 'mime_type'
    ];

    protected $casts = [
        'is_thumbnail' => 'boolean',
    ];

    protected $appends = ['full_url'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the full URL for the media file
     */
    public function getFullUrlAttribute()
    {
        if (empty($this->url)) {
            return null;
        }

        // If URL already starts with http/https, return as is
        if (str_starts_with($this->url, 'http://') || str_starts_with($this->url, 'https://')) {
            return $this->url;
        }

        // If URL starts with /storage, prepend the app URL
        if (str_starts_with($this->url, '/storage')) {
            return config('app.url') . $this->url;
        }

        // If URL doesn't start with /, add it and prepend app URL
        return config('app.url') . '/' . ltrim($this->url, '/');
    }

    /**
     * Override the url attribute to return full URL in JSON
     */
    public function getUrlAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        // If URL already starts with http/https, return as is
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        // If URL starts with /storage, prepend the app URL
        if (str_starts_with($value, '/storage')) {
            return config('app.url') . $value;
        }

        // If URL doesn't start with /, add it and prepend app URL
        return config('app.url') . '/' . ltrim($value, '/');
    }
}
