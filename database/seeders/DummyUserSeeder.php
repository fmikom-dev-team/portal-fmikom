<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyUserSeeder extends Seeder
{
    public function run(): void
    {
        $roleAdmin = Role::firstOrCreate(['slug' => 'super-admin'], ['nama' => 'Super Admin']);
        $roleUser = Role::firstOrCreate(['slug' => 'user'], ['nama' => 'User / Mahasiswa']);

        // Akun Admin Muchlisin
        User::updateOrCreate(
            ['email' => 'muchlisinmaruf@gmail.com'],
            [
                'name' => 'Muchlisin Maruf (Admin)',
                'password' => Hash::make('admin123'),
                'user_type' => 'super_admin',
                'email_verified_at' => now(),
                'password_changed_at' => now(),
                'role_title' => 'Dekan Fakultas Ilmu Komputer',
                'bio' => 'Dekan Fakultas Ilmu Komputer. Berdedikasi dalam riset Artificial Intelligence dan Cloud Computing. Silakan hubungi saya untuk urusan kemahasiswaan atau kolaborasi riset.',
                'location' => 'Purwokerto, Indonesia',
                'website' => 'https://muchlisin.dev',
                'twitter' => 'muchlisin_m',
                'linkedin' => 'muchlisin',
                'github' => 'muchlisin',
            ]
        );

        // Akun Pelajar Dummy
        User::updateOrCreate(
            ['email' => 'mahasiswa@example.com'],
            [
                'name' => 'Dummy Mahasiswa',
                'password' => Hash::make('mahasiswa123'),
                'user_type' => 'mahasiswa',
                'email_verified_at' => now(),
                'password_changed_at' => now(),
                'role_title' => 'Mahasiswa Teknik Informatika',
                'bio' => 'Mahasiswa Teknik Informatika Angkatan 2024. Senang belajar pengembangan web modern menggunakan Laravel, Vue.js, dan Tailwind CSS.',
                'location' => 'Purwokerto, Indonesia',
                'website' => 'https://dummystudent.github.io',
                'twitter' => 'dummystudent',
                'linkedin' => 'dummystudent',
                'github' => 'dummystudent',
            ]
        );
    }
}
