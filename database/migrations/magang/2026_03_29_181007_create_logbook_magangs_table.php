<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logbook_magangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_magangs')->cascadeOnDelete();
            $table->date('tanggal');
            $table->text('aktivitas_harian');
            $table->text('kompetensi_dicapai')->nullable();
            $table->text('catatan_dosen')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbook_magangs');
    }
};
