<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FakultasProdiSeeder extends Seeder
{
    public function run(): void
    {
        // Fakultas MIKOM (atau sesuaikan dengan fakultas aktual)
        $fakultasId = DB::table('fakultas')->insertGetId([
            'nama'       => 'Fakultas Matematika dan Ilmu Komputer',
            'kode'       => 'FMIKOM',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Program Studi — ID harus sesuai dengan hardcode di Register.vue:
        // { value: 1, label: "Informatika" }
        // { value: 2, label: "Sistem Informasi" }
        // { value: 3, label: "Matematika" }
        DB::table('program_studis')->insert([
            [
                'id'          => 1,
                'fakultas_id' => $fakultasId,
                'nama'        => 'Informatika',
                'kode'        => 'IF',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => 2,
                'fakultas_id' => $fakultasId,
                'nama'        => 'Sistem Informasi',
                'kode'        => 'SI',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => 3,
                'fakultas_id' => $fakultasId,
                'nama'        => 'Matematika',
                'kode'        => 'MTK',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);

        $this->command->info('✅ Fakultas dan Program Studi berhasil di-seed.');
    }
}
