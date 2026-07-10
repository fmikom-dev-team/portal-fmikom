<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('jenis_surat_roles')) {
            Schema::dropIfExists('jenis_surat_roles');
        }

        Schema::table('jenis_surats', function (Blueprint $table): void {
            if (Schema::hasColumn('jenis_surats', 'qr_mode')) {
                $table->dropColumn('qr_mode');
            }

            if (Schema::hasColumn('jenis_surats', 'allowed_roles')) {
                $table->dropColumn('allowed_roles');
            }

            if (Schema::hasColumn('jenis_surats', 'urutan')) {
                $table->dropColumn('urutan');
            }

            if (Schema::hasColumn('jenis_surats', 'template_surat')) {
                $table->dropColumn('template_surat');
            }

            if (Schema::hasColumn('jenis_surats', 'template_file_path')) {
                $table->dropColumn('template_file_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jenis_surats', function (Blueprint $table): void {
            if (! Schema::hasColumn('jenis_surats', 'qr_mode')) {
                $table->enum('qr_mode', ['immediate', 'after_approval'])->default('after_approval');
            }

            if (! Schema::hasColumn('jenis_surats', 'allowed_roles')) {
                $table->json('allowed_roles')->nullable()->after('qr_mode');
            }

            if (! Schema::hasColumn('jenis_surats', 'urutan')) {
                $table->unsignedSmallInteger('urutan')->default(0)->after('allowed_roles');
            }

            if (! Schema::hasColumn('jenis_surats', 'template_file_path')) {
                $table->string('template_file_path')->nullable();
            }

            if (! Schema::hasColumn('jenis_surats', 'template_surat')) {
                $table->longText('template_surat')->nullable();
            }
        });

        if (! Schema::hasTable('jenis_surat_roles')) {
            Schema::create('jenis_surat_roles', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('jenis_surat_id')->constrained('jenis_surats')->cascadeOnDelete();
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['jenis_surat_id', 'role_id']);
            });
        }
    }
};
