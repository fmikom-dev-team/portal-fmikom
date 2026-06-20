<?php

namespace App\Modules\Fast\Controllers\Admin;

use App\Http\Controllers\Controller;
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
    ) {
    }

    public function index(Request $request): Response
    {
        return $this->dashboardService->index($request);
    }

    public function show(int $id): \Inertia\Response
    {
        return $this->dashboardService->show($id);
    }

    public function previewTemplate(int $id): SymfonyResponse
    {
        return $this->dashboardService->previewTemplate($id);
    }

    public function previewGeneratedDocument(int $id): SymfonyResponse|StreamedResponse
    {
        return $this->dashboardService->previewGeneratedDocument($id);
    }

    public function downloadPdf(Request $request, int $id): SymfonyResponse
    {
        return $this->dashboardService->downloadPdf($request, $id);
    }

    public function previewAttachment(int $id): StreamedResponse
    {
        return $this->dashboardService->previewAttachment($id);
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        return $this->approvalActionService->approveAdmin($request, $id);
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        return $this->approvalActionService->rejectAdmin($request, $id);
    }

    public function rejectRedirect(int $id): RedirectResponse
    {
        return redirect()->route('admin.surat.show', $id);
    }
}
