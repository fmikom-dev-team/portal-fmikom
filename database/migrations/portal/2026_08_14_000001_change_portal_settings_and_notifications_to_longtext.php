<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Changes portal_settings.value and notifications.data from TEXT (max 65 KB)
     * to LONGTEXT (max 4 GB) to safely accommodate JSON payloads that may
     * include large data such as testimonial metadata.
     */
    public function up(): void
    {
        Schema::table('portal_settings', function (Blueprint $table) {
            $table->longText('value')->nullable()->change();
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->longText('data')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portal_settings', function (Blueprint $table) {
            $table->text('value')->nullable()->change();
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->text('data')->change();
        });
    }
};
