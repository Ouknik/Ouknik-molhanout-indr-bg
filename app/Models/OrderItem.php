<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'quantity', 'unit', 'notes',
    ];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:2'];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function offerItems()
    {
        return $this->hasMany(OfferItem::class);
    }
}
