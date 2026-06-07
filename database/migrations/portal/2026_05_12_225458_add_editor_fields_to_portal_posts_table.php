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
            if (! Schema::hasColumn('portal_posts', 'excerpt')) {
                $table->text('excerpt')->nullable()->after('content');
            }
            if (! Schema::hasColumn('portal_posts', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('meta_description');
            }
            if (! Schema::hasColumn('portal_posts', 'og_image')) {
                $table->string('og_image')->nullable()->after('meta_title');
            }
            if (! Schema::hasColumn('portal_posts', 'tags')) {
                $table->json('tags')->nullable()->after('category_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portal_posts', function (Blueprint $table) {
            $table->dropColumn(['excerpt', 'meta_title', 'og_image', 'tags']);
        });
    }
};
