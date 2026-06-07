<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_magangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_magangs')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('waktu_masuk')->nullable();
            $table->time('waktu_keluar')->nullable();
            $table->decimal('latitude_masuk', 10, 8)->nullable();
            $table->decimal('longitude_masuk', 11, 8)->nullable();
            $table->decimal('latitude_keluar', 10, 8)->nullable();
            $table->decimal('longitude_keluar', 11, 8)->nullable();
            $table->boolean('lokasi_valid')->default(false);
            $table->string('foto_bukti_path')->nullable();
            $table->string('status')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_magangs');
    }
};
