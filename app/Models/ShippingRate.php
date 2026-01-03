<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    protected $fillable = [
        'category',
        'subcategory',
        'description_bn',
        'description_en',
        'rate_air',
        'rate_ship',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'rate_air' => 'decimal:2',
        'rate_ship' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get rates as an object
     */
    public function getRatesAttribute()
    {
        return [
            'air' => (float) $this->rate_air,
            'ship' => (float) $this->rate_ship,
        ];
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeBySubcategory($query, $subcategory)
    {
        return $query->where('subcategory', $subcategory);
    }

    /**
     * Get rates grouped by category
     */
    public static function getGroupedRates()
    {
        return static::active()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('subcategory')
            ->get()
            ->groupBy('category');
    }
}
