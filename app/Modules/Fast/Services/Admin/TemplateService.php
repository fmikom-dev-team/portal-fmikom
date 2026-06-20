<?php

namespace App\Modules\Fast\Services\Admin;

use App\Models\JenisSurat;
use App\Modules\Fast\Template\Renderers\SuratTemplateRendererService;
use App\Modules\Fast\Support\TemplateAdminSupport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;

class TemplateService
{
    public function __construct(
        protected SuratTemplateRendererService $templateRenderer,
        protected TemplateAdminSupport $templateAdminSupport,
        protected TemplateMutationService $templateMutationService,
    ) {
    }

    public function index(Request $request): array
    {
        $this->templateAdminSupport->ensureGlobalSettingsExist();

        $jenisSurats = $this->templateAdminSupport->listJenisSurats();

        $selectedId = (int) $request->integer('jenis_surat_id');
        $selectedJenisSurat = $selectedId > 0
            ? $jenisSurats->firstWhere('id', $selectedId)
            : null;

        return [
            'jenisSurats' => $jenisSurats->map(fn (JenisSurat $jenisSurat): array => [
                'id'        => $jenisSurat->id,
                'nama'      => $jenisSurat->nama,
                'slug'      => $jenisSurat->slug,
                'is_active' => $jenisSurat->is_active,
                'category'  => [
                    'id'   => $jenisSurat->category?->id,
                    'nama' => $jenisSurat->category?->nama,
                ],
                'template' => $jenisSurat->template ? [
                    'id'         => $jenisSurat->template->id,
                    'name'       => $jenisSurat->template->name,
                    'version'    => $jenisSurat->template->version,
                    'created_at' => optional($jenisSurat->template->created_at)?->toISOString(),
                    'updated_at' => optional($jenisSurat->template->updated_at)?->toISOString(),
                ] : null,
            ])->values(),
            'selectedJenisSurat'   => $selectedJenisSurat ? $this->templateAdminSupport->serializeJenisSurat($selectedJenisSurat) : null,
            'selectedJenisSuratId' => $selectedJenisSurat?->id,
            'categories'           => $this->templateAdminSupport->listCategories(),
            'approvalRoles'        => $this->templateAdminSupport->listApprovalRoles(),
            'creatorRoles'         => $this->templateAdminSupport->listCreatorRoles(),
            'globalSettings'       => $this->templateAdminSupport->listGlobalSettings(),
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->templateMutationService->store($request);
    }

    public function update(Request $request, JenisSurat $jenisSurat): RedirectResponse
    {
        return $this->templateMutationService->update($request, $jenisSurat);
    }

    public function preview(JenisSurat $jenisSurat): HttpResponse
    {
        $template = $jenisSurat->template()->with('placeholders')->firstOrFail();
        $rendered = $this->templateRenderer->renderTemplatePreview($template, 'pdf');

        return response(
            $this->templateRenderer->wrapDocumentHtml('Preview Template ' . $jenisSurat->nama, $rendered['html'], $template),
            200,
        )->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function destroy(JenisSurat $jenisSurat): RedirectResponse
    {
        DB::transaction(function () use ($jenisSurat): void {
            $template = $jenisSurat->template()->withTrashed()->with('placeholders')->first();

            if ($template) {
                $template->placeholders()->forceDelete();
                $template->forceDelete();
            }

            if (! $jenisSurat->trashed()) {
                $jenisSurat->delete();
            }
        });

        return to_route('admin.templates.index')->with('success', 'Jenis surat berhasil dihapus.');
    }

    public function toggleActive(JenisSurat $jenisSurat): RedirectResponse
    {
        $jenisSurat->forceFill([
            'is_active' => ! $jenisSurat->is_active,
        ])->save();

        return to_route('admin.templates.index', [
            'jenis_surat_id' => $jenisSurat->id,
        ])->with('success', 'Status jenis surat diperbarui.');
    }

    public function duplicate(JenisSurat $jenisSurat): RedirectResponse
    {
        return $this->templateMutationService->duplicate($jenisSurat);
    }

}
