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
        Schema::table('pagi_portfolios', function (Blueprint $table) {
            $table->json('likes')->nullable();
            $table->json('comments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagi_portfolios', function (Blueprint $table) {
            $table->dropColumn(['likes', 'comments']);
        });
    }
};
