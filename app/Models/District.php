<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
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
        'division',
        'division_bn',
        'sort_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the upazillas for the district.
     */
    public function upazillas()
    {
        return $this->hasMany(Upazila::class);
    }

    /**
     * Get active upazillas for the district.
     */
    public function activeUpazillas()
    {
        return $this->hasMany(Upazila::class)->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope a query to only include active districts.
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
     * Scope a query to filter by division.
     */
    public function scopeByDivision($query, $division)
    {
        return $query->where('division', $division);
    }
}

