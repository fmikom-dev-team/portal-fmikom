<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remove OTP columns from users table.
 *
 * OTP logic is now handled by the dedicated auth_otp_tokens table.
 * Migrating OTP out of users:
 *   - Better isolation of auth credentials from user profile
 *   - Enables multi-purpose OTP (one user can have OTPs for different purposes)
 *   - Enables attempt tracking per-OTP
 *   - Simplifies User model (no more otp_code/otp_expires_at in fillable)
 *
 * ⚠️  PREREQUISITE: Ensure auth_otp_tokens table exists before running this.
 * ⚠️  In production: Run during low-traffic window. Any in-flight OTPs will be invalidated.
 *
 * password_changed_at is KEPT: still used as indicator for "admin-created users
 * who haven't set their own password yet" (null = must force-change).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop OTP columns — now in auth_otp_tokens
            if (Schema::hasColumn('users', 'otp_code')) {
                $table->dropColumn('otp_code');
            }
            if (Schema::hasColumn('users', 'otp_expires_at')) {
                $table->dropColumn('otp_expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Restore OTP columns if rolling back
            if (! Schema::hasColumn('users', 'otp_code')) {
                $table->string('otp_code')->nullable()->after('password');
            }
            if (! Schema::hasColumn('users', 'otp_expires_at')) {
                $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            }
        });
    }
};
