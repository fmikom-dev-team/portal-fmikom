<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('respons_kuesioners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuesioner_id')->constrained('kuesioners')->cascadeOnDelete();
            $table->foreignId('alumni_id')->constrained('profil_alumnis')->cascadeOnDelete();
            $table->boolean('is_complete')->default(false);
            $table->dateTime('tanggal_isi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respons_kuesioners');
    }
};
