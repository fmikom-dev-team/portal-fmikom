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
        Schema::table('auth_invitations', function (Blueprint $table) {
            $table->string('token')->unique()->nullable()->after('status');
            $table->string('invited_by')->nullable()->after('token');
            $table->string('organization_name')->nullable()->after('invited_by');
            $table->timestamp('expires_at')->nullable()->after('organization_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auth_invitations', function (Blueprint $table) {
            $table->dropColumn(['token', 'invited_by', 'organization_name', 'expires_at']);
        });
    }
};
