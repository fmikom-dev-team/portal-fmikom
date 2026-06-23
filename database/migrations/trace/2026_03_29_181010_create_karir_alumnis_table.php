<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profil_alumni_id')->constrained('profil_alumnis')->cascadeOnDelete();
            $table->string('type');
            $table->string('status');

            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->integer('tahun_mulai')->nullable();
            $table->integer('tahun_selesai')->nullable();
            $table->boolean('is_current')->default(false);

            $table->foreignId('provinsi_id')->nullable()->constrained('provinsi')->nullOnDelete();
            $table->foreignId('kota_id')->nullable()->constrained('kota')->nullOnDelete();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();

            $table->timestamps();

            $table->index(['profil_alumni_id', 'is_current']);
            $table->index('profil_alumni_id');
            $table->index('is_current');
            $table->index('status');
            $table->index('type');
            $table->index('tanggal_mulai');
            $table->index('tanggal_selesai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_history');
    }
};
