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
        // 1. pagi_work_likes
        Schema::create('pagi_work_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id')->constrained('pagi_works')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['work_id', 'user_id']);
        });

        // 2. pagi_work_comments
        Schema::create('pagi_work_comments', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('work_id')->constrained('pagi_works')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('pagi_work_comments')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        // 3. pagi_comment_likes
        Schema::create('pagi_comment_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('pagi_work_comments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['comment_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagi_comment_likes');
        Schema::dropIfExists('pagi_work_comments');
        Schema::dropIfExists('pagi_work_likes');
    }
};
