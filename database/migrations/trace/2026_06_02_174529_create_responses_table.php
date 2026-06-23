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
        Schema::create('responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuesioner_id')->constrained('kuesioner')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('stakeholder_name')->nullable();
            $table->string('stakeholder_email')->nullable();

            $table->year('angkatan')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['kuesioner_id', 'user_id'], 'unique_response_per_user_kuesioner');

            $table->index('kuesioner_id');
            $table->index('user_id');
            $table->index('submitted_at');
            $table->index(['kuesioner_id', 'user_id']);
            $table->index('angkatan');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('responses');
    }
};
