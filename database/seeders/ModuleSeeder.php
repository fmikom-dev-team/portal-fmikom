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
        $rolesMap = $this->seedRoles();
        $modulesMap = $this->seedModules();
        $this->syncModuleRoles($modulesMap, $rolesMap);
        $this->seedTestUsers($modulesMap, $rolesMap);
    }

    /**
     * Seed default roles.
     *
     * @return array<string, int> Map of role slug => role id
     */
    private function seedRoles(): array
    {
        $roles = [
            ['nama' => 'Super Admin', 'slug' => 'super-admin', 'deskripsi' => 'Administrator Tertinggi'],
            ['nama' => 'Admin Struktural', 'slug' => 'admin', 'deskripsi' => 'Admin Operasional dan Struktural'],
            ['nama' => 'Admin Universitas', 'slug' => 'admin-universitas', 'deskripsi' => 'Admin universitas untuk pengelolaan lintas modul'],
            ['nama' => 'Admin Akademik', 'slug' => 'admin-akademik', 'deskripsi' => 'Admin akademik untuk operasional layanan akademik'],
            ['nama' => 'Koordinator Program Studi', 'slug' => 'prodi', 'deskripsi' => 'Pengelola akademik tingkat program studi'],
            ['nama' => 'Dosen / Struktural', 'slug' => 'dosen', 'deskripsi' => 'Dosen Pengajar FMIKOM'],
            ['nama' => 'Mahasiswa', 'slug' => 'mahasiswa', 'deskripsi' => 'Mahasiswa Aktif FMIKOM'],
            ['nama' => 'Alumni', 'slug' => 'alumni', 'deskripsi' => 'Alumni FMIKOM'],
            ['nama' => 'Mitra Perusahaan', 'slug' => 'mitra', 'deskripsi' => 'Mitra / Pihak Eksternal'],
        ];

        $rolesMap = [];
        foreach ($roles as $r) {
            $role = Role::firstOrCreate(['slug' => $r['slug']], $r);
            $rolesMap[$r['slug']] = $role->id;
        }

        return $rolesMap;
    }

    /**
     * Seed default modules.
     *
     * @return array<string, int> Map of module code => module id
     */
    private function seedModules(): array
    {
        $modules = [
            ['code' => 'PAGI', 'name' => 'Works and Gallery for Interns', 'description' => 'Sistem galeri karya mahasiswa magang', 'is_active' => true],
            ['code' => 'WIMS', 'name' => 'Web-based Internship Management System', 'description' => 'Pengelolaan PKL dan magang FMIKOM: pendaftaran, penempatan, presensi, logbook, monitoring, penilaian, dan laporan akhir', 'is_active' => true],
            ['code' => 'FAST', 'name' => 'Fmikom Academic System and Tracking', 'description' => 'Sistem pelacakan dan akademik FMIKOM', 'is_active' => true],
            ['code' => 'TRACE', 'name' => 'Tracer Study System', 'description' => 'Sistem tracer study alumni', 'is_active' => true],
        ];

        $modulesMap = [];
        foreach ($modules as $m) {
            $module = Module::firstOrCreate(['code' => $m['code']], $m);
            $modulesMap[$m['code']] = $module->id;
        }

        return $modulesMap;
    }

    /**
     * Sync module-role permissions map.
     *
     * @param array<string, int> $modulesMap
     * @param array<string, int> $rolesMap
     */
    private function syncModuleRoles(array $modulesMap, array $rolesMap): void
    {
        $moduleRolesMap = [
            'FAST' => ['super-admin', 'admin', 'dosen', 'mahasiswa', 'alumni'],
            'PAGI' => ['super-admin', 'admin', 'admin-universitas', 'admin-akademik', 'prodi', 'dosen', 'mahasiswa', 'alumni', 'mitra'],
            'WIMS' => ['super-admin', 'admin', 'admin-universitas', 'admin-akademik', 'prodi', 'dosen', 'mahasiswa', 'mitra'],
            'TRACE' => ['super-admin', 'admin', 'alumni', 'mitra'],
        ];

        foreach ($moduleRolesMap as $modCode => $roleSlugs) {
            $moduleId = $modulesMap[$modCode] ?? null;
            if (! $moduleId) {
                continue;
            }

            $module = Module::find($moduleId, ['*']);
            if ($module) {
                foreach ($roleSlugs as $slug) {
                    $roleId = $rolesMap[$slug] ?? null;
                    if ($roleId) {
                        $module->roles()->syncWithoutDetaching([$roleId => ['is_default' => false]]);
                    }
                }
            }
        }
    }

    /**
     * Seed sample testing accounts and assign user module roles.
     *
     * @param array<string, int> $modulesMap
     * @param array<string, int> $rolesMap
     */
    private function seedTestUsers(array $modulesMap, array $rolesMap): void
    {
        $superAdminRoleId = $rolesMap['super-admin'];
        $adminRoleId = $rolesMap['admin'];
        $dosenRoleId = $rolesMap['dosen'];
        $mahasiswaRoleId = $rolesMap['mahasiswa'];
        $alumniRoleId = $rolesMap['alumni'];
        $mitraRoleId = $rolesMap['mitra'];

        // User 1: Muchlisin Maruf (Super Admin)
        $user1 = User::firstOrCreate(
            ['email' => 'muchlisinmaruf@gmail.com'],
            [
                'name' => 'Muchlisin Maruf',
                'password' => Hash::make('password123'),
                'is_active' => true,
                'user_type' => 'super-admin',
                'email_verified_at' => now(),
                'password_changed_at' => now(),
            ]
        );

        if ($user1->user_type !== 'super-admin') {
            $user1->update(['user_type' => 'super-admin']);
        }

        foreach (['FAST', 'WIMS', 'PAGI', 'TRACE'] as $modCode) {
            UserModuleRole::firstOrCreate([
                'user_id' => $user1->id,
                'module_id' => $modulesMap[$modCode],
                'role_id' => $superAdminRoleId,
            ]);
        }

        UserModuleRole::firstOrCreate(['user_id' => $user1->id, 'module_id' => $modulesMap['FAST'], 'role_id' => $adminRoleId]);
        UserModuleRole::firstOrCreate(['user_id' => $user1->id, 'module_id' => $modulesMap['WIMS'], 'role_id' => $adminRoleId]);
        UserModuleRole::firstOrCreate(['user_id' => $user1->id, 'module_id' => $modulesMap['TRACE'], 'role_id' => $alumniRoleId]);
        UserModuleRole::firstOrCreate(['user_id' => $user1->id, 'module_id' => $modulesMap['PAGI'], 'role_id' => $mitraRoleId]);

        // Testing account: superadmin@test.com
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@test.com'],
            [
                'name' => 'super admin',
                'password' => 'superadmin2026',
                'is_active' => true,
                'user_type' => 'super-admin',
                'email_verified_at' => now(),
                'password_changed_at' => now(),
            ]
        );

        foreach (['FAST', 'WIMS', 'PAGI', 'TRACE'] as $modCode) {
            UserModuleRole::firstOrCreate([
                'user_id' => $superAdmin->id,
                'module_id' => $modulesMap[$modCode],
                'role_id' => $superAdminRoleId,
            ]);
        }

        UserModuleRole::firstOrCreate([
            'user_id' => $superAdmin->id,
            'module_id' => $modulesMap['WIMS'],
            'role_id' => $adminRoleId,
        ]);

        // Testing account: adminwims@test.com
        $adminWims = User::updateOrCreate(
            ['email' => 'adminwims@test.com'],
            [
                'name' => 'admin wims',
                'password' => 'adminwims2026',
                'is_active' => true,
                'user_type' => 'admin',
                'email_verified_at' => now(),
                'password_changed_at' => now(),
            ]
        );

        UserModuleRole::firstOrCreate([
            'user_id' => $adminWims->id,
            'module_id' => $modulesMap['WIMS'],
            'role_id' => $adminRoleId,
        ]);

        // Testing account: dosenwims@test.com
        $dosenWims = User::updateOrCreate(
            ['email' => 'dosenwims@test.com'],
            [
                'name' => 'Alexander',
                'password' => 'dosenwims2026',
                'is_active' => true,
                'user_type' => 'dosen',
                'email_verified_at' => now(),
                'password_changed_at' => now(),
            ]
        );

        UserModuleRole::firstOrCreate([
            'user_id' => $dosenWims->id,
            'module_id' => $modulesMap['WIMS'],
            'role_id' => $dosenRoleId,
        ]);

        // Testing account: mitraunugha@test.com
        $mitraWims = User::updateOrCreate(
            ['email' => 'mitraunugha@test.com'],
            [
                'name' => 'Sanjaya',
                'password' => 'mitraunugha2026@',
                'is_active' => true,
                'user_type' => 'mitra',
                'email_verified_at' => now(),
                'password_changed_at' => now(),
            ]
        );

        UserModuleRole::firstOrCreate([
            'user_id' => $mitraWims->id,
            'module_id' => $modulesMap['WIMS'],
            'role_id' => $mitraRoleId,
        ]);

        UserModuleRole::firstOrCreate([
            'user_id' => $mitraWims->id,
            'module_id' => $modulesMap['PAGI'],
            'role_id' => $mitraRoleId,
        ]);

        // User 2: Andi (Mahasiswa di FAST & PAGI)
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
            'module_id' => $modulesMap['PAGI'],
            'role_id' => $mahasiswaRoleId,
        ]);

        UserModuleRole::firstOrCreate([
            'user_id' => $user2->id,
            'module_id' => $modulesMap['FAST'],
            'role_id' => $mahasiswaRoleId,
        ]);

        // User 3: Joni (Alumni di TRACE & FAST)
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
            'module_id' => $modulesMap['TRACE'],
            'role_id' => $alumniRoleId,
        ]);

        UserModuleRole::firstOrCreate([
            'user_id' => $user3->id,
            'module_id' => $modulesMap['FAST'],
            'role_id' => $alumniRoleId,
        ]);
    }
}
