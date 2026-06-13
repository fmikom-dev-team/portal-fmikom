<?php

namespace App\Http\Controllers\Wims\Mitra;

use App\Http\Controllers\Controller;
use App\Models\KetidakhadiranMagang;
use App\Services\Wims\Mitra\MitraAccessService;
use App\Services\Wims\Mitra\MitraKetidakhadiranReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KetidakhadiranController extends Controller
{
    public function __construct(
        private readonly MitraAccessService $mitraAccessService,
        private readonly MitraKetidakhadiranReviewService $mitraKetidakhadiranReviewService,
    ) {
    }

    public function approve(Request $request, KetidakhadiranMagang $ketidakhadiran): RedirectResponse
    {
        $validated = $request->validate([
            'catatan_mitra' => ['nullable', 'string', 'max:2000'],
        ]);

        abort_unless($this->mitraAccessService->canReviewAbsence($request->user(), $ketidakhadiran), 403);

        if ($ketidakhadiran->status !== 'pending') {
            return back()->withErrors([
                'ketidakhadiran' => 'Pengajuan ini sudah diproses sebelumnya.',
            ]);
        }

        $this->mitraKetidakhadiranReviewService->approve($ketidakhadiran, $request->user(), $validated['catatan_mitra'] ?? null);

        return back()->with('success', 'Pengajuan ketidakhadiran berhasil disetujui.');
    }

    public function reject(Request $request, KetidakhadiranMagang $ketidakhadiran): RedirectResponse
    {
        $validated = $request->validate([
            'catatan_mitra' => ['nullable', 'string', 'max:2000'],
        ]);

        abort_unless($this->mitraAccessService->canReviewAbsence($request->user(), $ketidakhadiran), 403);

        if ($ketidakhadiran->status !== 'pending') {
            return back()->withErrors([
                'ketidakhadiran' => 'Pengajuan ini sudah diproses sebelumnya.',
            ]);
        }

        $this->mitraKetidakhadiranReviewService->reject($ketidakhadiran, $request->user(), $validated['catatan_mitra'] ?? null);

        return back()->with('success', 'Pengajuan ketidakhadiran berhasil ditolak.');
    }
}
