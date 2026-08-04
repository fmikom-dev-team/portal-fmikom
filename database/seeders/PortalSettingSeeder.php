<?php

namespace Database\Seeders;

use App\Models\Portal\PortalSetting;
use Illuminate\Database\Seeder;

class PortalSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            'brand_name' => 'PORTAL FMIKOM',
            'brand_subtitle' => 'Fakultas Matematika dan Ilmu Komputer',
            'brand_description' => 'Sistem informasi terpadu untuk civitas akademika FMIKOM.',
            'brand_logo' => '/asset/brand-logo.webp',
            'brand_favicon' => '/asset/brand-logo.webp',
            'primary_color' => '#2563eb',
            'seo_meta_title' => 'Portal FMIKOM - Fakultas Matematika dan Ilmu Komputer UNUGHA',
            'seo_meta_description' => 'Sistem informasi terpadu, direktori alumni, jaringan mitra industri, dan layanan akademik FMIKOM UNUGHA.',
            'seo_og_image' => '/asset/brand-logo.webp',
        ];

        foreach ($defaults as $key => $value) {
            PortalSetting::firstOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
