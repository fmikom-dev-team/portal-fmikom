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
        Schema::create('pagi_cvs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title')->default('Untitled CV');
            $table->string('template_id')->default('ats-professional');
            $table->json('personal_info')->nullable();
            $table->json('education')->nullable();
            $table->json('experience')->nullable();
            $table->json('organizations')->nullable();
            $table->json('skills')->nullable();
            $table->json('certifications')->nullable();
            $table->json('trainings')->nullable();
            $table->json('achievements')->nullable();
            $table->json('languages')->nullable();
            $table->json('references')->nullable();
            $table->json('customization')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagi_cvs');
    }
};
