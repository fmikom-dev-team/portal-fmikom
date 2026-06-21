<?php

namespace Database\Seeders;

use App\Models\SuratCategory;
use Illuminate\Database\Seeder;

class SuratCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'nama' => 'Surat Permohonan',
                'slug' => 'surat-permohonan',
                'deskripsi' => 'Surat yang berisi permintaan resmi dari mahasiswa atau dosen.',
                'urutan' => 1,
                'is_active' => true,
            ],
            [
                'nama' => 'Surat Keterangan',
                'slug' => 'surat-keterangan',
                'deskripsi' => 'Surat yang menerangkan status atau kondisi akademik.',
                'urutan' => 2,
                'is_active' => true,
            ],
            [
                'nama' => 'Surat Tugas',
                'slug' => 'surat-tugas',
                'deskripsi' => 'Surat penugasan resmi untuk dosen atau staf.',
                'urutan' => 3,
                'is_active' => true,
            ],
            [
                'nama' => 'Surat Undangan',
                'slug' => 'surat-undangan',
                'deskripsi' => 'Surat undangan rapat atau kegiatan resmi.',
                'urutan' => 4,
                'is_active' => true,
            ],
            [
                'nama' => 'Surat Pengantar',
                'slug' => 'surat-pengantar',
                'deskripsi' => 'Surat pengantar ke instansi atau pihak lain.',
                'urutan' => 5,
                'is_active' => true,
            ],
            [
                'nama' => 'Surat Rekomendasi',
                'slug' => 'surat-rekomendasi',
                'deskripsi' => 'Surat rekomendasi akademik atau non-akademik.',
                'urutan' => 6,
                'is_active' => true,
            ],
            [
                'nama' => 'Surat Keputusan',
                'slug' => 'surat-keputusan',
                'deskripsi' => 'Dokumen keputusan resmi lembaga.',
                'urutan' => 7,
                'is_active' => true,
            ],
            [
                'nama' => 'Surat Edaran',
                'slug' => 'surat-edaran',
                'deskripsi' => 'Penyampaian informasi resmi yang bersifat umum.',
                'urutan' => 8,
                'is_active' => true,
            ],
            [
                'nama' => 'Surat Pemberitahuan',
                'slug' => 'surat-pemberitahuan',
                'deskripsi' => 'Informasi resmi yang lebih spesifik kepada penerima.',
                'urutan' => 9,
                'is_active' => true,
            ],
            [
                'nama' => 'Memo / Nota Dinas',
                'slug' => 'memo-nota-dinas',
                'deskripsi' => 'Komunikasi internal formal untuk kebutuhan administratif.',
                'urutan' => 10,
                'is_active' => true,
            ],
            [
                'nama' => 'Pengumuman',
                'slug' => 'pengumuman',
                'deskripsi' => 'Informasi publik internal kampus.',
                'urutan' => 11,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            SuratCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                $category,
            );
        }
    }
}
