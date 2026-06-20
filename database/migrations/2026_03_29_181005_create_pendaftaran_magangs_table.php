<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran_magangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('perusahaan_id')->constrained('perusahaan_mitras')->cascadeOnDelete();
            $table->foreignId('dosen_pembimbing_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pembimbing_lapangan_id')->nullable()->constrained('pembimbing_lapangans')->nullOnDelete();
            $table->foreignId('surat_tugas_id')->nullable()->constrained('surats')->nullOnDelete();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_magangs');
    }
};
