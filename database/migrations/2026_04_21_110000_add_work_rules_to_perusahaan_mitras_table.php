<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perusahaan_mitras', function (Blueprint $table) {
            $table->time('jam_masuk')->nullable()->after('radius_valid_meter');
            $table->time('jam_pulang')->nullable()->after('jam_masuk');
            $table->unsignedInteger('toleransi_terlambat_menit')->default(0)->after('jam_pulang');
            $table->boolean('is_active')->default(true)->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('perusahaan_mitras', function (Blueprint $table) {
            $table->dropColumn([
                'jam_masuk',
                'jam_pulang',
                'toleransi_terlambat_menit',
                'is_active',
            ]);
        });
    }
};
