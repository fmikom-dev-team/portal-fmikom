<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_magangs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pembimbing_lapangan_id');
        });

        Schema::table('ketidakhadiran_magangs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pembimbing_lapangan_id');
        });

        Schema::table('penilaian_magangs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pembimbing_lapangan_id');
        });

        Schema::dropIfExists('pembimbing_lapangans');
    }

    public function down(): void
    {
        Schema::create('pembimbing_lapangans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('perusahaan_id')->constrained('perusahaan_mitras')->cascadeOnDelete();
            $table->string('jabatan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('pendaftaran_magangs', function (Blueprint $table) {
            $table->foreignId('pembimbing_lapangan_id')
                ->nullable()
                ->after('dosen_pembimbing_id')
                ->constrained('pembimbing_lapangans')
                ->nullOnDelete();
        });

        Schema::table('ketidakhadiran_magangs', function (Blueprint $table) {
            $table->foreignId('pembimbing_lapangan_id')
                ->nullable()
                ->after('perusahaan_id')
                ->constrained('pembimbing_lapangans')
                ->nullOnDelete();
        });

        Schema::table('penilaian_magangs', function (Blueprint $table) {
            $table->foreignId('pembimbing_lapangan_id')
                ->nullable()
                ->after('pendaftaran_id')
                ->constrained('pembimbing_lapangans')
                ->nullOnDelete();
        });
    }
};
