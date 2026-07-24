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
            $table->string('organizer_logo')->nullable()->after('organizer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portal_events', function (Blueprint $table) {
            $table->dropColumn('organizer_logo');
        });
    }
};
