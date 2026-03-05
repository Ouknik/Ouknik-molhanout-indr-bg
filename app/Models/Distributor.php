<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Distributor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'company_name', 'registration_number',
        'address', 'city', 'region', 'latitude', 'longitude',
        'service_radius_km', 'phone', 'image', 'description',
        'rating', 'total_orders', 'is_verified', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'service_radius_km' => 'decimal:2',
            'rating' => 'decimal:2',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    /**
     * Scope: orders within distributor's service radius.
     * Uses Haversine formula for GPS filtering.
     */
    public function scopeNearby($query, float $lat, float $lng, float $radiusKm = 50)
    {
        return $query->selectRaw("
            *, (
                6371 * acos(
                    cos(radians(?)) * cos(radians(latitude))
                    * cos(radians(longitude) - radians(?))
                    + sin(radians(?)) * sin(radians(latitude))
                )
            ) AS distance
        ", [$lat, $lng, $lat])
        ->having('distance', '<=', $radiusKm)
        ->orderBy('distance');
    }
}
