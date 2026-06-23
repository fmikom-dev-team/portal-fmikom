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
         Schema::create('kuesioner', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->string('judul');
            $table->string('subtitle')->nullable();
            $table->string('kategori')->nullable();
            $table->year('tahun')->nullable();
            $table->date('date_mulai')->nullable();
            $table->date('date_selesai')->nullable();
            $table->text('deskripsi')->nullable();
            $table->enum('tipe_kuesioner', ['alumni', 'stakeholder'])->default('alumni');
            $table->boolean('is_active')->default(true);
            $table->string('status')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('created_by');
            $table->index('status');
            $table->index('is_active');
            $table->index('tipe_kuesioner');
            $table->index('tahun');
            $table->fullText(['judul', 'subtitle', 'deskripsi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuesioner');
    }
};
