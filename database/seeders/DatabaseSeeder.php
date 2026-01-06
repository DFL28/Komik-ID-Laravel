<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create default admin user
        User::create([
            'username' => env('DEFAULT_ADMIN_USERNAME', 'admin'),
            'email' => env('DEFAULT_ADMIN_EMAIL', 'admin@komik-id.local'),
            'password' => Hash::make(env('DEFAULT_ADMIN_PASSWORD', 'admin123')),
            'is_admin' => true,
        ]);

        // Create a regular test user
        User::create([
            'username' => 'user',
            'email' => 'user@komik-id.local',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);
    }
}
