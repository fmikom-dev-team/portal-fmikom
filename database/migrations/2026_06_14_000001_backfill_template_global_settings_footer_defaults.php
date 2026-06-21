<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('template_global_settings')) {
            return;
        }

        $defaults = [
            [
                'key' => 'kode_prefix_nomor_surat',
                'label' => 'Prefix Nomor Surat',
                'tipe' => 'text',
                'value' => 'B',
            ],
            [
                'key' => 'kode_fakultas_nomor_surat',
                'label' => 'Kode Fakultas Nomor Surat',
                'tipe' => 'text',
                'value' => 'Ybk.041.10',
            ],
            [
                'key' => 'nama_instansi_footer',
                'label' => 'Nama Instansi (Footer)',
                'tipe' => 'text',
                'value' => 'Universitas Nahdlatul Ulama Al Ghazali Cilacap',
            ],
            [
                'key' => 'alamat_footer',
                'label' => 'Alamat (Footer)',
                'tipe' => 'text',
                'value' => 'Jl. Kemerdekaan Barat No.17, Cilacap Tengah',
            ],
            [
                'key' => 'telepon',
                'label' => 'Telepon',
                'tipe' => 'text',
                'value' => '(0282) 695415, 695407',
            ],
            [
                'key' => 'website',
                'label' => 'Website',
                'tipe' => 'text',
                'value' => 'https://unugha.ac.id',
            ],
            [
                'key' => 'email',
                'label' => 'Email',
                'tipe' => 'text',
                'value' => 'fmikom@unugha.ac.id',
            ],
            [
                'key' => 'fax',
                'label' => 'Fax',
                'tipe' => 'text',
                'value' => '(0282) 695407',
            ],
            [
                'key' => 'logo_kop_position',
                'label' => 'Posisi Logo Kop',
                'tipe' => 'text',
                'value' => 'top',
            ],
            [
                'key' => 'font_size_kop_instansi',
                'label' => 'Ukuran Font Kop Instansi',
                'tipe' => 'text',
                'value' => '17pt',
            ],
            [
                'key' => 'font_size_kop_fakultas',
                'label' => 'Ukuran Font Kop Fakultas',
                'tipe' => 'text',
                'value' => '13pt',
            ],
            [
                'key' => 'font_size_footer_instansi',
                'label' => 'Ukuran Font Footer Instansi',
                'tipe' => 'text',
                'value' => '8.8pt',
            ],
            [
                'key' => 'font_size_footer_detail',
                'label' => 'Ukuran Font Footer Detail',
                'tipe' => 'text',
                'value' => '7.0pt',
            ],
            [
                'key' => 'kop_border_thickness',
                'label' => 'Ketebalan Garis Kop',
                'tipe' => 'text',
                'value' => '2px',
            ],
            [
                'key' => 'footer_border_thickness',
                'label' => 'Ketebalan Garis Footer',
                'tipe' => 'text',
                'value' => '2px',
            ],
            [
                'key' => 'line_height_paragraf',
                'label' => 'Line Height Paragraf',
                'tipe' => 'text',
                'value' => '1.45',
            ],
            [
                'key' => 'line_height_tabel',
                'label' => 'Line Height Tabel',
                'tipe' => 'text',
                'value' => '1.65',
            ],
            [
                'key' => 'line_height_header',
                'label' => 'Line Height Header',
                'tipe' => 'text',
                'value' => '1.65',
            ],
            [
                'key' => 'line_height_yth',
                'label' => 'Line Height Yth',
                'tipe' => 'text',
                'value' => '1.05',
            ],
        ];

        foreach ($defaults as $setting) {
            $existing = DB::table('template_global_settings')
                ->where('key', $setting['key'])
                ->first();

            if ($existing === null) {
                DB::table('template_global_settings')->insert([
                    'key' => $setting['key'],
                    'label' => $setting['label'],
                    'tipe' => $setting['tipe'],
                    'value' => $setting['value'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                continue;
            }

            $updates = [];

            if (blank($existing->label ?? null)) {
                $updates['label'] = $setting['label'];
            }

            if (blank($existing->tipe ?? null)) {
                $updates['tipe'] = $setting['tipe'];
            }

            if (blank($existing->value ?? null)) {
                $updates['value'] = $setting['value'];
            }

            if (! empty($updates)) {
                $updates['updated_at'] = now();

                DB::table('template_global_settings')
                    ->where('key', $setting['key'])
                    ->update($updates);
            }
        }
    }

    public function down(): void
    {
        // Tidak menghapus data agar konfigurasi yang sudah diisi tidak hilang.
    }
};
