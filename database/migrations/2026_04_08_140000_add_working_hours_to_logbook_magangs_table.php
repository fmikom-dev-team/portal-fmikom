<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbook_magangs', function (Blueprint $table) {
            $table->time('jam_mulai')->nullable()->after('tanggal');
            $table->time('jam_selesai')->nullable()->after('jam_mulai');
            $table->string('status')->default('draft')->after('catatan_dosen');
        });
    }

    public function down(): void
    {
        Schema::table('logbook_magangs', function (Blueprint $table) {
            $table->dropColumn(['jam_mulai', 'jam_selesai', 'status']);
        });
    }
};
