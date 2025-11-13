<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'address', 'role', 'otp', 'otp_expires_at', 'profile_picture',
        'is_banned', 'is_suspended', 'banned_at', 'suspended_at', 'ban_reason', 'suspend_reason'
    ];

    protected $hidden = [
        'password', 'remember_token', 'otp', 'otp_expires_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'password' => 'hashed',
        'is_banned' => 'boolean',
        'is_suspended' => 'boolean',
        'banned_at' => 'datetime',
        'suspended_at' => 'datetime',
    ];

    protected $appends = [
        'profile_picture_url',
    ];

    /**
     * Get the profile picture URL
     */
    public function getProfilePictureUrlAttribute()
    {
        if (!$this->profile_picture) {
            return null;
        }
        
        return asset('storage/' . $this->profile_picture);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if customer is banned
     */
    public function isBanned()
    {
        return $this->is_banned === true;
    }

    /**
     * Check if customer is suspended
     */
    public function isSuspended()
    {
        return $this->is_suspended === true;
    }

    /**
     * Check if customer can make purchases
     */
    public function canMakePurchases()
    {
        return !$this->isBanned() && !$this->isSuspended();
    }
}
