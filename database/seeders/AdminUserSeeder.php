<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModuleRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed akun admin beserta role assignment ke semua modul yang relevan.
     *
     * Jalankan: php artisan db:seed --class=AdminUserSeeder
     */
    public function run(): void
    {
        // Pastikan role admin tersedia
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['nama' => 'Admin Struktural', 'deskripsi' => 'Admin Operasional dan Struktural']
        );

        // Buat user admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@fmikom.id'],
            [
                'name'                => 'Administrator FMIKOM',
                'password'            => Hash::make('admin1234'),
                'user_type'           => 'staff',
                'is_active'           => true,
                'email_verified_at'   => now(),
                'password_changed_at' => now(),
                'role_title'          => 'Administrator Sistem',
                'bio'                 => 'Administrator portal FMIKOM. Bertanggung jawab atas pengelolaan sistem dan data akademik.',
                'location'            => 'Purwokerto, Indonesia',
            ]
        );

        // Assign role admin ke semua modul yang mendukung role admin
        $moduleCodes = ['FAST', 'WIMS', 'PAGI', 'TRACE'];

        foreach ($moduleCodes as $code) {
            $module = Module::where('code', $code)->first();

            if ($module) {
                UserModuleRole::firstOrCreate([
                    'user_id'   => $admin->id,
                    'module_id' => $module->id,
                    'role_id'   => $adminRole->id,
                ]);
            }
        }

        $this->command->info("✅ Admin user berhasil dibuat/diperbarui:");
        $this->command->info("   Email    : admin@fmikom.id");
        $this->command->info("   Password : admin1234");
        $this->command->info("   Modules  : " . implode(', ', $moduleCodes));
    }
}
