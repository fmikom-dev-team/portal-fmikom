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
        // Update portal_categories
        Schema::table('portal_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('portal_categories', 'name')) {
                $table->string('name')->after('id');
                $table->string('slug')->unique()->after('name');
                $table->text('description')->nullable()->after('slug');
            }
        });

        // Update portal_posts
        Schema::table('portal_posts', function (Blueprint $table) {
            if (! Schema::hasColumn('portal_posts', 'category_id')) {
                $table->foreignId('category_id')->nullable()->constrained('portal_categories')->nullOnDelete()->after('id');
            }
        });

        // Update portal_pages
        Schema::table('portal_pages', function (Blueprint $table) {
            if (! Schema::hasColumn('portal_pages', 'title')) {
                $table->string('title')->after('id');
                $table->string('slug')->unique()->after('title');
                $table->longText('content')->nullable()->after('slug');
                $table->boolean('is_published')->default(true)->after('content');
            }
        });

        // Update portal_comments
        Schema::table('portal_comments', function (Blueprint $table) {
            if (! Schema::hasColumn('portal_comments', 'post_id')) {
                $table->foreignId('post_id')->constrained('portal_posts')->onDelete('cascade')->after('id');
                $table->string('author_name')->after('post_id');
                $table->string('author_email')->nullable()->after('author_name');
                $table->text('content')->after('author_email');
                $table->string('status')->default('pending')->after('content'); // pending, approved, spam
            }
        });

        // Update portal_media
        Schema::table('portal_media', function (Blueprint $table) {
            if (! Schema::hasColumn('portal_media', 'filename')) {
                $table->string('filename')->after('id');
                $table->string('path')->after('filename');
                $table->string('mime_type')->nullable()->after('path');
                $table->unsignedBigInteger('size')->nullable()->after('mime_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portal_categories', function (Blueprint $table) {
            $table->dropColumn(['name', 'slug', 'description']);
        });
        Schema::table('portal_posts', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
        Schema::table('portal_pages', function (Blueprint $table) {
            $table->dropColumn(['title', 'slug', 'content', 'is_published']);
        });
        Schema::table('portal_comments', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
            $table->dropColumn(['post_id', 'author_name', 'author_email', 'content', 'status']);
        });
        Schema::table('portal_media', function (Blueprint $table) {
            $table->dropColumn(['filename', 'path', 'mime_type', 'size']);
        });
    }
};
