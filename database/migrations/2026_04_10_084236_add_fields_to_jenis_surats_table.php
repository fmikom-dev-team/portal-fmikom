<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// LEGACY-ONLY: migrasi historis penambahan role bridge pada jenis_surats.
// Field ini masih dipakai kompatibilitas lama, tetapi bukan skema domain baru.
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jenis_surats', function (Blueprint $table) {
            $table->foreignId('allowed_role_id')->nullable()->constrained('roles');
            $table->foreignId('approval_role_id')->nullable()->constrained('roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jenis_surats', function (Blueprint $table) {
            $table->dropForeign(['allowed_role_id']);
            $table->dropForeign(['approval_role_id']);
            $table->dropColumn(['allowed_role_id', 'approval_role_id']);
        });
    }
};
