<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surats', function (Blueprint $table): void {
            if (! Schema::hasColumn('surats', 'nomor_surat_status')) {
                $table->string('nomor_surat_status')->nullable()->after('nomor_surat');
            }
        });

        if (Schema::hasColumn('surats', 'nomor_surat_status')) {
            DB::table('surats')
                ->whereNotNull('nomor_surat')
                ->whereIn('status', ['rejected_admin', 'rejected_approver'])
                ->update(['nomor_surat_status' => 'void']);

            DB::table('surats')
                ->whereNotNull('nomor_surat')
                ->where('status', 'finished')
                ->update(['nomor_surat_status' => 'issued']);

            DB::table('surats')
                ->whereNotNull('nomor_surat')
                ->whereNull('nomor_surat_status')
                ->update(['nomor_surat_status' => 'reserved']);
        }
    }

    public function down(): void
    {
        Schema::table('surats', function (Blueprint $table): void {
            if (Schema::hasColumn('surats', 'nomor_surat_status')) {
                $table->dropColumn('nomor_surat_status');
            }
        });
    }
};
