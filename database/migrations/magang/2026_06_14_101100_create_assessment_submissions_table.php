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
                ->restrictOnDelete();
            $table->foreignId('assessment_template_id')
                ->constrained('assessment_templates')
                ->restrictOnDelete();
            $table->foreignId('assessor_id')
                ->constrained('users')
                ->restrictOnDelete();
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
            $table->index(['pendaftaran_magang_id', 'status']);
            $table->index('assessment_template_id');
            $table->index('assessor_id');
            $table->index('assessor_role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_submissions');
    }
};
