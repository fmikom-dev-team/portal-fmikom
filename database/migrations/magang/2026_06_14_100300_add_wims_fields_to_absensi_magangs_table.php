<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_magangs', function (Blueprint $table) {
            $table->timestamp('timestamp_masuk')->nullable();
            $table->timestamp('timestamp_keluar')->nullable();
            $table->decimal('distance_masuk', 10, 2)->nullable();
            $table->decimal('distance_keluar', 10, 2)->nullable();
            $table->string('foto_bukti_checkout_path')->nullable();
            $table->string('ip_address', 45)->nullable();
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
                'foto_bukti_checkout_path',
                'ip_address',
                'user_agent',
            ]);
        });
    }
};
