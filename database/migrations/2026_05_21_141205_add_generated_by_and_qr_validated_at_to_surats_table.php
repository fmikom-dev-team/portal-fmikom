<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            $table->foreignId('generated_by')
                ->nullable()
                ->after('generated_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('qr_validated_at')
                ->nullable()
                ->after('generated_by')
                ->comment('Waktu pertama kali QR di-scan dan surat sudah divalidasi');
        });
    }

    public function down(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            $table->dropForeign(['generated_by']);
            $table->dropColumn(['generated_by', 'qr_validated_at']);
        });
    }
};