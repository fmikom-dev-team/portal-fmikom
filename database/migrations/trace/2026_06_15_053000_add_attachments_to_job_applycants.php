<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applycants', function (Blueprint $table) {
            $table->json('attached_cv_ids')->nullable()->after('cover_letter');
            $table->json('attached_portfolio_ids')->nullable()->after('attached_cv_ids');
        });
    }

    public function down(): void
    {
        Schema::table('job_applycants', function (Blueprint $table) {
            $table->dropColumn(['attached_cv_ids', 'attached_portfolio_ids']);
        });
    }
};
