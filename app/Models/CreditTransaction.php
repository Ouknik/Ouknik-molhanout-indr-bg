<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditTransaction extends Model
{
    protected $fillable = [
        'credit_id', 'customer_id', 'shop_id',
        'type', 'amount', 'description', 'payment_method',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function credit()
    {
        return $this->belongsTo(Credit::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
