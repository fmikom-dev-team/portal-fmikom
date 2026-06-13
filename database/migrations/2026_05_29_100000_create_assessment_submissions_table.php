<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_magang_id')
                ->constrained('pendaftaran_magangs')
                ->cascadeOnDelete();
            $table->foreignId('assessment_template_id')
                ->constrained('assessment_templates')
                ->cascadeOnDelete();
            $table->foreignId('assessor_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('assessor_role', 32);
            $table->decimal('total_score', 8, 2)->nullable();
            $table->string('status', 32)->default('draft');
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['pendaftaran_magang_id', 'assessment_template_id', 'assessor_id', 'assessor_role'],
                'assessment_submissions_unique_submission'
            );
        });

        Schema::create('assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_submission_id')
                ->constrained('assessment_submissions')
                ->cascadeOnDelete();
            $table->foreignId('assessment_component_id')
                ->constrained('assessment_components')
                ->cascadeOnDelete();
            $table->decimal('score', 8, 2);
            $table->decimal('weighted_score', 8, 2);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(
                ['assessment_submission_id', 'assessment_component_id'],
                'assessment_scores_unique_component'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_scores');
        Schema::dropIfExists('assessment_submissions');
    }
};
