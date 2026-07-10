<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perusahaan_mitras', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable();
            $table->string('mitra_jabatan')->nullable();
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->unsignedInteger('toleransi_terlambat_menit')->default(0);
            $table->json('hari_kerja')->nullable();
            $table->boolean('is_active')->default(true);

            $table->unique('user_id', 'perusahaan_mitras_wims_user_id_unique');
            $table->foreign('user_id', 'perusahaan_mitras_wims_user_id_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('perusahaan_mitras', function (Blueprint $table) {
            $table->dropForeign('perusahaan_mitras_wims_user_id_foreign');
            $table->dropUnique('perusahaan_mitras_wims_user_id_unique');
            $table->dropColumn([
                'user_id',
                'mitra_jabatan',
                'jam_masuk',
                'jam_pulang',
                'toleransi_terlambat_menit',
                'hari_kerja',
                'is_active',
            ]);
        });
    }
};
