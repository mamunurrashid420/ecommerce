<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryHistory extends Model
{
    protected $fillable = [
        'product_id',
        'old_quantity',
        'new_quantity',
        'adjustment',
        'reason',
        'reference_type',
        'reference_id',
        'created_by',
    ];

    protected $casts = [
        'old_quantity' => 'integer',
        'new_quantity' => 'integer',
        'adjustment' => 'integer',
    ];

    /**
     * Get the product that owns the inventory history
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who created this history record
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
