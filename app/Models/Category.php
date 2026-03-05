<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name_ar', 'name_fr', 'name_en', 'slug',
        'image', 'description_ar', 'description_fr', 'description_en',
        'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();
        $col = "name_{$locale}";
        return $this->{$col} ?? $this->name_fr;
    }
}
