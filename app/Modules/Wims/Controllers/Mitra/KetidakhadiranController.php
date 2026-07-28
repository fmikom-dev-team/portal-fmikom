<?php

namespace App\Modules\Wims\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Magang\KetidakhadiranMagang;
use App\Modules\Wims\Services\Mitra\MitraAccessService;
use App\Modules\Wims\Services\Mitra\MitraKetidakhadiranReviewService;
use App\Support\WimsStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class KetidakhadiranController extends Controller
{
    public function __construct(
        private readonly MitraAccessService $mitraAccessService,
        private readonly MitraKetidakhadiranReviewService $mitraKetidakhadiranReviewService,
    ) {}

    public function downloadProof(Request $request, KetidakhadiranMagang $ketidakhadiran): BinaryFileResponse
    {
        abort_unless($this->mitraAccessService->canReviewAbsence($request->user(), $ketidakhadiran), 403);

        $location = WimsStorage::locate($ketidakhadiran->bukti_path);
        $absolutePath = $location['absolute_path'] ?? null;

        abort_unless(filled($ketidakhadiran->bukti_path) && $absolutePath && is_file($absolutePath), 404, 'File bukti ketidakhadiran tidak ditemukan.');

        return response()->download($absolutePath, $ketidakhadiran->proofDownloadName());
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
