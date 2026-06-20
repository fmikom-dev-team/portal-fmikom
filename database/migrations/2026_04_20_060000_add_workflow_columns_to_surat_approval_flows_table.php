<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_approval_flows', function (Blueprint $table): void {
            if (! Schema::hasColumn('surat_approval_flows', 'role')) {
                $table->string('role')->nullable()->after('urutan');
            }

            if (! Schema::hasColumn('surat_approval_flows', 'keterangan')) {
                $table->string('keterangan')->nullable()->after('status');
            }

            if (! Schema::hasColumn('surat_approval_flows', 'approved_at')) {
                $table->dateTime('approved_at')->nullable()->after('keterangan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surat_approval_flows', function (Blueprint $table): void {
            if (Schema::hasColumn('surat_approval_flows', 'approved_at')) {
                $table->dropColumn('approved_at');
            }

            if (Schema::hasColumn('surat_approval_flows', 'keterangan')) {
                $table->dropColumn('keterangan');
            }

            if (Schema::hasColumn('surat_approval_flows', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
