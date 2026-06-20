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
        Schema::create('surat_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->constrained()->cascadeOnDelete();
            $table->string('field_name');
            $table->text('field_value')->nullable();
            $table->timestamps();
            $table->unique(['surat_id', 'field_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_data');
    }
};
