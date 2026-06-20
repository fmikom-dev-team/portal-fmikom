<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            if (! Schema::hasColumn('surats', 'tanggal_kebutuhan')) {
                $table->date('tanggal_kebutuhan')->nullable()->after('tanggal_pengajuan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            if (Schema::hasColumn('surats', 'tanggal_kebutuhan')) {
                $table->dropColumn('tanggal_kebutuhan');
            }
        });
    }
};
