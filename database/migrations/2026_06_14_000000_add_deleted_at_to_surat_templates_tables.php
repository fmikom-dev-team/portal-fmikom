<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('surat_templates', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        Schema::table('surat_template_placeholders', function (Blueprint $table) {
            if (! Schema::hasColumn('surat_template_placeholders', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surat_template_placeholders', function (Blueprint $table) {
            if (Schema::hasColumn('surat_template_placeholders', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('surat_templates', function (Blueprint $table) {
            if (Schema::hasColumn('surat_templates', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
