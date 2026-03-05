<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Shop;
use App\Models\Distributor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // ── Shop Owner ──
        $shopOwner = User::firstOrCreate(
            ['email' => 'shop@molhanout.ma'],
            [
                'name' => 'Boutique Test',
                'phone' => '+212611111111',
                'password' => Hash::make('password'),
                'role' => 'shop_owner',
                'is_active' => true,
                'locale' => 'fr',
                'email_verified_at' => now(),
            ]
        );

        Shop::firstOrCreate(
            ['user_id' => $shopOwner->id],
            [
                'shop_name' => 'Épicerie Al Baraka',
                'address' => '12 Rue Mohammed V, Casablanca',
                'city' => 'Casablanca',
                'latitude' => 33.5731,
                'longitude' => -7.5898,
                'is_verified' => true,
            ]
        );

        // ── Distributor ──
        $distributor = User::firstOrCreate(
            ['email' => 'dist@molhanout.ma'],
            [
                'name' => 'Distributeur Test',
                'phone' => '+212622222222',
                'password' => Hash::make('password'),
                'role' => 'distributor',
                'is_active' => true,
                'locale' => 'fr',
                'email_verified_at' => now(),
            ]
        );

        Distributor::firstOrCreate(
            ['user_id' => $distributor->id],
            [
                'company_name' => 'Distribution Atlas',
                'address' => '45 Bd Zerktouni, Casablanca',
                'city' => 'Casablanca',
                'latitude' => 33.5890,
                'longitude' => -7.6100,
                'service_radius_km' => 50,
                'is_verified' => true,
            ]
        );
    }
}
