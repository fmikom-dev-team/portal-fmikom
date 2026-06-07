<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_lampirans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->constrained('surats')->cascadeOnDelete();
            $table->string('nama_file');
            $table->string('file_path');
            $table->string('tipe')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_lampirans');
    }
};
