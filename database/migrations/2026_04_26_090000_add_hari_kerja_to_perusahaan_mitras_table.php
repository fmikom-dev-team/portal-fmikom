<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perusahaan_mitras', function (Blueprint $table) {
            $table->json('hari_kerja')->nullable()->after('toleransi_terlambat_menit');
        });
    }

    public function down(): void
    {
        Schema::table('perusahaan_mitras', function (Blueprint $table) {
            $table->dropColumn('hari_kerja');
        });
    }
};
