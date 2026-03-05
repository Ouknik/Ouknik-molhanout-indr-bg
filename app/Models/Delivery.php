<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delivery extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id', 'offer_id', 'distributor_id',
        'status', 'confirmation_pin', 'qr_code',
        'is_confirmed', 'picked_up_at', 'delivered_at',
        'current_latitude', 'current_longitude', 'delivery_notes',
    ];

    protected function casts(): array
    {
        return [
            'is_confirmed' => 'boolean',
            'picked_up_at' => 'datetime',
            'delivered_at' => 'datetime',
            'current_latitude' => 'decimal:8',
            'current_longitude' => 'decimal:8',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

    public function confirmDelivery(string $pin): bool
    {
        if ($this->confirmation_pin === $pin) {
            $this->update([
                'status' => 'delivered',
                'is_confirmed' => true,
                'delivered_at' => now(),
            ]);

            $this->order->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);

            return true;
        }

        return false;
    }

    public function updateLocation(float $lat, float $lng): void
    {
        $this->update([
            'current_latitude' => $lat,
            'current_longitude' => $lng,
        ]);
    }
}
