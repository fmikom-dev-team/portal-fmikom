<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
          Schema::create('detail_jawabans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('responses')->cascadeOnDelete();
            $table->foreignId('pertanyaan_id')->constrained('pertanyaan')->restrictOnDelete();
            $table->foreignId('opsi_jawaban_id')->nullable()->constrained('opsi_jawabans')->nullOnDelete();
            
            $table->text('jawaban_text')->nullable();
            
            $table->timestamps();
            
            $table->index('response_id');
            $table->index('pertanyaan_id');
            $table->index(['response_id', 'pertanyaan_id']);
            $table->index('opsi_jawaban_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_jawabans');
    }
};
