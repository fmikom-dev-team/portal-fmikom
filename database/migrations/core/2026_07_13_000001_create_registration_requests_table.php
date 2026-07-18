<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create registration_requests table.
 *
 * Tujuan: Memisahkan "calon user yang mendaftar" dari "user aktif".
 * Pending registrations TIDAK masuk ke tabel users hingga admin approve.
 *
 * Berlaku untuk Case B: Self-Registration (Mitra, Alumni Baru, Partner)
 * dan OAuth-based registrations yang belum diapprove.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // ── Identitas Calon User ────────────────────────────────────────
            $table->string('full_name');
            $table->string('email')->index();
            $table->string('phone', 20)->nullable();
            $table->string('role', 30); // 'mitra', 'alumni', 'partner', etc.

            // ── Data Tambahan Berdasarkan Role ──────────────────────────────
            $table->string('student_number', 50)->nullable(); // NIM untuk alumni
            $table->string('employee_number', 50)->nullable(); // NIP/NIDN
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studis')->nullOnDelete();
            $table->year('tahun_lulus')->nullable();
            $table->json('extra_data')->nullable(); // data tambahan per-role (JSON)

            // ── Lifecycle Status ────────────────────────────────────────────
            $table->string('status', 30)->default('pending')->index();
            // pending | approved | rejected | otp_sent | otp_verified | activated | expired

            // ── Admin Review ────────────────────────────────────────────────
            $table->text('approval_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // ── Activation Token (diisi setelah admin approve) ──────────────
            $table->string('activation_token_hash', 255)->nullable(); // hashed
            $table->timestamp('activation_token_expires_at')->nullable();

            // ── OAuth Integration ───────────────────────────────────────────
            // Disimpan jika registrasi berasal dari Google OAuth
            $table->json('oauth_data')->nullable();
            // { provider, provider_id, external_id, name, email, access_token, refresh_token, expires_at }

            // ── Audit / Traceability ────────────────────────────────────────
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // User yang akhirnya dibuat setelah aktivasi selesai
            $table->foreignId('created_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes(); // Jangan hapus permanen — untuk audit trail

            // ── Indexes ──────────────────────────────────────────────────────
            $table->index(['email', 'status']);
            $table->index(['role', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_requests');
    }
};
