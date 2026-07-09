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
            ->whereIn('slug', ['admin', 'mahasiswa', 'dosen', 'kaprodi', 'dekan'])
            ->pluck('id', 'slug')
            ->all();

        $categoryIds = SuratCategory::query()
            ->pluck('id', 'slug')
            ->all();

        foreach ($this->jenisSurats($categoryIds, $roleIds) as $jenisSurat) {
            $model = JenisSurat::withTrashed()
                ->where('slug', $jenisSurat['slug'])
                ->orWhere('nama', $jenisSurat['nama'])
                ->first() ?? new JenisSurat;

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
                'nama' => 'Surat Permohonan Cuti Mahasiswa',
                'slug' => 'surat-permohonan-cuti-mahasiswa-1782391599',
                'kode_surat' => null,
                'kode_klasifikasi' => 'KM.00.01',
                'deskripsi' => null,
                'allowed_role_id' => $roleIds['mahasiswa'] ?? null,
                'approval_role_id' => $roleIds['kaprodi'] ?? null,
                'perlu_approval' => true,
                'alur_pengajuan' => 'submission',
                'letter_mode' => 'personal',
                'is_active' => true,
                'field_config' => $this->jsonArray(<<<'JSON'
[
  {
    "help": "Diisi otomatis dari akun pemohon.",
    "name": "nama",
    "type": "text",
    "label": "Nama",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_pemohon",
    "editable_role": "mahasiswa",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "Diisi otomatis dari akun pemohon.",
    "name": "nim",
    "type": "text",
    "label": "NIM",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_pemohon",
    "editable_role": "mahasiswa",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "",
    "name": "semester",
    "type": "text",
    "label": "Semester",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_pemohon",
    "editable_role": "mahasiswa",
    "mode_form_pemohon": "editable"
  },
  {
    "help": "Diisi otomatis dari akun pemohon.",
    "name": "fak_prodi",
    "type": "text",
    "label": "Fak/Prodi",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_pemohon",
    "editable_role": "mahasiswa",
    "mode_form_pemohon": "editable"
  },
  {
    "help": "",
    "name": "alasan_cuti",
    "type": "text",
    "label": "Alasan Cuti",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_pemohon",
    "editable_role": "mahasiswa",
    "mode_form_pemohon": "editable"
  },
  {
    "help": "",
    "name": "kaprodi",
    "type": "text",
    "label": "Nama Kaprodi",
    "options": [],
    "required": false,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "",
    "name": "nik_kaprodi",
    "type": "text",
    "label": "NIK Kaprodi",
    "options": [],
    "required": false,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  }
]
JSON),
            ],
            [
                'category_id' => $categoryIds['surat-permohonan'] ?? null,
                'nama' => 'Surat Permohonan Dispensasi Mahasiswa',
                'slug' => 'surat-permohonan-dispensasi-mahasiswa-1782467416',
                'kode_surat' => null,
                'kode_klasifikasi' => 'DI',
                'deskripsi' => null,
                'allowed_role_id' => $roleIds['admin'] ?? null,
                'approval_role_id' => $roleIds['dekan'] ?? null,
                'perlu_approval' => true,
                'alur_pengajuan' => 'submission',
                'letter_mode' => 'personal',
                'is_active' => true,
                'field_config' => $this->jsonArray(<<<'JSON'
[
  {
    "help": "",
    "name": "text",
    "type": "text",
    "label": "Text",
    "options": [],
    "required": false,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "",
    "name": "hari_tanggal",
    "type": "text",
    "label": "Hari/Tanggal",
    "options": [],
    "required": false,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "",
    "name": "waktu",
    "type": "text",
    "label": "Waktu",
    "options": [],
    "required": false,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "",
    "name": "tempat",
    "type": "text",
    "label": "Tempat",
    "options": [],
    "required": false,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "",
    "name": "nama",
    "type": "text",
    "label": "Nama",
    "options": [],
    "required": false,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  }
]
JSON),
            ],
            [
                'category_id' => $categoryIds['surat-permohonan'] ?? null,
                'nama' => 'Surat Permohonan Observasi Mahasiswa',
                'slug' => 'surat-permohonan-observasi-mahasiswa-1782469453',
                'kode_surat' => null,
                'kode_klasifikasi' => 'PT',
                'deskripsi' => null,
                'allowed_role_id' => $roleIds['mahasiswa'] ?? null,
                'approval_role_id' => $roleIds['dekan'] ?? null,
                'perlu_approval' => true,
                'alur_pengajuan' => 'submission',
                'letter_mode' => 'personal',
                'is_active' => true,
                'field_config' => $this->jsonArray(<<<'JSON'
[
  {
    "help": "Diisi otomatis dari akun pemohon.",
    "name": "nama",
    "type": "text",
    "label": "Nama",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_pemohon",
    "editable_role": "mahasiswa",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "Diisi otomatis dari akun pemohon.",
    "name": "nim",
    "type": "text",
    "label": "NIM",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_pemohon",
    "editable_role": "mahasiswa",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "Diisi otomatis dari akun pemohon.",
    "name": "program_studi",
    "type": "text",
    "label": "Program Studi",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_pemohon",
    "editable_role": "mahasiswa",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "",
    "name": "judul_skripsi",
    "type": "text",
    "label": "Judul Skripsi",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_pemohon",
    "editable_role": "mahasiswa",
    "mode_form_pemohon": "editable"
  },
  {
    "help": "",
    "name": "nama_lembaga",
    "type": "text",
    "label": "Nama Lembaga",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_pemohon",
    "editable_role": "mahasiswa",
    "mode_form_pemohon": "editable"
  }
]
JSON),
            ],
            [
                'category_id' => $categoryIds['surat-permohonan'] ?? null,
                'nama' => 'Surat Permohonan menjadi Penguji Sidang',
                'slug' => 'surat-permohonan-menjadi-penguji-sidang-1782470954',
                'kode_surat' => null,
                'kode_klasifikasi' => 'KM',
                'deskripsi' => null,
                'allowed_role_id' => $roleIds['admin'] ?? null,
                'approval_role_id' => $roleIds['dekan'] ?? null,
                'perlu_approval' => true,
                'alur_pengajuan' => 'submission',
                'letter_mode' => 'institution',
                'is_active' => true,
                'field_config' => $this->jsonArray(<<<'JSON'
[
  {
    "help": "",
    "name": "hari_tanggal",
    "type": "text",
    "label": "Hari/tanggal",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "",
    "name": "waktu",
    "type": "text",
    "label": "Waktu",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "",
    "name": "tempat",
    "type": "text",
    "label": "Tempat",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  }
]
JSON),
            ],
            [
                'category_id' => $categoryIds['surat-keterangan'] ?? null,
                'nama' => 'Surat Keterangan Lulus Mahasiswa',
                'slug' => 'surat-keterangan-lulus-mahasiswa-1782471658',
                'kode_surat' => null,
                'kode_klasifikasi' => 'PK.05.00',
                'deskripsi' => null,
                'allowed_role_id' => $roleIds['mahasiswa'] ?? null,
                'approval_role_id' => $roleIds['dekan'] ?? null,
                'perlu_approval' => true,
                'alur_pengajuan' => 'submission',
                'letter_mode' => 'personal',
                'is_active' => true,
                'field_config' => $this->jsonArray(<<<'JSON'
[
  {
    "help": "Diisi otomatis dari akun pemohon.",
    "name": "nama",
    "type": "text",
    "label": "Nama",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_pemohon",
    "editable_role": "mahasiswa",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "Diisi otomatis dari akun pemohon.",
    "name": "nim",
    "type": "text",
    "label": "NIM",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_pemohon",
    "editable_role": "mahasiswa",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "Diisi otomatis dari akun pemohon.",
    "name": "program_studi",
    "type": "text",
    "label": "Program Studi",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_pemohon",
    "editable_role": "mahasiswa",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "Diisi otomatis dari akun pemohon.",
    "name": "fakultas",
    "type": "text",
    "label": "Fakultas",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_pemohon",
    "editable_role": "mahasiswa",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "",
    "name": "tempat_tanggal_lahir",
    "type": "text",
    "label": "Tempat, Tanggal Lahir",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_pemohon",
    "editable_role": "mahasiswa",
    "mode_form_pemohon": "editable"
  },
  {
    "help": "",
    "name": "tahun_masuk",
    "type": "text",
    "label": "Tahun Masuk",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_pemohon",
    "editable_role": "mahasiswa",
    "mode_form_pemohon": "editable"
  },
  {
    "help": "",
    "name": "jenjang",
    "type": "text",
    "label": "Jenjang",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_pemohon",
    "editable_role": "mahasiswa",
    "mode_form_pemohon": "editable"
  },
  {
    "help": "",
    "name": "akreditasi",
    "type": "text",
    "label": "Akreditasi",
    "options": [],
    "required": false,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "",
    "name": "nomor_sk_akreditasi",
    "type": "text",
    "label": "Nomor SK Akreditasi",
    "options": [],
    "required": false,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "",
    "name": "nomor_sk_yudisium",
    "type": "text",
    "label": "Nomor SK Yudisium",
    "options": [],
    "required": false,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "",
    "name": "tanggal_lulus",
    "type": "text",
    "label": "Tanggal Lulus",
    "options": [],
    "required": false,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  }
]
JSON),
            ],
            [
                'category_id' => $categoryIds['surat-undangan'] ?? null,
                'nama' => 'Surat Undangan Persiapan PKL FMIKOM',
                'slug' => 'yoyoy-1782530493',
                'kode_surat' => null,
                'kode_klasifikasi' => 'RT',
                'deskripsi' => null,
                'allowed_role_id' => $roleIds['admin'] ?? null,
                'approval_role_id' => null,
                'perlu_approval' => true,
                'alur_pengajuan' => 'submission',
                'letter_mode' => 'institution',
                'is_active' => true,
                'field_config' => $this->jsonArray(<<<'JSON'
[
  {
    "help": "",
    "name": "tahun_dilaksanakan",
    "type": "text",
    "label": "Tahun dilaksanakan",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "",
    "name": "hari_tanggal",
    "type": "text",
    "label": "Hari/Tanggal",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "",
    "name": "waktu",
    "type": "text",
    "label": "Waktu",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "",
    "name": "tempat",
    "type": "text",
    "label": "Tempat",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "",
    "name": "agenda",
    "type": "text",
    "label": "Agenda",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "",
    "name": "ketua_panitia",
    "type": "text",
    "label": "Ketua Panitia",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  },
  {
    "help": "",
    "name": "nik",
    "type": "number",
    "label": "NIK",
    "options": [],
    "required": true,
    "add_label": "Tambah",
    "item_label": "Item",
    "repeatable": false,
    "placeholder": "",
    "sumber_data": "data_kampus",
    "editable_role": "admin",
    "mode_form_pemohon": "readonly"
  }
]
JSON),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function jsonArray(string $json): array
    {
        /** @var array<int, array<string, mixed>> $decoded */
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }
}
