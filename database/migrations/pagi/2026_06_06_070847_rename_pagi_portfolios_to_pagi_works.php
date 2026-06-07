<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop foreign keys referencing pagi_portfolios first to prevent constraint errors
        Schema::table('pagi_portfolio_tags', function (Blueprint $table) {
            $table->dropForeign(['portfolio_id']);
        });
        Schema::table('pagi_reports', function (Blueprint $table) {
            $table->dropForeign(['portfolio_id']);
        });
        Schema::table('pagi_warnings', function (Blueprint $table) {
            $table->dropForeign(['portfolio_id']);
        });

        // 2. Rename the main table
        Schema::rename('pagi_portfolios', 'pagi_works');

        // 3. Rename the pivot table
        Schema::rename('pagi_portfolio_tags', 'pagi_work_tags');

        // 4. Rename the columns and re-add foreign keys
        Schema::table('pagi_work_tags', function (Blueprint $table) {
            $table->renameColumn('portfolio_id', 'work_id');
        });
        Schema::table('pagi_work_tags', function (Blueprint $table) {
            $table->foreign('work_id')->references('id')->on('pagi_works')->cascadeOnDelete();
        });

        Schema::table('pagi_reports', function (Blueprint $table) {
            $table->renameColumn('portfolio_id', 'work_id');
        });
        Schema::table('pagi_reports', function (Blueprint $table) {
            $table->foreign('work_id')->references('id')->on('pagi_works')->cascadeOnDelete();
        });

        Schema::table('pagi_warnings', function (Blueprint $table) {
            $table->renameColumn('portfolio_id', 'work_id');
        });
        Schema::table('pagi_warnings', function (Blueprint $table) {
            $table->foreign('work_id')->references('id')->on('pagi_works')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pagi_warnings', function (Blueprint $table) {
            $table->dropForeign(['work_id']);
        });
        Schema::table('pagi_warnings', function (Blueprint $table) {
            $table->renameColumn('work_id', 'portfolio_id');
        });

        Schema::table('pagi_reports', function (Blueprint $table) {
            $table->dropForeign(['work_id']);
        });
        Schema::table('pagi_reports', function (Blueprint $table) {
            $table->renameColumn('work_id', 'portfolio_id');
        });

        Schema::table('pagi_work_tags', function (Blueprint $table) {
            $table->dropForeign(['work_id']);
        });
        Schema::table('pagi_work_tags', function (Blueprint $table) {
            $table->renameColumn('work_id', 'portfolio_id');
        });

        Schema::rename('pagi_work_tags', 'pagi_portfolio_tags');
        Schema::rename('pagi_works', 'pagi_portfolios');

        Schema::table('pagi_portfolio_tags', function (Blueprint $table) {
            $table->foreign('portfolio_id')->references('id')->on('pagi_portfolios')->cascadeOnDelete();
        });
        Schema::table('pagi_reports', function (Blueprint $table) {
            $table->foreign('portfolio_id')->references('id')->on('pagi_portfolios')->cascadeOnDelete();
        });
        Schema::table('pagi_warnings', function (Blueprint $table) {
            $table->foreign('portfolio_id')->references('id')->on('pagi_portfolios')->nullOnDelete();
        });
    }
};
