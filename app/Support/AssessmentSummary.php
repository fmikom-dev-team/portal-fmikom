<?php

namespace App\Support;

use App\Models\AssessmentSubmission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class AssessmentSummary
{
    public static function fromSubmissions(iterable $submissions): array
    {
        $collection = Collection::wrap($submissions);
        $dosenSubmission = self::latestSubmission($collection, 'dosen');
        $mitraSubmission = self::latestSubmission($collection, 'mitra');

        $hasDosenSubmitted = $dosenSubmission?->status === 'submitted';
        $hasMitraSubmitted = $mitraSubmission?->status === 'submitted';
        $hasAnyDraft = collect([$dosenSubmission, $mitraSubmission])
            ->filter()
            ->contains(fn (AssessmentSubmission $submission) => $submission->status === 'draft');

        $statusKey = match (true) {
            $hasDosenSubmitted && $hasMitraSubmitted => 'submitted',
            $hasDosenSubmitted => 'final_dosen',
            $hasMitraSubmitted => 'final_mitra',
            $hasAnyDraft => 'draft',
            default => 'not_assessed',
        };

        return [
            'status_key' => $statusKey,
            'status_label' => self::resolveStatusLabel($statusKey),
            'is_complete' => $hasDosenSubmitted && $hasMitraSubmitted,
            'latest_submitted_at' => collect([
                $dosenSubmission?->submitted_at,
                $mitraSubmission?->submitted_at,
            ])->filter()->sortDesc()->first(),
            'dosen' => self::transformSubmission($dosenSubmission),
            'mitra' => self::transformSubmission($mitraSubmission),
        ];
    }

    public static function orderLatestFirst(Builder|Relation $query): Builder|Relation
    {
        return $query
            ->orderByDesc('submitted_at')
            ->orderByDesc('updated_at')
            ->orderByDesc('id');
    }

    public static function latestSubmission(iterable $submissions, string $role, ?int $assessorId = null): ?AssessmentSubmission
    {
        return Collection::wrap($submissions)
            ->filter(fn (AssessmentSubmission $submission) => $submission->assessor_role === $role)
            ->when(
                $assessorId !== null,
                fn (Collection $collection) => $collection->filter(
                    fn (AssessmentSubmission $submission) => (int) $submission->assessor_id === $assessorId,
                ),
            )
            ->sortByDesc(function (AssessmentSubmission $submission): array {
                return [
                    $submission->submitted_at?->timestamp ?? 0,
                    $submission->updated_at?->timestamp ?? 0,
                    $submission->id,
                ];
            })
            ->first();
    }

    private static function transformSubmission(?AssessmentSubmission $submission): array
    {
        return [
            'id' => $submission?->id,
            'status_key' => $submission?->status ?? 'not_assessed',
            'status_label' => self::resolveStatusLabel($submission?->status ?? 'not_assessed'),
            'score' => $submission?->total_score !== null ? round((float) $submission->total_score, 2) : null,
            'notes' => $submission?->notes,
            'submitted_at' => $submission?->submitted_at,
        ];
    }

    private static function resolveStatusLabel(?string $status): string
    {
        return match ($status) {
            'submitted' => 'Lengkap',
            'final_dosen' => 'Dosen Selesai',
            'final_mitra' => 'Mitra Selesai',
            'draft' => 'Draft',
            default => 'Belum Dinilai',
        };
    }
}
