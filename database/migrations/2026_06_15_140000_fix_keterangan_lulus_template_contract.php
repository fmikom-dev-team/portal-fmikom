<?php

use App\Models\JenisSurat;
use App\Support\TemplatePlaceholderSynchronizer;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $jenisSurat = JenisSurat::query()
            ->whereIn('nama', ['Keterangan Lulus', 'Surat Keterangan Lulus'])
            ->with('template')
            ->first();

        if ($jenisSurat === null || $jenisSurat->template === null) {
            return;
        }

        $components = [
            [
                'type' => 'judul',
                'teks' => 'SURAT KETERANGAN LULUS',
                'align' => 'center',
                'bold' => true,
                'font_size' => '12pt',
                'underline' => true,
            ],
            [
                'type' => 'subjudul',
                'teks' => 'Nomor: {{nomor_surat}}',
                'font_size' => '12pt',
                'margin_left' => 0,
            ],
            [
                'type' => 'paragraf',
                'teks' => 'Yang bertanda tangan di bawah ini menerangkan bahwa:',
                'align' => 'justify',
                'margin_left' => 12,
                'text_indent' => 0,
                'font_size' => '12pt',
            ],
            [
                'type' => 'tabel_data',
                'rows' => [
                    ['label' => 'Nama', 'nilai' => '{{nama}}'],
                    ['label' => 'NIM', 'nilai' => '{{nim}}'],
                    ['label' => 'Tempat, Tanggal Lahir', 'nilai' => '{{tempat_tanggal_lahir}}'],
                    ['label' => 'Tahun Masuk', 'nilai' => '{{tahun_masuk}}'],
                    ['label' => 'Jenjang', 'nilai' => '{{jenjang}}'],
                    ['label' => 'Program Studi', 'nilai' => '{{program_studi}}'],
                ],
                'margin_left' => 30,
                'font_size' => '12pt',
            ],
            [
                'type' => 'paragraf',
                'teks' => 'Berdasarkan data akademik yang tercantum di atas, mahasiswa tersebut dinyatakan lulus.',
                'align' => 'justify',
                'margin_left' => 12,
                'text_indent' => 0,
                'font_size' => '12pt',
            ],
            [
                'type' => 'paragraf',
                'teks' => 'Demikian surat keterangan ini kami buat, untuk dipergunakan sebagaimana mestinya.',
                'align' => 'justify',
                'margin_left' => 12,
                'text_indent' => 0,
                'font_size' => '12pt',
            ],
            [
                'type' => 'tanda_tangan',
                'kolom' => [
                    [
                        'jabatan' => 'Dekan/Direktur',
                        'nama' => 'Mochamad T.A. Aziz Zein, M.Kom',
                        'nik' => 'NIK. 41 230714 020',
                    ],
                ],
                'posisi' => 'kanan',
                'tanggal' => 'Cilacap, 20 September 2026',
                'show_tanggal' => true,
                'margin_left' => 0,
                'font_size' => '12pt',
            ],
        ];

        $jenisSurat->template->forceFill([
            'template_body' => json_encode($components, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'subject' => 'Surat Keterangan Lulus',
        ])->save();

        TemplatePlaceholderSynchronizer::syncTemplate(
            $jenisSurat->template,
            $jenisSurat->field_config ?? [],
        );
    }

    public function down(): void
    {
        // Data migration: perubahan contract template tidak dibalik otomatis.
    }
};
