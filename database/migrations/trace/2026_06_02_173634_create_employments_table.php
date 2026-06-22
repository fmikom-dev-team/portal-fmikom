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
        Schema::create('employment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_history_id')->unique()->constrained('career_history')->cascadeOnDelete();
            
            $table->string('nama_perusahaan');
            $table->string('jabatan');
            $table->string('sektor_industri')->nullable();
            $table->text('alamat_perusahaan')->nullable();
            
            $table->integer('gaji_min')->nullable();
            $table->integer('gaji_max')->nullable();
            
            $table->timestamps();
            
            $table->index('career_history_id');
            $table->index('sektor_industri');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employment');
    }
};
