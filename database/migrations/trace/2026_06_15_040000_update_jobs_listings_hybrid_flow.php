<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Make mitra_id nullable (admin can post without mitra)
        Schema::table('jobs_listings', function (Blueprint $table) {
            $table->foreignId('mitra_id')->nullable()->change();
        });

        // 2. Update status enum: add pending_review and rejected
        DB::statement("ALTER TABLE jobs_listings MODIFY COLUMN status ENUM('draft', 'pending_review', 'published', 'rejected', 'closed') DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE jobs_listings MODIFY COLUMN status ENUM('draft', 'published', 'closed') DEFAULT 'draft'");

        Schema::table('jobs_listings', function (Blueprint $table) {
            $table->foreignId('mitra_id')->nullable(false)->change();
        });
    }
};
