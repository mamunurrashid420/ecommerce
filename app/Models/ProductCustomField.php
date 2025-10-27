<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCustomField extends Model
{
    protected $fillable = [
        'product_id', 'label_name', 'value', 'field_type', 'sort_order'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
