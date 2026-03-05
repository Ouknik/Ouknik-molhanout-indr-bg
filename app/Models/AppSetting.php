<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'description'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) return $default;

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    public static function setValue(string $key, mixed $value): void
    {
        $setting = self::where('key', $key)->first();
        if ($setting) {
            $val = is_array($value) ? json_encode($value) : (string) $value;
            $setting->update(['value' => $val]);
        }
    }

    public static function getThemeConfig(): array
    {
        return self::where('group', 'theme')
            ->pluck('value', 'key')
            ->toArray();
    }

    public static function getAllSettings(): array
    {
        return self::all()
            ->groupBy('group')
            ->map(fn($group) => $group->pluck('value', 'key'))
            ->toArray();
    }
}
