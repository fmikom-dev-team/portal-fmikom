<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_respons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('respons_id')->constrained('respons_kuesioners')->cascadeOnDelete();
            $table->foreignId('pertanyaan_id')->constrained('pertanyaan_kuesioners')->cascadeOnDelete();
            $table->foreignId('pilihan_id')->nullable()->constrained('pilihan_jawabans')->nullOnDelete();
            $table->text('jawaban_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_respons');
    }
};
