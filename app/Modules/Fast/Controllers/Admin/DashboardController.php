<?php

namespace App\Modules\Fast\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use App\Models\SuratLampiran;
use App\Modules\Fast\Services\Admin\ApprovalActionService;
use App\Modules\Fast\Services\Admin\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected ApprovalActionService $approvalActionService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Surat::class);

        return $this->dashboardService->index($request);
    }

    public function show(int $id): Response
    {
        $surat = Surat::query()->findOrFail($id);
        $this->authorize('view', $surat);

        return $this->dashboardService->show($id);
    }

    public function previewTemplate(int $id): SymfonyResponse
    {
        $surat = Surat::query()->findOrFail($id);
        $this->authorize('view', $surat);

        return $this->dashboardService->previewTemplate($id);
    }

    public function previewGeneratedDocument(int $id): SymfonyResponse|StreamedResponse
    {
        $surat = Surat::query()->findOrFail($id);
        $this->authorize('download', $surat);

        return $this->dashboardService->previewGeneratedDocument($id);
    }

    public function downloadPdf(Request $request, int $id): SymfonyResponse
    {
        $surat = Surat::query()->findOrFail($id);
        $this->authorize('download', $surat);

        return $this->dashboardService->downloadPdf($request, $id);
    }

    public function previewAttachmentDocument(int $id): SymfonyResponse|StreamedResponse
    {
        $surat = Surat::query()->findOrFail($id);
        $this->authorize('download', $surat);

        return $this->dashboardService->previewAttachmentDocument($id);
    }

    public function downloadAttachmentPdf(Request $request, int $id): SymfonyResponse
    {
        $surat = Surat::query()->findOrFail($id);
        $this->authorize('download', $surat);

        return $this->dashboardService->downloadAttachmentPdf($request, $id);
    }

    public function previewAttachment(Request $request, int $id): SymfonyResponse|StreamedResponse
    {
        $lampiran = SuratLampiran::query()
            ->with('surat')
            ->findOrFail($id);

        if ($request->user() !== null) {
            abort_unless($lampiran->surat !== null, 404, 'Lampiran tidak ditemukan.');
            $this->authorize('previewAttachment', $lampiran->surat);
        }

        return $this->dashboardService->previewAttachment($id);
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $surat = Surat::query()->findOrFail($id);
        $this->authorize('approve', $surat);

        return $this->approvalActionService->approveAdmin($request, $id);
    }

    public function bulkApprove(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'surat_ids' => ['required', 'array', 'min:1'],
            'surat_ids.*' => ['integer', 'distinct', 'exists:surats,id'],
        ]);

        $surats = Surat::query()
            ->whereIn('id', $data['surat_ids'])
            ->get();

        if ($surats->count() !== count($data['surat_ids'])) {
            abort(422, 'Sebagian surat tidak ditemukan.');
        }

        foreach ($surats as $surat) {
            $this->authorize('approve', $surat);
        }

        return $this->approvalActionService->bulkApproveAdmin($request, $surats->all());
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $surat = Surat::query()->findOrFail($id);
        $this->authorize('reject', $surat);

        return $this->approvalActionService->rejectAdmin($request, $id);
    }

    public function rejectRedirect(int $id): RedirectResponse
    {
        return redirect()->route('admin.surat.show', $id);
    }
}
