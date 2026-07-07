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
                ->orWhere('nama', $templateData['jenis_surat_nama'])
                ->first();

            if ($jenisSurat === null) {
                continue;
            }

            $template = SuratTemplate::withTrashed()
                ->where('slug', $templateData['slug'])
                ->orWhere('jenis_surat_id', $jenisSurat->id)
                ->orWhere('name', $templateData['name'])
                ->orderByDesc('id')
                ->first() ?? new SuratTemplate;

            $template->fill([
                'jenis_surat_id' => $jenisSurat->id,
                'name' => $templateData['name'],
                'slug' => $templateData['slug'],
                'format' => 'html',
                'source_reference' => $templateData['source_reference'],
                'subject' => $templateData['subject'],
                'template_header' => $templateData['template_header'],
                'template_body' => $templateData['template_body'],
                'template_footer' => $templateData['template_footer'],
                'version' => $templateData['version'],
                'is_active' => true,
                'css_style' => $templateData['css_style'],
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
                'jenis_surat_slug' => 'surat-permohonan-cuti-mahasiswa-1782391599',
                'jenis_surat_nama' => 'Surat Permohonan Cuti Mahasiswa',
                'name' => 'Surat Permohonan Cuti Mahasiswa',
                'slug' => 'template-surat-permohonan-cuti-mahasiswa-1782391599-v1',
                'source_reference' => null,
                'subject' => 'personal',
                'template_header' => null,
                'template_body' => $this->jsonBody(<<<'JSON'
[{"type":"judul","teks":"Permohonan cuti mahasiswa","align":"center","bold":true,"font_size":"12pt","underline":true},{"type":"subjudul","teks":"Nomor: {{nomor_surat}}","font_size":"12pt","margin_left":0,"align":"center","bold":false,"underline":false},{"type":"spasi","tinggi":12},{"type":"paragraf","teks":"Yang bertanda tangan di bawah ini:","align":"justify","margin_left":0,"text_indent":0,"font_size":"12pt"},{"type":"tabel_data","rows":[{"label":"Nama ","nilai":"{{nama_pemohon}}"},{"label":"NIM","nilai":"{{nim}}"},{"label":"Semester","nilai":"{{semester}}"},{"label":"Fak/Prodi","nilai":"{{fak_prodi}}"}],"margin_left":0,"font_size":"12pt"},{"type":"paragraf","teks":"Dengan ini mengajukan permohonan cuti, dengan alasan dikarenakan {{alasan}}","align":"justify","margin_left":0,"text_indent":0,"font_size":"12pt"},{"type":"paragraf","teks":"Demikian permohonan ini saya sampaikan, atas perhatian dan terkabulnya permohonan ini disampaikan terima kasih.","align":"justify","margin_left":0,"text_indent":0,"font_size":"12pt"},{"type":"tanda_tangan","kolom":[{"jabatan":"Kaprodi,","nama":"{{kaprodi}}","nik":"NIK: {{nik}}","posisi":"kanan","jabatan_bold":false,"jabatan_underline":false,"nama_bold":true,"nama_underline":true,"nik_bold":false,"nik_underline":false}],"tanggal":"Cilacap, {{tanggal_surat_panjang}}","show_tanggal":true,"margin_left":0,"font_size":"12pt"}]
JSON),
                'template_footer' => null,
                'version' => 61,
                'css_style' => $this->commonCss(),
            ],
            [
                'jenis_surat_slug' => 'surat-permohonan-dispensasi-mahasiswa-1782467416',
                'jenis_surat_nama' => 'Surat Permohonan Dispensasi Mahasiswa',
                'name' => 'Surat Permohonan Dispensasi Mahasiswa',
                'slug' => 'template-surat-permohonan-dispensasi-mahasiswa-1782467416-v1',
                'source_reference' => null,
                'subject' => 'institution',
                'template_header' => null,
                'template_body' => $this->jsonBody(<<<'JSON'
[{"type":"header_surat","nomor":"{{nomor_surat}}","lampiran":"-","perihal":"Surat Permohonan Dispensasi","kota":"{{kota_surat}}","tanggal":"{{tanggal_surat_panjang}}","margin_left":"","font_size":"12pt"},{"type":"spasi","tinggi":16},{"type":"kepada_yth","penerima":["Bapak/Ibu"],"lokasi":"di-","tempat":"Tempat","margin_left":100,"font_size":"12pt"},{"type":"spasi","tinggi":16},{"type":"paragraf","teks":"Assalamu’alaikum Wr. Wb.","align":"justify","margin_left":0,"text_indent":100,"font_size":"12pt","bold":false,"italic":true},{"type":"paragraf","teks":"Salam silaturrahmi kami sampaikan semoga Bapak/Ibu Dosen di Lingkungan Fakultas Matematika dan Ilmu Komputer Universitas Nahdlatul Ulama Al Ghazali Cilacap, senantiasa dalam lindungan Allah  SWT. Amin.","align":"justify","margin_left":100,"text_indent":"","font_size":"12pt"},{"type":"spasi","tinggi":12},{"type":"paragraf","teks":"Sehubungan dengan adanya {{text}}, yang akan dilaksanakan pada:","align":"justify","margin_left":100,"text_indent":0,"font_size":"12pt"},{"type":"tabel_data","rows":[{"label":"Hari/Tanggal","nilai":"{{hari_tanggal}}"},{"label":"Waktu","nilai":"{{waktu}}"},{"label":"Tempat","nilai":"{{tempat}}"}],"margin_left":120,"font_size":"12pt"},{"type":"spasi","tinggi":12},{"type":"paragraf","teks":"Maka kami dari Fakultas Matematika dan Ilmu Komputer UNUGHA Cilacap bermaksud mohon ijin kepada Bapak/Ibu Dosen untuk mahasiswa kami atas nama {{nama}} mengikuti kegiatan tersebut.","align":"justify","margin_left":100,"text_indent":0,"font_size":"12pt"},{"type":"spasi","tinggi":12},{"type":"paragraf","teks":"Demikian surat ini kami sampaikan, atas kesediaan Bapak/Ibu Dosen memberikan dispensasi kepada mahasiswa tersebut disampaikan terima kasih.","align":"justify","margin_left":100,"text_indent":0,"font_size":"12pt"},{"type":"spasi","tinggi":12},{"type":"paragraf","teks":"Wassalamu’alaikum Wr. Wb.","align":"justify","margin_left":0,"text_indent":100,"font_size":"12pt","bold":false,"italic":true},{"type":"tanda_tangan","kolom":[{"jabatan":"Dekan FMIKOM,","nama":"Mochamad T.A. Aziz Zein, M.Kom.","nik":"NIK. 41 230714 020","posisi":"kanan","nama_bold":true,"nama_underline":true}],"tanggal":"","show_tanggal":false,"margin_left":0,"font_size":"12pt"}]
JSON),
                'template_footer' => null,
                'version' => 28,
                'css_style' => $this->commonCss(),
            ],
            [
                'jenis_surat_slug' => 'surat-permohonan-observasi-mahasiswa-1782469453',
                'jenis_surat_nama' => 'Surat Permohonan Observasi Mahasiswa',
                'name' => 'Surat Permohonan Observasi Mahasiswa',
                'slug' => 'template-surat-permohonan-observasi-mahasiswa-1782469453-v1',
                'source_reference' => null,
                'subject' => 'personal',
                'template_header' => null,
                'template_body' => $this->jsonBody(<<<'JSON'
[{"type":"header_surat","nomor":"{{nomor_surat}}","lampiran":"-","perihal":"Surat Permohonan Observasi","kota":"{{kota_surat}}","tanggal":"{{tanggal_surat_panjang}}","margin_left":0,"font_size":"12pt"},{"type":"spasi","tinggi":12},{"type":"kepada_yth","penerima":["Bapak/Ibu"],"lokasi":"di-","tempat":"Tempat","margin_left":100,"font_size":"12pt"},{"type":"spasi","tinggi":12},{"type":"paragraf","teks":"Assalamu’alaikum Wr.Wb.","align":"justify","margin_left":100,"text_indent":0,"font_size":"12pt","italic":true},{"type":"paragraf","teks":"Salam silaturahmi dan sejahtera kami sampaikan semoga kita senantiasa mendapatkan ridlo dan pertolongan dari Allah SWT dalam menjalankan aktivitas sehari-hari. Amin","align":"justify","margin_left":100,"text_indent":20,"font_size":"12pt"},{"type":"paragraf","teks":"Sehubungan dengan tuntutan kebutuhan mahasiswa untuk mendapatkan pengalaman nyata di lapangan, maka Fakultas Matematika dan Ilmu Komputer Universitas Nahdlatul Ulama Al Ghazali Cilacap menugaskan kepada mahasiswa kami:","align":"justify","margin_left":100,"text_indent":20,"font_size":"12pt"},{"type":"tabel_data","rows":[{"label":"Nama","nilai":"{{nama_pemohon}}"},{"label":"NIM","nilai":"{{nim}}"},{"label":"Prodi","nilai":"{{prodi}}"}],"margin_left":120,"font_size":"12pt"},{"type":"paragraf","teks":"Untuk mengadakan riset terkait dengan skripsi yang sedang di kerjakan dengan Judul “{{judul_skripsi}}”. Berkenaan dengan hal tersebut, maka kami mengajukan permohonan kepada Bapak/Ibu {{nama_lembaga}}untuk mengizinkan mahasiswa kami melaksanakan kegiatan tersebut.","align":"justify","margin_left":100,"text_indent":0,"font_size":"12pt"},{"type":"paragraf","teks":"Demikian surat ini kami sampaikan, atas bimbingan dan kerjasamanya disampaikan terimakasih.","align":"justify","margin_left":100,"text_indent":20,"font_size":"12pt"},{"type":"paragraf","teks":"Wassalamu’alaikum Wr.Wb","align":"justify","margin_left":100,"text_indent":0,"font_size":"12pt","italic":true},{"type":"tanda_tangan","kolom":[{"jabatan":"Dekan,","nama":"Mochamad T.A. Aziz Zein, M.Kom","nik":"NIK. 41 230714 020","posisi":"kanan","nama_underline":true,"nama_bold":true}],"tanggal":"","show_tanggal":false,"margin_left":0,"font_size":"12pt"}]
JSON),
                'template_footer' => null,
                'version' => 14,
                'css_style' => $this->commonCss(),
            ],
            [
                'jenis_surat_slug' => 'surat-permohonan-menjadi-penguji-sidang-1782470954',
                'jenis_surat_nama' => 'Surat Permohonan menjadi Penguji Sidang',
                'name' => 'Surat Permohonan menjadi Penguji Sidang',
                'slug' => 'template-surat-permohonan-menjadi-penguji-sidang-1782470954-v1',
                'source_reference' => null,
                'subject' => 'institution',
                'template_header' => null,
                'template_body' => $this->jsonBody(<<<'JSON'
[{"type":"header_surat","nomor":"{{nomor_surat}}","lampiran":"-","perihal":"Surat Permohonan menjadi Penguji Sidang","kota":"{{kota_surat}}","tanggal":"{{tanggal_surat_panjang}}","margin_left":0,"font_size":"12pt"},{"type":"spasi","tinggi":12},{"type":"kepada_yth","penerima":["Bapak/Ibu"],"lokasi":"di -","tempat":"Tempat","margin_left":100,"font_size":"12pt"},{"type":"spasi","tinggi":12},{"type":"paragraf","teks":"Assalamu’alaikum Wr. Wb.","align":"justify","margin_left":100,"text_indent":0,"font_size":"12pt"},{"type":"paragraf","teks":"Salam silaturahmi dan sejahtera semoga kita senantiasa mendapatkan ridlo dan pertolongan dari Allah SWT dalam menjalankan aktivitas sehari-hari, Amiiin.\\n","align":"justify","margin_left":100,"text_indent":20,"font_size":"12pt"},{"type":"paragraf","teks":"Sehubungan dengan adanya Sidang Skripsi Mahasiswa Program Studi S1 Fakultas Matematika dan Ilmu Komputer, maka kami mohon kehadiran Bapak/Ibu sebagai Penguji yang akan dilaksanakan pada:","align":"justify","margin_left":100,"text_indent":20,"font_size":"12pt"},{"type":"tabel_data","rows":[{"label":"Hari/Tanggal","nilai":"{{hari_tanggal}}"},{"label":"Waktu","nilai":"{{waktu}}"},{"label":"Tempat","nilai":"{{tempat}}"}],"margin_left":100,"font_size":"12pt"},{"type":"paragraf","teks":"Adapun terkait dengan berita acara sidang dapat dilakukan pengisiannya melalui Sistem Akademik (SIAKAD) dan untuk tandatangan berita acara dapat dilakukan di Fakultas Matematika dan Ilmu Komputer.","align":"justify","margin_left":100,"text_indent":20,"font_size":"12pt"},{"type":"paragraf","teks":"Demikian surat ini kami sampaikan, atas perhatian dan kerjasamanya diucapkan terimakasih. ","align":"justify","margin_left":100,"text_indent":0,"font_size":"12pt"},{"type":"paragraf","teks":"Wassalamu’alaikum Wr.Wb.","align":"justify","margin_left":100,"text_indent":20,"font_size":"12pt"},{"type":"tanda_tangan","kolom":[{"jabatan":"Dekan,","nama":"Mochamad T.A. Aziz Zein, S.Si, M.Kom","nik":"NIK. 41230714020","posisi":"kanan","nama_bold":true,"nama_underline":true}],"tanggal":"","show_tanggal":false,"margin_left":0,"font_size":"12pt"}]
JSON),
                'template_footer' => null,
                'version' => 8,
                'css_style' => $this->commonCss(),
            ],
            [
                'jenis_surat_slug' => 'surat-keterangan-lulus-mahasiswa-1782471658',
                'jenis_surat_nama' => 'Surat Keterangan Lulus Mahasiswa',
                'name' => 'Surat Keterangan Lulus Mahasiswa',
                'slug' => 'template-surat-keterangan-lulus-mahasiswa-1782471658-v1',
                'source_reference' => null,
                'subject' => 'personal',
                'template_header' => null,
                'template_body' => $this->jsonBody(<<<'JSON'
[{"type":"judul","teks":"surat keterangan lulus","align":"center","bold":true,"font_size":"12pt","underline":true},{"type":"subjudul","teks":"Nomor: {{nomor_surat}}","align":"center","bold":false,"underline":false,"font_size":"12pt","margin_left":0},{"type":"spasi","tinggi":16},{"type":"paragraf","teks":"Yang bertandatangan di bawah ini :","align":"justify","margin_left":0,"text_indent":0,"font_size":"12pt"},{"type":"tabel_data","rows":[{"label":"Nama","nilai":"Mochamad T.A. Aziz Zein, S.Si, M.Kom"},{"label":"NIK","nilai":"41 230714 020"},{"label":"Jabatan","nilai":"Dekan Fakultas Matematika dan Ilmu Komputer"}],"margin_left":0,"font_size":"12pt"},{"type":"spasi","tinggi":16},{"type":"paragraf","teks":"Menerangkan Mahasiswa tersebut di bawah ini :","align":"justify","margin_left":0,"text_indent":0,"font_size":"12pt"},{"type":"tabel_data","rows":[{"label":"Nama","nilai":"{{nama_pemohon}}"},{"label":"NIM","nilai":"{{nim}}"},{"label":"Tempat, Tanggal Lahir","nilai":"{{tempat_tanggal_lahir}}"},{"label":"Tahun Masuk","nilai":"{{tahun_masuk}}"},{"label":"Jenjang","nilai":"{{jenjang}}"},{"label":"Program Studi","nilai":"{{program_studi}}"},{"label":"Akreditasi","nilai":"{{akreditasi}}"},{"label":"Nomor SK Akreditasi","nilai":"{{nomor_sk_akreditasi}}"},{"label":"Nomor SK Yudisium","nilai":"{{nomor_sk_yudisium}}"}],"margin_left":0,"font_size":"12pt"},{"type":"spasi","tinggi":12},{"type":"paragraf","teks":"Adalah benar mahasiswa kami yang TELAH DINYATAKAN LULUS pada Tanggal {{tanggal_lulus}}. Adapun ijazah masih dalam proses. ","align":"justify","margin_left":0,"text_indent":0,"font_size":"12pt"},{"type":"paragraf","teks":"Demikian surat keterangan ini kami buat, untuk dipergunakan sebagaimana mestinya.","align":"justify","margin_left":0,"text_indent":0,"font_size":"12pt"},{"type":"tanda_tangan","kolom":[{"jabatan":"Dekan,","nama":"Mochamad T.A. Aziz Zein, S.Si, M.Kom","nik":"NIK. 41 230714 020","posisi":"kanan","nama_underline":true,"nama_bold":true,"nik_bold":false}],"tanggal":"Cilacap, {{tanggal_surat_panjang}}","show_tanggal":true,"margin_left":0,"font_size":"12pt"}]
JSON),
                'template_footer' => null,
                'version' => 19,
                'css_style' => $this->commonCss(),
            ],
            [
                'jenis_surat_slug' => 'yoyoy-1782530493',
                'jenis_surat_nama' => 'Surat Undangan Persiapan PKL FMIKOM',
                'name' => 'Surat Undangan Persiapan PKL FMIKOM',
                'slug' => 'template-yoyoy-1782530493-v1',
                'source_reference' => null,
                'subject' => 'institution',
                'template_header' => null,
                'template_body' => $this->jsonBody(<<<'JSON'
[{"type":"header_surat","nomor":"{{nomor_surat}}","lampiran":"-","perihal":"Surat Undangan Persiapan PKL FMIKOM","kota":"{{kota_surat}}","tanggal":"{{tanggal_surat_panjang}}","margin_left":0,"font_size":"12pt"},{"type":"spasi","tinggi":16},{"type":"kepada_yth","penerima":["Bapak/Ibu"],"lokasi":"di-","tempat":"Tempat","margin_left":100,"font_size":"12pt"},{"type":"spasi","tinggi":16},{"type":"paragraf","teks":"Assalamualaikum Wr.Wb","align":"justify","margin_left":100,"text_indent":0,"font_size":"12pt","italic":true},{"type":"paragraf","teks":"Salam silaturahmi kami sampaikan, semoga kita senantiasa dalam lindungan Allah SWT. Amiin.","align":"justify","margin_left":100,"text_indent":0,"font_size":"12pt"},{"type":"paragraf","teks":"Mengharap dengan hormat kehadiran Bapak/Ibu Panitia PKL FMIKOM Tahun {{tahun_dilaksanakan}}, Pada:","align":"justify","margin_left":100,"text_indent":0,"font_size":"12pt"},{"type":"tabel_data","rows":[{"label":"Hari/Tanggal","nilai":"{{hari_tanggal}}"},{"label":"Waktu","nilai":"{{waktu}}"},{"label":"Tempat","nilai":"{{tempat}}"},{"label":"Agenda","nilai":"{{agenda}}"}],"margin_left":100,"font_size":"12pt"},{"type":"paragraf","teks":"Demikian undangan ini, atas kehadirannya disampaikan terima kasih.","align":"justify","margin_left":100,"text_indent":"","font_size":"12pt"},{"type":"paragraf","teks":"Wassalamualaikum Wr. Wb","align":"justify","margin_left":100,"text_indent":0,"font_size":"12pt","italic":true},{"type":"tanda_tangan","kolom":[{"jabatan":"Ketua Panitia PKL FMIKOM,","nama":"{{ketua_panitia}}","nik":"NIK. {{nik}}","posisi":"kanan","nama_bold":true,"nama_underline":true}],"tanggal":"","show_tanggal":true,"margin_left":0,"font_size":"12pt"}]
JSON),
                'template_footer' => null,
                'version' => 13,
                'css_style' => $this->commonCss(),
            ],
        ];
    }

    protected function jsonBody(string $json): string
    {
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return json_encode($decoded, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    protected function commonCss(): string
    {
        return <<<'CSS'
@page {
    margin: 12mm 15mm 25mm 15mm;
}
.surat-content {
    padding-left: 0mm;
    padding-right: 0mm;
}
.surat-paragraf {
    text-indent: 0mm;
}
.surat-tabel {
    margin-left: 0mm;
}
CSS;
    }
}
