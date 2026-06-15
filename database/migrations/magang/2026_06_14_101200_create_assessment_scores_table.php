<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_submission_id')
                ->constrained('assessment_submissions')
                ->cascadeOnDelete();
            $table->foreignId('assessment_component_id')
                ->constrained('assessment_components')
                ->restrictOnDelete();
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
    }
};
