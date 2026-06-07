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
        Schema::table('radar_blocked_items', function (Blueprint $table) {
            if (! Schema::hasColumn('radar_blocked_items', 'action')) {
                $table->enum('action', ['Allow', 'Block'])->default('Block')->after('value');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('radar_blocked_items', function (Blueprint $table) {
            if (Schema::hasColumn('radar_blocked_items', 'action')) {
                $table->dropColumn('action');
            }
        });
    }
};
