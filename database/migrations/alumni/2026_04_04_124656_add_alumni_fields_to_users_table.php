<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tambah kolom untuk mendukung role Alumni dan Mitra pada registrasi.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tahun lulus — khusus role Alumni
            $table->year('tahun_lulus')->nullable()->after('nomor_induk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tahun_lulus']);
        });
    }
};
