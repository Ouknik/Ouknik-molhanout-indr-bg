<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'app_name', 'value' => 'Molhanout', 'type' => 'string'],
            ['key' => 'primary_color', 'value' => '#2E7D32', 'type' => 'string'],
            ['key' => 'secondary_color', 'value' => '#FF8F00', 'type' => 'string'],
            ['key' => 'accent_color', 'value' => '#1565C0', 'type' => 'string'],
            ['key' => 'font_family', 'value' => 'Cairo', 'type' => 'string'],
            ['key' => 'currency', 'value' => 'MAD', 'type' => 'string'],
            ['key' => 'default_language', 'value' => 'fr', 'type' => 'string'],
            ['key' => 'max_delivery_radius', 'value' => '50', 'type' => 'integer'],
            ['key' => 'offer_expiry_hours', 'value' => '24', 'type' => 'integer'],
            ['key' => 'require_verification', 'value' => '1', 'type' => 'boolean'],
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean'],
            ['key' => 'push_notifications', 'value' => '1', 'type' => 'boolean'],
        ];

        foreach ($defaults as $setting) {
            AppSetting::firstOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'type' => $setting['type']]
            );
        }
    }
}
