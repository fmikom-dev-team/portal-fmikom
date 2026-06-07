<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_username_unique');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'nim_nip']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('email');
            $table->string('nim_nip')->nullable()->after('program_studi_id');
        });
    }
};
