<?php

namespace App\Services;

use App\Models\Tracer\ProfilAlumni;
use App\Models\Tracer\CareerHistory;
use App\Models\Tracer\Employment;
use App\Models\Tracer\Education;
use App\Enums\CareerStatus;
use App\Enums\CareerType;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CareerService
{
    public function store(ProfilAlumni $profile, array $data): CareerHistory
    {
        return DB::transaction(function () use ($profile, $data) {
            $status = $data['status'];
            $type = 'unemployment';
            if (in_array($status, ['bekerja', 'wirausaha'])) {
                $type = 'employment';
            } elseif ($status === 'lanjut_studi') {
                $type = 'education';
            }

            // Handle current career
            if ($data['is_current'] ?? false) {
                $data['tanggal_selesai'] = null;
                $profile->careers()->update(['is_current' => false]);
            }

            // Create base history
            $careerHistory = $profile->careers()->create([
                'type' => $type,
                'status' => $status,
                'tanggal_mulai' => $data['tanggal_mulai'] ?? null,
                'tanggal_selesai' => $data['tanggal_selesai'] ?? null,
                'is_current' => $data['is_current'] ?? false,
                'provinsi_id' => $data['provinsi_id'] ?? null,
                'kota_id' => $data['kota_id'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ]);

            // Save details
            if ($type === 'employment') {
                $careerHistory->employment()->create([
                    'nama_perusahaan' => $data['nama_perusahaan'],
                    'jabatan' => $data['jabatan'],
                    'sektor_industri' => $data['sektor_industri'] ?? null,
                    'alamat_perusahaan' => $data['alamat_perusahaan'] ?? null,
                    'gaji_min' => $data['gaji_min'] ?? null,
                    'gaji_max' => $data['gaji_max'] ?? null,
                ]);
            } elseif ($type === 'education') {
                $careerHistory->education()->create([
                    'nama_universitas' => $data['nama_universitas'],
                    'program_studi_lanjutan' => $data['program_studi_lanjutan'],
                    'jenjang_pendidikan' => $data['jenjang_pendidikan'] ?? null,
                    'sumber_biaya' => $data['sumber_biaya'] ?? null,
                    'alamat_universitas' => $data['alamat_universitas'] ?? null,
                ]);
            }

            return $careerHistory;
        });
    }

    public function update(CareerHistory $career, array $data): CareerHistory
    {
        return DB::transaction(function () use ($career, $data) {
            $status = $data['status'];
            $type = 'unemployment';
            if (in_array($status, ['bekerja', 'wirausaha'])) {
                $type = 'employment';
            } elseif ($status === 'lanjut_studi') {
                $type = 'education';
            }

            // Handle current career
            if ($data['is_current'] ?? false) {
                $data['tanggal_selesai'] = null;
                $career->alumniProfile->careers()
                    ->where('id', '!=', $career->id)
                    ->update(['is_current' => false]);
            }

            // Update base history
            $career->update([
                'type' => $type,
                'status' => $status,
                'tanggal_mulai' => $data['tanggal_mulai'] ?? null,
                'tanggal_selesai' => $data['tanggal_selesai'] ?? null,
                'is_current' => $data['is_current'] ?? false,
                'provinsi_id' => $data['provinsi_id'] ?? null,
                'kota_id' => $data['kota_id'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ]);

            // Clean up other relation details if type changed
            if ($type !== 'employment') {
                $career->employment()->delete();
            }
            if ($type !== 'education') {
                $career->education()->delete();
            }

            // Update or create details
            if ($type === 'employment') {
                $career->employment()->updateOrCreate([], [
                    'nama_perusahaan' => $data['nama_perusahaan'],
                    'jabatan' => $data['jabatan'],
                    'sektor_industri' => $data['sektor_industri'] ?? null,
                    'alamat_perusahaan' => $data['alamat_perusahaan'] ?? null,
                    'gaji_min' => $data['gaji_min'] ?? null,
                    'gaji_max' => $data['gaji_max'] ?? null,
                ]);
            } elseif ($type === 'education') {
                $career->education()->updateOrCreate([], [
                    'nama_universitas' => $data['nama_universitas'],
                    'program_studi_lanjutan' => $data['program_studi_lanjutan'],
                    'jenjang_pendidikan' => $data['jenjang_pendidikan'] ?? null,
                    'sumber_biaya' => $data['sumber_biaya'] ?? null,
                    'alamat_universitas' => $data['alamat_universitas'] ?? null,
                ]);
            }

            return $career;
        });
    }

    public function setCurrent(CareerHistory $career): void
    {
        DB::transaction(function () use ($career) {
            $career->alumniProfile->careers()->update(['is_current' => false]);
            $career->update(['is_current' => true]);
        });
    }

    public function calculateYearsOfExperience($careers): string
    {
        $totalMonths = 0;
        
        foreach ($careers as $career) {
            $statusVal = is_object($career->status) ? $career->status->value : $career->status;
            if ($career->tanggal_mulai && in_array($statusVal, ['bekerja', 'wirausaha'])) {
                $start = Carbon::parse($career->tanggal_mulai);
                $end = $career->tanggal_selesai ? Carbon::parse($career->tanggal_selesai) : now();
                $totalMonths += $start->diffInMonths($end);
            }
        }
        
        if ($totalMonths <= 0) {
            return "Belum ada";
        }
        
        $years = floor($totalMonths / 12);
        $months = $totalMonths % 12;
        
        if ($years > 0 && $months > 0) {
            return "$years tahun $months bulan";
        } elseif ($years > 0) {
            return "$years tahun";
        } elseif ($months > 0) {
            return "$months bulan";
        }
        
        return "Baru mulai";
    }

    /**
     * Flatten a CareerHistory model for frontend compatibility.
     */
    public function flattenCareer(CareerHistory $career): array
    {
        $flat = $career->toArray();
        $flat['status'] = is_object($career->status) ? $career->status->value : $career->status;
        $flat['type'] = is_object($career->type) ? $career->type->value : $career->type;

        if ($flat['type'] === 'employment' || $flat['status'] === 'bekerja' || $flat['status'] === 'wirausaha') {
            if ($career->employment) {
                $flat = array_merge($flat, $career->employment->toArray());
            }
        } elseif ($flat['type'] === 'education' || $flat['status'] === 'lanjut_studi') {
            if ($career->education) {
                $flat = array_merge($flat, $career->education->toArray());
            }
        }
        return $flat;
    }

    /**
     * Flatten a collection of CareerHistory models.
     */
    public function flattenCareers($careers): array
    {
        return collect($careers)->map(fn($c) => $this->flattenCareer($c))->toArray();
    }
}
