<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('absensi_magangs', function (Blueprint $table) {

            // waktu dari server
            $table->timestamp('timestamp_masuk')->nullable();
            $table->timestamp('timestamp_keluar')->nullable();

            // jarak hasil perhitungan
            $table->double('distance_masuk')->nullable();
            $table->double('distance_keluar')->nullable();

            // keamanan
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('absensi_magangs', function (Blueprint $table) {
            $table->dropColumn([
                'timestamp_masuk',
                'timestamp_keluar',
                'distance_masuk',
                'distance_keluar',
                'ip_address',
                'user_agent'
            ]);
        });
    }
};
