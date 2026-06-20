<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenis_surats', function (Blueprint $table) {
            if (! Schema::hasColumn('jenis_surats', 'category_id')) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('surat_categories')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('jenis_surats', 'slug')) {
                $table->string('slug')->nullable()->after('nama');
            }

            if (! Schema::hasColumn('jenis_surats', 'kode_surat')) {
                $table->string('kode_surat')->nullable()->after('slug');
            }

            if (! Schema::hasColumn('jenis_surats', 'alur_pengajuan')) {
                $table->string('alur_pengajuan')->default('submission')->after('perlu_approval');
            }

            if (! Schema::hasColumn('jenis_surats', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('alur_pengajuan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jenis_surats', function (Blueprint $table) {
            if (Schema::hasColumn('jenis_surats', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }

            $columns = collect(['slug', 'kode_surat', 'alur_pengajuan', 'is_active'])
                ->filter(fn (string $column): bool => Schema::hasColumn('jenis_surats', $column))
                ->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
