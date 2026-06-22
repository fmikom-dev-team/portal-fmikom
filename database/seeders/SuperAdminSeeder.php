<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModuleRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Seed akun Super Admin dengan akses penuh ke semua modul.
     *
     * Jalankan: php artisan db:seed --class=SuperAdminSeeder
     */
    public function run(): void
    {
        // Pastikan role super-admin tersedia
        $superAdminRole = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            ['nama' => 'Super Admin', 'deskripsi' => 'Administrator Tertinggi']
        );

        // Buat user super admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@fmikom.id'],
            [
                'name'                => 'Super Administrator',
                'password'            => Hash::make('superadmin1234'),
                'user_type'           => 'staff',
                'is_active'           => true,
                'email_verified_at'   => now(),
                'password_changed_at' => now(),
                'role_title'          => 'Super Administrator',
                'bio'                 => 'Super Administrator portal FMIKOM. Akses penuh ke seluruh modul dan fitur sistem.',
                'location'            => 'Purwokerto, Indonesia',
            ]
        );

        // Assign role super-admin ke SEMUA modul aktif
        $modules = Module::where('is_active', true)->get();

        foreach ($modules as $module) {
            UserModuleRole::firstOrCreate([
                'user_id'   => $superAdmin->id,
                'module_id' => $module->id,
                'role_id'   => $superAdminRole->id,
            ]);
        }

        $moduleNames = $modules->pluck('code')->implode(', ');

        $this->command->info("✅ Super Admin berhasil dibuat/diperbarui:");
        $this->command->info("   Email    : superadmin@fmikom.id");
        $this->command->info("   Password : superadmin1234");
        $this->command->info("   Role     : super-admin (bypass semua role check)");
        $this->command->info("   Modules  : {$moduleNames}");
    }
}
