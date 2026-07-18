<?php

namespace Database\Seeders;

use App\Models\Portal\PortalMenu;
use App\Models\Portal\PortalPage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FakultasProdiSeeder extends Seeder
{
    public function run(): void
    {
        // Fakultas MIKOM (atau sesuaikan dengan fakultas aktual)
        $fakultas = DB::table('fakultas')->where('kode', 'FMIKOM')->first();
        if ($fakultas) {
            $fakultasId = $fakultas->id;
        } else {
            $fakultasId = DB::table('fakultas')->insertGetId([
                'nama' => 'Fakultas Matematika dan Ilmu Komputer',
                'kode' => 'FMIKOM',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Program Studi
        $prodis = [
            [
                'id' => 1,
                'fakultas_id' => $fakultasId,
                'nama' => 'Informatika',
                'kode' => 'IF',
            ],
            [
                'id' => 2,
                'fakultas_id' => $fakultasId,
                'nama' => 'Sistem Informasi',
                'kode' => 'SI',
            ],
            [
                'id' => 3,
                'fakultas_id' => $fakultasId,
                'nama' => 'Matematika',
                'kode' => 'MTK',
            ],
        ];

        foreach ($prodis as $prodi) {
            DB::table('program_studis')->updateOrInsert(
                ['id' => $prodi['id']],
                [
                    'fakultas_id' => $prodi['fakultas_id'],
                    'nama' => $prodi['nama'],
                    'kode' => $prodi['kode'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Ensure portal pages exist for each program study
        $prodiPages = [
            'informatika' => [
                'title' => 'Program Studi Informatika',
                'category' => 'Akademik',
                'template' => 'full-width',
                'is_published' => true,
                'content' => '<h1>Program Studi Informatika</h1><p>Selamat datang di Program Studi Informatika Fakultas Matematika dan Ilmu Komputer.</p>',
                'excerpt' => 'Program Studi Informatika FMIKOM',
            ],
            'sistem-informasi' => [
                'title' => 'Program Studi Sistem Informasi',
                'category' => 'Akademik',
                'template' => 'full-width',
                'is_published' => true,
                'content' => '<h1>Program Studi Sistem Informasi</h1><p>Selamat datang di Program Studi Sistem Informasi Fakultas Matematika dan Ilmu Komputer.</p>',
                'excerpt' => 'Program Studi Sistem Informasi FMIKOM',
            ],
            'matematika' => [
                'title' => 'Program Studi Matematika',
                'category' => 'Akademik',
                'template' => 'full-width',
                'is_published' => true,
                'content' => '<h1>Program Studi Matematika</h1><p>Selamat datang di Program Studi Matematika Fakultas Matematika dan Ilmu Komputer.</p>',
                'excerpt' => 'Program Studi Matematika FMIKOM',
            ],
        ];

        $pageIds = [];
        foreach ($prodiPages as $slug => $data) {
            $page = PortalPage::firstOrCreate(
                ['slug' => $slug],
                $data
            );
            $pageIds[$slug] = $page->id;
        }

        // Ensure parent menu 'Akademik' exists
        $parentMenu = PortalMenu::firstOrCreate(
            ['title' => 'Akademik', 'parent_id' => null],
            ['order' => 1]
        );

        // Ensure child menus exist under 'Akademik'
        $prodiMenus = [
            [
                'title' => 'Program Studi Informatika',
                'portal_page_id' => $pageIds['informatika'],
                'parent_id' => $parentMenu->id,
                'order' => 0,
            ],
            [
                'title' => 'Program Studi Sistem Informasi',
                'portal_page_id' => $pageIds['sistem-informasi'],
                'parent_id' => $parentMenu->id,
                'order' => 1,
            ],
            [
                'title' => 'Program Studi Matematika',
                'portal_page_id' => $pageIds['matematika'],
                'parent_id' => $parentMenu->id,
                'order' => 2,
            ],
        ];

        foreach ($prodiMenus as $menuData) {
            PortalMenu::firstOrCreate(
                ['title' => $menuData['title'], 'parent_id' => $parentMenu->id],
                $menuData
            );
        }

        $this->command->info('✅ Fakultas dan Program Studi berhasil di-seed.');
    }
}
