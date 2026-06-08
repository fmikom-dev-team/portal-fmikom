<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profil_alumnis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->year('angkatan');
            $table->string('nik')->nullable();
            $table->string('npwp')->nullable();
            $table->foreignId('provinsi_id')->nullable()->constrained('provinsi')->nullOnDelete();
            $table->foreignId('kota_id')->nullable()->constrained('kota')->nullOnDelete();
            $table->text('alamat_rumah')->nullable();
            $table->double('latitude_rumah')->nullable();
            $table->double('longitude_rumah')->nullable();
            
            $table->timestamps();

            $table->index('user_id');
            $table->index(['provinsi_id', 'kota_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_alumnis');
    }
};
