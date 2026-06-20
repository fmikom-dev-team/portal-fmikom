<?php

namespace App\Modules\Fast\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Fast\Services\Admin\ApprovalActionService;
use App\Modules\Fast\Services\Admin\ApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApprovalController extends Controller
{
    public function __construct(
        protected ApprovalActionService $approvalActionService,
        protected ApprovalService $approvalService,
    ) {
    }

    public function index(Request $request): Response
    {
        return $this->approvalService->index($request);
    }

    public function queue(Request $request): Response
    {
        return $this->approvalService->queue($request);
    }

    public function archive(Request $request): Response
    {
        return $this->approvalService->archive($request);
    }

    public function download(Request $request): Response
    {
        return $this->approvalService->download($request);
    }

    public function detail(Request $request, int $id): Response
    {
        return $this->approvalService->detail($request, $id);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->approvalService->show($id));
    }

    public function previewAttachment(int $id): StreamedResponse
    {
        return $this->approvalService->previewAttachment($id);
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        return $this->approvalActionService->approve($request, $id);
    }

    public function saveNote(Request $request, int $id): RedirectResponse
    {
        return $this->approvalActionService->saveNote($request, $id);
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        return $this->approvalActionService->reject($request, $id);
    }

    public function finalReject(Request $request, int $id): RedirectResponse
    {
        return $this->approvalActionService->finalReject($request, $id);
    }
}
