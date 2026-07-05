<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_magangs', function (Blueprint $table): void {
            $table->string('proposal_pkl_path')->nullable()->after('catatan_revisi_admin');
            $table->string('proposal_pkl_original_name')->nullable()->after('proposal_pkl_path');
            $table->timestamp('proposal_pkl_uploaded_at')->nullable()->after('proposal_pkl_original_name');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_magangs', function (Blueprint $table): void {
            $table->dropColumn([
                'proposal_pkl_path',
                'proposal_pkl_original_name',
                'proposal_pkl_uploaded_at',
            ]);
        });
    }
};