<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profil_alumnis', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('mitra_profiles', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('profil_alumnis', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('mitra_profiles', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
