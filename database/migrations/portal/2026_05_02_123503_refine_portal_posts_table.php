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
        Schema::table('portal_posts', function (Blueprint $table) {
            if (! Schema::hasColumn('portal_posts', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->after('id');
            }
            if (! Schema::hasColumn('portal_posts', 'status')) {
                $table->string('status')->default('draft')->after('is_published');
            }
            if (! Schema::hasColumn('portal_posts', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('portal_posts', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('published_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portal_posts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'status', 'published_at', 'meta_description']);
        });
    }
};
