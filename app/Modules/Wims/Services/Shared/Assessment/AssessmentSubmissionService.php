<?php

namespace App\Modules\Wims\Services\Shared\Assessment;

use App\Models\Magang\AssessmentSubmission;
use App\Models\Magang\AssessmentTemplate;
use App\Models\Magang\PendaftaranMagang;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AssessmentSubmissionService
{
    public function resolveLatestSubmission(
        PendaftaranMagang $pendaftaran,
        User $user,
        string $role,
    ): ?AssessmentSubmission {
        return AssessmentSubmission::query()
            ->with('template')
            ->where('pendaftaran_magang_id', $pendaftaran->id)
            ->where('assessor_id', $user->id)
            ->where('assessor_role', $role)
            ->orderByDesc('submitted_at')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
    }

    public function saveSubmission(
        PendaftaranMagang $pendaftaran,
        AssessmentTemplate $template,
        User $user,
        string $role,
        ?AssessmentSubmission $existingSubmission,
        array $validated,
        string $submitActionValue,
    ): void {
        $allowedComponentIds = $template->components->pluck('id')->all();
        $scoresByComponent = collect($validated['scores'])->keyBy('component_id');

        DB::transaction(function () use (
            $allowedComponentIds,
            $existingSubmission,
            $pendaftaran,
            $role,
            $scoresByComponent,
            $submitActionValue,
            $template,
            $user,
            $validated
        ): void {
            // Draft dan submit memakai record yang sama agar assessor bisa melanjutkan penilaian
            // tanpa membuat duplikasi submission untuk pendaftaran dan template yang sama.
            $status = $validated['action'] === $submitActionValue ? 'submitted' : 'draft';

            $submission = AssessmentSubmission::query()->updateOrCreate(
                [
                    'pendaftaran_magang_id' => $pendaftaran->id,
                    'assessment_template_id' => $template->id,
                    'assessor_id' => $user->id,
                    'assessor_role' => $role,
                ],
                [
                    'status' => $status,
                    'notes' => $validated['notes'] ?? null,
                    'submitted_at' => $status === 'submitted'
                        ? now()
                        : $existingSubmission?->submitted_at,
                ],
            );

            $totalScore = 0;

            foreach ($template->components as $component) {
                $scorePayload = $scoresByComponent->get($component->id);
                $score = round((float) ($scorePayload['score'] ?? 0), 2);
                // Nilai akhir dihitung sebagai akumulasi skor terbobot per komponen template.
                $weightedScore = round($score * ((float) $component->weight_percentage / 100), 2);
                $totalScore += $weightedScore;

                $submission->scores()->updateOrCreate(
                    [
                        'assessment_component_id' => $component->id,
                    ],
                    [
                        'score' => $score,
                        'weighted_score' => $weightedScore,
                        'note' => $scorePayload['note'] ?? null,
                    ],
                );
            }

            $submission->scores()
                // Komponen lama dibersihkan agar struktur skor selalu mengikuti template aktif saat disimpan.
                ->whereNotIn('assessment_component_id', $allowedComponentIds)
                ->delete();

            $submission->update([
                'total_score' => round($totalScore, 2),
            ]);
        });
    }

    public function allowedComponentIds(AssessmentTemplate $template): array
    {
        return $template->components->pluck('id')->all();
    }

    public function scoreMap(?AssessmentSubmission $submission): Collection
    {
        return $submission?->scores?->keyBy('assessment_component_id') ?? collect();
    }
}
