<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('has_completed_pkl')->default(false)->after('is_active');
            $table->timestamp('pkl_completed_at')->nullable()->after('has_completed_pkl');
        });

        $completedStudentIds = DB::table('pendaftaran_magangs')
            ->where('status', 'selesai')
            ->whereNotNull('mahasiswa_id')
            ->pluck('mahasiswa_id')
            ->unique()
            ->values();

        if ($completedStudentIds->isNotEmpty()) {
            DB::table('users')
                ->whereIn('id', $completedStudentIds)
                ->update([
                    'has_completed_pkl' => true,
                    'pkl_completed_at' => now(),
                ]);
        }

    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['has_completed_pkl', 'pkl_completed_at']);
        });
    }
};
