<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// LEGACY-ONLY: cleanup role slug lama dari fase awal bootstrap aplikasi.
// Tidak dipakai sebagai skema aktif setelah sumber identitas pindah ke user_type.
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Only 5 roles are allowed: admin, mahasiswa, dosen, kaprodi, dekan.
     * The legacy "super-admin" and "user" roles are removed. Any users still
     * pointing to them are reassigned (super-admin -> admin, user -> mahasiswa).
     */
    public function up(): void
    {
        $roles = DB::table('roles')->pluck('id', 'slug');

        $adminId = $roles['admin'] ?? null;
        $mahasiswaId = $roles['mahasiswa'] ?? null;

        if (isset($roles['super-admin'])) {
            if ($adminId !== null) {
                DB::table('users')
                    ->where('role_id', $roles['super-admin'])
                    ->update(['role_id' => $adminId]);
            }

            DB::table('roles')->where('slug', 'super-admin')->delete();
        }

        if (isset($roles['user'])) {
            if ($mahasiswaId !== null) {
                DB::table('users')
                    ->where('role_id', $roles['user'])
                    ->update(['role_id' => $mahasiswaId]);
            }

            DB::table('roles')->where('slug', 'user')->delete();
        }
    }

    /**
     * Reverse the migrations.
     *
     * Recreates the removed roles. User assignments are not restored.
     */
    public function down(): void
    {
        foreach ([
            ['nama' => 'Super Admin', 'slug' => 'super-admin', 'deskripsi' => null],
            ['nama' => 'User / Mahasiswa', 'slug' => 'user', 'deskripsi' => null],
        ] as $role) {
            DB::table('roles')->updateOrInsert(
                ['slug' => $role['slug']],
                [
                    'nama' => $role['nama'],
                    'deskripsi' => $role['deskripsi'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }
};
