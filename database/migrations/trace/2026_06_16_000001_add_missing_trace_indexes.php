<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profil_alumnis', function (Blueprint $table) {
            $table->index('angkatan');
        });

        Schema::table('career_history', function (Blueprint $table) {
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::table('profil_alumnis', function (Blueprint $table) {
            $table->dropIndex(['angkatan']);
        });

        Schema::table('career_history', function (Blueprint $table) {
            $table->dropIndex(['latitude', 'longitude']);
        });
    }
};
