<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('pagi_custom_portfolios', 'pagi_custom_works');
    }

    public function down(): void
    {
        Schema::rename('pagi_custom_works', 'pagi_custom_portfolios');
    }
};
