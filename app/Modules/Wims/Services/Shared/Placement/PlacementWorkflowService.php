<?php

namespace App\Modules\Wims\Services\Shared\Placement;

use App\Models\Magang\PendaftaranMagang;
use App\Modules\Wims\Services\Shared\Placement\PlacementIndexService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class PlacementWorkflowService
{
    public function __construct(
        private readonly PlacementIndexService $placementIndexService,
    ) {
    }

    public function validatePlacementUpdate(Request $request): array
    {
        return $request->validate([
            'perusahaan_id' => ['required', 'integer', Rule::exists('perusahaan_mitras', 'id')],
            'dosen_pembimbing_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('role_id', function ($subQuery) {
                        $subQuery->select('id')
                            ->from('roles')
                            ->where('slug', 'dosen');
                    });
                }),
            ],
        ], [
            'perusahaan_id.required' => 'Perusahaan wajib dipilih sebelum penempatan disimpan.',
            'dosen_pembimbing_id.required' => 'Dosen pembimbing wajib dipilih sebelum penempatan disimpan.',
        ]);
    }

    public function validateSelectedCompletion(Request $request): array
    {
        return $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', Rule::exists('pendaftaran_magangs', 'id')],
        ], [
            'ids.required' => 'Pilih minimal satu mahasiswa untuk ditandai selesai.',
            'ids.min' => 'Pilih minimal satu mahasiswa untuk ditandai selesai.',
        ]);
    }

    public function canUpdatePlacement(PendaftaranMagang $pendaftaran): bool
    {
        return $pendaftaran->status === 'approved';
    }

    public function hasCompletePlacementData(PendaftaranMagang $pendaftaran): bool
    {
        // Aktivasi/generate surat ditahan sampai data penempatan inti lengkap
        // agar workflow PKL tidak berjalan dengan relasi atau periode yang kosong.
        return filled($pendaftaran->perusahaan_id)
            && filled($pendaftaran->dosen_pembimbing_id)
            && filled($pendaftaran->tanggal_mulai)
            && filled($pendaftaran->tanggal_selesai);
    }

    public function hasGeneratedSuratRequest(PendaftaranMagang $pendaftaran): bool
    {
        $surat = $pendaftaran->suratPenetapan;

        return $surat !== null && in_array($surat->status, ['requested', 'generated'], true);
    }

    public function canComplete(PendaftaranMagang $pendaftaran): bool
    {
        return $pendaftaran->status === 'aktif' && $pendaftaran->canBeMarkedComplete();
    }

    public function resolveCompletableSelected(array $ids): Collection
    {
        return PendaftaranMagang::with('mahasiswa')
            ->whereIn('id', $ids)
            ->where('status', 'aktif')
            ->get()
            ->filter(fn (PendaftaranMagang $pendaftaran) => $pendaftaran->canBeMarkedComplete())
            ->values();
    }

    public function resolveCompletableFiltered(Request $request): Collection
    {
        return $this->placementIndexService->buildIndexQuery($request)
            ->where('status', 'aktif')
            ->with('mahasiswa')
            ->get()
            // Batch selesai tetap disaring ulang di backend supaya aman walau selection/filter di UI berubah.
            ->filter(fn (PendaftaranMagang $pendaftaran) => $pendaftaran->canBeMarkedComplete())
            ->values();
    }
}
