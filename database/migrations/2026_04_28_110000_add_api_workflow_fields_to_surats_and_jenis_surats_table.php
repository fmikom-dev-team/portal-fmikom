<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('jenis_surats', 'template_surat')) {
            Schema::table('jenis_surats', function (Blueprint $table): void {
                $table->longText('template_surat')->nullable()->after('template_file_path');
            });
        }

        Schema::table('surats', function (Blueprint $table): void {
            if (! Schema::hasColumn('surats', 'type')) {
                $table->string('type')->default('pengajuan')->after('jenis_surat_id');
            }

            if (! Schema::hasColumn('surats', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('isi_surat');
            }

            if (! Schema::hasColumn('surats', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('admin_note');
            }

            if (! Schema::hasColumn('surats', 'validated_by_admin_id')) {
                $table->foreignId('validated_by_admin_id')
                    ->nullable()
                    ->after('rejection_reason')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('surats', 'validated_by_admin_at')) {
                $table->dateTime('validated_by_admin_at')->nullable()->after('validated_by_admin_id');
            }

            if (! Schema::hasColumn('surats', 'approved_by_id')) {
                $table->foreignId('approved_by_id')
                    ->nullable()
                    ->after('validated_by_admin_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('surats', 'approved_at')) {
                $table->dateTime('approved_at')->nullable()->after('approved_by_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surats', function (Blueprint $table): void {
            if (Schema::hasColumn('surats', 'approved_at')) {
                $table->dropColumn('approved_at');
            }

            if (Schema::hasColumn('surats', 'approved_by_id')) {
                $table->dropConstrainedForeignId('approved_by_id');
            }

            if (Schema::hasColumn('surats', 'validated_by_admin_at')) {
                $table->dropColumn('validated_by_admin_at');
            }

            if (Schema::hasColumn('surats', 'validated_by_admin_id')) {
                $table->dropConstrainedForeignId('validated_by_admin_id');
            }

            if (Schema::hasColumn('surats', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }

            if (Schema::hasColumn('surats', 'admin_note')) {
                $table->dropColumn('admin_note');
            }

            if (Schema::hasColumn('surats', 'type')) {
                $table->dropColumn('type');
            }
        });

        if (Schema::hasColumn('jenis_surats', 'template_surat')) {
            Schema::table('jenis_surats', function (Blueprint $table): void {
                $table->dropColumn('template_surat');
            });
        }
    }
};
