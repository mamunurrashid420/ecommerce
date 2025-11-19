<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Deal;

class Product extends Model
{
    protected $fillable = [
        'name', 'description', 'long_description', 'price', 'stock_quantity', 'sku', 'image_url', 
        'category_id', 'is_active', 'meta_title', 'meta_description', 'meta_keywords', 'slug',
        'weight', 'dimensions', 'brand', 'model', 'tags', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
        'tags' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function media()
    {
        return $this->hasMany(ProductMedia::class)->orderBy('sort_order');
    }

    public function customFields()
    {
        return $this->hasMany(ProductCustomField::class)->orderBy('sort_order');
    }

    public function inventoryHistory()
    {
        return $this->hasMany(InventoryHistory::class)->orderBy('created_at', 'desc');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getThumbnailAttribute()
    {
        return $this->media()->where('is_thumbnail', true)->first() ?: $this->media()->first();
    }

    public function getImagesAttribute()
    {
        return $this->media()->where('type', 'image')->get();
    }

    public function getVideosAttribute()
    {
        return $this->media()->where('type', 'video')->get();
    }

    /**
     * Get deals that apply to this product
     * Note: This is a method, not a relationship, due to JSON field queries
     */
    public function getDeals()
    {
        return Deal::where(function($query) {
            $query->where('type', 'product')
                  ->whereJsonContains('applicable_products', $this->id);
        })->orWhere(function($query) {
            if ($this->category_id) {
                $query->where('type', 'category')
                      ->whereJsonContains('applicable_categories', $this->category_id);
            }
        })->valid()->get();
    }
}
