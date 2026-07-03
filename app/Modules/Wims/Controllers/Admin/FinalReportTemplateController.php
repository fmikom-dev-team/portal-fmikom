<?php

namespace App\Modules\Wims\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Magang\FinalReportTemplate;
use App\Modules\Wims\Requests\Admin\UpsertFinalReportTemplateRequest;
use App\Modules\Wims\Services\Admin\AdminFinalReportTemplateActionService;
use App\Modules\Wims\Services\Admin\AdminFinalReportTemplatePageService;
use App\Modules\Wims\Services\Shared\Report\FinalReportTemplateAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FinalReportTemplateController extends Controller
{
    public function __construct(
        private readonly AdminFinalReportTemplatePageService $pageService,
        private readonly AdminFinalReportTemplateActionService $actionService,
        private readonly FinalReportTemplateAccessService $templateAccessService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Modules/Wims/Admin/LaporanTemplate/Index', $this->pageService->build($request));
    }

    public function store(UpsertFinalReportTemplateRequest $request): RedirectResponse
    {
        $template = $this->actionService->create(
            $request->validated(),
            $request->user()?->id,
        );

        return redirect()
            ->route('wims.admin.final-report-templates.index', ['template' => $template->id])
            ->with('success', 'Template laporan akhir berhasil ditambahkan.');
    }

    public function update(UpsertFinalReportTemplateRequest $request, FinalReportTemplate $finalReportTemplate): RedirectResponse
    {
        $template = $this->actionService->update(
            $finalReportTemplate,
            $request->validated(),
            $request->user()?->id,
        );

        return redirect()
            ->route('wims.admin.final-report-templates.index', ['template' => $template->id])
            ->with('success', 'Template laporan akhir berhasil diperbarui.');
    }

    public function download(FinalReportTemplate $finalReportTemplate): BinaryFileResponse
    {
        $absolutePath = $this->templateAccessService->resolveAbsolutePath($finalReportTemplate);

        return response()->download(
            $absolutePath,
            $this->templateAccessService->resolveActiveTemplateDownloadName($finalReportTemplate),
        );
    }

    public function destroy(FinalReportTemplate $finalReportTemplate): RedirectResponse
    {
        $this->actionService->delete($finalReportTemplate);

        return redirect()
            ->route('wims.admin.final-report-templates.index')
            ->with('success', 'Template laporan akhir berhasil dihapus.');
    }
}
