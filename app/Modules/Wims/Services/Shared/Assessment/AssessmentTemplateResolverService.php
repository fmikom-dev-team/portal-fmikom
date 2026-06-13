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
            return $submission->template->assessor_role === $assessorRole
                ? $submission->template
                : null;
        }

        return AssessmentTemplate::query()
            ->where('assessor_role', $assessorRole)
            ->where('is_active', true)
            ->whereDate('periode_mulai', '<=', $date->toDateString())
            ->whereDate('periode_selesai', '>=', $date->toDateString())
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
