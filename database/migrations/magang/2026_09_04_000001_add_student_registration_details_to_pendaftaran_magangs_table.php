<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_magangs', function (Blueprint $table): void {
            $table->string('status_kip', 20)->nullable()->after('catatan_pengajuan');
            $table->unsignedSmallInteger('sks_ditempuh')->nullable()->after('status_kip');
            $table->string('transkrip_nilai_path')->nullable()->after('sks_ditempuh');
            $table->string('transkrip_nilai_original_name')->nullable()->after('transkrip_nilai_path');
            $table->timestamp('transkrip_nilai_uploaded_at')->nullable()->after('transkrip_nilai_original_name');
            $table->string('surat_rekomendasi_kaprodi_path')->nullable()->after('transkrip_nilai_uploaded_at');
            $table->string('surat_rekomendasi_kaprodi_original_name')->nullable()->after('surat_rekomendasi_kaprodi_path');
            $table->timestamp('surat_rekomendasi_kaprodi_uploaded_at')->nullable()->after('surat_rekomendasi_kaprodi_original_name');
            $table->string('bidang_minat')->nullable()->after('surat_rekomendasi_kaprodi_uploaded_at');
            $table->string('bidang_minat_lainnya')->nullable()->after('bidang_minat');
            $table->string('ukuran_seragam')->nullable()->after('bidang_minat_lainnya');
            $table->string('ukuran_seragam_custom')->nullable()->after('ukuran_seragam');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_magangs', function (Blueprint $table): void {
            $table->dropColumn([
                'status_kip',
                'sks_ditempuh',
                'transkrip_nilai_path',
                'transkrip_nilai_original_name',
                'transkrip_nilai_uploaded_at',
                'surat_rekomendasi_kaprodi_path',
                'surat_rekomendasi_kaprodi_original_name',
                'surat_rekomendasi_kaprodi_uploaded_at',
                'bidang_minat',
                'bidang_minat_lainnya',
                'ukuran_seragam',
                'ukuran_seragam_custom',
            ]);
        });
    }
};
