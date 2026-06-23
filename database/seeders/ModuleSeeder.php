<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModuleRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat Dummy Role FMIKOM
        $roles = [
            ['nama' => 'Super Admin', 'slug' => 'super-admin', 'deskripsi' => 'Administrator Tertinggi'],
            ['nama' => 'Admin Struktural', 'slug' => 'admin', 'deskripsi' => 'Admin Operasional dan Struktural'],
            ['nama' => 'Admin Universitas', 'slug' => 'admin-universitas', 'deskripsi' => 'Admin universitas untuk pengelolaan lintas modul'],
            ['nama' => 'Admin Akademik', 'slug' => 'admin-akademik', 'deskripsi' => 'Admin akademik untuk operasional layanan akademik'],
            ['nama' => 'Koordinator Program Studi', 'slug' => 'prodi', 'deskripsi' => 'Pengelola akademik tingkat program studi'],
            ['nama' => 'Kaprodi', 'slug' => 'kaprodi', 'deskripsi' => 'Koordinator program studi untuk approval FAST'],
            ['nama' => 'Dekan', 'slug' => 'dekan', 'deskripsi' => 'Dekan fakultas untuk approval FAST'],
            ['nama' => 'Dosen / Struktural', 'slug' => 'dosen', 'deskripsi' => 'Dosen Pengajar FMIKOM'],
            ['nama' => 'Mahasiswa', 'slug' => 'mahasiswa', 'deskripsi' => 'Mahasiswa Aktif FMIKOM'],
            ['nama' => 'Alumni', 'slug' => 'alumni', 'deskripsi' => 'Alumni FMIKOM'],
            ['nama' => 'Mitra Perusahaan', 'slug' => 'mitra', 'deskripsi' => 'Mitra / Pihak Eksternal'],
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(['slug' => $r['slug']], $r);
        }

        // 2. Buat Data Master Modules (Aplikasi Inti FMIKOM)
        $modules = [
            ['code' => 'PAGI', 'name' => 'Works and Gallery for Interns', 'description' => 'Sistem galeri karya mahasiswa magang', 'is_active' => true],
            ['code' => 'WIMS', 'name' => 'Web-based Internship Management System', 'description' => 'Pengelolaan PKL dan magang FMIKOM: pendaftaran, penempatan, presensi, logbook, monitoring, penilaian, dan laporan akhir', 'is_active' => true],
            ['code' => 'FAST', 'name' => 'Fmikom Academic System and Tracking', 'description' => 'Sistem pelacakan dan akademik FMIKOM', 'is_active' => true],
            ['code' => 'TRACE', 'name' => 'Tracer Study System', 'description' => 'Sistem tracer study alumni', 'is_active' => true],
        ];

        foreach ($modules as $m) {
            Module::firstOrCreate(['code' => $m['code']], $m);
        }

        // Mapping role yang diizinkan untuk setiap modul (tabel module_roles)
        $moduleRolesMap = [
            'FAST' => ['super-admin', 'admin', 'admin-universitas', 'admin-akademik', 'prodi', 'kaprodi', 'dekan', 'dosen', 'mahasiswa', 'alumni'],
            'PAGI' => ['super-admin', 'admin', 'dosen', 'mahasiswa', 'alumni', 'mitra'],
            'WIMS' => ['super-admin', 'admin', 'admin-universitas', 'admin-akademik', 'prodi', 'dosen', 'mahasiswa', 'mitra'],
            'TRACE' => ['super-admin', 'admin', 'alumni', 'mitra'],
        ];

        foreach ($moduleRolesMap as $modCode => $roleSlugs) {
            $module = Module::where('code', $modCode)->first();
            if ($module) {
                foreach ($roleSlugs as $slug) {
                    $role = Role::where('slug', $slug)->first();
                    if ($role) {
                        $module->roles()->syncWithoutDetaching([$role->id => ['is_default' => false]]);
                    }
                }
            }
        }

        // 3. SEEDING CONTOH: User dengan banyak role di berbagai Modul
        // ========================================================

        // Contoh 1: Pak Muchlisin Maruf (Pemilik Berbagai Role)
        $user1 = User::firstOrCreate(
            ['email' => 'muchlisinmaruf@gmail.com'],
            [
                'name' => 'Muchlisin Maruf',
                'password' => Hash::make('password123'),
                'is_active' => true,
                'user_type' => 'super-admin',
            ]
        );

        if ($user1->user_type !== 'super-admin') {
            $user1->update(['user_type' => 'super-admin']);
        }

        // --- Role Super Admin (Akses Semua Modul) ---
        $superAdminRole = Role::where('slug', 'super-admin')->first()->id;
        foreach (['FAST', 'WIMS', 'PAGI', 'TRACE'] as $modCode) {
            UserModuleRole::firstOrCreate([
                'user_id' => $user1->id,
                'module_id' => Module::where('code', $modCode)->first()->id,
                'role_id' => $superAdminRole,
            ]);
        }

        // --- Role Admin Struktural (Akses FAST & WIMS) ---
        $adminRole = Role::where('slug', 'admin')->first()->id;
        UserModuleRole::firstOrCreate(['user_id' => $user1->id, 'module_id' => Module::where('code', 'FAST')->first()->id, 'role_id' => $adminRole]);
        UserModuleRole::firstOrCreate(['user_id' => $user1->id, 'module_id' => Module::where('code', 'WIMS')->first()->id, 'role_id' => $adminRole]);

        // --- Role Alumni (Akses TRACE) ---
        UserModuleRole::firstOrCreate(['user_id' => $user1->id, 'module_id' => Module::where('code', 'TRACE')->first()->id, 'role_id' => Role::where('slug', 'alumni')->first()->id]);

        // --- Role Mitra (Akses PAGI) ---
        UserModuleRole::firstOrCreate(['user_id' => $user1->id, 'module_id' => Module::where('code', 'PAGI')->first()->id, 'role_id' => Role::where('slug', 'mitra')->first()->id]);

        // ========================================================

        // Contoh 2: Andi (Mahasiswa di FAST & PAGI)
        $user2 = User::firstOrCreate(
            ['email' => 'andimahasiswa@example.com'],
            [
                'name' => 'Andi Darmawan',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );

        UserModuleRole::firstOrCreate([
            'user_id' => $user2->id,
            'module_id' => Module::where('code', 'PAGI')->first()->id,
            'role_id' => Role::where('slug', 'mahasiswa')->first()->id,
        ]);

        UserModuleRole::firstOrCreate([
            'user_id' => $user2->id,
            'module_id' => Module::where('code', 'FAST')->first()->id,
            'role_id' => Role::where('slug', 'mahasiswa')->first()->id,
        ]);

        // ========================================================

        // Contoh 3: Akun Alumni yang sedang Anda test untuk role pendaftar baru
        // Supaya ketika Anda daftar baru/login bisa langsung di tes
        $user3 = User::firstOrCreate(
            ['email' => 'alumni@example.com'],
            [
                'name' => 'Joni Alumni',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );

        UserModuleRole::firstOrCreate([
            'user_id' => $user3->id,
            'module_id' => Module::where('code', 'TRACE')->first()->id,
            'role_id' => Role::where('slug', 'alumni')->first()->id,
        ]);

        UserModuleRole::firstOrCreate([
            'user_id' => $user3->id,
            'module_id' => Module::where('code', 'FAST')->first()->id,
            'role_id' => Role::where('slug', 'alumni')->first()->id, // Bisa ajukan surat legalisir dst
        ]);

    }
}