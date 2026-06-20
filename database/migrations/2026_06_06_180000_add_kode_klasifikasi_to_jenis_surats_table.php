<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('jenis_surats', 'kode_klasifikasi')) {
            Schema::table('jenis_surats', function (Blueprint $table): void {
                $table->string('kode_klasifikasi', 50)
                    ->nullable()
                    ->after('kode_surat');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('jenis_surats', 'kode_klasifikasi')) {
            Schema::table('jenis_surats', function (Blueprint $table): void {
                $table->dropColumn('kode_klasifikasi');
            });
        }
    }
};
