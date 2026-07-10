<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_qr_codes', function (Blueprint $table): void {
            if (Schema::hasColumn('surat_qr_codes', 'revoked_reason')) {
                $table->dropColumn('revoked_reason');
            }

            if (Schema::hasColumn('surat_qr_codes', 'revoked_by')) {
                $table->dropConstrainedForeignId('revoked_by');
            }

            if (Schema::hasColumn('surat_qr_codes', 'revoked_at')) {
                $table->dropColumn('revoked_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surat_qr_codes', function (Blueprint $table): void {
            if (! Schema::hasColumn('surat_qr_codes', 'revoked_reason')) {
                $table->string('revoked_reason')->nullable()->after('status');
            }

            if (! Schema::hasColumn('surat_qr_codes', 'revoked_by')) {
                $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('surat_qr_codes', 'revoked_at')) {
                $table->timestamp('revoked_at')->nullable();
            }
        });
    }
};
