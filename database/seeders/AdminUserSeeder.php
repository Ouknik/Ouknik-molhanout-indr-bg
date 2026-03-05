<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@molhanout.ma'],
            [
                'name' => 'Admin Molhanout',
                'phone' => '+212600000000',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'locale' => 'fr',
                'email_verified_at' => now(),
            ]
        );
    }
}
