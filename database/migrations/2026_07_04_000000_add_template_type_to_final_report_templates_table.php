<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('final_report_templates', function (Blueprint $table) {
            $table->string('template_type', 40)->default('final_report')->after('id');
            $table->index(['template_type', 'is_active', 'updated_at'], 'final_report_templates_type_active_updated_idx');
        });

        DB::table('final_report_templates')
            ->whereNull('template_type')
            ->orWhere('template_type', '')
            ->update(['template_type' => 'final_report']);
    }

    public function down(): void
    {
        Schema::table('final_report_templates', function (Blueprint $table) {
            $table->dropIndex('final_report_templates_type_active_updated_idx');
            $table->dropColumn('template_type');
        });
    }
};
