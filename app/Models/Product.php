<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'name_ar', 'name_fr', 'name_en', 'slug',
        'description_ar', 'description_fr', 'description_en',
        'image', 'unit', 'barcode', 'reference_price',
        'is_active', 'is_custom', 'created_by', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'reference_price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_custom' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();
        $col = "name_{$locale}";
        return $this->{$col} ?? $this->name_fr;
    }

    public function getDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        $col = "description_{$locale}";
        return $this->{$col} ?? $this->description_fr;
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFromCatalog($query)
    {
        return $query->where('is_custom', false);
    }
}
