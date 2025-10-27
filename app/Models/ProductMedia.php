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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
