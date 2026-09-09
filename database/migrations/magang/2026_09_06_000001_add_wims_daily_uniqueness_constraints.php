<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateAttendance = DB::table('absensi_magangs')
            ->select('pendaftaran_id', 'tanggal')
            ->groupBy('pendaftaran_id', 'tanggal')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        $duplicateLogbooks = DB::table('logbook_magangs')
            ->select('pendaftaran_id', 'tanggal')
            ->groupBy('pendaftaran_id', 'tanggal')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicateAttendance || $duplicateLogbooks) {
            throw new \RuntimeException(
                'Migrasi WIMS dibatalkan karena ditemukan data presensi atau logbook ganda pada pendaftaran dan tanggal yang sama. Bersihkan duplikasi secara manual sebelum menjalankan migrasi ulang.',
            );
        }

        Schema::table('absensi_magangs', function (Blueprint $table): void {
            $table->unique(['pendaftaran_id', 'tanggal'], 'absensi_magangs_pendaftaran_tanggal_unique');
        });

        Schema::table('logbook_magangs', function (Blueprint $table): void {
            $table->unique(['pendaftaran_id', 'tanggal'], 'logbook_magangs_pendaftaran_tanggal_unique');
        });

        Schema::table('pendaftaran_magangs', function (Blueprint $table): void {
            $table->index(
                ['dosen_pembimbing_id', 'status', 'tanggal_mulai'],
                'pendaftaran_magangs_dosen_status_mulai_index',
            );
            $table->index(
                ['perusahaan_id', 'status', 'tanggal_mulai'],
                'pendaftaran_magangs_perusahaan_status_mulai_index',
            );
        });
    }

    public function down(): void
    {
        Schema::table('absensi_magangs', function (Blueprint $table): void {
            $table->dropUnique('absensi_magangs_pendaftaran_tanggal_unique');
        });

        Schema::table('logbook_magangs', function (Blueprint $table): void {
            $table->dropUnique('logbook_magangs_pendaftaran_tanggal_unique');
        });

        Schema::table('pendaftaran_magangs', function (Blueprint $table): void {
            $table->dropIndex('pendaftaran_magangs_dosen_status_mulai_index');
            $table->dropIndex('pendaftaran_magangs_perusahaan_status_mulai_index');
        });
    }
};
