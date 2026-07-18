<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create identity_verifications table.
 *
 * Tujuan: Menyimpan sesi verifikasi identitas sementara untuk Case A
 * (Admin-Driven users: mahasiswa, dosen, staff, alumni lama).
 *
 * User membuktikan identitas mereka dengan NIM/NIDN + tanggal lahir.
 * Session ini bertahan selama proses aktivasi berlangsung (short-lived).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // ── Session Token ───────────────────────────────────────────────
            // Digunakan sebagai "key" untuk lookup selama proses aktivasi
            $table->string('session_token', 64)->unique()->index();

            // ── Identity Claim ──────────────────────────────────────────────
            $table->string('user_type', 30); // mahasiswa | dosen | staff | alumni
            $table->string('identifier', 50); // NIM atau NIDN

            // ─ Tanggal lahir sebagai second factor (anti-identity-takeover) ──
            $table->date('tanggal_lahir');

            // ── Resolved User ───────────────────────────────────────────────
            // Diisi setelah identitas berhasil dicocokkan dengan DB
            $table->foreignId('resolved_user_id')->nullable()->constrained('users')->nullOnDelete();

            // ── Status ──────────────────────────────────────────────────────
            $table->string('status', 20)->default('pending');
            // pending | verified | failed | expired

            // ── Security: Rate limiting & attempt tracking ──────────────────
            $table->tinyInteger('attempt_count')->default(0);
            $table->tinyInteger('max_attempts')->default(5);

            // ── Timeline ─────────────────────────────────────────────────────
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at'); // Short TTL: 30 menit

            // ── Audit ─────────────────────────────────────────────────────────
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // ── Indexes ──────────────────────────────────────────────────────
            $table->index(['user_type', 'identifier']);
            $table->index('expires_at'); // For cleanup jobs
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_verifications');
    }
};
