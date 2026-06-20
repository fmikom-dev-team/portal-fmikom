<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'slug' => 'admin',
                'nama' => 'Admin',
                'deskripsi' => 'Pengelola validasi administrasi surat FAST.',
            ],
            [
                'slug' => 'mahasiswa',
                'nama' => 'Mahasiswa',
                'deskripsi' => 'Pengguna mahasiswa untuk layanan akademik FAST.',
            ],
            [
                'slug' => 'dosen',
                'nama' => 'Dosen',
                'deskripsi' => 'Pengguna dosen untuk layanan akademik FAST.',
            ],
            [
                'slug' => 'kaprodi',
                'nama' => 'Kaprodi',
                'deskripsi' => 'Pemberi persetujuan surat tingkat program studi.',
            ],
            [
                'slug' => 'dekan',
                'nama' => 'Dekan',
                'deskripsi' => 'Pemberi persetujuan surat tingkat fakultas.',
            ],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['slug' => $role['slug']],
                $role,
            );
        }
    }
}
