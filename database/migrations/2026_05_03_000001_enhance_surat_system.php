<?php
// FILE: database/migrations/2026_05_03_000001_enhance_surat_system.php
// Jalankan: php artisan migrate
// AMAN: Semua pakai hasColumn/hasTable, tidak akan error jika dijalankan ulang

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ══════════════════════════════════════════════════════════════
        // 1. surat_categories
        // Sudah ada: id, nama, slug, deskripsi, urutan, is_active ✅
        // Belum ada: icon, warna
        // ══════════════════════════════════════════════════════════════
        Schema::table('surat_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_categories', 'icon')) {
                $table->string('icon', 50)->nullable()->after('deskripsi')
                    ->comment('Lucide icon name, e.g. mail, file-text');
            }
            if (!Schema::hasColumn('surat_categories', 'warna')) {
                $table->string('warna', 20)->nullable()->after('icon')
                    ->comment('Tailwind color key, e.g. indigo, emerald, amber');
            }
        });

        // ══════════════════════════════════════════════════════════════
        // 2. jenis_surats
        // Sudah ada: id, category_id, nama, slug, kode_surat, deskripsi,
        //            field_config, template_file_path, template_surat,
        //            allowed_role_id, approval_role_id, perlu_approval,
        //            alur_pengajuan, is_active ✅
        // Belum ada: qr_mode, allowed_roles (json), urutan
        // ══════════════════════════════════════════════════════════════
        Schema::table('jenis_surats', function (Blueprint $table) {
            if (!Schema::hasColumn('jenis_surats', 'qr_mode')) {
                $table->enum('qr_mode', ['immediate', 'after_approval'])
                    ->default('after_approval')
                    ->after('perlu_approval')
                    ->comment('immediate=QR langsung aktif, after_approval=setelah disetujui');
            }
            if (!Schema::hasColumn('jenis_surats', 'allowed_roles')) {
                $table->json('allowed_roles')->nullable()->after('qr_mode')
                    ->comment('Array slug role yang boleh buat surat ini, null=semua');
            }
            if (!Schema::hasColumn('jenis_surats', 'urutan')) {
                $table->unsignedSmallInteger('urutan')->default(0)->after('allowed_roles');
            }
        });

        // ══════════════════════════════════════════════════════════════
        // 3. surat_templates
        // Sudah ada: id, jenis_surat_id, name, slug, format,
        //            source_reference, subject, template_header,
        //            template_body, template_footer, version, is_active ✅
        // Belum ada: deskripsi, docx_path, kop_surat, footer_surat,
        //            css_style, created_by, updated_by
        // ══════════════════════════════════════════════════════════════
        Schema::table('surat_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_templates', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('name');
            }
            if (!Schema::hasColumn('surat_templates', 'docx_path')) {
                $table->string('docx_path')->nullable()->after('source_reference')
                    ->comment('Path file .docx yang diupload admin');
            }
            if (!Schema::hasColumn('surat_templates', 'kop_surat')) {
                $table->text('kop_surat')->nullable()->after('docx_path')
                    ->comment('HTML kop override (null = pakai global settings)');
            }
            if (!Schema::hasColumn('surat_templates', 'footer_surat')) {
                $table->text('footer_surat')->nullable()->after('kop_surat')
                    ->comment('HTML footer override');
            }
            if (!Schema::hasColumn('surat_templates', 'css_style')) {
                $table->text('css_style')->nullable()->after('footer_surat')
                    ->comment('CSS kustom untuk template ini');
            }
            if (!Schema::hasColumn('surat_templates', 'created_by')) {
                $table->foreignId('created_by')->nullable()
                    ->after('is_active')
                    ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('surat_templates', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()
                    ->after('created_by')
                    ->constrained('users')->nullOnDelete();
            }
        });

        // ══════════════════════════════════════════════════════════════
        // 4. surats
        // Sudah ada: semua kolom utama + qr_token ✅
        // Belum ada: kepada_yth, lampiran_keterangan, revisi_ke,
        //            catatan_revisi, print_count, last_printed_at
        // ══════════════════════════════════════════════════════════════
        Schema::table('surats', function (Blueprint $table) {
            if (!Schema::hasColumn('surats', 'kepada_yth')) {
                $table->json('kepada_yth')->nullable()->after('keperluan')
                    ->comment('Array penerima surat, bisa multiple');
            }
            if (!Schema::hasColumn('surats', 'lampiran_keterangan')) {
                $table->string('lampiran_keterangan')->nullable()->after('kepada_yth')
                    ->comment('Contoh: 1 (satu) lembar');
            }
            if (!Schema::hasColumn('surats', 'revisi_ke')) {
                $table->unsignedTinyInteger('revisi_ke')->default(0)
                    ->after('template_version');
            }
            if (!Schema::hasColumn('surats', 'catatan_revisi')) {
                $table->text('catatan_revisi')->nullable()->after('revisi_ke');
            }
            if (!Schema::hasColumn('surats', 'print_count')) {
                $table->unsignedSmallInteger('print_count')->default(0)
                    ->after('catatan_revisi');
            }
            if (!Schema::hasColumn('surats', 'last_printed_at')) {
                $table->timestamp('last_printed_at')->nullable()
                    ->after('print_count');
            }
        });

        // ══════════════════════════════════════════════════════════════
        // 5. surat_qr_codes (TABEL BARU)
        // Manajemen QR yang lebih detail: revoke, status, activated_at
        // qr_token di surats tetap dipakai untuk kompatibilitas
        // ══════════════════════════════════════════════════════════════
        if (!Schema::hasTable('surat_qr_codes')) {
            Schema::create('surat_qr_codes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('surat_id')->constrained('surats')->cascadeOnDelete();
                $table->string('token', 64)->unique()->comment('UUID token QR');
                $table->enum('status', ['active', 'revoked', 'expired'])->default('active');
                $table->string('revoked_reason')->nullable();
                $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamp('activated_at')->nullable()
                    ->comment('Kapan QR mulai aktif — null jika belum disetujui');
                $table->timestamps();

                $table->index(['token', 'status']);
                $table->index('surat_id');
            });
        }

        // ══════════════════════════════════════════════════════════════
        // 6. surat_histories (TABEL BARU — audit trail)
        // ══════════════════════════════════════════════════════════════
        if (!Schema::hasTable('surat_histories')) {
            Schema::create('surat_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('surat_id')->constrained('surats')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 50)
                    ->comment('created|submitted|validated|approved|rejected|generated|printed|qr_scanned|qr_revoked|revised');
                $table->string('action_label')
                    ->comment('Label ramah: Surat dibuat, Surat disetujui Kaprodi');
                $table->text('keterangan')->nullable();
                $table->json('meta')->nullable()
                    ->comment('Data tambahan: {status_lama, status_baru, ...}');
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->index(['surat_id', 'created_at']);
                $table->index('user_id');
            });
        }

        // ══════════════════════════════════════════════════════════════
        // 7. template_global_settings (TABEL BARU)
        // Kop surat, footer, logo, warna, format nomor — bisa ubah dari UI
        // ══════════════════════════════════════════════════════════════
        if (!Schema::hasTable('template_global_settings')) {
            Schema::create('template_global_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->text('value')->nullable();
                $table->string('tipe', 20)->default('text')
                    ->comment('text|html|json|image_path');
                $table->string('label');
                $table->text('deskripsi')->nullable();
                $table->timestamps();
            });

            DB::table('template_global_settings')->insert([
                ['key' => 'nama_instansi', 'tipe' => 'text',       'label' => 'Nama Instansi',       'value' => 'Universitas Nahdlatul Ulama Al Ghazali Cilacap', 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'nama_fakultas', 'tipe' => 'text',       'label' => 'Nama Fakultas',       'value' => 'Fakultas Matematika dan Ilmu Komputer',          'created_at' => now(), 'updated_at' => now()],
                ['key' => 'singkatan',     'tipe' => 'text',       'label' => 'Singkatan Fakultas',  'value' => 'FMIKOM',                                         'created_at' => now(), 'updated_at' => now()],
                ['key' => 'alamat',        'tipe' => 'text',       'label' => 'Alamat',              'value' => 'Jl. Kemerdekaan Barat No. 17, Cilacap Tengah',   'created_at' => now(), 'updated_at' => now()],
                ['key' => 'telepon',       'tipe' => 'text',       'label' => 'Telepon',             'value' => null,                                             'created_at' => now(), 'updated_at' => now()],
                ['key' => 'website',       'tipe' => 'text',       'label' => 'Website',             'value' => null,                                             'created_at' => now(), 'updated_at' => now()],
                ['key' => 'email',         'tipe' => 'text',       'label' => 'Email',               'value' => null,                                             'created_at' => now(), 'updated_at' => now()],
                ['key' => 'logo_path',     'tipe' => 'image_path', 'label' => 'Logo Universitas',    'value' => null,                                             'created_at' => now(), 'updated_at' => now()],
                ['key' => 'kop_html',      'tipe' => 'html',       'label' => 'Kop Surat (HTML)',    'value' => null,                                             'created_at' => now(), 'updated_at' => now()],
                ['key' => 'footer_html',   'tipe' => 'html',       'label' => 'Footer Surat (HTML)', 'value' => null,                                             'created_at' => now(), 'updated_at' => now()],
                ['key' => 'warna_primer',  'tipe' => 'text',       'label' => 'Warna Primer Kop',    'value' => '#1B5E20',                                        'created_at' => now(), 'updated_at' => now()],
                ['key' => 'kota_surat',    'tipe' => 'text',       'label' => 'Kota Default',        'value' => 'Cilacap',                                        'created_at' => now(), 'updated_at' => now()],
                ['key' => 'format_nomor',  'tipe' => 'text',       'label' => 'Format Nomor Surat',  'value' => '{kode}/{urutan}/{bulan_romawi}/{tahun}',          'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // ══════════════════════════════════════════════════════════════
        // 8. nomor_surat_sequences (TABEL BARU)
        // Nomor surat otomatis per kode per bulan/tahun
        // Contoh hasil: CUTI-MHS/0042/IV/2026
        // ══════════════════════════════════════════════════════════════
        if (!Schema::hasTable('nomor_surat_sequences')) {
            Schema::create('nomor_surat_sequences', function (Blueprint $table) {
                $table->id();
                $table->string('kode_surat', 50)->comment('Kode jenis surat, e.g. CUTI-MHS');
                $table->unsignedSmallInteger('tahun');
                $table->unsignedSmallInteger('bulan');
                $table->unsignedInteger('urutan')->default(0);
                $table->timestamps();

                $table->unique(['kode_surat', 'tahun', 'bulan']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('nomor_surat_sequences');
        Schema::dropIfExists('template_global_settings');
        Schema::dropIfExists('surat_histories');
        Schema::dropIfExists('surat_qr_codes');

        Schema::table('surats', function (Blueprint $table) {
            $cols = ['kepada_yth', 'lampiran_keterangan', 'revisi_ke', 'catatan_revisi', 'print_count', 'last_printed_at'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('surats', $col)) $table->dropColumn($col);
            }
        });
        Schema::table('surat_templates', function (Blueprint $table) {
            $cols = ['deskripsi', 'docx_path', 'kop_surat', 'footer_surat', 'css_style', 'created_by', 'updated_by'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('surat_templates', $col)) $table->dropColumn($col);
            }
        });
        Schema::table('jenis_surats', function (Blueprint $table) {
            $cols = ['qr_mode', 'allowed_roles', 'urutan'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('jenis_surats', $col)) $table->dropColumn($col);
            }
        });
        Schema::table('surat_categories', function (Blueprint $table) {
            $cols = ['icon', 'warna'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('surat_categories', $col)) $table->dropColumn($col);
            }
        });
    }
};
