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
         Schema::create('education_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profil_alumni_id')->constrained('profil_alumnis')->cascadeOnDelete();
            
            $table->string('perguruan_tinggi');
            $table->string('program_studi');
            $table->enum('jenjang', ['S2', 'S3', 'Spesialis', 'Profesi']);
            $table->string('sumber_biaya')->nullable();
            $table->text('alamat')->nullable();
            
            $table->year('tahun_mulai');
            $table->year('tahun_lulus')->nullable();
            $table->boolean('is_current')->default(false);
            
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            
            $table->timestamps();
            
            $table->index('profil_alumni_id');
            $table->index('is_current');
            $table->index('tahun_mulai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education_histories');
    }
};
