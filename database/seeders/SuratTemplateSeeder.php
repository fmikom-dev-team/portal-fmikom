<?php

namespace Database\Seeders;

use App\Models\JenisSurat;
use App\Models\SuratTemplate;
use Illuminate\Database\Seeder;

class SuratTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'jenis_surat_slug' => 'permohonan-cuti-mahasiswa',
                'name' => 'Template Permohonan Cuti Mahasiswa',
                'slug' => 'template-permohonan-cuti-mahasiswa',
                'source_reference' => '[Template] Permohonan Cuti Mahasiswa.docx',
                'subject' => 'Permohonan Cuti Mahasiswa',
                'template_header' => <<<'HTML'
<section style="margin-bottom: 10px; text-align: center;">
    <img src="{{kop_logo_data_uri}}" alt="Logo UNU Al Ghazali Cilacap" style="display: block; height: 70px; margin: 0 auto 2px;">
    <div style="color: #00b050; font-family: inherit; font-weight: 700; line-height: 1.04; text-align: center;">
        <div style="font-size: 16pt;">UNUGHA CILACAP</div>
        <div style="font-size: 14pt;">FAKULTAS MATEMATIKA DAN ILMU KOMPUTER</div>
        <div style="font-size: 14pt;">(FMIKOM)</div>
    </div>
    <p style="color: #00b050; font-size: 10pt; line-height: 1.2; margin: 3px 0 5px; text-align: center;">
        Keputusan Kemendikbud RI Nomor : 264/E/O/2014 Tanggal 23 Juli 2014
    </p>
    <div style="margin: 0 auto; width: 100%;">
        <div style="border-top: 1.6px solid #00b050; margin-bottom: 1.5px;"></div>
        <div style="border-top: 0.8px solid #00b050;"></div>
    </div>
</section>
HTML,
                'template_body' => <<<'HTML'
<section class="surat surat-permohonan-cuti">
    <h1 style="margin-bottom: 0; text-align: center;">PERMOHONAN CUTI MAHASISWA</h1>
    <p style="margin-bottom: 10px; text-align: center;">Nomor: {{nomor_surat}}</p>

    <p style="margin-bottom: 8px; text-align: justify;">Yang bertanda tangan di bawah ini:</p>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">
        <tbody>
            <tr>
                <td style="width: 126px; padding: 1px 0; vertical-align: top;">Nama</td>
                <td style="width: 14px; padding: 1px 0; vertical-align: top;">:</td>
                <td style="padding: 1px 0; vertical-align: top;">{{nama_pemohon}}</td>
            </tr>
            <tr>
                <td style="padding: 1px 0; vertical-align: top;">NIM</td>
                <td style="padding: 1px 0; vertical-align: top;">:</td>
                <td style="padding: 1px 0; vertical-align: top;">{{nim}}</td>
            </tr>
            <tr>
                <td style="padding: 1px 0; vertical-align: top;">Semester</td>
                <td style="padding: 1px 0; vertical-align: top;">:</td>
                <td style="padding: 1px 0; vertical-align: top;">{{semester}} ({{semester_terbilang}})</td>
            </tr>
            <tr>
                <td style="padding: 1px 0; vertical-align: top;">Fak/Prodi</td>
                <td style="padding: 1px 0; vertical-align: top;">:</td>
                <td style="padding: 1px 0; vertical-align: top;">{{fakultas}}/{{program_studi}}</td>
            </tr>
        </tbody>
    </table>

    <p style="margin-bottom: 8px; text-align: justify;">
        Dengan ini mengajukan permohonan cuti, dengan alasan {{alasan_cuti}}.
    </p>

    <p style="text-align: justify;">
        Demikian permohonan ini saya sampaikan, atas perhatian dan terkabulnya
        permohonan ini disampaikan terima kasih.
    </p>

    <div style="margin-top: 8px; width: 100%;">
        <div style="margin-left: auto; width: 220px;">
            <p style="margin-bottom: 0;">{{kota_surat}}, {{tanggal_surat_panjang}}</p>
            <p style="margin-bottom: 40px;">Pemohon,</p>
            <p style="margin-bottom: 0;"><strong>{{nama_pemohon}}</strong></p>
            <p>NIM. {{nim}}</p>
        </div>
    </div>

    <p style="margin-top: 2px; margin-bottom: 2px; text-align: center;">Mengetahui,</p>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">
        <tbody>
            <tr>
                <td style="width: 50%; padding: 0 12px 0 0; vertical-align: top; text-align: center;">
                    <div style="height: 30px; margin-bottom: 34px;">DPA</div>
                    <p style="margin-bottom: 0;"><strong>Verry, M.Kom.</strong></p>
                    <p>NIK. 41230714</p>
                </td>
                <td style="width: 50%; padding: 0 0 0 12px; vertical-align: top; text-align: center;">
                    <div style="height: 30px; margin-bottom: 34px;">
                        <div>Kaprodi Informatika</div>
                    </div>
                    <p style="margin-bottom: 0;"><strong>Tri Anggoro, M.Kom.</strong></p>
                    <p>NIK. 41230714268</p>
                </td>
            </tr>
        </tbody>
    </table>

    <div style="font-size: 10.5pt; line-height: 1.2;">
        <p style="margin-bottom: 2px;">Tembusan:</p>
        <p style="margin-bottom: 0;">1. Ka. Prodi</p>
        <p style="margin-bottom: 0;">2. Bagian Akademik</p>
        <p style="margin-bottom: 0;">3. Bagian Keuangan</p>
        <p style="margin-bottom: 0;">4. Fakultas</p>
        <p style="margin-bottom: 0;">5. Mahasiswa</p>
    </div>
