<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('jenis_surats', 'field_config')) {
            Schema::table('jenis_surats', function (Blueprint $table): void {
                $table->json('field_config')->nullable()->after('deskripsi');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('jenis_surats', 'field_config')) {
            Schema::table('jenis_surats', function (Blueprint $table): void {
                $table->dropColumn('field_config');
            });
        }
    }
};
