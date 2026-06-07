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
            $table->foreignId('program_studi_id')->constrained('program_studis')->cascadeOnDelete();
            $table->string('nim')->unique();
            $table->integer('tahun_lulus');
            $table->string('no_telepon')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('foto_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_alumnis');
    }
};
