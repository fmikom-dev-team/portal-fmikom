<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perusahaan_mitras', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('mitra_jabatan')->nullable()->after('user_id');
            $table->unique('user_id');
        });

        $profiles = DB::table('pembimbing_lapangans')
            ->select('perusahaan_id', 'user_id', 'jabatan')
            ->orderByDesc('is_active')
            ->orderBy('id')
            ->get()
            ->unique('perusahaan_id')
            ->values();

        foreach ($profiles as $profile) {
            DB::table('perusahaan_mitras')
                ->where('id', $profile->perusahaan_id)
                ->update([
                    'user_id' => $profile->user_id,
                    'mitra_jabatan' => $profile->jabatan,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('perusahaan_mitras', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('mitra_jabatan');
        });
    }
};
