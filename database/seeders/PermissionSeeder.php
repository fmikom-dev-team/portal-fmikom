<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // ─── SYSTEM / WORKOS ─────────────────────────────────────
            ['group' => 'system',      'name' => 'View WorkOS Dashboard',    'slug' => 'system:workos.view',          'description' => 'Akses halaman WorkOS Admin'],
            ['group' => 'system',      'name' => 'Manage Users',             'slug' => 'system:users.manage',         'description' => 'CRUD user di WorkOS'],
            ['group' => 'system',      'name' => 'Manage Roles',             'slug' => 'system:roles.manage',         'description' => 'CRUD role di WorkOS'],
            ['group' => 'system',      'name' => 'Manage Permissions',       'slug' => 'system:permissions.manage',   'description' => 'CRUD permission di WorkOS'],
            ['group' => 'system',      'name' => 'Manage Organizations',     'slug' => 'system:organizations.manage', 'description' => 'CRUD modul/organisasi'],
            ['group' => 'system',      'name' => 'Approve Users',            'slug' => 'system:users.approve',        'description' => 'Setujui atau tolak pendaftaran user'],
            ['group' => 'system',      'name' => 'View Audit Logs',          'slug' => 'system:audit.view',           'description' => 'Lihat log aktivitas sistem'],

            // ─── AUTHENTICATION ───────────────────────────────────────
            ['group' => 'auth',        'name' => 'View Auth Settings',       'slug' => 'auth:settings.view',          'description' => 'Lihat konfigurasi autentikasi'],
            ['group' => 'auth',        'name' => 'Manage Auth Settings',     'slug' => 'auth:settings.manage',        'description' => 'Ubah konfigurasi autentikasi'],
            ['group' => 'auth',        'name' => 'Manage OTP Settings',      'slug' => 'auth:otp.manage',             'description' => 'Konfigurasi OTP verification'],
            ['group' => 'auth',        'name' => 'Manage Password Policy',   'slug' => 'auth:password.manage',        'description' => 'Atur kebijakan password'],
            ['group' => 'auth',        'name' => 'Manage SSO Providers',     'slug' => 'auth:sso.manage',             'description' => 'Konfigurasi Single Sign-On'],

            // ─── PORTAL ADMIN ─────────────────────────────────────────
            ['group' => 'portal',      'name' => 'View Portal Admin',        'slug' => 'portal:admin.view',           'description' => 'Akses halaman manajemen portal'],
            ['group' => 'portal',      'name' => 'Manage Posts',             'slug' => 'portal:posts.manage',         'description' => 'CRUD artikel berita/pengumuman'],
            ['group' => 'portal',      'name' => 'Publish Posts',            'slug' => 'portal:posts.publish',        'description' => 'Publikasikan atau arsipkan artikel'],
            ['group' => 'portal',      'name' => 'Manage Categories',        'slug' => 'portal:categories.manage',    'description' => 'Kelola kategori artikel'],
            ['group' => 'portal',      'name' => 'Manage Comments',          'slug' => 'portal:comments.manage',      'description' => 'Moderasi komentar artikel'],
            ['group' => 'portal',      'name' => 'Manage Media Gallery',     'slug' => 'portal:media.manage',         'description' => 'Upload/hapus galeri media'],
            ['group' => 'portal',      'name' => 'Manage Appearance',        'slug' => 'portal:appearance.manage',    'description' => 'Konfigurasi tampilan landing page'],
            ['group' => 'portal',      'name' => 'Manage Settings',          'slug' => 'portal:settings.manage',      'description' => 'Konfigurasi pengaturan portal'],

            // ─── PAGI ─────────────────────────────────────────────────
            ['group' => 'pagi',        'name' => 'View PAGI Module',         'slug' => 'pagi:module.view',            'description' => 'Akses modul Works & Gallery'],
            ['group' => 'pagi',        'name' => 'Manage Own Works',         'slug' => 'pagi:works.own',              'description' => 'Edit karya sendiri'],
            ['group' => 'pagi',        'name' => 'View All Works',           'slug' => 'pagi:works.view-all',         'description' => 'Lihat semua karya'],
            ['group' => 'pagi',        'name' => 'Manage All Works',         'slug' => 'pagi:works.manage-all',       'description' => 'Edit/hapus karya semua user'],
            ['group' => 'pagi',        'name' => 'Manage Gallery',           'slug' => 'pagi:gallery.manage',         'description' => 'Kelola galeri foto modul PAGI'],

            // ─── WIMS ─────────────────────────────────────────────────
            ['group' => 'wims',        'name' => 'View WIMS Module',         'slug' => 'wims:module.view',            'description' => 'Akses modul Web Information Management'],
            ['group' => 'wims',        'name' => 'Manage Content',           'slug' => 'wims:content.manage',         'description' => 'CRUD konten web informasi'],
            ['group' => 'wims',        'name' => 'Publish Content',          'slug' => 'wims:content.publish',        'description' => 'Publikasikan konten'],
            ['group' => 'wims',        'name' => 'Manage Users WIMS',        'slug' => 'wims:users.manage',           'description' => 'Kelola user dalam modul WIMS'],

            // ─── FAST ─────────────────────────────────────────────────
            ['group' => 'fast',        'name' => 'View FAST Module',         'slug' => 'fast:module.view',            'description' => 'Akses modul Academic System'],
            ['group' => 'fast',        'name' => 'View Academic Data',       'slug' => 'fast:academic.view',          'description' => 'Lihat data akademik'],
            ['group' => 'fast',        'name' => 'Manage Academic Data',     'slug' => 'fast:academic.manage',        'description' => 'Input/edit data akademik'],
            ['group' => 'fast',        'name' => 'Manage Students',          'slug' => 'fast:students.manage',        'description' => 'Kelola data mahasiswa'],
            ['group' => 'fast',        'name' => 'Manage Courses',           'slug' => 'fast:courses.manage',         'description' => 'Kelola mata kuliah'],
            ['group' => 'fast',        'name' => 'Generate Reports',         'slug' => 'fast:reports.generate',       'description' => 'Ekspor laporan akademik'],

            // ─── TRACE ────────────────────────────────────────────────
            ['group' => 'trace',       'name' => 'View TRACE Module',        'slug' => 'trace:module.view',           'description' => 'Akses modul Tracer Study'],
            ['group' => 'trace',       'name' => 'Fill Tracer Form',         'slug' => 'trace:form.fill',             'description' => 'Isi form tracer study (alumni)'],
            ['group' => 'trace',       'name' => 'View Tracer Data',         'slug' => 'trace:data.view',             'description' => 'Lihat data hasil tracer study'],
            ['group' => 'trace',       'name' => 'Manage Tracer Data',       'slug' => 'trace:data.manage',           'description' => 'Edit/hapus data tracer study'],
            ['group' => 'trace',       'name' => 'Export Tracer Report',     'slug' => 'trace:report.export',         'description' => 'Ekspor laporan tracer study'],
        ];

        $created = 0;
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['slug' => $perm['slug']],
                [
                    'name'        => $perm['name'],
                    'group'       => $perm['group'],
                    'description' => $perm['description'],
                ]
            );
            $created++;
        }

        // Assign permissions ke Super Admin role (semua)
        $superAdmin = Role::where('slug', 'super-admin')->first();
        if ($superAdmin) {
            $allIds = Permission::pluck('id')->toArray();
            $superAdmin->permissions()->sync($allIds);
            $this->command->info("✅ Super Admin diberi semua {$created} permissions.");
        }

        // Assign permissions ke Admin role (kecuali system)
        $admin = Role::where('slug', 'admin')->first();
        if ($admin) {
            $adminPerms = Permission::whereNotIn('group', ['system'])
                ->whereNotIn('slug', ['auth:settings.manage', 'auth:otp.manage', 'auth:password.manage', 'auth:sso.manage'])
                ->pluck('id')->toArray();
            $admin->permissions()->sync($adminPerms);
            $this->command->info("✅ Admin Struktural diberi " . count($adminPerms) . " permissions.");
        }

        // Assign permissions ke Dosen
        $dosen = Role::where('slug', 'dosen')->first();
        if ($dosen) {
            $dosenPerms = Permission::whereIn('slug', [
                'pagi:module.view', 'pagi:works.own',
                'wims:module.view', 'wims:content.manage', 'wims:content.publish',
                'fast:module.view', 'fast:academic.view', 'fast:academic.manage',
                'fast:students.manage', 'fast:courses.manage', 'fast:reports.generate',
                'trace:module.view', 'trace:data.view', 'trace:report.export',
            ])->pluck('id')->toArray();
            $dosen->permissions()->sync($dosenPerms);
        }

        // Assign permissions ke Mahasiswa
        $mahasiswa = Role::where('slug', 'mahasiswa')->first();
        if ($mahasiswa) {
            $mhsPerms = Permission::whereIn('slug', [
                'pagi:module.view', 'pagi:works.own', 'pagi:works.view-all',
                'wims:module.view',
                'fast:module.view', 'fast:academic.view',
                'trace:module.view',
            ])->pluck('id')->toArray();
            $mahasiswa->permissions()->sync($mhsPerms);
        }

        // Assign permissions ke Alumni
        $alumni = Role::where('slug', 'alumni')->first();
        if ($alumni) {
            $alumniPerms = Permission::whereIn('slug', [
                'pagi:module.view', 'pagi:works.own', 'pagi:works.view-all',
                'wims:module.view',
                'trace:module.view', 'trace:form.fill', 'trace:data.view',
            ])->pluck('id')->toArray();
            $alumni->permissions()->sync($alumniPerms);
        }

        // Assign permissions ke Mitra
        $mitra = Role::where('slug', 'mitra')->first();
        if ($mitra) {
            $mitraPerms = Permission::whereIn('slug', [
                'pagi:module.view', 'pagi:works.view-all',
                'wims:module.view',
                'trace:module.view', 'trace:data.view',
            ])->pluck('id')->toArray();
            $mitra->permissions()->sync($mitraPerms);
        }

        $this->command->info("✅ Total {$created} permissions berhasil di-seed dan di-assign ke roles.");
    }
}
