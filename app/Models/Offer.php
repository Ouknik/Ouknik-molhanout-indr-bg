<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id', 'distributor_id', 'user_id',
        'subtotal', 'delivery_cost', 'total_amount',
        'estimated_delivery_time', 'estimated_delivery_date',
        'notes', 'status', 'is_cheapest', 'is_fastest',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_cost' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'estimated_delivery_date' => 'datetime',
            'is_cheapest' => 'boolean',
            'is_fastest' => 'boolean',
        ];
    }

    // ── Relationships ──

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OfferItem::class);
    }

    // ── Business Logic ──

    public function calculateTotals(): void
    {
        $this->subtotal = $this->items()->sum('subtotal');
        $this->total_amount = $this->subtotal + $this->delivery_cost;
        $this->save();
    }

    /**
     * Mark cheapest and fastest offers for an order.
     */
    public static function markBestOffers(int $orderId): void
    {
        $offers = self::where('order_id', $orderId)
            ->where('status', 'submitted')
            ->get();

        // Reset all flags
        self::where('order_id', $orderId)->update([
            'is_cheapest' => false,
            'is_fastest' => false,
        ]);

        if ($offers->isEmpty()) return;

        // Mark cheapest
        $cheapest = $offers->sortBy('total_amount')->first();
        $cheapest->update(['is_cheapest' => true]);

        // Mark fastest
        $fastest = $offers->sortBy('estimated_delivery_date')->first();
        if ($fastest) {
            $fastest->update(['is_fastest' => true]);
        }
    }
}
