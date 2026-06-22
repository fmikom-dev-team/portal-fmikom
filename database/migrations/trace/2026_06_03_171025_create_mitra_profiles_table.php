<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mitra_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            $table->string('nama_perusahaan');
            $table->text('deskripsi')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();
            
            $table->string('email_perusahaan')->nullable();
            $table->string('no_telp')->nullable();
            $table->text('alamat_lengkap')->nullable();
            $table->foreignId('provinsi_id')->nullable()->constrained('provinsi')->nullOnDelete();
            $table->foreignId('kota_id')->nullable()->constrained('kota')->nullOnDelete();


            $table->timestamps();

            $table->index('user_id');
            $table->index('nama_perusahaan');
            $table->index(['provinsi_id', 'kota_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mitra_profiles');
    }
};
