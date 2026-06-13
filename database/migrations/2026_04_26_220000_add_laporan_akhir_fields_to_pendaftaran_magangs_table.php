<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_magangs', function (Blueprint $table) {
            $table->string('laporan_akhir_path')->nullable()->after('catatan_revisi_admin');
            $table->string('laporan_akhir_original_name')->nullable()->after('laporan_akhir_path');
            $table->timestamp('laporan_akhir_uploaded_at')->nullable()->after('laporan_akhir_original_name');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_magangs', function (Blueprint $table) {
            $table->dropColumn([
                'laporan_akhir_path',
                'laporan_akhir_original_name',
                'laporan_akhir_uploaded_at',
            ]);
        });
    }
};
