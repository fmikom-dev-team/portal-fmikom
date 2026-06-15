<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MitraTestSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'mitra@test.com'],
            [
                'name' => 'PT Teknologi Nusantara',
                'password' => Hash::make('password'),
                'user_type' => 'mitra',
                'email_verified_at' => now(),
            ]
        );

        echo "=== AKUN MITRA TEST ===\n";
        echo "Email   : mitra@test.com\n";
        echo "Password: password\n";
        echo "User ID : {$user->id}\n";
        echo "========================\n";
    }
}
