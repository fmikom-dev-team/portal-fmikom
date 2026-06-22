<?php

namespace App\Modules\Wims\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Magang\AssessmentTemplate;
use App\Modules\Wims\Requests\Admin\UpsertAssessmentTemplateRequest;
use App\Modules\Wims\Services\Admin\AdminAssessmentTemplateActionService;
use App\Modules\Wims\Services\Admin\AdminAssessmentTemplatePageService;
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
        return Inertia::render('Modules/Wims/Admin/PenilaianTemplate/Index', $this->pageService->build($request));
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
