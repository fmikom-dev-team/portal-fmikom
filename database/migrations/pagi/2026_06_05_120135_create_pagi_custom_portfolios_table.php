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
        Schema::create('pagi_custom_portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('theme')->default('sydney');
            $table->integer('palette_index')->default(0);
            $table->boolean('is_published')->default(false);
            $table->string('custom_title')->nullable();
            $table->text('custom_bio')->nullable();
            $table->json('selected_projects')->nullable();
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagi_custom_portfolios');
    }
};
