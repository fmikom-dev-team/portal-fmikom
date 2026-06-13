<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ketidakhadiran_magangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_magangs')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('perusahaan_id')->constrained('perusahaan_mitras')->cascadeOnDelete();
            $table->foreignId('pembimbing_lapangan_id')->nullable()->constrained('pembimbing_lapangans')->nullOnDelete();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('jenis', 30);
            $table->text('alasan');
            $table->string('bukti_path')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by_mitra_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_by_mitra_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('catatan_mitra')->nullable();
            $table->timestamps();

            $table->index(['pendaftaran_id', 'status']);
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ketidakhadiran_magangs');
    }
};
