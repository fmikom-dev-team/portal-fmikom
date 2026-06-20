<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_magangs', function (Blueprint $table) {
            $table->string('perusahaan_diminati_nama')->nullable();
            $table->text('perusahaan_diminati_alamat')->nullable();
            $table->text('catatan_pengajuan')->nullable();
            $table->text('catatan_revisi_admin')->nullable();
            $table->string('laporan_akhir_path')->nullable();
            $table->string('laporan_akhir_original_name')->nullable();
            $table->timestamp('laporan_akhir_uploaded_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_magangs', function (Blueprint $table) {
            $table->dropColumn([
                'perusahaan_diminati_nama',
                'perusahaan_diminati_alamat',
                'catatan_pengajuan',
                'catatan_revisi_admin',
                'laporan_akhir_path',
                'laporan_akhir_original_name',
                'laporan_akhir_uploaded_at',
            ]);
        });
    }
};
