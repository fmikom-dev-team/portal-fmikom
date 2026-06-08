<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Tracer\Provinsi;
class ProvinsiSeeder extends Seeder
{
   
    public function run(): void
    {
        $provincesJson = File::get(database_path('seeders/data/provinces.json'));
        $provinces = json_decode($provincesJson,true);

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
