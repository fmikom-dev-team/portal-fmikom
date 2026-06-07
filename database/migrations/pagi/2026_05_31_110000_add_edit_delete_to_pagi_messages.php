<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom untuk fitur edit pesan (20 menit) & delete placeholder (WhatsApp-style).
     */
    public function up(): void
    {
        Schema::table('pagi_messages', function (Blueprint $table) {
            // Waktu terakhir pesan diedit
            $table->timestamp('edited_at')->nullable()->after('deleted_for');
            // Apakah pesan sudah dihapus (meninggalkan placeholder "Pesan ini telah dihapus")
            $table->boolean('is_deleted')->default(false)->after('edited_at');
        });
    }

    public function down(): void
    {
        Schema::table('pagi_messages', function (Blueprint $table) {
            $table->dropColumn(['edited_at', 'is_deleted']);
        });
    }
};
