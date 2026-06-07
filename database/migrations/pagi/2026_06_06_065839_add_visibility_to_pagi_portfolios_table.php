<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagi_portfolios', function (Blueprint $table) {
            if (! Schema::hasColumn('pagi_portfolios', 'visibility')) {
                $table->string('visibility')->default('Everyone')->after('is_published');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagi_portfolios', function (Blueprint $table) {
            if (Schema::hasColumn('pagi_portfolios', 'visibility')) {
                $table->dropColumn('visibility');
            }
        });
    }
};
