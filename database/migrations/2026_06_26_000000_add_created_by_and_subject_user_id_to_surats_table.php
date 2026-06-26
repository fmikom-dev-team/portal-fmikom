<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->after('pemohon_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('subject_user_id')
                ->nullable()
                ->after('created_by')
                ->constrained('users')
                ->nullOnDelete();

            $table->index('created_by');
            $table->index('subject_user_id');
        });

        DB::table('surats')
            ->whereNull('subject_user_id')
            ->update(['subject_user_id' => DB::raw('pemohon_id')]);

        DB::table('surats')
            ->where('type', 'surat_keluar')
            ->whereNull('created_by')
            ->update(['created_by' => DB::raw('pemohon_id')]);
    }

    public function down(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
            $table->dropIndex(['subject_user_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['subject_user_id']);
            $table->dropColumn(['created_by', 'subject_user_id']);
        });
    }
};
