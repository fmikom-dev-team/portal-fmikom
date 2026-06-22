<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModuleRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyUserSeeder extends Seeder
{
    public function run(): void
    {
        $roleAdmin = Role::firstOrCreate(['slug' => 'super-admin'], ['nama' => 'Super Admin']);
        $roleMahasiswa = Role::firstOrCreate(['slug' => 'mahasiswa'], ['nama' => 'Mahasiswa']);
        $fastModule = Module::firstOrCreate(
            ['code' => 'FAST'],
            [
                'name' => 'Fmikom Academic System and Tracking',
                'description' => 'Sistem pelacakan dan akademik FMIKOM',
                'is_active' => true,
            ]
        );
        $wimsModule = Module::firstOrCreate(
            ['code' => 'WIMS'],
            [
                'name' => 'Web-based Internship Management System',
                'description' => 'Pengelolaan PKL dan magang FMIKOM',
                'is_active' => true,
            ]
        );

        // Akun Admin Muchlisin
        $admin = User::updateOrCreate(
            ['email' => 'muchlisinmaruf@gmail.com'],
            [
                'name' => 'Muchlisin Maruf (Admin)',
                'password' => Hash::make('admin123'),
                'user_type' => 'super_admin',
                'status_approval' => 'approved',
                'is_active' => true,
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
        $this->assignFastRole($admin, $fastModule, $roleAdmin);

        $fastAdminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['nama' => 'Admin Struktural']
        );
        $fastDosenRole = Role::firstOrCreate(
            ['slug' => 'dosen'],
            ['nama' => 'Dosen / Struktural']
        );
        $fastKaprodiRole = Role::firstOrCreate(
            ['slug' => 'kaprodi'],
            ['nama' => 'Kaprodi']
        );
        $fastDekanRole = Role::firstOrCreate(
            ['slug' => 'dekan'],
            ['nama' => 'Dekan']
        );
        $wimsAdminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['nama' => 'Admin']
        );
        $wimsDosenRole = Role::firstOrCreate(
            ['slug' => 'dosen'],
            ['nama' => 'Dosen']
        );

        // Akun admin FAST untuk pengelolaan portal/approval
        $fastAdmin = User::updateOrCreate(
            ['email' => 'fast.admin@fmikom.test'],
            [
                'name' => 'FAST Admin',
                'password' => Hash::make('admin123'),
                'user_type' => 'admin',
                'status_approval' => 'approved',
                'is_active' => true,
                'email_verified_at' => now(),
                'password_changed_at' => now(),
                'role_title' => 'Admin FAST',
                'location' => 'FMIKOM UNUGHA',
            ]
        );
        $this->assignFastRole($fastAdmin, $fastModule, $fastAdminRole);

        // Akun Kaprodi FAST
        $kaprodi = User::updateOrCreate(
            ['email' => 'fast.kaprodi@fmikom.test'],
            [
                'name' => 'FAST Kaprodi',
                'password' => Hash::make('kaprodi123'),
                'user_type' => 'dosen',
                'status_approval' => 'approved',
                'is_active' => true,
                'email_verified_at' => now(),
                'password_changed_at' => now(),
                'role_title' => 'Kaprodi',
                'location' => 'FMIKOM UNUGHA',
            ]
        );
        $this->assignFastRole($kaprodi, $fastModule, $fastKaprodiRole);

        // Akun Dekan FAST
        $dekan = User::updateOrCreate(
            ['email' => 'fast.dekan@fmikom.test'],
            [
                'name' => 'FAST Dekan',
                'password' => Hash::make('dekan123'),
                'user_type' => 'dosen',
                'status_approval' => 'approved',
                'is_active' => true,
                'email_verified_at' => now(),
                'password_changed_at' => now(),
                'role_title' => 'Dekan',
                'location' => 'FMIKOM UNUGHA',
            ]
        );
        $this->assignFastRole($dekan, $fastModule, $fastDekanRole);

        // Akun Dosen FAST
        $dosen = User::updateOrCreate(
            ['email' => 'fast.dosen@fmikom.test'],
            [
                'name' => 'FAST Dosen',
                'password' => Hash::make('dosen123'),
                'user_type' => 'dosen',
                'status_approval' => 'approved',
                'is_active' => true,
                'email_verified_at' => now(),
                'password_changed_at' => now(),
                'role_title' => 'Dosen',
                'location' => 'FMIKOM UNUGHA',
            ]
        );
        $this->assignFastRole($dosen, $fastModule, $fastDosenRole);

        // Akun admin WIMS
        $wimsAdmin = User::updateOrCreate(
            ['email' => 'wims.admin@fmikom.test'],
            [
                'name' => 'WIMS Admin',
                'password' => Hash::make('admin123'),
                'user_type' => 'admin',
                'status_approval' => 'approved',
                'is_active' => true,
                'email_verified_at' => now(),
                'password_changed_at' => now(),
                'role_title' => 'Admin WIMS',
                'location' => 'FMIKOM UNUGHA',
            ]
        );
        $this->assignFastRole($wimsAdmin, $wimsModule, $wimsAdminRole);

        // Akun dosen WIMS
        $wimsDosen = User::updateOrCreate(
            ['email' => 'wims.dosen@fmikom.test'],
            [
                'name' => 'WIMS Dosen',
                'password' => Hash::make('dosen123'),
                'user_type' => 'dosen',
                'status_approval' => 'approved',
                'is_active' => true,
                'email_verified_at' => now(),
                'password_changed_at' => now(),
                'role_title' => 'Dosen WIMS',
                'location' => 'FMIKOM UNUGHA',
            ]
        );
        $this->assignFastRole($wimsDosen, $wimsModule, $wimsDosenRole);

        // Akun Pelajar Dummy
        $student = User::updateOrCreate(
            ['email' => 'mahasiswa@example.com'],
            [
                'name' => 'Dummy Mahasiswa',
                'password' => Hash::make('mahasiswa123'),
                'user_type' => 'mahasiswa',
                'status_approval' => 'approved',
                'is_active' => true,
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
        $this->assignFastRole($student, $fastModule, $roleMahasiswa);
    }

    private function assignFastRole(User $user, Module $module, Role $role): void
    {
        UserModuleRole::firstOrCreate([
            'user_id' => $user->id,
            'module_id' => $module->id,
            'role_id' => $role->id,
        ], [
            'is_active' => true,
        ]);
    }
}
