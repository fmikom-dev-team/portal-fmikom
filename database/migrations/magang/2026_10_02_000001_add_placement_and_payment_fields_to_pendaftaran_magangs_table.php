<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_magangs', function (Blueprint $table) {
            $table->string('metode_penempatan', 20)->nullable()->after('perusahaan_diminati_alamat');
            $table->string('bukti_pembayaran_path')->nullable()->after('transkrip_nilai_uploaded_at');
            $table->string('bukti_pembayaran_original_name')->nullable()->after('bukti_pembayaran_path');
            $table->timestamp('bukti_pembayaran_uploaded_at')->nullable()->after('bukti_pembayaran_original_name');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_magangs', function (Blueprint $table) {
            $table->dropColumn([
                'metode_penempatan',
                'bukti_pembayaran_path',
                'bukti_pembayaran_original_name',
                'bukti_pembayaran_uploaded_at',
            ]);
        });
    }
};
