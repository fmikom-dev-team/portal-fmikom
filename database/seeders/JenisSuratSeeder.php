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
        $roleIds = Role::query()
            ->whereIn('slug', ['mahasiswa', 'dosen', 'kaprodi', 'dekan'])
            ->pluck('id', 'slug')
            ->all();

        $categoryIds = SuratCategory::query()
            ->pluck('id', 'slug')
            ->all();

        foreach ($this->jenisSurats($categoryIds, $roleIds) as $jenisSurat) {
            $model = JenisSurat::withTrashed()->firstOrNew([
                'slug' => $jenisSurat['slug'],
            ]);

            $model->fill($jenisSurat);

            if ($model->trashed()) {
                $model->restore();
            }

            $model->save();
        }
    }

    /**
     * @param  array<string, int>  $categoryIds
     * @param  array<string, int>  $roleIds
     * @return array<int, array<string, mixed>>
     */
    protected function jenisSurats(array $categoryIds, array $roleIds): array
    {
        return [
            [
                'category_id' => $categoryIds['surat-permohonan'] ?? null,
                'nama' => 'Permohonan Cuti Mahasiswa',
                'slug' => 'permohonan-cuti-mahasiswa',
                'kode_surat' => 'CUTI-MHS',
                'deskripsi' => 'Pengajuan cuti akademik mahasiswa pada semester berjalan.',
                'allowed_role_id' => $roleIds['mahasiswa'] ?? null,
                'approval_role_id' => $roleIds['kaprodi'] ?? null,
                'perlu_approval' => true,
                'alur_pengajuan' => 'submission',
                'letter_mode' => 'personal',
                'is_active' => true,
                'field_config' => [
                    $this->field('nim', 'NIM'),
                    $this->field('semester', 'Semester Saat Ini', 'number'),
                    $this->field('program_studi', 'Program Studi'),
                    $this->field('tahun_akademik', 'Tahun Akademik'),
                    $this->field('semester_pengajuan', 'Semester Pengajuan', 'select', true, '', '', ['Ganjil', 'Genap']),
                    $this->field('alasan_cuti', 'Alasan Cuti', 'textarea'),
                ],
            ],
            [
                'category_id' => $categoryIds['surat-keterangan'] ?? null,
                'nama' => 'Surat Keterangan Lulus',
                'slug' => 'surat-keterangan-lulus',
                'kode_surat' => 'SKL',
                'deskripsi' => 'Surat keterangan bagi mahasiswa yang telah dinyatakan lulus.',
                'allowed_role_id' => $roleIds['mahasiswa'] ?? null,
                'approval_role_id' => $roleIds['dekan'] ?? null,
                'perlu_approval' => true,
                'alur_pengajuan' => 'submission',
                'letter_mode' => 'personal',
                'is_active' => true,
                'field_config' => [
                    $this->field('nama_mahasiswa', 'Nama Mahasiswa'),
                    $this->field('nim', 'NIM'),
                    $this->field('tempat_tanggal_lahir', 'Tempat/Tanggal Lahir'),
                    $this->field('tahun_masuk', 'Tahun Masuk', 'number'),
                    $this->field('jenjang', 'Jenjang', 'select', true, '', '', ['D3', 'D4', 'S1', 'S2', 'S3']),
                    $this->field('program_studi', 'Program Studi'),
                    $this->field('ipk_akhir', 'IPK Akhir', 'number'),
                    $this->field('judul_tugas_akhir', 'Judul Tugas Akhir', 'textarea', false),
                ],
            ],
            [
                'category_id' => $categoryIds['surat-permohonan'] ?? null,
                'nama' => 'Surat Permohonan Observasi',
                'slug' => 'surat-permohonan-observasi',
                'kode_surat' => 'OBSERVASI',
                'deskripsi' => 'Surat pengantar untuk observasi atau pengambilan data ke instansi tujuan.',
                'allowed_role_id' => $roleIds['mahasiswa'] ?? null,
                'approval_role_id' => $roleIds['dekan'] ?? null,
                'perlu_approval' => true,
                'alur_pengajuan' => 'submission',
                'letter_mode' => 'personal',
                'is_active' => true,
                'field_config' => [
                    $this->field('nim', 'NIM'),
                    $this->field('program_studi', 'Program Studi'),
                    $this->field('instansi_tujuan', 'Instansi Tujuan'),
                    $this->field('alamat_instansi', 'Alamat Instansi', 'textarea'),
                    $this->field('topik_observasi', 'Topik Observasi', 'textarea'),
                    $this->field('tanggal_mulai', 'Tanggal Mulai Observasi', 'date'),
                    $this->field('tanggal_selesai', 'Tanggal Selesai Observasi', 'date'),
                ],
            ],
            [
                'category_id' => $categoryIds['surat-permohonan'] ?? null,
                'nama' => 'Permohonan Dispensasi Mahasiswa',
                'slug' => 'permohonan-dispensasi-mahasiswa',
                'kode_surat' => 'DISPENSASI-MHS',
                'deskripsi' => 'Surat permohonan dispensasi mahasiswa karena kegiatan atau kondisi tertentu.',
                'allowed_role_id' => $roleIds['mahasiswa'] ?? null,
                'approval_role_id' => $roleIds['kaprodi'] ?? null,
                'perlu_approval' => true,
                'alur_pengajuan' => 'submission',
                'letter_mode' => 'personal',
                'is_active' => true,
                'field_config' => [
                    $this->field('nim', 'NIM'),
                    $this->field('program_studi', 'Program Studi'),
                    $this->field('mata_kuliah', 'Mata Kuliah'),
                    $this->field('kelas', 'Kelas', 'text', false),
                    $this->field('dosen_pengampu', 'Dosen Pengampu', 'text', false),
                    $this->field('tanggal_kegiatan', 'Tanggal Kegiatan / Dispensasi', 'date'),
                    $this->field('alasan_dispensasi', 'Alasan Dispensasi', 'textarea'),
                ],
            ],
            [
                'category_id' => $categoryIds['surat-permohonan'] ?? null,
                'nama' => 'Permohonan Pindah Kelas',
                'slug' => 'permohonan-pindah-kelas',
                'kode_surat' => 'PINDAH-KELAS',
                'deskripsi' => 'Surat permohonan perpindahan kelas pada mata kuliah tertentu.',
                'allowed_role_id' => $roleIds['mahasiswa'] ?? null,
                'approval_role_id' => $roleIds['kaprodi'] ?? null,
                'perlu_approval' => true,
                'alur_pengajuan' => 'submission',
                'letter_mode' => 'personal',
                'is_active' => true,
                'field_config' => [
                    $this->field('nim', 'NIM'),
                    $this->field('program_studi', 'Program Studi'),
                    $this->field('mata_kuliah', 'Mata Kuliah'),
                    $this->field('kelas_asal', 'Kelas Asal'),
                    $this->field('kelas_tujuan', 'Kelas Tujuan'),
                    $this->field('alasan_pindah', 'Alasan Pindah Kelas', 'textarea'),
                ],
            ],
            [
                'category_id' => $categoryIds['surat-tugas'] ?? null,
                'nama' => 'Permohonan Menjadi Penguji Sidang',
                'slug' => 'permohonan-menjadi-penguji-sidang',
                'kode_surat' => 'PENGUJI-SIDANG',
                'deskripsi' => 'Surat penugasan atau permohonan bagi dosen untuk menjadi penguji sidang.',
                'allowed_role_id' => $roleIds['dosen'] ?? null,
                'approval_role_id' => $roleIds['dekan'] ?? null,
                'perlu_approval' => true,
                'alur_pengajuan' => 'submission',
                'letter_mode' => 'personal',
                'is_active' => true,
                'field_config' => [
                    $this->field('nip', 'NIP / NIDN'),
                    $this->field('nama_dosen', 'Nama Dosen'),
                    $this->field('nama_mahasiswa', 'Nama Mahasiswa'),
                    $this->field('nim_mahasiswa', 'NIM Mahasiswa'),
                    $this->field('judul_skripsi', 'Judul Skripsi / Tugas Akhir', 'textarea'),
                    $this->field('tanggal_sidang', 'Tanggal Sidang', 'date'),
                    $this->field('ruang_sidang', 'Ruang Sidang', 'text', false),
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<string, mixed>
     */
    protected function field(
        string $name,
        string $label,
        string $type = 'text',
        bool $required = true,
        string $placeholder = '',
        string $help = '',
        array $options = []
    ): array {
        return [
            'name' => $name,
            'label' => $label,
            'type' => $type,
            'required' => $required,
            'placeholder' => $placeholder,
            'help' => $help,
            'options' => $options,
            'repeatable' => false,
            'add_label' => 'Tambah',
            'item_label' => 'Item',
        ];
    }
}
