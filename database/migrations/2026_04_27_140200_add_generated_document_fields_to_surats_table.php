<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            if (! Schema::hasColumn('surats', 'generated_file_path')) {
                $table->string('generated_file_path')->nullable()->after('isi_surat');
            }

            if (! Schema::hasColumn('surats', 'generated_file_type')) {
                $table->string('generated_file_type')->nullable()->after('generated_file_path');
            }

            if (! Schema::hasColumn('surats', 'generated_at')) {
                $table->dateTime('generated_at')->nullable()->after('generated_file_type');
            }

            if (! Schema::hasColumn('surats', 'template_version')) {
                $table->unsignedInteger('template_version')->nullable()->after('generated_at');
            }

            if (! Schema::hasColumn('surats', 'rendered_snapshot')) {
                $table->longText('rendered_snapshot')->nullable()->after('template_version');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            $columns = collect([
                'generated_file_path',
                'generated_file_type',
                'generated_at',
                'template_version',
                'rendered_snapshot',
            ])->filter(fn (string $column): bool => Schema::hasColumn('surats', $column))->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
