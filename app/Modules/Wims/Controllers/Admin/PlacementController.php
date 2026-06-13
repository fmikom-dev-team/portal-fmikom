<?php

namespace App\Modules\Wims\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranMagang;
use App\Modules\Wims\Services\Shared\Placement\PlacementActionService;
use App\Modules\Wims\Services\Shared\Placement\PlacementIndexService;
use App\Modules\Wims\Services\Shared\Placement\PlacementWorkflowService;
use App\Modules\Wims\Services\Shared\Placement\SuratPenetapanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlacementController extends Controller
{
    public function __construct(
        private readonly PlacementIndexService $placementIndexService,
        private readonly PlacementActionService $placementActionService,
        private readonly PlacementWorkflowService $placementWorkflowService,
        private readonly SuratPenetapanService $suratPenetapanService,
    ) {
    }

    public function index(Request $request): Response
    {
        $placements = $this->placementIndexService->buildPlacements($request);

        return Inertia::render('Wims/Admin/Penempatan/Index', [
            'filters' => $this->placementIndexService->buildFilters($request),
            'summary' => $this->placementIndexService->buildSummary(),
            'placements' => $placements,
            'batchActions' => $this->placementIndexService->buildBatchActions($request, $placements),
            'options' => $this->placementIndexService->buildOptions(),
        ]);
    }

    public function update(Request $request, PendaftaranMagang $pendaftaran): RedirectResponse
    {
        $validated = $this->placementWorkflowService->validatePlacementUpdate($request);

        if (! $this->placementWorkflowService->canUpdatePlacement($pendaftaran)) {
            return back()->with('error', 'Penempatan hanya dapat diubah sebelum mahasiswa diaktifkan.');
        }

        $this->placementActionService->updatePlacement($pendaftaran, $validated);

        return back()->with('success', 'Penempatan perusahaan dan dosen pembimbing berhasil disimpan.');
    }

    public function generateSurat(Request $request, PendaftaranMagang $pendaftaran): RedirectResponse
    {
        if (! $this->placementWorkflowService->canUpdatePlacement($pendaftaran)) {
            return back()->with('error', 'Surat penetapan hanya dapat dibuat sebelum mahasiswa diaktifkan.');
        }

        if (! $this->placementWorkflowService->hasCompletePlacementData($pendaftaran)) {
            return back()->with('error', 'Lengkapi perusahaan, dosen pembimbing, dan periode magang sebelum generate surat penetapan.');
        }

        $this->suratPenetapanService->requestGeneration($pendaftaran, $request->user()?->id);

        return back()->with('success', 'Permintaan generate surat penetapan berhasil dibuat dan menunggu integrasi FASt.');
    }

    public function activate(PendaftaranMagang $pendaftaran): RedirectResponse
    {
        if (! $this->placementWorkflowService->canUpdatePlacement($pendaftaran)) {
            return back()->with('error', 'Hanya pendaftaran berstatus approved yang dapat diaktifkan.');
        }

        if (! $this->placementWorkflowService->hasCompletePlacementData($pendaftaran)) {
            return back()->with('error', 'Lengkapi perusahaan, dosen pembimbing, dan periode magang sebelum mengaktifkan mahasiswa.');
        }

        if (! $this->placementWorkflowService->hasGeneratedSuratRequest($pendaftaran)) {
            return back()->with('error', 'Generate surat penetapan terlebih dahulu sebelum mengaktifkan mahasiswa magang.');
        }

        $this->placementActionService->activate($pendaftaran);

        return back()->with('success', 'Status pendaftaran berhasil diubah menjadi aktif.');
    }

    public function complete(PendaftaranMagang $pendaftaran): RedirectResponse
    {
        if ($pendaftaran->status !== 'aktif') {
            return back()->with('error', 'Hanya pendaftaran berstatus aktif yang dapat diselesaikan.');
        }

        if (! $this->placementWorkflowService->canComplete($pendaftaran)) {
            return back()->with('error', 'Status selesai hanya dapat diberikan setelah tanggal akhir magang terlewati.');
        }

        $this->placementActionService->complete($pendaftaran);

        return back()->with('success', 'PKL mahasiswa telah ditandai selesai dan dipindahkan ke arsip.');
    }

    public function completeSelected(Request $request): RedirectResponse
    {
        $validated = $this->placementWorkflowService->validateSelectedCompletion($request);
        $registrations = $this->placementWorkflowService->resolveCompletableSelected($validated['ids']);

        if ($registrations->isEmpty()) {
            return back()->with('error', 'Tidak ada mahasiswa aktif yang memenuhi syarat untuk ditandai selesai.');
        }

        $this->placementActionService->completeMany($registrations);

        return back()->with('success', sprintf('%d mahasiswa berhasil ditandai selesai dan dipindahkan ke arsip.', $registrations->count()));
    }

    public function completeFiltered(Request $request): RedirectResponse
    {
        $registrations = $this->placementWorkflowService->resolveCompletableFiltered($request);

        if ($registrations->isEmpty()) {
            return back()->with('error', 'Tidak ada mahasiswa aktif pada hasil filter yang memenuhi syarat untuk ditandai selesai.');
        }

        $this->placementActionService->completeMany($registrations);

        return back()->with('success', sprintf('%d mahasiswa dari hasil filter berhasil ditandai selesai dan dipindahkan ke arsip.', $registrations->count()));
    }
}
