<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pagi_works', function (Blueprint $table) {
            $table->dropColumn(['likes', 'comments']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagi_works', function (Blueprint $table) {
            $table->json('likes')->nullable();
            $table->json('comments')->nullable();
        });

        // Rollback data dari tabel normalized kembali ke kolom JSON
        Artisan::call('pagi:migrate-social-data', ['--rollback' => true]);
    }
};
