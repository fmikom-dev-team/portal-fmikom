<?php

namespace Database\Seeders;

use App\Models\JenisSurat;
use App\Models\Role;
use App\Models\SuratCategory;
use Illuminate\Database\Seeder;

class JenisSuratSeeder extends Seeder
{
    public function run(): void
    {
        $deprecatedJenisSuratNames = [
            'Surat Cuti Akademik',
            'Surat Keterangan Mahasiswa Aktif',
            'Surat Pengantar Riset / Skripsi',
            'Surat Rekomendasi Akademik',
        ];

        JenisSurat::query()
            ->whereIn('nama', $deprecatedJenisSuratNames)
            ->delete();

        $mahasiswaRoleId = Role::query()->where('slug', 'mahasiswa')->value('id');
        $dosenRoleId = Role::query()->where('slug', 'dosen')->value('id');
        $kaprodiRoleId = Role::query()->where('slug', 'kaprodi')->value('id');
        $dekanRoleId = Role::query()->where('slug', 'dekan')->value('id');
        $suratPermohonanCategoryId = SuratCategory::query()->where('slug', 'surat-permohonan')->value('id');
        $suratKeteranganCategoryId = SuratCategory::query()->where('slug', 'surat-keterangan')->value('id');
        $suratTugasCategoryId = SuratCategory::query()->where('slug', 'surat-tugas')->value('id');
        $suratUndanganCategoryId = SuratCategory::query()->where('slug', 'surat-undangan')->value('id');
        $memoNotaDinasCategoryId = SuratCategory::query()->where('slug', 'memo-nota-dinas')->value('id');
        $suratPemberitahuanCategoryId = SuratCategory::query()->where('slug', 'surat-pemberitahuan')->value('id');

        $jenisSurats = [
            [
                'category_id' => $suratPermohonanCategoryId,
                'nama' => 'Permohonan Cuti Mahasiswa',
                'slug' => 'permohonan-cuti-mahasiswa',
                'kode_surat' => 'CUTI-MHS',
                'deskripsi' => 'Pengajuan cuti akademik mahasiswa pada semester berjalan.',
                'allowed_role_id' => $mahasiswaRoleId,
                'approval_role_id' => $kaprodiRoleId,
                'perlu_approval' => true,
                'alur_pengajuan' => 'submission',
                'is_active' => true,
                'field_config' => [
                    [
                        'name' => 'nim',
                        'label' => 'NIM',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Contoh: 202231001',
                    ],
                    [
                        'name' => 'semester',
                        'label' => 'Semester Saat Ini',
                        'type' => 'number',
                        'required' => true,
                        'placeholder' => 'Contoh: 4',
                    ],
                    [
                        'name' => 'program_studi',
                        'label' => 'Program Studi',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Contoh: Informatika',
                    ],
                    [
                        'name' => 'tahun_akademik',
                        'label' => 'Tahun Akademik',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Contoh: 2026/2027',
                    ],
                    [
                        'name' => 'semester_pengajuan',
                        'label' => 'Semester Pengajuan',
                        'type' => 'select',
                        'required' => true,
                        'options' => ['Ganjil', 'Genap'],
                    ],
                    [
                        'name' => 'alasan_cuti',
                        'label' => 'Alasan Cuti',
                        'type' => 'textarea',
                        'required' => true,
                        'placeholder' => 'Jelaskan alasan mengajukan cuti',
                    ],
                ],
            ],
            [
                'category_id' => $suratKeteranganCategoryId,
                'nama' => 'Surat Keterangan Lulus',
                'slug' => 'surat-keterangan-lulus',
                'kode_surat' => 'SKL',
                'deskripsi' => 'Surat keterangan sementara bagi mahasiswa yang telah dinyatakan lulus.',
                'allowed_role_id' => $mahasiswaRoleId,
                'approval_role_id' => $dekanRoleId,
                'perlu_approval' => true,
                'alur_pengajuan' => 'submission',
                'is_active' => true,
                'field_config' => [
                    [
                        'name' => 'nim',
                        'label' => 'NIM',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Contoh: 202231001',
                    ],
                    [
                        'name' => 'program_studi',
                        'label' => 'Program Studi',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Contoh: Sistem Informasi',
                    ],
                    [
                        'name' => 'akreditasi',
                        'label' => 'Akreditasi',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Contoh: A',
                    ],
                    [
                        'name' => 'tanggal_yudisium',
                        'label' => 'Tanggal Yudisium',
                        'type' => 'date',
                        'required' => true,
                    ],
                    [
                        'name' => 'judul_tugas_akhir',
                        'label' => 'Judul Tugas Akhir',
                        'type' => 'textarea',
                        'required' => false,
                        'placeholder' => 'Opsional jika diperlukan pada surat',
                    ],
                ],
            ],
            [
                'category_id' => $suratPermohonanCategoryId,
                'nama' => 'Surat Permohonan Observasi',
                'slug' => 'surat-permohonan-observasi',
                'kode_surat' => 'OBSERVASI',
                'deskripsi' => 'Surat pengantar untuk observasi atau pengambilan data ke instansi tujuan.',
                'allowed_role_id' => $mahasiswaRoleId,
                'approval_role_id' => $dekanRoleId,
                'perlu_approval' => true,
                'alur_pengajuan' => 'submission',
                'is_active' => true,
                'field_config' => [
                    [
                        'name' => 'nim',
                        'label' => 'NIM',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Contoh: 202231001',
                    ],
                    [
                        'name' => 'program_studi',
                        'label' => 'Program Studi',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Contoh: Informatika',
                    ],
                    [
                        'name' => 'instansi_tujuan',
                        'label' => 'Instansi Tujuan',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Contoh: Dinas Kominfo Kabupaten Cilacap',
                    ],
                    [
                        'name' => 'alamat_instansi',
                        'label' => 'Alamat Instansi',
                        'type' => 'textarea',
                        'required' => true,
                        'placeholder' => 'Alamat lengkap instansi tujuan',
                    ],
                    [
                        'name' => 'topik_observasi',
                        'label' => 'Topik Observasi',
                        'type' => 'textarea',
                        'required' => true,
                        'placeholder' => 'Jelaskan topik atau fokus observasi',
                    ],
                    [
                        'name' => 'tanggal_mulai',
                        'label' => 'Tanggal Mulai Observasi',
                        'type' => 'date',
                        'required' => true,
                    ],
                    [
                        'name' => 'tanggal_selesai',
                        'label' => 'Tanggal Selesai Observasi',
                        'type' => 'date',
                        'required' => true,
                    ],
                ],
            ],
            [
                'category_id' => $suratPermohonanCategoryId,
                'nama' => 'Permohonan Dispensasi Mahasiswa',
                'slug' => 'permohonan-dispensasi-mahasiswa',
                'kode_surat' => 'DISPENSASI-MHS',
                'deskripsi' => 'Surat permohonan dispensasi mahasiswa karena kegiatan atau kondisi tertentu.',
                'allowed_role_id' => $mahasiswaRoleId,
                'approval_role_id' => $kaprodiRoleId,
                'perlu_approval' => true,
                'alur_pengajuan' => 'submission',
                'is_active' => true,
                'field_config' => [
                    [
                        'name' => 'nim',
                        'label' => 'NIM',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Contoh: 202231001',
                    ],
                    [
                        'name' => 'program_studi',
                        'label' => 'Program Studi',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Contoh: Informatika',
                    ],
                    [
                        'name' => 'mata_kuliah',
                        'label' => 'Mata Kuliah',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Contoh: Basis Data',
                    ],
                    [
                        'name' => 'kelas',
                        'label' => 'Kelas',
                        'type' => 'text',
                        'required' => false,
                        'placeholder' => 'Contoh: IF-4A',
                    ],
                    [
                        'name' => 'dosen_pengampu',
                        'label' => 'Dosen Pengampu',
                        'type' => 'text',
                        'required' => false,
                        'placeholder' => 'Nama dosen pengampu',
                    ],
                    [
                        'name' => 'tanggal_kegiatan',
                        'label' => 'Tanggal Kegiatan / Dispensasi',
                        'type' => 'date',
                        'required' => true,
                    ],
                    [
                        'name' => 'alasan_dispensasi',
                        'label' => 'Alasan Dispensasi',
                        'type' => 'textarea',
                        'required' => true,
                        'placeholder' => 'Jelaskan alasan permohonan dispensasi',
                    ],
                ],
            ],
            [
                'category_id' => $suratPermohonanCategoryId,
                'nama' => 'Permohonan Pindah Kelas',
                'slug' => 'permohonan-pindah-kelas',
                'kode_surat' => 'PINDAH-KELAS',
                'deskripsi' => 'Surat permohonan perpindahan kelas pada mata kuliah tertentu.',
                'allowed_role_id' => $mahasiswaRoleId,
                'approval_role_id' => $kaprodiRoleId,
                'perlu_approval' => true,
                'alur_pengajuan' => 'submission',
                'is_active' => true,
                'field_config' => [
                    [
                        'name' => 'nim',
                        'label' => 'NIM',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Contoh: 202231001',
                    ],
                    [
                        'name' => 'program_studi',
                        'label' => 'Program Studi',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Contoh: Sistem Informasi',
                    ],
                    [
                        'name' => 'mata_kuliah',
                        'label' => 'Mata Kuliah',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Contoh: Pemrograman Web',
                    ],
                    [
                        'name' => 'kelas_asal',
                        'label' => 'Kelas Asal',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Contoh: SI-2A',
                    ],
                    [
                        'name' => 'kelas_tujuan',
                        'label' => 'Kelas Tujuan',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Contoh: SI-2B',
                    ],
                    [
                        'name' => 'alasan_pindah',
                        'label' => 'Alasan Pindah Kelas',
                        'type' => 'textarea',
                        'required' => true,
                        'placeholder' => 'Jelaskan alasan perpindahan kelas',
                    ],
                ],
            ],
            [
                'category_id' => $suratTugasCategoryId,
                'nama' => 'Permohonan Menjadi Penguji Sidang',
                'slug' => 'permohonan-menjadi-penguji-sidang',
                'kode_surat' => 'PENGUJI-SIDANG',
                'deskripsi' => 'Surat penugasan atau permohonan bagi dosen untuk menjadi penguji sidang.',
                'allowed_role_id' => $dosenRoleId,
                'approval_role_id' => $dekanRoleId,
                'perlu_approval' => true,
                'alur_pengajuan' => 'submission',
                'is_active' => true,
                'field_config' => [
                    [
                        'name' => 'nip',
                        'label' => 'NIP / NIDN',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Contoh: 198812312020121001',
                    ],
                    [
                        'name' => 'nama_dosen',
                        'label' => 'Nama Dosen',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Nama dosen yang diajukan',
                    ],
                    [
                        'name' => 'nama_mahasiswa',
                        'label' => 'Nama Mahasiswa',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Nama mahasiswa yang akan sidang',
                    ],
                    [
                        'name' => 'nim_mahasiswa',
                        'label' => 'NIM Mahasiswa',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Contoh: 202231001',
                    ],
                    [
                        'name' => 'judul_skripsi',
                        'label' => 'Judul Skripsi / Tugas Akhir',
                        'type' => 'textarea',
                        'required' => true,
                        'placeholder' => 'Masukkan judul skripsi atau tugas akhir',
                    ],
                    [
                        'name' => 'tanggal_sidang',
                        'label' => 'Tanggal Sidang',
                        'type' => 'date',
                        'required' => true,
                    ],
                    [
                        'name' => 'ruang_sidang',
                        'label' => 'Ruang Sidang',
                        'type' => 'text',
                        'required' => false,
                        'placeholder' => 'Contoh: Ruang Sidang 1',
                    ],
                ],
            ],
        ];

        foreach ($jenisSurats as $jenisSurat) {
            JenisSurat::query()->updateOrCreate(
                ['nama' => $jenisSurat['nama']],
                $jenisSurat,
            );
        }
    }
}
