<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('DEFAULT_ADMIN_EMAIL', 'admin@komik-id.local')],
            [
                'username' => env('DEFAULT_ADMIN_USERNAME', 'admin'),
                'password' => Hash::make(env('DEFAULT_ADMIN_PASSWORD', 'admin123')),
                'is_admin' => true,
            ]
        );
    }
}
