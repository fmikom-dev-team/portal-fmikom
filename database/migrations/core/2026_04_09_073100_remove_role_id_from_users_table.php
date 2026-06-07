<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role_id')) {
                // Pastikan drop foreign key terlebih dahulu
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            }
        });
    }
};
