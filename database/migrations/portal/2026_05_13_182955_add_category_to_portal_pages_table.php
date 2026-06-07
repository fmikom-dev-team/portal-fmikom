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
        Schema::table('portal_pages', function (Blueprint $table) {
            $table->string('category')->nullable()->after('slug');  // profil, akademik, media, layanan
            $table->text('excerpt')->nullable()->after('content');
            $table->string('meta_description', 255)->nullable()->after('excerpt');
            $table->string('featured_image')->nullable()->after('meta_description');
            $table->string('template')->default('default')->after('featured_image'); // default, sidebar, full-width
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portal_pages', function (Blueprint $table) {
            //
        });
    }
};
