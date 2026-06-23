<?php

namespace App\Modules\Trace\Services;

use App\Models\Tracer\CareerHistory;
use App\Models\Tracer\ProfilAlumni;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CareerService
{
    public function store(ProfilAlumni $profile, array $data): CareerHistory
    {
        return DB::transaction(function () use ($profile, $data) {
            $type = $this->resolveType($data['status']);

            if ($data['is_current'] ?? false) {
                $data['tanggal_selesai'] = null;
                $profile->careers()->update(['is_current' => false]);
            }

            $careerHistory = $profile->careers()->create([
                'type' => $type,
                'status' => $data['status'],
                'tanggal_mulai' => $data['tanggal_mulai'] ?? null,
                'tanggal_selesai' => $data['tanggal_selesai'] ?? null,
                'is_current' => $data['is_current'] ?? false,
                'provinsi_id' => $data['provinsi_id'] ?? null,
                'kota_id' => $data['kota_id'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ]);

            $this->saveDetail($careerHistory, $type, $data);

            return $careerHistory;
        });
    }

    public function update(CareerHistory $career, array $data): CareerHistory
    {
        return DB::transaction(function () use ($career, $data) {
            $type = $this->resolveType($data['status']);

            if ($data['is_current'] ?? false) {
                $data['tanggal_selesai'] = null;
                $career->load('alumniProfile');
                $career->alumniProfile->careers()
                    ->where('id', '!=', $career->id)
                    ->update(['is_current' => false]);
            }

            $career->update([
                'type' => $type,
                'status' => $data['status'],
                'tanggal_mulai' => $data['tanggal_mulai'] ?? null,
                'tanggal_selesai' => $data['tanggal_selesai'] ?? null,
                'is_current' => $data['is_current'] ?? false,
                'provinsi_id' => $data['provinsi_id'] ?? null,
                'kota_id' => $data['kota_id'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ]);

            // Hapus relasi lama jika type berubah
            if ($type !== 'employment') {
                $career->employment()->delete();
            }
            if ($type !== 'education') {
                $career->education()->delete();
            }

            $this->saveDetail($career, $type, $data, isUpdate: true);

            return $career;
        });
    }

    public function setCurrent(CareerHistory $career): void
    {
        DB::transaction(function () use ($career) {
            $career->load('alumniProfile');
            $career->alumniProfile->careers()->update(['is_current' => false]);
            $career->update(['is_current' => true]);
        });
    }

    public function calculateYearsOfExperience($careers): string
    {
        $totalMonths = 0;

        foreach ($careers as $career) {
            $statusVal = is_object($career->status)
                ? $career->status->value
                : $career->status;

            if ($career->tanggal_mulai && in_array($statusVal, ['bekerja', 'wirausaha'])) {
                $start = Carbon::parse($career->tanggal_mulai);
                $end = $career->tanggal_selesai
                    ? Carbon::parse($career->tanggal_selesai)
                    : now();
                $totalMonths += $start->diffInMonths($end);
            }
        }

        if ($totalMonths <= 0) {
            return 'Belum ada';
        }

        $years = floor($totalMonths / 12);
        $months = $totalMonths % 12;

        if ($years > 0 && $months > 0) {
            return "{$years} tahun {$months} bulan";
        }
        if ($years > 0) {
            return "{$years} tahun";
        }
        if ($months > 0) {
            return "{$months} bulan";
        }

        return 'Baru mulai';
    }

    public function flattenCareer(CareerHistory $career): array
    {
        $flat = $career->toArray();
        $flat['status'] = is_object($career->status)
            ? $career->status->value
            : $career->status;
        $flat['type'] = is_object($career->type)
            ? $career->type->value
            : $career->type;

        if (in_array($flat['status'], ['bekerja', 'wirausaha']) && $career->employment) {
            $employmentData = $career->employment->toArray();
            unset($employmentData['id']); // ← hapus id sebelum merge
            $flat = array_merge($flat, $employmentData);
        } elseif ($flat['status'] === 'lanjut_studi' && $career->education) {
            $educationData = $career->education->toArray();
            unset($educationData['id']); // ← hapus id sebelum merge
            $flat = array_merge($flat, $educationData);
        }

        return $flat;
    }

    public function flattenCareers($careers): array
    {
        return collect($careers)
            ->map(fn ($c) => $this->flattenCareer($c))
            ->toArray();
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function resolveType(string $status): string
    {
        return match (true) {
            in_array($status, ['bekerja', 'wirausaha']) => 'employment',
            $status === 'lanjut_studi' => 'education',
            default => 'unemployment',
        };
    }

    private function saveDetail(
        CareerHistory $career,
        string $type,
        array $data,
        bool $isUpdate = false
    ): void {
        if ($type === 'employment') {
            $payload = [
                'nama_perusahaan' => $data['nama_perusahaan'],
                'jabatan' => $data['jabatan'],
                'sektor_industri' => $data['sektor_industri'] ?? null,
                'alamat_perusahaan' => $data['alamat_perusahaan'] ?? null,
                'gaji_min' => $data['gaji_min'] ?? null,
                'gaji_max' => $data['gaji_max'] ?? null,
            ];
            $isUpdate
                ? $career->employment()->updateOrCreate([], $payload)
                : $career->employment()->create($payload);
        } elseif ($type === 'education') {
            $payload = [
                'nama_universitas' => $data['nama_universitas'],
                'program_studi_lanjutan' => $data['program_studi_lanjutan'],
                'jenjang_pendidikan' => $data['jenjang_pendidikan'] ?? null,
                'sumber_biaya' => $data['sumber_biaya'] ?? null,
                'alamat_universitas' => $data['alamat_universitas'] ?? null,
            ];
            $isUpdate
                ? $career->education()->updateOrCreate([], $payload)
                : $career->education()->create($payload);
        }
    }
}
