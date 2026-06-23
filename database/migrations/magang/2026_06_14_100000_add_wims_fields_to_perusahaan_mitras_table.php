<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perusahaan_mitras', function (Blueprint $table) {
            if (!Schema::hasColumn('perusahaan_mitras', 'user_id')) {
                $table->foreignId('user_id')->nullable();
            }
            if (!Schema::hasColumn('perusahaan_mitras', 'mitra_jabatan')) {
                $table->string('mitra_jabatan')->nullable();
            }
            if (!Schema::hasColumn('perusahaan_mitras', 'jam_masuk')) {
                $table->time('jam_masuk')->nullable();
            }
            if (!Schema::hasColumn('perusahaan_mitras', 'jam_pulang')) {
                $table->time('jam_pulang')->nullable();
            }
            if (!Schema::hasColumn('perusahaan_mitras', 'toleransi_terlambat_menit')) {
                $table->unsignedInteger('toleransi_terlambat_menit')->default(0);
            }
            if (!Schema::hasColumn('perusahaan_mitras', 'hari_kerja')) {
                $table->json('hari_kerja')->nullable();
            }
            if (!Schema::hasColumn('perusahaan_mitras', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });

        // Add constraints safely
        Schema::table('perusahaan_mitras', function (Blueprint $table) {
            try {
                $table->unique('user_id', 'perusahaan_mitras_wims_user_id_unique');
            } catch (\Throwable $e) {
                // Ignore if unique index already exists
            }
            
            try {
                $table->foreign('user_id', 'perusahaan_mitras_wims_user_id_foreign')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            } catch (\Throwable $e) {
                // Ignore if foreign key already exists
            }
        });
    }

    public function down(): void
    {
        Schema::table('perusahaan_mitras', function (Blueprint $table) {
            try {
                $table->dropForeign('perusahaan_mitras_wims_user_id_foreign');
            } catch (\Throwable $e) {}
            
            try {
                $table->dropUnique('perusahaan_mitras_wims_user_id_unique');
            } catch (\Throwable $e) {}

            $columnsToDrop = [];
            foreach ([
                'user_id',
                'mitra_jabatan',
                'jam_masuk',
                'jam_pulang',
                'toleransi_terlambat_menit',
                'hari_kerja',
                'is_active',
            ] as $column) {
                if (Schema::hasColumn('perusahaan_mitras', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
