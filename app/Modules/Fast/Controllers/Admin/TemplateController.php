<?php

namespace App\Modules\Fast\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use App\Modules\Fast\Services\Admin\TemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class TemplateController extends Controller
{
    public function __construct(
        protected TemplateService $templateService,
    ) {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('admin/templates/Index', $this->templateService->index($request));
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->templateService->store($request);
    }

    public function update(Request $request, JenisSurat $jenisSurat): RedirectResponse
    {
        return $this->templateService->update($request, $jenisSurat);
    }

    public function preview(JenisSurat $jenisSurat): HttpResponse
    {
        return $this->templateService->preview($jenisSurat);
    }

    public function destroy(JenisSurat $jenisSurat): RedirectResponse
    {
        return $this->templateService->destroy($jenisSurat);
    }

    public function toggleActive(JenisSurat $jenisSurat): RedirectResponse
    {
        return $this->templateService->toggleActive($jenisSurat);
    }

    public function duplicate(JenisSurat $jenisSurat): RedirectResponse
    {
        return $this->templateService->duplicate($jenisSurat);
    }
}