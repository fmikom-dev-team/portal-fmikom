<?php

namespace App\Http\Controllers\Wims\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wims\Admin\UpsertAssessmentTemplateRequest;
use App\Models\AssessmentTemplate;
use App\Services\Wims\Admin\AdminAssessmentTemplateActionService;
use App\Services\Wims\Admin\AdminAssessmentTemplatePageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssessmentTemplateController extends Controller
{
    public function __construct(
        private readonly AdminAssessmentTemplatePageService $pageService,
        private readonly AdminAssessmentTemplateActionService $actionService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Wims/Admin/PenilaianTemplate/Index', $this->pageService->build($request));
    }

    public function store(UpsertAssessmentTemplateRequest $request): RedirectResponse
    {
        $template = $this->actionService->create(
            $request->validated(),
            $request->user()?->id,
        );

        return redirect()
            ->route('wims.admin.assessment-templates.index', ['template' => $template->id])
            ->with('success', 'Template penilaian berhasil ditambahkan.');
    }

    public function update(UpsertAssessmentTemplateRequest $request, AssessmentTemplate $assessmentTemplate): RedirectResponse
    {
        $validated = $request->validated();

        if ($this->actionService->hasLockedComponentChanges($assessmentTemplate, $validated)) {
            return back()->withErrors([
                'components' => 'Komponen template yang sudah dipakai penilaian tidak dapat diubah.',
            ]);
        }

        $this->actionService->update($assessmentTemplate, $validated);

        return redirect()
            ->route('wims.admin.assessment-templates.index', ['template' => $assessmentTemplate->id])
            ->with('success', 'Template penilaian berhasil diperbarui.');
    }

    public function destroy(AssessmentTemplate $assessmentTemplate): RedirectResponse
    {
        if ($this->actionService->hasSubmissions($assessmentTemplate)) {
            return back()->with('error', 'Template yang sudah dipakai penilaian tidak dapat dihapus.');
        }

        $this->actionService->delete($assessmentTemplate);

        return redirect()
            ->route('wims.admin.assessment-templates.index')
            ->with('success', 'Template penilaian berhasil dihapus.');
    }
}
