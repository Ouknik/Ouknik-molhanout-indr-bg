<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number', 'shop_id', 'user_id',
        'delivery_address', 'delivery_latitude', 'delivery_longitude',
        'preferred_delivery_time', 'notes', 'status',
        'accepted_offer_id', 'total_amount',
        'confirmation_pin', 'qr_code',
        'published_at', 'accepted_at', 'delivered_at', 'offer_count',
    ];

    protected function casts(): array
    {
        return [
            'delivery_latitude' => 'decimal:8',
            'delivery_longitude' => 'decimal:8',
            'preferred_delivery_time' => 'datetime',
            'total_amount' => 'decimal:2',
            'published_at' => 'datetime',
            'accepted_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(Str::random(8));
            }
            if (empty($order->confirmation_pin)) {
                $order->confirmation_pin = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            }
        });
    }

    // ── Relationships ──

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function acceptedOffer()
    {
        return $this->belongsTo(Offer::class, 'accepted_offer_id');
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }

    // ── Scopes ──

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeNearby($query, float $lat, float $lng, float $radiusKm = 50)
    {
        return $query->selectRaw("
            orders.*, (
                6371 * acos(
                    cos(radians(?)) * cos(radians(delivery_latitude))
                    * cos(radians(delivery_longitude) - radians(?))
                    + sin(radians(?)) * sin(radians(delivery_latitude))
                )
            ) AS distance
        ", [$lat, $lng, $lat])
        ->having('distance', '<=', $radiusKm)
        ->orderBy('distance');
    }

    // ── Actions ──

    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function acceptOffer(Offer $offer): void
    {
        $this->update([
            'status' => 'accepted',
            'accepted_offer_id' => $offer->id,
            'total_amount' => $offer->total_amount,
            'accepted_at' => now(),
        ]);

        $offer->update(['status' => 'accepted']);

        // Reject other offers
        $this->offers()
            ->where('id', '!=', $offer->id)
            ->update(['status' => 'rejected']);

        // Create delivery record
        Delivery::create([
            'order_id' => $this->id,
            'offer_id' => $offer->id,
            'distributor_id' => $offer->distributor_id,
            'confirmation_pin' => $this->confirmation_pin,
            'status' => 'preparing',
        ]);
    }
}
