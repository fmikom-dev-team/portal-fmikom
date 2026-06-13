<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_penetapans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_magangs')->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->string('provider')->default('fast');
            $table->string('fast_reference_id')->nullable();
            $table->string('nomor_surat')->nullable();
            $table->string('file_url')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('payload_snapshot')->nullable();
            $table->timestamps();

            $table->unique('pendaftaran_id');
            $table->index(['status', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_penetapans');
    }
};
