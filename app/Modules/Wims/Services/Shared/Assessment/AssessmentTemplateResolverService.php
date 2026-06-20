<?php

namespace App\Modules\Wims\Services\Shared\Assessment;

use App\Models\Magang\AssessmentSubmission;
use App\Models\Magang\AssessmentTemplate;
use App\Models\Magang\PendaftaranMagang;
use Carbon\CarbonInterface;

class AssessmentTemplateResolverService
{
    public function resolveForRoleAndDate(
        string $assessorRole,
        CarbonInterface $date,
        ?AssessmentSubmission $submission = null,
    ): ?AssessmentTemplate {
        if ($submission?->template) {
            return $submission->template->appliesToAssessorRole($assessorRole)
                ? $submission->template
                : null;
        }

        return AssessmentTemplate::query()
            ->whereIn('assessor_role', [$assessorRole, 'both'])
            ->where('is_active', true)
            ->whereDate('periode_mulai', '<=', $date->toDateString())
            ->whereDate('periode_selesai', '>=', $date->toDateString())
            ->orderByRaw(
                "case when assessor_role = ? then 0 when assessor_role = 'both' then 1 else 2 end",
                [$assessorRole],
            )
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
    }

    public function resolveForRegistration(
        PendaftaranMagang $pendaftaran,
        string $assessorRole,
        ?AssessmentSubmission $submission = null,
    ): ?AssessmentTemplate {
        if (! $pendaftaran->tanggal_mulai) {
            return null;
        }

        return $this->resolveForRoleAndDate($assessorRole, $pendaftaran->tanggal_mulai, $submission);
    }
}
