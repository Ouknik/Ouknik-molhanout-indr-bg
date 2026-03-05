<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferItem extends Model
{
    protected $fillable = [
        'offer_id', 'order_item_id', 'product_id',
        'unit_price', 'quantity', 'subtotal',
        'is_available', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'quantity' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted(): void
    {
        static::saving(function (OfferItem $item) {
            $item->subtotal = $item->unit_price * $item->quantity;
        });
    }
}
