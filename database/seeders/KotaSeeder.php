<?php

namespace Database\Seeders;

use App\Models\Tracer\Kota;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class KotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regenciesJson = File::get(database_path('seeders/data/regencies.json'));
        $regencies = json_decode($regenciesJson, true);

        foreach ($regencies as $regency) {
            Kota::updateOrCreate(
                ['id' => $regency['id']],
                [
                    'provinsi_id' => $regency['province_id'],
                    'name' => $regency['name'],
                    'latitude' => $regency['latitude'] ?? null,
                    'longitude' => $regency['longitude'] ?? null,
                ]
            );
        }
    }
}
