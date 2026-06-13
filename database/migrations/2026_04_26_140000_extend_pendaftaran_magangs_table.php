<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_magangs', function (Blueprint $table) {
            $table->unsignedBigInteger('perusahaan_id')->nullable()->change();
            $table->string('perusahaan_diminati_nama')->nullable()->after('mahasiswa_id');
            $table->text('perusahaan_diminati_alamat')->nullable()->after('perusahaan_diminati_nama');
            $table->text('catatan_pengajuan')->nullable()->after('perusahaan_diminati_alamat');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_magangs', function (Blueprint $table) {
            $table->dropColumn([
                'perusahaan_diminati_nama',
                'perusahaan_diminati_alamat',
                'catatan_pengajuan',
            ]);
            $table->unsignedBigInteger('perusahaan_id')->nullable(false)->change();
        });
    }
};
