<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_surat_id')->constrained('jenis_surats')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('format')->default('html');
            $table->string('source_reference')->nullable();
            $table->text('subject')->nullable();
            $table->longText('template_header')->nullable();
            $table->longText('template_body');
            $table->longText('template_footer')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_templates');
    }
};
