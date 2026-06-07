<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('auth_login_attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('auth_login_attempts', 'provider')) {
                $table->string('provider')->nullable()->after('is_successful')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auth_login_attempts', function (Blueprint $table) {
            if (Schema::hasColumn('auth_login_attempts', 'provider')) {
                $table->dropColumn('provider');
            }
        });
    }
};
