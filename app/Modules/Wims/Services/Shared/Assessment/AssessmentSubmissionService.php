<?php

namespace App\Modules\Wims\Services\Shared\Assessment;

use App\Models\Magang\AssessmentSubmission;
use App\Models\Magang\AssessmentTemplate;
use App\Models\Magang\PendaftaranMagang;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
    ): void {
        $this->assertRoleMatchesTemplate($template, $role);
        $this->assertEditable($existingSubmission);

        $allowedComponentIds = $template->components->pluck('id')->map(fn ($id) => (int) $id)->all();
        $scorePayloads = collect($validated['scores'] ?? [])->values();
        $scoresByComponent = $scorePayloads->keyBy(fn (array $score) => (int) $score['component_id']);
        $status = $validated['action'] === 'submitted' ? 'submitted' : 'draft';

        $this->assertNoDuplicateComponents($scorePayloads);
        $this->assertComponentIdsBelongToTemplate($scoresByComponent, $allowedComponentIds);
        $this->assertCompletenessForStatus($status, $template, $scoresByComponent);

        DB::transaction(function () use (
            $allowedComponentIds,
            $existingSubmission,
            $pendaftaran,
            $role,
            $scoresByComponent,
            $template,
            $user,
            $validated,
            $status
        ): void {
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
            $retainedScoreIds = [];

            foreach ($scoresByComponent as $componentId => $scorePayload) {
                $component = $template->components->firstWhere('id', (int) $componentId);
                $score = round((float) $scorePayload['score'], 2);

                if ($score < 0 || $score > 100) {
                    throw ValidationException::withMessages([
                        'scores' => 'Nilai setiap komponen harus berada pada rentang 0 sampai 100.',
                    ]);
                }

                $weightedScore = round($score * ((float) $component->weight_percentage / 100), 2);
                $totalScore += $weightedScore;

                $scoreRow = $submission->scores()->updateOrCreate(
                    [
                        'assessment_component_id' => $component->id,
                    ],
                    [
                        'score' => $score,
                        'weighted_score' => $weightedScore,
                        'note' => $scorePayload['note'] ?? null,
                    ],
                );
                $retainedScoreIds[] = (int) $scoreRow->id;
            }

            if ($status === 'draft') {
                $submission->scores()
                    ->whereNotIn('assessment_component_id', $allowedComponentIds)
                    ->delete();

                $submission->scores()
                    ->when($retainedScoreIds !== [], fn ($query) => $query->whereNotIn('id', $retainedScoreIds))
                    ->when($retainedScoreIds === [], fn ($query) => $query)
                    ->delete();
            }

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

    private function assertRoleMatchesTemplate(AssessmentTemplate $template, string $role): void
    {
        if ($template->appliesToAssessorRole($role)) {
            return;
        }

        throw ValidationException::withMessages([
            'template' => 'Template penilaian tidak sesuai dengan role penilai.',
        ]);
    }

    private function assertEditable(?AssessmentSubmission $existingSubmission): void
    {
        if (! $existingSubmission || $existingSubmission->status !== 'submitted') {
            return;
        }

        throw ValidationException::withMessages([
            'submission' => 'Submission yang sudah dikirim tidak dapat diubah.',
        ]);
    }

    private function assertNoDuplicateComponents(Collection $scorePayloads): void
    {
        $componentIds = $scorePayloads
            ->pluck('component_id')
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($componentIds->count() === $componentIds->unique()->count()) {
            return;
        }

        throw ValidationException::withMessages([
            'scores' => 'Satu komponen hanya boleh memiliki satu nilai dalam satu submission.',
        ]);
    }

    private function assertComponentIdsBelongToTemplate(Collection $scoresByComponent, array $allowedComponentIds): void
    {
        $invalidComponentId = $scoresByComponent
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->first(fn (int $id) => ! in_array($id, $allowedComponentIds, true));

        if ($invalidComponentId === null) {
            return;
        }

        throw ValidationException::withMessages([
            'scores' => 'Komponen penilaian harus berasal dari template yang sama.',
        ]);
    }

    private function assertCompletenessForStatus(
        string $status,
        AssessmentTemplate $template,
        Collection $scoresByComponent,
    ): void {
        if ($status !== 'submitted') {
            return;
        }

        if ($scoresByComponent->count() !== $template->components->count()) {
            throw ValidationException::withMessages([
                'scores' => 'Submission final wajib berisi nilai untuk seluruh komponen template.',
            ]);
        }
    }
}
