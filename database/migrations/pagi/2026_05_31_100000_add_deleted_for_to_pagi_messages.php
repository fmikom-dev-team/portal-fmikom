<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom deleted_for untuk fitur "Hapus hanya di saya"
     * (WhatsApp-style "Delete for me" — hanya hilang di sisi penghapus).
     */
    public function up(): void
    {
        Schema::table('pagi_messages', function (Blueprint $table) {
            // Menyimpan array user_id yang telah menghapus pesan dari sisi mereka
            // Contoh: [5, 12] → user 5 dan 12 tidak lagi melihat pesan ini
            $table->json('deleted_for')->nullable()->after('reactions');
        });
    }

    public function down(): void
    {
        Schema::table('pagi_messages', function (Blueprint $table) {
            $table->dropColumn('deleted_for');
        });
    }
};
