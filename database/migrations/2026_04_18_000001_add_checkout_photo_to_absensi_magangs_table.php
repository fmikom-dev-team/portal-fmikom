<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_magangs', function (Blueprint $table) {
            $table->string('foto_bukti_checkout_path')->nullable()->after('foto_bukti_path');
        });
    }

    public function down(): void
    {
        Schema::table('absensi_magangs', function (Blueprint $table) {
            $table->dropColumn('foto_bukti_checkout_path');
        });
    }
};
