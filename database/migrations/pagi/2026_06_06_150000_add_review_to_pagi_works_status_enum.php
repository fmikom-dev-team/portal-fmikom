<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter status column to allow 'review'
        if (Schema::hasTable('pagi_works')) {
            // Determine DB driver
            $driver = DB::connection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE pagi_works MODIFY COLUMN status ENUM('active', 'warning', 'hidden', 'removed', 'review') NOT NULL DEFAULT 'active'");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pagi_works')) {
            $driver = DB::connection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE pagi_works MODIFY COLUMN status ENUM('active', 'warning', 'hidden', 'removed') NOT NULL DEFAULT 'active'");
            }
        }
    }
};
