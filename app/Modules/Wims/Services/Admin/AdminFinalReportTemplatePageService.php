<?php

namespace App\Modules\Wims\Services\Admin;

use App\Models\Magang\FinalReportTemplate;
use Illuminate\Http\Request;

class AdminFinalReportTemplatePageService
{
    public function build(Request $request): array
    {
        $selectedTemplateId = $request->integer('template') ?: null;

        $template = null;

        if ($selectedTemplateId) {
            $template = FinalReportTemplate::query()->find($selectedTemplateId);
        }

        if (! $template) {
            $template = FinalReportTemplate::query()
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->first();
        }

        return [
            'current_template' => $template ? [
                'id' => $template->id,
                'title' => $template->title,
                'description' => $template->description,
                'original_name' => $template->original_name,
                'mime_type' => $template->mime_type,
                'file_size' => $template->file_size,
                'is_active' => $template->is_active,
                'updated_at' => $template->updated_at?->translatedFormat('d M Y H:i'),
                'download_url' => route('wims.admin.final-report-templates.download', ['finalReportTemplate' => $template->id]),
            ] : null,
        ];
    }
}
