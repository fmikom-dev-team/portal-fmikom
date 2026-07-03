<?php

namespace App\Modules\Wims\Services\Mahasiswa\Report;

use App\Models\Magang\FinalReportTemplate;
use App\Modules\Wims\Services\Shared\Report\FinalReportTemplateAccessService;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentFinalReportTemplateService
{
    public function __construct(
        private readonly FinalReportTemplateAccessService $templateAccessService,
    ) {}

    public function resolveActiveTemplate(): ?FinalReportTemplate
    {
        return FinalReportTemplate::query()
            ->active()
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
    }

    public function buildTemplateCard(): ?array
    {
        $template = $this->resolveActiveTemplate();

        if (! $template) {
            return null;
        }

        return [
            'id' => $template->id,
            'title' => $template->title,
            'description' => $template->description,
            'original_name' => $template->original_name,
            'mime_type' => $template->mime_type,
            'updated_at' => $this->formatDate($template->updated_at),
            'download_url' => route('wims.laporan.template.download'),
        ];
    }

    public function downloadActiveTemplate(): BinaryFileResponse
    {
        $template = $this->resolveActiveTemplate();

        abort_unless($template, 404, 'Template laporan akhir belum tersedia.');

        $absolutePath = $this->templateAccessService->resolveAbsolutePath($template);

        return response()->download(
            $absolutePath,
            $this->templateAccessService->resolveActiveTemplateDownloadName($template),
        );
    }

    private function formatDate(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->translatedFormat('d M Y H:i');
        }

        return Carbon::parse($value)->translatedFormat('d M Y H:i');
    }
}
