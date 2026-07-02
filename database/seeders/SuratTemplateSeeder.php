<?php

namespace Database\Seeders;

use App\Models\JenisSurat;
use App\Models\SuratTemplate;
use App\Modules\Fast\Template\Resolvers\TemplatePlaceholderSynchronizer;
use Illuminate\Database\Seeder;

class SuratTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->templates() as $templateData) {
            $jenisSurat = JenisSurat::query()
                ->with('template.placeholders')
                ->where('slug', $templateData['jenis_surat_slug'])
                ->first();

            if ($jenisSurat === null) {
                continue;
            }

            $template = SuratTemplate::withTrashed()->firstOrNew([
                'slug' => $templateData['slug'],
            ]);

            $template->fill([
                'jenis_surat_id' => $jenisSurat->id,
                'name' => $templateData['name'],
                'slug' => $templateData['slug'],
                'format' => 'html',
                'source_reference' => $templateData['source_reference'],
                'subject' => $templateData['subject'],
                'template_header' => $templateData['template_header'] ?? null,
                'template_body' => $this->body($templateData['components']),
                'template_footer' => $templateData['template_footer'] ?? null,
                'version' => $templateData['version'] ?? 1,
                'is_active' => true,
            ]);

            if ($template->trashed()) {
                $template->restore();
            }

            $template->save();

