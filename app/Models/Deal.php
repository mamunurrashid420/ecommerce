<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Deal extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'short_description',
        'type', // 'product', 'category', 'flash', 'buy_x_get_y', 'minimum_purchase'
        'discount_type', // 'percentage', 'fixed'
        'discount_value',
        'original_price',
        'deal_price',
        'minimum_purchase_amount',
        'maximum_discount',
        'applicable_products',
        'applicable_categories',
        'buy_quantity',
        'get_quantity',
        'get_product_id',
        'start_date',
        'end_date',
        'is_active',
        'is_featured',
        'priority',
        'image_url',
        'banner_image_url',
        'usage_limit',
        'usage_count',
        'usage_limit_per_customer',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'original_price' => 'decimal:2',
        'deal_price' => 'decimal:2',
        'minimum_purchase_amount' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'buy_quantity' => 'integer',
        'get_quantity' => 'integer',
        'priority' => 'integer',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'usage_limit_per_customer' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'applicable_products' => 'array',
        'applicable_categories' => 'array',
    ];

    protected $appends = [
        'is_valid',
        'time_remaining',
        'discount_percentage',
    ];

    /**
     * Get products associated with this deal
     */
    public function products()
    {
        if ($this->applicable_products) {
            return Product::whereIn('id', $this->applicable_products);
        }
        return Product::whereRaw('1 = 0'); // Return empty query
    }

    /**
     * Get categories associated with this deal
     */
    public function categories()
    {
        if ($this->applicable_categories) {
            return Category::whereIn('id', $this->applicable_categories);
        }
        return Category::whereRaw('1 = 0'); // Return empty query
    }

    /**
     * Get the product for "buy X get Y" deals
     */
    public function getProduct()
    {
        if ($this->get_product_id) {
            return Product::find($this->get_product_id);
        }
        return null;
    }

    /**
     * Get creator user
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get updater user
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if deal is currently valid
     */
    public function getIsValidAttribute(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Get time remaining until deal ends
     */
    public function getTimeRemainingAttribute(): ?array
    {
        if (!$this->end_date) {
            return null;
        }

        $now = Carbon::now();
        $end = Carbon::parse($this->end_date);

        if ($now->gt($end)) {
            return [
                'days' => 0,
                'hours' => 0,
                'minutes' => 0,
                'seconds' => 0,
                'expired' => true,
            ];
        }

        $diff = $now->diff($end);

        return [
            'days' => $diff->days,
            'hours' => $diff->h,
            'minutes' => $diff->i,
            'seconds' => $diff->s,
            'expired' => false,
            'total_seconds' => $now->diffInSeconds($end),
        ];
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentageAttribute(): ?float
    {
        if ($this->discount_type === 'percentage') {
            return (float) $this->discount_value;
        }

        if ($this->original_price && $this->deal_price) {
            $discount = (($this->original_price - $this->deal_price) / $this->original_price) * 100;
            return round($discount, 2);
        }

        return null;
    }

    /**
     * Check if deal can be used by a specific customer
     */
    public function canBeUsedBy(int $customerId): bool
    {
        if (!$this->is_valid) {
            return false;
        }

        if ($this->usage_limit_per_customer) {
            $usageCount = DB::table('deal_usages')
                ->where('deal_id', $this->id)
                ->where('customer_id', $customerId)
                ->count();

            if ($usageCount >= $this->usage_limit_per_customer) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if deal applies to a product
     */
    public function appliesToProduct(int $productId): bool
    {
        if ($this->type === 'product' && $this->applicable_products) {
            return in_array($productId, $this->applicable_products);
        }

        if ($this->type === 'category') {
            $product = Product::find($productId);
            if ($product && $this->applicable_categories) {
                return in_array($product->category_id, $this->applicable_categories);
            }
        }

        return false;
    }

    /**
     * Calculate discount amount for a given order total
     */
    public function calculateDiscount(float $orderTotal): float
    {
        if ($this->discount_type === 'percentage') {
            $discount = ($orderTotal * $this->discount_value) / 100;
            
            if ($this->maximum_discount && $discount > $this->maximum_discount) {
                $discount = $this->maximum_discount;
            }
        } else {
            $discount = $this->discount_value;
        }

        // Discount cannot exceed order total
        return min($discount, $orderTotal);
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeValid($query)
    {
        $now = Carbon::now();
        return $query->where('is_active', true)
            ->where(function($q) use ($now) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', $now);
            })
            ->where(function($q) use ($now) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $now);
            });
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}

