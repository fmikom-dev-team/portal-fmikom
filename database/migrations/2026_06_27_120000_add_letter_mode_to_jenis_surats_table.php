<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenis_surats', function (Blueprint $table): void {
            $table->string('letter_mode', 20)
                ->default('personal')
                ->after('alur_pengajuan');
        });

        DB::table('jenis_surats')
            ->where('letter_mode', 'personal')
            ->update([
                'letter_mode' => DB::raw("
                    CASE
                        WHEN EXISTS (
                            SELECT 1
                            FROM surat_templates
                            WHERE surat_templates.jenis_surat_id = jenis_surats.id
                              AND surat_templates.subject = 'institution'
                              AND surat_templates.deleted_at IS NULL
                        ) THEN 'institution'
                        ELSE 'personal'
                    END
                "),
            ]);
    }

    public function down(): void
    {
        Schema::table('jenis_surats', function (Blueprint $table): void {
            $table->dropColumn('letter_mode');
        });
    }
};