            TemplatePlaceholderSynchronizer::syncTemplate(
                $template,
                $jenisSurat->field_config ?? [],
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function templates(): array
    {
        return [
            [
                'jenis_surat_slug' => 'permohonan-cuti-mahasiswa',
                'name' => 'Template Permohonan Cuti Mahasiswa',
                'slug' => 'template-permohonan-cuti-mahasiswa',
                'source_reference' => '[Template] Permohonan Cuti Mahasiswa.docx',
                'subject' => 'Permohonan Cuti Mahasiswa',
                'components' => [
                    $this->judul('PERMOHONAN CUTI MAHASISWA'),
                    $this->subjudul('Nomor: {{nomor_surat}}'),
                    $this->paragraf('Yang bertanda tangan di bawah ini:'),
                    $this->tabelData([
                        ['label' => 'Nama', 'nilai' => '{{nama_pemohon}}'],
                        ['label' => 'NIM', 'nilai' => '{{nim}}'],
                        ['label' => 'Semester', 'nilai' => '{{semester}} ({{semester_terbilang}})'],
                        ['label' => 'Program Studi', 'nilai' => '{{program_studi}}'],
                        ['label' => 'Tahun Akademik', 'nilai' => '{{tahun_akademik}}'],
                        ['label' => 'Semester Pengajuan', 'nilai' => '{{semester_pengajuan}}'],
                    ]),
                    $this->paragraf('Dengan ini mengajukan permohonan cuti akademik pada Tahun Akademik {{tahun_akademik}} dengan alasan {{alasan_cuti}}.'),
                    $this->paragraf('Demikian permohonan ini saya sampaikan. Atas perhatian dan persetujuannya, saya ucapkan terima kasih.'),
                    $this->signature(),
                ],
            ],
            [
                'jenis_surat_slug' => 'surat-keterangan-lulus',
                'name' => 'Template Surat Keterangan Lulus',
                'slug' => 'template-surat-keterangan-lulus',
                'source_reference' => '[Template] Surat Keterangan Lulus.docx',
                'subject' => 'Surat Keterangan Lulus',
                'components' => [
                    $this->judul('SURAT KETERANGAN LULUS'),
                    $this->subjudul('Nomor: {{nomor_surat}}'),
                    $this->paragraf('Yang bertanda tangan di bawah ini menerangkan bahwa:'),
                    $this->tabelData([
                        ['label' => 'Nama', 'nilai' => '{{nama_mahasiswa}}'],
                        ['label' => 'NIM', 'nilai' => '{{nim}}'],
                        ['label' => 'Tempat, Tanggal Lahir', 'nilai' => '{{tempat_tanggal_lahir}}'],
                        ['label' => 'Tahun Masuk', 'nilai' => '{{tahun_masuk}}'],
                        ['label' => 'Jenjang', 'nilai' => '{{jenjang}}'],
                        ['label' => 'Program Studi', 'nilai' => '{{program_studi}}'],
                        ['label' => 'IPK Akhir', 'nilai' => '{{ipk_akhir}}'],
                    ]),
                    $this->paragraf('Berdasarkan data akademik yang tercantum di atas, mahasiswa tersebut dinyatakan lulus.'),
                    $this->paragraf('{{judul_tugas_akhir_kalimat}}'),
                    $this->paragraf('Demikian surat keterangan ini kami buat, untuk dipergunakan sebagaimana mestinya.'),
                    $this->signature(),
                ],
            ],
            [
                'jenis_surat_slug' => 'surat-permohonan-observasi',
                'name' => 'Template Surat Permohonan Observasi',
                'slug' => 'template-surat-permohonan-observasi',
                'source_reference' => '[Template] Surat Permohonan Observasi.docx',
                'subject' => 'Surat Permohonan Observasi',
                'components' => [
                    $this->judul('SURAT PERMOHONAN OBSERVASI'),
                    $this->subjudul('Nomor: {{nomor_surat}}'),
                    $this->paragraf('Kepada Yth. Pimpinan {{instansi_tujuan}}'),
                    $this->paragraf('di {{alamat_instansi}}'),
                    $this->paragraf('Dengan hormat, kami mengajukan permohonan observasi untuk mahasiswa berikut:'),
                    $this->tabelData([
                        ['label' => 'Nama', 'nilai' => '{{nama_pemohon}}'],
                        ['label' => 'NIM', 'nilai' => '{{nim}}'],
                        ['label' => 'Program Studi', 'nilai' => '{{program_studi}}'],
                        ['label' => 'Topik Observasi', 'nilai' => '{{topik_observasi}}'],
                        ['label' => 'Tanggal Mulai', 'nilai' => '{{tanggal_mulai_panjang}}'],
                        ['label' => 'Tanggal Selesai', 'nilai' => '{{tanggal_selesai_panjang}}'],
                    ]),
                    $this->paragraf('Adapun topik observasi yang diajukan adalah {{topik_observasi}}, yang direncanakan berlangsung pada {{tanggal_mulai_panjang}} sampai dengan {{tanggal_selesai_panjang}}.'),
                    $this->paragraf('Demikian permohonan ini kami sampaikan. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.'),
                    $this->signature(),
                ],
            ],
            [
                'jenis_surat_slug' => 'permohonan-dispensasi-mahasiswa',
                'name' => 'Template Permohonan Dispensasi Mahasiswa',
                'slug' => 'template-permohonan-dispensasi-mahasiswa',
                'source_reference' => '142. permohonan dispensasi Mhs.docx',
                'subject' => 'Permohonan Dispensasi Mahasiswa',
                'components' => [
                    $this->judul('SURAT PERMOHONAN DISPENSASI'),
                    $this->subjudul('Nomor: {{nomor_surat}}'),
                    $this->paragraf('Yang bertanda tangan di bawah ini:'),
                    $this->tabelData([
                        ['label' => 'Nama', 'nilai' => '{{nama_pemohon}}'],
                        ['label' => 'NIM', 'nilai' => '{{nim}}'],
                        ['label' => 'Program Studi', 'nilai' => '{{program_studi}}'],
                        ['label' => 'Mata Kuliah', 'nilai' => '{{mata_kuliah}}'],
                        ['label' => 'Kelas', 'nilai' => '{{kelas}}'],
                        ['label' => 'Dosen Pengampu', 'nilai' => '{{dosen_pengampu}}'],
                        ['label' => 'Tanggal Kegiatan', 'nilai' => '{{tanggal_kegiatan_panjang}}'],
                        ['label' => 'Alasan Dispensasi', 'nilai' => '{{alasan_dispensasi}}'],
                    ]),
                    $this->paragraf('Mengajukan permohonan dispensasi untuk mata kuliah {{mata_kuliah}}{{kelas_info}} pada tanggal {{tanggal_kegiatan_panjang}}{{dosen_pengampu_info}}, dengan alasan {{alasan_dispensasi}}.'),
                    $this->paragraf('Demikian surat permohonan ini saya sampaikan. Atas perhatian dan kebijaksanaannya saya ucapkan terima kasih.'),
                    $this->signature(),
                ],
            ],
            [
                'jenis_surat_slug' => 'permohonan-pindah-kelas',
                'name' => 'Template Permohonan Pindah Kelas',
                'slug' => 'template-permohonan-pindah-kelas',
                'source_reference' => 'permohonan  pindah kelas.docx',
                'subject' => 'Permohonan Pindah Kelas',
                'components' => [
                    $this->judul('SURAT PERMOHONAN PINDAH KELAS'),
                    $this->subjudul('Nomor: {{nomor_surat}}'),
                    $this->paragraf('Yang bertanda tangan di bawah ini:'),
                    $this->tabelData([
                        ['label' => 'Nama', 'nilai' => '{{nama_pemohon}}'],
                        ['label' => 'NIM', 'nilai' => '{{nim}}'],
                        ['label' => 'Program Studi', 'nilai' => '{{program_studi}}'],
                        ['label' => 'Mata Kuliah', 'nilai' => '{{mata_kuliah}}'],
                        ['label' => 'Kelas Asal', 'nilai' => '{{kelas_asal}}'],
                        ['label' => 'Kelas Tujuan', 'nilai' => '{{kelas_tujuan}}'],
                        ['label' => 'Alasan Pindah', 'nilai' => '{{alasan_pindah}}'],
                    ]),
                    $this->paragraf('Mengajukan permohonan pindah kelas untuk mata kuliah {{mata_kuliah}} dari kelas {{kelas_asal}} ke kelas {{kelas_tujuan}} dengan alasan {{alasan_pindah}}.'),
                    $this->paragraf('Demikian permohonan ini saya ajukan. Atas perhatian dan persetujuannya, saya ucapkan terima kasih.'),
                    $this->signature(),
                ],
            ],
            [
                'jenis_surat_slug' => 'permohonan-menjadi-penguji-sidang',
                'name' => 'Template Permohonan Menjadi Penguji Sidang',
                'slug' => 'template-permohonan-menjadi-penguji-sidang',
                'source_reference' => 'PERMOHONAN MENJADI PENGUJI SIDANG (fix) (1).docx',
                'subject' => 'Permohonan Menjadi Penguji Sidang',
                'components' => [
                    $this->judul('SURAT TUGAS / PERMOHONAN MENJADI PENGUJI SIDANG'),
                    $this->subjudul('Nomor: {{nomor_surat}}'),
                    $this->paragraf('Yang bertanda tangan di bawah ini menugaskan / memohonkan kepada:'),
                    $this->tabelData([
                        ['label' => 'Nama Dosen', 'nilai' => '{{nama_dosen}}'],
                        ['label' => 'NIP / NIDN', 'nilai' => '{{nip}}'],
                        ['label' => 'Nama Mahasiswa', 'nilai' => '{{nama_mahasiswa}}'],
                        ['label' => 'NIM Mahasiswa', 'nilai' => '{{nim_mahasiswa}}'],
                        ['label' => 'Judul Skripsi', 'nilai' => '{{judul_skripsi}}'],
                        ['label' => 'Tanggal Sidang', 'nilai' => '{{tanggal_sidang_panjang}}'],
                        ['label' => 'Ruang Sidang', 'nilai' => '{{ruang_sidang}}'],
                    ]),
                    $this->paragraf('Untuk menjadi penguji sidang mahasiswa {{nama_mahasiswa}} (NIM {{nim_mahasiswa}}) dengan judul tugas akhir {{judul_skripsi}}.'),
                    $this->paragraf('Sidang dijadwalkan pada {{tanggal_sidang_panjang}}{{ruang_sidang_info}}.'),
                    $this->paragraf('Demikian surat ini dibuat untuk dilaksanakan sebagaimana mestinya.'),
                    $this->signature(),
                ],
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $components
     */
    protected function body(array $components): string
    {
        return json_encode($components, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array<string, mixed>
     */
    protected function judul(string $teks): array
    {
        return [
            'type' => 'judul',
            'teks' => $teks,
            'align' => 'center',
            'bold' => true,
            'font_size' => '12pt',
            'underline' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function subjudul(string $teks): array
    {
        return [
            'type' => 'subjudul',
            'teks' => $teks,
            'align' => 'center',
            'font_size' => '12pt',
            'margin_left' => 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function paragraf(string $teks): array
    {
        return [
            'type' => 'paragraf',
            'teks' => $teks,
            'align' => 'justify',
            'margin_left' => 12,
            'text_indent' => 0,
            'font_size' => '12pt',
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    protected function tabelData(array $rows): array
    {
        return [
            'type' => 'tabel_data',
            'rows' => $rows,
            'margin_left' => 12,
            'font_size' => '12pt',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function signature(): array
    {
        return [
            'type' => 'tanda_tangan',
            'kolom' => [
                [
                    'jabatan' => '{{jabatan_penanda_tangan}}',
                    'nama' => '{{nama_penanda_tangan}}',
                    'nik' => '{{nik_penanda_tangan}}',
                ],
            ],
            'posisi' => 'kanan',
            'tanggal' => '{{kota_surat}}, {{tanggal_surat_panjang}}',
            'show_tanggal' => true,
            'margin_left' => 0,
            'font_size' => '12pt',
        ];
    }
}
