<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagi_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained('pagi_portfolios')->cascadeOnDelete();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->enum('reason', [
                'inappropriate_content',
                'copyright_violation',
                'spam',
                'harassment',
                'misinformation',
                'other'
            ])->default('other');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'dismissed', 'actioned'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pagi_warnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('portfolio_id')->nullable()->constrained('pagi_portfolios')->nullOnDelete();
            $table->foreignId('issued_by')->constrained('users')->cascadeOnDelete();
            $table->enum('severity', ['low', 'medium', 'high'])->default('medium');
            $table->enum('type', [
                'inappropriate_content',
                'copyright',
                'spam',
                'harassment',
                'repeat_violation',
                'other'
            ])->default('other');
            $table->text('reason');
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pagi_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 120)->unique();
            $table->string('color', 20)->default('#6366f1'); // hex color
            $table->unsignedInteger('usage_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pagi_portfolio_tags', function (Blueprint $table) {
            $table->foreignId('portfolio_id')->constrained('pagi_portfolios')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('pagi_tags')->cascadeOnDelete();
            $table->primary(['portfolio_id', 'tag_id']);
        });

        // Add moderation fields to pagi_portfolios if not exists
        Schema::table('pagi_portfolios', function (Blueprint $table) {
            if (!Schema::hasColumn('pagi_portfolios', 'status')) {
                $table->enum('status', ['active', 'warning', 'hidden', 'removed', 'review'])->default('active')->after('is_published');
            }
            if (!Schema::hasColumn('pagi_portfolios', 'views_count')) {
                $table->unsignedBigInteger('views_count')->default(0)->after('status');
            }
            if (!Schema::hasColumn('pagi_portfolios', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('pagi_portfolios', 'category')) {
                $table->string('category', 100)->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagi_portfolio_tags');
        Schema::dropIfExists('pagi_tags');
        Schema::dropIfExists('pagi_warnings');
        Schema::dropIfExists('pagi_reports');
        Schema::table('pagi_portfolios', function (Blueprint $table) {
            $table->dropColumn(['status', 'views_count', 'description', 'category']);
        });
    }
};
