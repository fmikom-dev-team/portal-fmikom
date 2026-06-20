<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            DB::table('surat_approval_flows')
                ->where('status', 'rejected')
                ->where('role', 'admin')
                ->update(['status' => 'rejected_final']);

            DB::table('surat_approval_flows')
                ->where('status', 'rejected')
                ->whereIn('role', ['kaprodi', 'dekan'])
                ->update(['status' => 'revision_requested']);

            $revisionSuratIds = DB::table('surat_approval_flows')
                ->where('status', 'revision_requested')
                ->pluck('surat_id')
                ->unique()
                ->all();

            if ($revisionSuratIds !== []) {
                DB::table('surats')
                    ->whereIn('id', $revisionSuratIds)
                    ->where('status', 'rejected')
                    ->update([
                        'status' => 'revision_requested',
                        'rejection_reason' => null,
                    ]);
            }

            DB::table('surats')
                ->where('status', 'rejected')
                ->update(['status' => 'rejected_admin']);
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            DB::table('surat_approval_flows')
                ->whereIn('status', ['revision_requested', 'rejected_final'])
                ->update(['status' => 'rejected']);

            DB::table('surats')
                ->whereIn('status', ['revision_requested', 'rejected_admin', 'rejected_approver'])
                ->update(['status' => 'rejected']);
        });
    }
};
