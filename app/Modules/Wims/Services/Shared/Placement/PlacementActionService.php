<?php

namespace App\Modules\Wims\Services\Shared\Placement;

use App\Models\Magang\PendaftaranMagang;
use Illuminate\Database\Eloquent\Builder;
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

    public function completeFiltered(Builder $query): int
    {
        $completed = 0;

        $query
            ->select('pendaftaran_magangs.*')
            ->chunkById(100, function (Collection $registrations) use (&$completed): void {
                $eligible = $registrations
                    ->filter(fn (PendaftaranMagang $pendaftaran) => $pendaftaran->canBeMarkedComplete())
                    ->values();

                if ($eligible->isEmpty()) {
                    return;
                }

                DB::transaction(function () use ($eligible): void {
                    $this->markRegistrationsComplete($eligible);
                });

                $completed += $eligible->count();
            });

        return $completed;
    }

    private function markRegistrationsComplete(Collection $registrations): void
    {
        $registrations->each(function (PendaftaranMagang $pendaftaran): void {
            $pendaftaran->forceFill([
                'status' => 'selesai',
            ])->save();
        });
    }
}
