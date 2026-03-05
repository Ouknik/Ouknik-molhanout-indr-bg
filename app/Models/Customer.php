<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shop_id', 'name', 'phone', 'address',
        'total_debt', 'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'total_debt' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function credits()
    {
        return $this->hasMany(Credit::class);
    }

    public function transactions()
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function recalculateDebt(): void
    {
        $totalDebt = $this->credits()
            ->where('status', '!=', 'paid')
            ->sum(\DB::raw('amount - paid_amount'));

        $this->update(['total_debt' => $totalDebt]);
    }
}
