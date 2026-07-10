<?php

namespace App\Modules\Fast\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use App\Models\SuratLampiran;
use App\Modules\Fast\Services\Admin\ApprovalActionService;
use App\Modules\Fast\Services\Admin\ApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApprovalController extends Controller
{
    public function __construct(
        protected ApprovalActionService $approvalActionService,
        protected ApprovalService $approvalService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Surat::class);

        return $this->approvalService->index($request);
    }

    public function queue(Request $request): Response
    {
        $this->authorize('viewAny', Surat::class);

        return $this->approvalService->queue($request);
    }

    public function archive(Request $request): Response
    {
        $this->authorize('viewAny', Surat::class);

        return $this->approvalService->archive($request);
    }

    public function download(Request $request): Response
    {
        $this->authorize('viewAny', Surat::class);

        return $this->approvalService->download($request);
    }

    public function detail(Request $request, int $id): Response
    {
        $surat = Surat::query()->findOrFail($id);
        $this->authorize('view', $surat);

        return $this->approvalService->detail($request, $id);
    }

    public function show(int $id): JsonResponse
    {
        $surat = Surat::query()->findOrFail($id);
        $this->authorize('view', $surat);

        return response()->json($this->approvalService->show($id));
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

        return $this->approvalService->previewAttachment($id);
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $surat = Surat::query()->findOrFail($id);
        $this->authorize('approve', $surat);

        return $this->approvalActionService->approve($request, $id);
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

        return $this->approvalActionService->bulkApprove($request, $surats->all());
    }

    public function saveNote(Request $request, int $id): RedirectResponse
    {
        $surat = Surat::query()->findOrFail($id);
        $this->authorize('view', $surat);

        return $this->approvalActionService->saveNote($request, $id);
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $surat = Surat::query()->findOrFail($id);
        $this->authorize('reject', $surat);

        return $this->approvalActionService->reject($request, $id);
    }

    public function finalReject(Request $request, int $id): RedirectResponse
    {
        $surat = Surat::query()->findOrFail($id);
        $this->authorize('finalReject', $surat);

        return $this->approvalActionService->finalReject($request, $id);
    }
}
