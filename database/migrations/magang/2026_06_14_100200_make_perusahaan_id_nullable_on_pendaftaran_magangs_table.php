<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_magangs', function (Blueprint $table) {
            $table->dropForeign(['perusahaan_id']);
        });

        Schema::table('pendaftaran_magangs', function (Blueprint $table) {
            $table->foreignId('perusahaan_id')->nullable()->change();
        });

        Schema::table('pendaftaran_magangs', function (Blueprint $table) {
            $table->foreign('perusahaan_id')
                ->references('id')
                ->on('perusahaan_mitras')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::table('pendaftaran_magangs')->whereNull('perusahaan_id')->exists()) {
            throw new RuntimeException('Rollback dibatalkan: terdapat pendaftaran_magangs.perusahaan_id bernilai null.');
        }

        Schema::table('pendaftaran_magangs', function (Blueprint $table) {
            $table->dropForeign(['perusahaan_id']);
        });

        Schema::table('pendaftaran_magangs', function (Blueprint $table) {
            $table->foreignId('perusahaan_id')->nullable(false)->change();
        });

        Schema::table('pendaftaran_magangs', function (Blueprint $table) {
            $table->foreign('perusahaan_id')
                ->references('id')
                ->on('perusahaan_mitras')
                ->cascadeOnDelete();
        });
    }
};
