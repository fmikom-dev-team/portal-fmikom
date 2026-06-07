<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pilihan_jawabans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pertanyaan_id')->constrained('pertanyaan_kuesioners')->cascadeOnDelete();
            $table->string('teks_pilihan');
            $table->integer('nilai')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pilihan_jawabans');
    }
};
