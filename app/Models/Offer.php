<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
        'url',
        'description',
        'is_featured',
        'available_start_time',
        'available_end_time',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'available_start_time' => 'datetime',
        'available_end_time' => 'datetime',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'image_url',
        'is_available',
        'time_remaining'
    ];

    /**
     * Scope to get only active offers
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only featured offers
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to get offers that are currently available (within time range)
     */
    public function scopeAvailable(Builder $query): Builder
    {
        $now = Carbon::now();
        
        return $query->where(function ($q) use ($now) {
            $q->where(function ($subQ) use ($now) {
                // Both start and end time are set
                $subQ->whereNotNull('available_start_time')
                     ->whereNotNull('available_end_time')
                     ->where('available_start_time', '<=', $now)
                     ->where('available_end_time', '>=', $now);
            })->orWhere(function ($subQ) use ($now) {
                // Only start time is set
                $subQ->whereNotNull('available_start_time')
                     ->whereNull('available_end_time')
                     ->where('available_start_time', '<=', $now);
            })->orWhere(function ($subQ) use ($now) {
                // Only end time is set
                $subQ->whereNull('available_start_time')
                     ->whereNotNull('available_end_time')
                     ->where('available_end_time', '>=', $now);
            })->orWhere(function ($subQ) {
                // Neither start nor end time is set (always available)
                $subQ->whereNull('available_start_time')
                     ->whereNull('available_end_time');
            });
        });
    }

    /**
     * Scope to get offers ordered by sort order and creation date
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order', 'desc')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get the full URL for the image
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        // If it's already a full URL, return as is
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        // Otherwise, prepend the storage URL
        return asset('storage/' . $this->image);
    }

    /**
     * Check if the offer is currently available
     */
    public function getIsAvailableAttribute(): bool
    {
        $now = Carbon::now();

        // If both start and end times are set
        if ($this->available_start_time && $this->available_end_time) {
            return $now->between($this->available_start_time, $this->available_end_time);
        }

        // If only start time is set
        if ($this->available_start_time && !$this->available_end_time) {
            return $now->gte($this->available_start_time);
        }

        // If only end time is set
        if (!$this->available_start_time && $this->available_end_time) {
            return $now->lte($this->available_end_time);
        }

        // If neither is set, it's always available
        return true;
    }

    /**
     * Get time remaining until offer expires (in seconds)
     */
    public function getTimeRemainingAttribute(): ?int
    {
        if (!$this->available_end_time) {
            return null;
        }

        $now = Carbon::now();
        if ($now->gt($this->available_end_time)) {
            return 0;
        }

        return (int) $now->diffInSeconds($this->available_end_time);
    }
}