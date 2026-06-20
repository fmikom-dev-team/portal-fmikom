<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_template_placeholders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_template_id')->constrained('surat_templates')->cascadeOnDelete();
            $table->string('placeholder_key');
            $table->string('label');
            $table->string('source_type');
            $table->string('source_key')->nullable();
            $table->boolean('is_required')->default(true);
            $table->text('default_value')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['surat_template_id', 'placeholder_key'], 'surat_template_placeholder_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_template_placeholders');
    }
};
