<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penilaian_magangs', function (Blueprint $table) {
            $table->decimal('nilai_disiplin', 5, 2)->nullable()->after('pembimbing_lapangan_id');
            $table->decimal('nilai_komunikasi', 5, 2)->nullable()->after('nilai_disiplin');
            $table->decimal('nilai_kerja_tim', 5, 2)->nullable()->after('nilai_komunikasi');
            $table->decimal('nilai_kerja_mandiri', 5, 2)->nullable()->after('nilai_kerja_tim');
            $table->decimal('nilai_penampilan', 5, 2)->nullable()->after('nilai_kerja_mandiri');
            $table->decimal('nilai_perilaku', 5, 2)->nullable()->after('nilai_penampilan');
            $table->decimal('nilai_pengetahuan_adaptif', 5, 2)->nullable()->after('nilai_perilaku');
            $table->text('catatan_dosen')->nullable()->after('catatan');
            $table->text('catatan_pembimbing')->nullable()->after('catatan_dosen');
            $table->string('status_penilaian', 20)->default('draft')->after('tanggal_nilai');
            $table->timestamp('dosen_submitted_at')->nullable()->after('status_penilaian');
            $table->timestamp('pembimbing_submitted_at')->nullable()->after('dosen_submitted_at');
            $table->timestamp('finalized_at')->nullable()->after('pembimbing_submitted_at');
        });

        Schema::table('penilaian_magangs', function (Blueprint $table) {
            $table->unique('pendaftaran_id', 'penilaian_magangs_pendaftaran_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('penilaian_magangs', function (Blueprint $table) {
            $table->dropUnique('penilaian_magangs_pendaftaran_id_unique');
            $table->dropColumn([
                'nilai_disiplin',
                'nilai_komunikasi',
                'nilai_kerja_tim',
                'nilai_kerja_mandiri',
                'nilai_penampilan',
                'nilai_perilaku',
                'nilai_pengetahuan_adaptif',
                'catatan_dosen',
                'catatan_pembimbing',
                'status_penilaian',
                'dosen_submitted_at',
                'pembimbing_submitted_at',
                'finalized_at',
            ]);
        });
    }
};
