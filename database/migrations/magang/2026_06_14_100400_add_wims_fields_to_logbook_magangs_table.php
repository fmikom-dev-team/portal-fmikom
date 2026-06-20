<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbook_magangs', function (Blueprint $table) {
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->string('status', 32)->nullable();
            $table->text('catatan_mitra')->nullable();
            $table->foreignId('reviewed_by_mitra_user_id')->nullable();
            $table->timestamp('reviewed_by_mitra_at')->nullable();

            $table->foreign('reviewed_by_mitra_user_id', 'logbook_magangs_reviewed_by_mitra_user_id_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('logbook_magangs', function (Blueprint $table) {
            $table->dropForeign('logbook_magangs_reviewed_by_mitra_user_id_foreign');
            $table->dropColumn([
                'jam_mulai',
                'jam_selesai',
                'status',
                'catatan_mitra',
                'reviewed_by_mitra_user_id',
                'reviewed_by_mitra_at',
            ]);
        });
    }
};