</section>
HTML,
                'placeholders' => [
                    ['placeholder_key' => 'kop_logo_data_uri', 'label' => 'Logo Kop Surat', 'source_type' => 'system', 'source_key' => 'kop_logo_data_uri', 'is_required' => true, 'description' => 'Logo sementara untuk kop surat fakultas.'],
                    ['placeholder_key' => 'nomor_surat', 'label' => 'Nomor Surat', 'source_type' => 'surat', 'source_key' => 'nomor_surat', 'is_required' => true, 'description' => 'Nomor surat final yang digenerate sistem.'],
                    ['placeholder_key' => 'nama_pemohon', 'label' => 'Nama Pemohon', 'source_type' => 'user', 'source_key' => 'name', 'is_required' => true, 'description' => 'Nama mahasiswa dari akun pemohon.'],
                    ['placeholder_key' => 'nim', 'label' => 'NIM', 'source_type' => 'surat_data', 'source_key' => 'nim', 'is_required' => true, 'description' => 'Nomor induk mahasiswa.'],
                    ['placeholder_key' => 'semester', 'label' => 'Semester', 'source_type' => 'surat_data', 'source_key' => 'semester', 'is_required' => true, 'description' => 'Semester saat ini dalam angka.'],
                    ['placeholder_key' => 'semester_terbilang', 'label' => 'Semester Terbilang', 'source_type' => 'computed', 'source_key' => 'semester_terbilang', 'is_required' => true, 'description' => 'Konversi angka semester ke teks.'],
                    ['placeholder_key' => 'fakultas', 'label' => 'Fakultas', 'source_type' => 'system', 'source_key' => 'fakultas_nama', 'is_required' => true, 'default_value' => 'FMIKOM', 'description' => 'Nama fakultas default.'],
                    ['placeholder_key' => 'program_studi', 'label' => 'Program Studi', 'source_type' => 'surat_data', 'source_key' => 'program_studi', 'is_required' => true, 'description' => 'Program studi mahasiswa.'],
                    ['placeholder_key' => 'tahun_akademik', 'label' => 'Tahun Akademik', 'source_type' => 'surat_data', 'source_key' => 'tahun_akademik', 'is_required' => true, 'description' => 'Tahun akademik pengajuan cuti.'],
                    ['placeholder_key' => 'semester_pengajuan', 'label' => 'Semester Pengajuan', 'source_type' => 'surat_data', 'source_key' => 'semester_pengajuan', 'is_required' => true, 'description' => 'Semester ganjil atau genap.'],
                    ['placeholder_key' => 'alasan_cuti', 'label' => 'Alasan Cuti', 'source_type' => 'surat_data', 'source_key' => 'alasan_cuti', 'is_required' => true, 'description' => 'Alasan pengajuan cuti.'],
                    ['placeholder_key' => 'kota_surat', 'label' => 'Kota Surat', 'source_type' => 'system', 'source_key' => 'kota_surat', 'is_required' => true, 'default_value' => 'Cilacap', 'description' => 'Kota penerbitan surat.'],
                    ['placeholder_key' => 'tanggal_surat_panjang', 'label' => 'Tanggal Surat Panjang', 'source_type' => 'computed', 'source_key' => 'tanggal_surat_panjang', 'is_required' => true, 'description' => 'Tanggal final dalam format panjang bahasa Indonesia.'],
                ],
            ],
            [
                'jenis_surat_slug' => 'surat-keterangan-lulus',
                'name' => 'Template Surat Keterangan Lulus',
                'slug' => 'template-surat-keterangan-lulus',
                'source_reference' => '[Template] Surat Keterangan Lulus.docx',
                'subject' => 'Surat Keterangan Lulus',
                'template_body' => <<<'HTML'
<section class="surat surat-keterangan-lulus">
    <h1 style="text-align:center;">SURAT KETERANGAN LULUS</h1>
    <p style="text-align:center;">Nomor: {{nomor_surat}}</p>

    <p>Yang bertanda tangan di bawah ini menerangkan bahwa:</p>
    <p>Nama: {{nama_pemohon}}</p>
    <p>NIM: {{nim}}</p>
    <p>Program Studi: {{program_studi}}</p>

    <p>
        Telah dinyatakan lulus pada tanggal {{tanggal_yudisium_panjang}}
        dengan IPK akhir {{ipk_akhir}}.
    </p>

    <p>{{judul_tugas_akhir_kalimat}}</p>

    <p>
        Surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.
    </p>

    <p>{{kota_surat}}, {{tanggal_surat_panjang}}</p>
    <p><strong>{{penanda_tangan_jabatan}}</strong></p>
    <br><br>
    <p><strong>{{penanda_tangan_nama}}</strong></p>
    <p>NIDN. {{penanda_tangan_nidn}}</p>
</section>
HTML,
                'placeholders' => [
                    ['placeholder_key' => 'nomor_surat', 'label' => 'Nomor Surat', 'source_type' => 'surat', 'source_key' => 'nomor_surat', 'is_required' => true, 'description' => 'Nomor surat final.'],
                    ['placeholder_key' => 'nama_pemohon', 'label' => 'Nama Pemohon', 'source_type' => 'user', 'source_key' => 'name', 'is_required' => true, 'description' => 'Nama mahasiswa yang dinyatakan lulus.'],
                    ['placeholder_key' => 'nim', 'label' => 'NIM', 'source_type' => 'surat_data', 'source_key' => 'nim', 'is_required' => true, 'description' => 'Nomor induk mahasiswa.'],
                    ['placeholder_key' => 'program_studi', 'label' => 'Program Studi', 'source_type' => 'surat_data', 'source_key' => 'program_studi', 'is_required' => true, 'description' => 'Program studi mahasiswa.'],
                    ['placeholder_key' => 'tanggal_yudisium_panjang', 'label' => 'Tanggal Yudisium Panjang', 'source_type' => 'computed', 'source_key' => 'tanggal_yudisium_panjang', 'is_required' => true, 'description' => 'Tanggal yudisium dalam format panjang.'],
                    ['placeholder_key' => 'ipk_akhir', 'label' => 'IPK Akhir', 'source_type' => 'surat_data', 'source_key' => 'ipk_akhir', 'is_required' => true, 'description' => 'Nilai IPK akhir mahasiswa.'],
                    ['placeholder_key' => 'judul_tugas_akhir', 'label' => 'Judul Tugas Akhir', 'source_type' => 'surat_data', 'source_key' => 'judul_tugas_akhir', 'is_required' => false, 'description' => 'Judul tugas akhir jika dibutuhkan dalam surat.'],
                    ['placeholder_key' => 'judul_tugas_akhir_kalimat', 'label' => 'Kalimat Judul Tugas Akhir', 'source_type' => 'computed', 'source_key' => 'judul_tugas_akhir_kalimat', 'is_required' => false, 'description' => 'Kalimat lengkap judul tugas akhir jika nilainya tersedia.'],
                    ['placeholder_key' => 'kota_surat', 'label' => 'Kota Surat', 'source_type' => 'system', 'source_key' => 'kota_surat', 'is_required' => true, 'default_value' => 'Cilacap', 'description' => 'Kota penerbitan surat.'],
                    ['placeholder_key' => 'tanggal_surat_panjang', 'label' => 'Tanggal Surat Panjang', 'source_type' => 'computed', 'source_key' => 'tanggal_surat_panjang', 'is_required' => true, 'description' => 'Tanggal final surat.'],
                    ['placeholder_key' => 'penanda_tangan_jabatan', 'label' => 'Jabatan Penanda Tangan', 'source_type' => 'system', 'source_key' => 'penanda_tangan_jabatan', 'is_required' => true, 'default_value' => 'Dekan', 'description' => 'Jabatan pejabat penanda tangan.'],
                    ['placeholder_key' => 'penanda_tangan_nama', 'label' => 'Nama Penanda Tangan', 'source_type' => 'system', 'source_key' => 'penanda_tangan_nama', 'is_required' => true, 'description' => 'Nama pejabat penanda tangan.'],
                    ['placeholder_key' => 'penanda_tangan_nidn', 'label' => 'NIDN Penanda Tangan', 'source_type' => 'system', 'source_key' => 'penanda_tangan_nidn', 'is_required' => true, 'description' => 'NIDN pejabat penanda tangan.'],
                ],
            ],
            [
                'jenis_surat_slug' => 'surat-permohonan-observasi',
                'name' => 'Template Surat Permohonan Observasi',
                'slug' => 'template-surat-permohonan-observasi',
                'source_reference' => '[Template] Surat Permohonan Observasi.docx',
                'subject' => 'Surat Permohonan Observasi',
                'template_body' => <<<'HTML'
<section class="surat surat-permohonan-observasi">
    <h1 style="text-align:center;">SURAT PERMOHONAN OBSERVASI</h1>
    <p style="text-align:center;">Nomor: {{nomor_surat}}</p>

    <p>Kepada Yth. Pimpinan {{instansi_tujuan}}</p>
    <p>di {{alamat_instansi}}</p>

    <p>
        Dengan hormat, kami mengajukan permohonan observasi untuk mahasiswa berikut:
    </p>
    <p>Nama: {{nama_pemohon}}</p>
    <p>NIM: {{nim}}</p>
    <p>Program Studi: {{program_studi}}</p>

    <p>
        Adapun topik observasi yang diajukan adalah {{topik_observasi}},
        yang direncanakan berlangsung pada {{tanggal_mulai_panjang}} sampai
        dengan {{tanggal_selesai_panjang}}.
    </p>

    <p>
        Demikian permohonan ini kami sampaikan. Atas perhatian dan kerja samanya,
        kami ucapkan terima kasih.
    </p>

    <p>{{kota_surat}}, {{tanggal_surat_panjang}}</p>
    <p><strong>{{penanda_tangan_jabatan}}</strong></p>
    <br><br>
    <p><strong>{{penanda_tangan_nama}}</strong></p>
    <p>NIDN. {{penanda_tangan_nidn}}</p>
</section>
HTML,
                'placeholders' => [
                    ['placeholder_key' => 'nomor_surat', 'label' => 'Nomor Surat', 'source_type' => 'surat', 'source_key' => 'nomor_surat', 'is_required' => true, 'description' => 'Nomor surat final.'],
                    ['placeholder_key' => 'instansi_tujuan', 'label' => 'Instansi Tujuan', 'source_type' => 'surat_data', 'source_key' => 'instansi_tujuan', 'is_required' => true, 'description' => 'Nama instansi tujuan observasi.'],
                    ['placeholder_key' => 'alamat_instansi', 'label' => 'Alamat Instansi', 'source_type' => 'surat_data', 'source_key' => 'alamat_instansi', 'is_required' => true, 'description' => 'Alamat lengkap instansi tujuan.'],
                    ['placeholder_key' => 'nama_pemohon', 'label' => 'Nama Pemohon', 'source_type' => 'user', 'source_key' => 'name', 'is_required' => true, 'description' => 'Nama mahasiswa pemohon.'],
                    ['placeholder_key' => 'nim', 'label' => 'NIM', 'source_type' => 'surat_data', 'source_key' => 'nim', 'is_required' => true, 'description' => 'NIM mahasiswa.'],
                    ['placeholder_key' => 'program_studi', 'label' => 'Program Studi', 'source_type' => 'surat_data', 'source_key' => 'program_studi', 'is_required' => true, 'description' => 'Program studi mahasiswa.'],
                    ['placeholder_key' => 'topik_observasi', 'label' => 'Topik Observasi', 'source_type' => 'surat_data', 'source_key' => 'topik_observasi', 'is_required' => true, 'description' => 'Topik atau fokus observasi.'],
                    ['placeholder_key' => 'tanggal_mulai_panjang', 'label' => 'Tanggal Mulai Panjang', 'source_type' => 'computed', 'source_key' => 'tanggal_mulai_panjang', 'is_required' => true, 'description' => 'Tanggal mulai observasi format panjang.'],
                    ['placeholder_key' => 'tanggal_selesai_panjang', 'label' => 'Tanggal Selesai Panjang', 'source_type' => 'computed', 'source_key' => 'tanggal_selesai_panjang', 'is_required' => true, 'description' => 'Tanggal selesai observasi format panjang.'],
                    ['placeholder_key' => 'kota_surat', 'label' => 'Kota Surat', 'source_type' => 'system', 'source_key' => 'kota_surat', 'is_required' => true, 'default_value' => 'Cilacap', 'description' => 'Kota penerbitan surat.'],
                    ['placeholder_key' => 'tanggal_surat_panjang', 'label' => 'Tanggal Surat Panjang', 'source_type' => 'computed', 'source_key' => 'tanggal_surat_panjang', 'is_required' => true, 'description' => 'Tanggal final surat.'],
                    ['placeholder_key' => 'penanda_tangan_jabatan', 'label' => 'Jabatan Penanda Tangan', 'source_type' => 'system', 'source_key' => 'penanda_tangan_jabatan', 'is_required' => true, 'default_value' => 'Dekan', 'description' => 'Jabatan penanda tangan.'],
                    ['placeholder_key' => 'penanda_tangan_nama', 'label' => 'Nama Penanda Tangan', 'source_type' => 'system', 'source_key' => 'penanda_tangan_nama', 'is_required' => true, 'description' => 'Nama pejabat penanda tangan.'],
                    ['placeholder_key' => 'penanda_tangan_nidn', 'label' => 'NIDN Penanda Tangan', 'source_type' => 'system', 'source_key' => 'penanda_tangan_nidn', 'is_required' => true, 'description' => 'NIDN pejabat penanda tangan.'],
                ],
            ],
            [
                'jenis_surat_slug' => 'permohonan-dispensasi-mahasiswa',
                'name' => 'Template Permohonan Dispensasi Mahasiswa',
                'slug' => 'template-permohonan-dispensasi-mahasiswa',
                'source_reference' => '142. permohonan dispensasi Mhs.docx',
                'subject' => 'Permohonan Dispensasi Mahasiswa',
                'template_body' => <<<'HTML'
<section class="surat surat-permohonan-dispensasi">
    <h1 style="text-align:center;">SURAT PERMOHONAN DISPENSASI</h1>
    <p style="text-align:center;">Nomor: {{nomor_surat}}</p>

    <p>Yang bertanda tangan di bawah ini:</p>
    <p>Nama: {{nama_pemohon}}</p>
    <p>NIM: {{nim}}</p>
    <p>Program Studi: {{program_studi}}</p>

    <p>
        Mengajukan permohonan dispensasi untuk mata kuliah {{mata_kuliah}}
        {{kelas_info}} pada tanggal {{tanggal_kegiatan_panjang}}
        {{dosen_pengampu_info}}, dengan alasan {{alasan_dispensasi}}.
    </p>

    <p>
        Demikian surat permohonan ini saya sampaikan. Atas perhatian dan kebijaksanaannya
        saya ucapkan terima kasih.
    </p>

    <p>{{kota_surat}}, {{tanggal_surat_panjang}}</p>
    <p>Pemohon,</p>
    <br><br>
    <p><strong>{{nama_pemohon}}</strong></p>
    <p>NIM. {{nim}}</p>
</section>
HTML,
                'placeholders' => [
                    ['placeholder_key' => 'nomor_surat', 'label' => 'Nomor Surat', 'source_type' => 'surat', 'source_key' => 'nomor_surat', 'is_required' => true, 'description' => 'Nomor surat final.'],
                    ['placeholder_key' => 'nama_pemohon', 'label' => 'Nama Pemohon', 'source_type' => 'user', 'source_key' => 'name', 'is_required' => true, 'description' => 'Nama mahasiswa pemohon.'],
                    ['placeholder_key' => 'nim', 'label' => 'NIM', 'source_type' => 'surat_data', 'source_key' => 'nim', 'is_required' => true, 'description' => 'NIM mahasiswa.'],
                    ['placeholder_key' => 'program_studi', 'label' => 'Program Studi', 'source_type' => 'surat_data', 'source_key' => 'program_studi', 'is_required' => true, 'description' => 'Program studi mahasiswa.'],
                    ['placeholder_key' => 'mata_kuliah', 'label' => 'Mata Kuliah', 'source_type' => 'surat_data', 'source_key' => 'mata_kuliah', 'is_required' => true, 'description' => 'Mata kuliah yang didispensasi.'],
                    ['placeholder_key' => 'kelas_info', 'label' => 'Informasi Kelas', 'source_type' => 'computed', 'source_key' => 'kelas_info', 'is_required' => false, 'description' => 'Potongan teks kelas jika data kelas tersedia.'],
                    ['placeholder_key' => 'tanggal_kegiatan_panjang', 'label' => 'Tanggal Kegiatan Panjang', 'source_type' => 'computed', 'source_key' => 'tanggal_kegiatan_panjang', 'is_required' => true, 'description' => 'Tanggal kegiatan/dispen format panjang.'],
                    ['placeholder_key' => 'dosen_pengampu_info', 'label' => 'Informasi Dosen Pengampu', 'source_type' => 'computed', 'source_key' => 'dosen_pengampu_info', 'is_required' => false, 'description' => 'Potongan teks dosen pengampu jika tersedia.'],
                    ['placeholder_key' => 'alasan_dispensasi', 'label' => 'Alasan Dispensasi', 'source_type' => 'surat_data', 'source_key' => 'alasan_dispensasi', 'is_required' => true, 'description' => 'Alasan pengajuan dispensasi.'],
                    ['placeholder_key' => 'kota_surat', 'label' => 'Kota Surat', 'source_type' => 'system', 'source_key' => 'kota_surat', 'is_required' => true, 'default_value' => 'Cilacap', 'description' => 'Kota penerbitan surat.'],
                    ['placeholder_key' => 'tanggal_surat_panjang', 'label' => 'Tanggal Surat Panjang', 'source_type' => 'computed', 'source_key' => 'tanggal_surat_panjang', 'is_required' => true, 'description' => 'Tanggal final surat.'],
                ],
            ],
            [
                'jenis_surat_slug' => 'permohonan-pindah-kelas',
                'name' => 'Template Permohonan Pindah Kelas',
                'slug' => 'template-permohonan-pindah-kelas',
                'source_reference' => 'permohonan  pindah kelas.docx',
                'subject' => 'Permohonan Pindah Kelas',
                'template_body' => <<<'HTML'
<section class="surat surat-permohonan-pindah-kelas">
    <h1 style="text-align:center;">SURAT PERMOHONAN PINDAH KELAS</h1>
    <p style="text-align:center;">Nomor: {{nomor_surat}}</p>

    <p>Yang bertanda tangan di bawah ini:</p>
    <p>Nama: {{nama_pemohon}}</p>
    <p>NIM: {{nim}}</p>
    <p>Program Studi: {{program_studi}}</p>

    <p>
        Mengajukan permohonan pindah kelas untuk mata kuliah {{mata_kuliah}}
        dari kelas {{kelas_asal}} ke kelas {{kelas_tujuan}} dengan alasan
        {{alasan_pindah}}.
    </p>

    <p>
        Demikian permohonan ini saya ajukan. Atas perhatian dan persetujuannya,
        saya ucapkan terima kasih.
    </p>

    <p>{{kota_surat}}, {{tanggal_surat_panjang}}</p>
    <p>Pemohon,</p>
    <br><br>
    <p><strong>{{nama_pemohon}}</strong></p>
    <p>NIM. {{nim}}</p>
</section>
HTML,
                'placeholders' => [
                    ['placeholder_key' => 'nomor_surat', 'label' => 'Nomor Surat', 'source_type' => 'surat', 'source_key' => 'nomor_surat', 'is_required' => true, 'description' => 'Nomor surat final.'],
                    ['placeholder_key' => 'nama_pemohon', 'label' => 'Nama Pemohon', 'source_type' => 'user', 'source_key' => 'name', 'is_required' => true, 'description' => 'Nama mahasiswa pemohon.'],
                    ['placeholder_key' => 'nim', 'label' => 'NIM', 'source_type' => 'surat_data', 'source_key' => 'nim', 'is_required' => true, 'description' => 'NIM mahasiswa.'],
                    ['placeholder_key' => 'program_studi', 'label' => 'Program Studi', 'source_type' => 'surat_data', 'source_key' => 'program_studi', 'is_required' => true, 'description' => 'Program studi mahasiswa.'],
                    ['placeholder_key' => 'mata_kuliah', 'label' => 'Mata Kuliah', 'source_type' => 'surat_data', 'source_key' => 'mata_kuliah', 'is_required' => true, 'description' => 'Mata kuliah yang dimohonkan pindah kelas.'],
                    ['placeholder_key' => 'kelas_asal', 'label' => 'Kelas Asal', 'source_type' => 'surat_data', 'source_key' => 'kelas_asal', 'is_required' => true, 'description' => 'Kelas asal mahasiswa.'],
                    ['placeholder_key' => 'kelas_tujuan', 'label' => 'Kelas Tujuan', 'source_type' => 'surat_data', 'source_key' => 'kelas_tujuan', 'is_required' => true, 'description' => 'Kelas tujuan mahasiswa.'],
                    ['placeholder_key' => 'alasan_pindah', 'label' => 'Alasan Pindah', 'source_type' => 'surat_data', 'source_key' => 'alasan_pindah', 'is_required' => true, 'description' => 'Alasan perpindahan kelas.'],
                    ['placeholder_key' => 'kota_surat', 'label' => 'Kota Surat', 'source_type' => 'system', 'source_key' => 'kota_surat', 'is_required' => true, 'default_value' => 'Cilacap', 'description' => 'Kota penerbitan surat.'],
                    ['placeholder_key' => 'tanggal_surat_panjang', 'label' => 'Tanggal Surat Panjang', 'source_type' => 'computed', 'source_key' => 'tanggal_surat_panjang', 'is_required' => true, 'description' => 'Tanggal final surat.'],
                ],
            ],
            [
                'jenis_surat_slug' => 'permohonan-menjadi-penguji-sidang',
                'name' => 'Template Permohonan Menjadi Penguji Sidang',
                'slug' => 'template-permohonan-menjadi-penguji-sidang',
                'source_reference' => 'PERMOHONAN MENJADI PENGUJI SIDANG (fix) (1).docx',
                'subject' => 'Permohonan Menjadi Penguji Sidang',
                'template_body' => <<<'HTML'
<section class="surat surat-penguji-sidang">
    <h1 style="text-align:center;">SURAT TUGAS / PERMOHONAN MENJADI PENGUJI SIDANG</h1>
    <p style="text-align:center;">Nomor: {{nomor_surat}}</p>

    <p>Yang bertanda tangan di bawah ini menugaskan / memohonkan kepada:</p>
    <p>Nama Dosen: {{nama_dosen}}</p>
    <p>NIP / NIDN: {{nip}}</p>

    <p>
        Untuk menjadi penguji sidang mahasiswa {{nama_mahasiswa}}
        (NIM {{nim_mahasiswa}}) dengan judul tugas akhir:
    </p>
    <p><em>{{judul_skripsi}}</em></p>

    <p>
        Sidang dijadwalkan pada {{tanggal_sidang_panjang}}{{ruang_sidang_info}}.
    </p>

    <p>
        Demikian surat ini dibuat untuk dilaksanakan sebagaimana mestinya.
    </p>

    <p>{{kota_surat}}, {{tanggal_surat_panjang}}</p>
    <p><strong>{{penanda_tangan_jabatan}}</strong></p>
    <br><br>
    <p><strong>{{penanda_tangan_nama}}</strong></p>
    <p>NIDN. {{penanda_tangan_nidn}}</p>
</section>
HTML,
                'placeholders' => [
                    ['placeholder_key' => 'nomor_surat', 'label' => 'Nomor Surat', 'source_type' => 'surat', 'source_key' => 'nomor_surat', 'is_required' => true, 'description' => 'Nomor surat final.'],
                    ['placeholder_key' => 'nama_dosen', 'label' => 'Nama Dosen', 'source_type' => 'surat_data', 'source_key' => 'nama_dosen', 'is_required' => true, 'description' => 'Nama dosen penguji yang diajukan.'],
                    ['placeholder_key' => 'nip', 'label' => 'NIP / NIDN', 'source_type' => 'surat_data', 'source_key' => 'nip', 'is_required' => true, 'description' => 'NIP atau NIDN dosen.'],
                    ['placeholder_key' => 'nama_mahasiswa', 'label' => 'Nama Mahasiswa', 'source_type' => 'surat_data', 'source_key' => 'nama_mahasiswa', 'is_required' => true, 'description' => 'Nama mahasiswa yang akan sidang.'],
                    ['placeholder_key' => 'nim_mahasiswa', 'label' => 'NIM Mahasiswa', 'source_type' => 'surat_data', 'source_key' => 'nim_mahasiswa', 'is_required' => true, 'description' => 'NIM mahasiswa yang akan sidang.'],
                    ['placeholder_key' => 'judul_skripsi', 'label' => 'Judul Skripsi', 'source_type' => 'surat_data', 'source_key' => 'judul_skripsi', 'is_required' => true, 'description' => 'Judul skripsi atau tugas akhir.'],
                    ['placeholder_key' => 'tanggal_sidang_panjang', 'label' => 'Tanggal Sidang Panjang', 'source_type' => 'computed', 'source_key' => 'tanggal_sidang_panjang', 'is_required' => true, 'description' => 'Tanggal sidang dalam format panjang.'],
                    ['placeholder_key' => 'ruang_sidang_info', 'label' => 'Informasi Ruang Sidang', 'source_type' => 'computed', 'source_key' => 'ruang_sidang_info', 'is_required' => false, 'description' => 'Potongan teks ruang sidang jika tersedia.'],
                    ['placeholder_key' => 'kota_surat', 'label' => 'Kota Surat', 'source_type' => 'system', 'source_key' => 'kota_surat', 'is_required' => true, 'default_value' => 'Cilacap', 'description' => 'Kota penerbitan surat.'],
                    ['placeholder_key' => 'tanggal_surat_panjang', 'label' => 'Tanggal Surat Panjang', 'source_type' => 'computed', 'source_key' => 'tanggal_surat_panjang', 'is_required' => true, 'description' => 'Tanggal final surat.'],
                    ['placeholder_key' => 'penanda_tangan_jabatan', 'label' => 'Jabatan Penanda Tangan', 'source_type' => 'system', 'source_key' => 'penanda_tangan_jabatan', 'is_required' => true, 'default_value' => 'Dekan', 'description' => 'Jabatan penanda tangan.'],
                    ['placeholder_key' => 'penanda_tangan_nama', 'label' => 'Nama Penanda Tangan', 'source_type' => 'system', 'source_key' => 'penanda_tangan_nama', 'is_required' => true, 'description' => 'Nama pejabat penanda tangan.'],
                    ['placeholder_key' => 'penanda_tangan_nidn', 'label' => 'NIDN Penanda Tangan', 'source_type' => 'system', 'source_key' => 'penanda_tangan_nidn', 'is_required' => true, 'description' => 'NIDN pejabat penanda tangan.'],
                ],
            ],
        ];

        foreach ($templates as $templateData) {
            $jenisSurat = JenisSurat::query()->where('slug', $templateData['jenis_surat_slug'])->first();

            if ($jenisSurat === null) {
                continue;
            }

            $template = SuratTemplate::query()->updateOrCreate(
                ['slug' => $templateData['slug']],
                [
                    'jenis_surat_id' => $jenisSurat->id,
                    'name' => $templateData['name'],
                    'slug' => $templateData['slug'],
                    'format' => 'html',
                    'source_reference' => $templateData['source_reference'],
                    'subject' => $templateData['subject'],
                    'template_header' => $templateData['template_header'] ?? null,
                    'template_body' => $templateData['template_body'],
                    'template_footer' => $templateData['template_footer'] ?? null,
                    'version' => 1,
                    'is_active' => true,
                ],
            );

            foreach ($templateData['placeholders'] as $placeholder) {
                $template->placeholders()->updateOrCreate(
                    ['placeholder_key' => $placeholder['placeholder_key']],
                    $placeholder,
                );
            }
        }
    }
}
