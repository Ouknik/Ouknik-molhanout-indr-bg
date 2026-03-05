<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Credit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id', 'shop_id', 'description', 'amount',
        'status', 'paid_amount', 'due_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function transactions()
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function getRemainingAttribute(): float
    {
        return $this->amount - $this->paid_amount;
    }

    public function addPayment(float $amount, ?string $method = null, ?string $description = null): CreditTransaction
    {
        $transaction = $this->transactions()->create([
            'customer_id' => $this->customer_id,
            'shop_id' => $this->shop_id,
            'type' => 'payment',
            'amount' => $amount,
            'payment_method' => $method,
            'description' => $description,
        ]);

        $this->paid_amount += $amount;

        if ($this->paid_amount >= $this->amount) {
            $this->status = 'paid';
        } else {
            $this->status = 'partial';
        }

        $this->save();
        $this->customer->recalculateDebt();

        return $transaction;
    }
}
