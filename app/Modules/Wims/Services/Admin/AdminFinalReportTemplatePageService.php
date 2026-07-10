<?php

namespace App\Modules\Wims\Services\Admin;

use App\Models\Magang\FinalReportTemplate;
use Illuminate\Http\Request;

class AdminFinalReportTemplatePageService
{
    public function build(Request $request): array
    {
        return [
            'proposal_template' => $this->resolveTemplate('proposal', $request->integer('proposal_template') ?: null),
            'final_report_template' => $this->resolveTemplate('final_report', $request->integer('final_report_template') ?: null),
        ];
    }

    private function resolveTemplate(string $type, ?int $selectedTemplateId): ?array
    {
        $template = null;

        if ($selectedTemplateId) {
            $template = FinalReportTemplate::query()
                ->type($type)
                ->find($selectedTemplateId);
        }

        if (! $template) {
            $template = FinalReportTemplate::query()
                ->type($type)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->first();
        }

        return $template ? [
            'id' => $template->id,
            'template_type' => $template->template_type,
            'title' => $template->title,
            'description' => $template->description,
            'original_name' => $template->original_name,
            'mime_type' => $template->mime_type,
            'file_size' => $template->file_size,
            'is_active' => $template->is_active,
            'updated_at' => $template->updated_at?->translatedFormat('d M Y H:i'),
            'download_url' => route('wims.admin.final-report-templates.download', ['finalReportTemplate' => $template->id]),
        ] : null;
    }
}
