<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian_magangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_magangs')->cascadeOnDelete();
            $table->foreignId('dosen_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pembimbing_lapangan_id')->nullable()->constrained('pembimbing_lapangans')->nullOnDelete();
            $table->decimal('nilai_logbook', 5, 2)->nullable();
            $table->decimal('nilai_presentasi', 5, 2)->nullable();
            $table->decimal('nilai_dari_pembimbing', 5, 2)->nullable();
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->dateTime('tanggal_nilai')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian_magangs');
    }
};
