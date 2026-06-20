<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance Migration: Add missing indexes to frequently-queried PAGI tables.
 *
 * Context:
 * - pagi_reports.status + reporter_id: used in admin dashboard COUNT queries and feed filter
 * - pagi_works: is_published + status used in every feed/gallery query
 * - pagi_work_comments: work_id + parent_id used when loading comments for a work
 * - pagi_comment_likes: comment_id used when counting/plucking likes per comment
 * - pagi_work_likes: work_id used when counting/plucking likes per work
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- pagi_reports ---
        Schema::table('pagi_reports', function (Blueprint $table) {
            if (! $this->hasIndex('pagi_reports', 'pagi_reports_status_index')) {
                $table->index('status', 'pagi_reports_status_index');
            }
            if (! $this->hasIndex('pagi_reports', 'pagi_reports_reporter_id_index')) {
                $table->index('reporter_id', 'pagi_reports_reporter_id_index');
            }
            if (! $this->hasIndex('pagi_reports', 'pagi_reports_work_id_status_index')) {
                $table->index(['work_id', 'status'], 'pagi_reports_work_id_status_index');
            }
        });

        // --- pagi_works ---
        Schema::table('pagi_works', function (Blueprint $table) {
            // Composite index for the most common feed query:
            // WHERE is_published = 1 AND ... ORDER BY created_at DESC
            if (! $this->hasIndex('pagi_works', 'pagi_works_published_created_index')) {
                $table->index(['is_published', 'created_at'], 'pagi_works_published_created_index');
            }
            if (! $this->hasIndex('pagi_works', 'pagi_works_status_index')) {
                $table->index('status', 'pagi_works_status_index');
            }
            if (! $this->hasIndex('pagi_works', 'pagi_works_views_count_index')) {
                $table->index('views_count', 'pagi_works_views_count_index');
            }
        });

        // --- pagi_work_comments ---
        Schema::table('pagi_work_comments', function (Blueprint $table) {
            // For loading top-level comments per work: WHERE work_id = ? AND parent_id IS NULL
            if (! $this->hasIndex('pagi_work_comments', 'pagi_work_comments_work_parent_index')) {
                $table->index(['work_id', 'parent_id'], 'pagi_work_comments_work_parent_index');
            }
        });

        // --- pagi_comment_likes ---
        Schema::table('pagi_comment_likes', function (Blueprint $table) {
            if (! $this->hasIndex('pagi_comment_likes', 'pagi_comment_likes_comment_id_index')) {
                $table->index('comment_id', 'pagi_comment_likes_comment_id_index');
            }
        });

        // --- pagi_work_likes ---
        Schema::table('pagi_work_likes', function (Blueprint $table) {
            if (! $this->hasIndex('pagi_work_likes', 'pagi_work_likes_work_id_index')) {
                $table->index('work_id', 'pagi_work_likes_work_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pagi_reports', function (Blueprint $table) {
            $table->dropIndexIfExists('pagi_reports_status_index');
            $table->dropIndexIfExists('pagi_reports_reporter_id_index');
            $table->dropIndexIfExists('pagi_reports_work_id_status_index');
        });

        Schema::table('pagi_works', function (Blueprint $table) {
            $table->dropIndexIfExists('pagi_works_published_created_index');
            $table->dropIndexIfExists('pagi_works_status_index');
            $table->dropIndexIfExists('pagi_works_views_count_index');
        });

        Schema::table('pagi_work_comments', function (Blueprint $table) {
            $table->dropIndexIfExists('pagi_work_comments_work_parent_index');
        });

        Schema::table('pagi_comment_likes', function (Blueprint $table) {
            $table->dropIndexIfExists('pagi_comment_likes_comment_id_index');
        });

        Schema::table('pagi_work_likes', function (Blueprint $table) {
            $table->dropIndexIfExists('pagi_work_likes_work_id_index');
        });
    }

    /**
     * Check if a named index already exists on a table.
     * Compatible with MySQL, SQLite, and PostgreSQL (uses Laravel Schema API).
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        return Schema::hasIndex($table, $indexName);
    }
};
