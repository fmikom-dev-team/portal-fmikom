<?php

namespace App\Services\Wims\Shared\Placement;

use App\Models\PendaftaranMagang;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlacementActionService
{
    public function updatePlacement(PendaftaranMagang $pendaftaran, array $validated): void
    {
        $pendaftaran->update([
            'perusahaan_id' => $validated['perusahaan_id'],
            'dosen_pembimbing_id' => $validated['dosen_pembimbing_id'],
        ]);
    }

    public function activate(PendaftaranMagang $pendaftaran): void
    {
        $pendaftaran->update([
            'status' => 'aktif',
        ]);
    }

    public function complete(PendaftaranMagang $pendaftaran): void
    {
        DB::transaction(function () use ($pendaftaran): void {
            $this->markRegistrationsComplete(collect([$pendaftaran->loadMissing('mahasiswa')]));
        });
    }

    public function completeMany(Collection $registrations): void
    {
        DB::transaction(function () use ($registrations): void {
            $this->markRegistrationsComplete($registrations);
        });
    }

    private function markRegistrationsComplete(Collection $registrations): void
    {
        $completedAt = now();

        $registrations->each(function (PendaftaranMagang $pendaftaran) use ($completedAt): void {
            $mahasiswa = $pendaftaran->mahasiswa;

            if ($mahasiswa) {
                $mahasiswa->forceFill([
                    'has_completed_pkl' => true,
                    'pkl_completed_at' => $completedAt,
                ])->save();
            }

            $pendaftaran->forceFill([
                'status' => 'selesai',
            ])->save();
        });
    }
}
