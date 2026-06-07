<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom user_type ke tabel users.
     *
     * user_type = Identity Layer dalam arsitektur SSO + RBAC:
     *   - Menggantikan peran role_id sebagai penentu "siapa" user ini
     *   - Digunakan sebagai fallback role di modul jika tidak ada assignment
     *   - Bukan untuk otorisasi — hanya untuk identity dan default behavior
     *
     * Nilai: mahasiswa | alumni | mitra | dosen | staff
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->string('user_type', 50)
                    ->default('mahasiswa')
                    ->after('email')
                    ->comment('Identity layer: mahasiswa, alumni, mitra, dosen, staff');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'user_type')) {
                $table->dropColumn('user_type');
            }
        });
    }
};
