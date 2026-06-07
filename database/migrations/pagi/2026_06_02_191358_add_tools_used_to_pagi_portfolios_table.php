<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagi_portfolios', function (Blueprint $table) {
            if (!Schema::hasColumn('pagi_portfolios', 'tools_used')) {
                $table->string('tools_used', 255)->nullable()->after('category');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagi_portfolios', function (Blueprint $table) {
            if (Schema::hasColumn('pagi_portfolios', 'tools_used')) {
                $table->dropColumn('tools_used');
            }
        });
    }
};
