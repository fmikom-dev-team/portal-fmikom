<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karir_alumnis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_id')->constrained('profil_alumnis')->cascadeOnDelete();
            $table->string('nama_perusahaan');
            $table->string('jabatan');
            $table->string('bidang_industri')->nullable();
            $table->string('kota')->nullable();
            $table->string('negara')->default('Indonesia');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->string('gaji_range')->nullable();
            $table->string('status_kerja')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karir_alumnis');
    }
};
