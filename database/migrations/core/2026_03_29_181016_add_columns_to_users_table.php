<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studis')->nullOnDelete();
            $table->string('nim_nip')->nullable();
            $table->string('no_telepon')->nullable();
            $table->string('foto_path')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['program_studi_id']);
            $table->dropColumn(['role_id', 'program_studi_id', 'nim_nip', 'no_telepon', 'foto_path', 'is_active']);
        });
    }
};
