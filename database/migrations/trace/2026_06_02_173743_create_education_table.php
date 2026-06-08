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
      Schema::create('education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_history_id')->unique()->constrained('career_history')->cascadeOnDelete();
            
            // Education Info
            $table->string('nama_universitas');
            $table->string('program_studi_lanjutan');
            $table->string('jenjang_pendidikan')->nullable(); 
            $table->string('sumber_biaya')->nullable(); 
            $table->text('alamat_universitas')->nullable();
            
            $table->timestamps();
            
            $table->index('career_history_id');
            $table->index('jenjang_pendidikan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education');
    }
};
