<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminUsername = env('DEFAULT_ADMIN_USERNAME');
        $adminEmail = env('DEFAULT_ADMIN_EMAIL');
        $adminPassword = env('DEFAULT_ADMIN_PASSWORD');

        if ($adminUsername && $adminEmail && $adminPassword) {
            User::firstOrCreate(
                ['email' => $adminEmail],
                [
                    'username' => $adminUsername,
                    'password' => Hash::make($adminPassword),
                    'is_admin' => true,
                ]
            );
        }
    }
}
