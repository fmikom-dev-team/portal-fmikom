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
               Schema::create('pertanyaan', function (Blueprint $table) {
            $table->id();
            $table->softDeletes(); 
            $table->foreignId('kuesioner_id')->constrained('kuesioner')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            
            $table->text('teks');
            $table->string('tipe');
            $table->string('tipe_data')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('urutan')->default(0);
            $table->string('kategori')->nullable();
            
            $table->json('meta')->nullable();
            $table->json('acuan')->nullable();
            $table->json('logic_condition')->nullable();
            $table->json('skoring')->nullable();
            
            $table->timestamps();
            
            $table->index('kuesioner_id');
            $table->index('section_id');
            $table->index('tipe');
            $table->index(['kuesioner_id', 'section_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pertanyaan');
    }
};
