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
        Schema::create('jobs_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('mitra_id')->constrained('mitra_profiles')->cascadeOnDelete();
            $table->foreignId('job_category_id') ->nullable()->constrained('job_categories')->onDelete('set null');
            $table->string('title');
            $table->text('description');
            $table->enum('experience_level', ['fresh_graduate', 'junior', 'mid_level', 'senior', 'internship'])->default('fresh_graduate');
            $table->enum('location_type', ['onsite', 'remote', 'hybrid'])->default('onsite');
            $table->string('location_city')->nullable();    
            $table->enum('tipe_kerja', ['full_time', 'part_time', 'magang', 'freelance'])->default('full_time');
            $table->unsignedInteger('salary_min')->nullable(); 
            $table->unsignedInteger('salary_max')->nullable();
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->date('deadline')->nullable();
            $table->boolean('is_salary_visible')->default(true);
            $table->softDeletes();

            $table->timestamps();
            $table->index('mitra_id');
            $table->index('job_category_id');
            $table->index('status');
            $table->index('experience_level');
            $table->index(['status', 'deadline']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs_listings');
    }
};
