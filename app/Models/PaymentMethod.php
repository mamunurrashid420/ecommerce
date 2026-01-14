<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PaymentMethod extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'name_bn',
        'logo',
        'information',
        'description',
        'description_bn',
        'sort_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'information' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['logo_url'];

    /**
     * Get the full URL for the logo.
     *
     * @return string|null
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            // If logo starts with http:// or https://, return as is
            if (preg_match('/^https?:\/\//', $this->logo)) {
                return $this->logo;
            }
            
            // Otherwise, return storage URL
            return Storage::disk('public')->url($this->logo);
        }
        
        return null;
    }

    /**
     * Scope a query to only include active payment methods.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by sort_order and name.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Delete the logo file when deleting the payment method.
     */
    protected static function booted()
    {
        static::deleting(function ($paymentMethod) {
            if ($paymentMethod->logo && !preg_match('/^https?:\/\//', $paymentMethod->logo)) {
                Storage::disk('public')->delete($paymentMethod->logo);
            }
        });
    }
}

