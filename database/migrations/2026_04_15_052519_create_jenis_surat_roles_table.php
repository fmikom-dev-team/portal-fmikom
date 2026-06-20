<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// LEGACY-ONLY: tabel bridge lama untuk mapping role ke jenis surat.
// Disimpan untuk histori migrasi, bukan jalur utama setelah cutover.
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jenis_surat_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_surat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['jenis_surat_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_surat_roles');
    }
};
