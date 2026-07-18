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
        Schema::table('portal_events', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false)->after('registration_link');
            $table->unsignedInteger('price')->default(0)->nullable()->after('is_paid');
            $table->string('audience_type')->default('umum')->after('price'); // umum, khusus
            $table->boolean('is_quota_limited')->default(false)->after('audience_type');
            $table->unsignedInteger('quota')->nullable()->after('is_quota_limited');
            $table->dateTime('published_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portal_events', function (Blueprint $table) {
            $table->dropColumn([
                'is_paid',
                'price',
                'audience_type',
                'is_quota_limited',
                'quota',
                'published_at',
            ]);
        });
    }
};
