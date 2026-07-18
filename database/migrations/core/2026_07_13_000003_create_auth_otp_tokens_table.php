<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create auth_otp_tokens table.
 *
 * Tujuan: Memindahkan OTP dari kolom-kolom di tabel users ke tabel
 * dedicated yang terisolasi dan memiliki lifecycle sendiri.
 *
 * Keuntungan vs menyimpan di users:
 * - Isolasi: User profile tidak tercampur dengan auth credentials
 * - Multi-purpose: Satu user bisa punya OTP untuk berbagai tujuan sekaligus
 * - Attempt tracking: Bisa blokir brute force per-purpose
 * - Audit trail: Histori OTP tersimpan (is_used, used_at)
 * - Cleanup: Bisa prune otomatis tanpa update users table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_otp_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // ── Relasi ──────────────────────────────────────────────────────
            // Nullable: OTP bisa dikirim sebelum user dibuat (pre-activation)
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();

            // Email adalah target pengiriman (bisa berbeda dari user.email jika email claim)
            $table->string('email')->index();

            // ── Purpose ─────────────────────────────────────────────────────
            // email_verification | account_activation | password_reset | magic_link
            $table->string('purpose', 30)->index();

            // ── Token (Hashed) ───────────────────────────────────────────────
            // NEVER store plaintext OTP. Always hash before storing.
            $table->string('token_hash', 255);

            // ── Attempt Tracking (Brute Force Prevention) ────────────────────
            $table->tinyInteger('attempt_count')->default(0);
            $table->tinyInteger('max_attempts')->default(5);

            // ── Lifecycle ────────────────────────────────────────────────────
            $table->boolean('is_used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at'); // Required — OTP selalu ada expiry

            // ── Audit ────────────────────────────────────────────────────────
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // ── Indexes ──────────────────────────────────────────────────────
            $table->index(['email', 'purpose', 'is_used']); // For lookup during verify
            $table->index(['user_id', 'purpose']);
            $table->index('expires_at'); // For cleanup jobs
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_otp_tokens');
    }
};
