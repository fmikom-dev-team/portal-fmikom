<?php

namespace Database\Seeders;

use App\Models\Tracer\Provinsi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProvinsiSeeder extends Seeder
{
    public function run(): void
    {
        $provincesJson = File::get(database_path('seeders/data/provinces.json'));
        $provinces = json_decode($provincesJson, true);

        foreach ($provinces as $province) {
            Provinsi::updateOrCreate(
                ['id' => $province['id']],
                [
                    'name' => $province['name'],
                    'latitude' => $province['latitude'] ?? null,
                    'longitude' => $province['longitude'] ?? null,
                ]
            );
        }
    }
}
