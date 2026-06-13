<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueAbsensiConstraint extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_magangs', function (Blueprint $table) {
            $table->unique(
                ['pendaftaran_id', 'tanggal'],
                'unique_absensi_per_hari'
            );
        });
    }

    public function down(): void
    {
        Schema::table('absensi_magangs', function (Blueprint $table) {
            $table->dropUnique('unique_absensi_per_hari');
        });
    }
}
