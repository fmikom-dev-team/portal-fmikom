<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applycants', function (Blueprint $table) {
            $table->text('reviewer_note')->nullable()->after('status');
            $table->timestamp('reviewed_at')->nullable()->after('reviewer_note');
        });
    }

    public function down(): void
    {
        Schema::table('job_applycants', function (Blueprint $table) {
            $table->dropColumn(['reviewer_note', 'reviewed_at']);
        });
    }
};
