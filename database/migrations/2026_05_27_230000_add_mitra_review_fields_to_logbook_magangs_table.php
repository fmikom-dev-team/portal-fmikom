<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbook_magangs', function (Blueprint $table): void {
            $table->text('catatan_mitra')->nullable()->after('kompetensi_dicapai');
            $table->foreignId('reviewed_by_mitra_user_id')
                ->nullable()
                ->after('catatan_mitra')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reviewed_by_mitra_at')
                ->nullable()
                ->after('reviewed_by_mitra_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('logbook_magangs', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('reviewed_by_mitra_user_id');
            $table->dropColumn([
                'catatan_mitra',
                'reviewed_by_mitra_at',
            ]);
        });
    }
};
