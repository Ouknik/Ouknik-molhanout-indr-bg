<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role',
        'avatar', 'locale', 'is_active', 'fcm_token',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ── Relationships ──

    public function shop()
    {
        return $this->hasOne(Shop::class);
    }

    public function distributor()
    {
        return $this->hasOne(Distributor::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function notifications()
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function disputesRaised()
    {
        return $this->hasMany(Dispute::class, 'raised_by');
    }

    public function disputesAgainst()
    {
        return $this->hasMany(Dispute::class, 'against_user');
    }

    // ── Helpers ──

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isShopOwner(): bool
    {
        return $this->role === 'shop_owner';
    }

    public function isDistributor(): bool
    {
        return $this->role === 'distributor';
    }
}
