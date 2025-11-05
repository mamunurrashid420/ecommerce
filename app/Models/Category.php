<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'name', 'description', 'slug', 'parent_id', 'image_url', 'icon', 'sort_order',
        'is_active', 'is_featured', 'meta_title', 'meta_description', 'meta_keywords',
        'created_by', 'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['full_image_url'];

    /**
     * Relationships
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
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

    public function scopeParent($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeChild($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Accessors
     */
    public function getFullImageUrlAttribute()
    {
        if (empty($this->image_url)) {
            return null;
        }

        // If URL already starts with http/https, return as is
        if (str_starts_with($this->image_url, 'http://') || str_starts_with($this->image_url, 'https://')) {
            return $this->image_url;
        }

        // If URL starts with /storage, prepend the app URL
        if (str_starts_with($this->image_url, '/storage')) {
            return config('app.url') . $this->image_url;
        }

        // If URL doesn't start with /, add it and prepend app URL
        return config('app.url') . '/' . ltrim($this->image_url, '/');
    }

    /**
     * Get active products count
     */
    public function getActiveProductsCountAttribute()
    {
        return $this->products()->where('is_active', true)->count();
    }

    /**
     * Get category hierarchy path
     */
    public function getHierarchyPathAttribute()
    {
        $path = collect([$this->name]);
        $parent = $this->parent;
        
        while ($parent) {
            $path->prepend($parent->name);
            $parent = $parent->parent;
        }
        
        return $path->implode(' > ');
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
                
                // Ensure slug uniqueness
                $originalSlug = $category->slug;
                $counter = 1;
                while (static::where('slug', $category->slug)->exists()) {
                    $category->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && !$category->isDirty('slug')) {
                $newSlug = Str::slug($category->name);
                
                // Ensure slug uniqueness (excluding current category)
                $originalSlug = $newSlug;
                $counter = 1;
                while (static::where('slug', $newSlug)->where('id', '!=', $category->id)->exists()) {
                    $newSlug = $originalSlug . '-' . $counter;
                    $counter++;
                }
                $category->slug = $newSlug;
            }
        });
    }
}
