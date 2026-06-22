<?php

namespace App\Modules\Wims\Services\Admin;

use App\Models\Magang\AssessmentSubmission;
use App\Models\Magang\PendaftaranMagang;
use App\Modules\Wims\Services\Shared\Portal\WimsModuleRoleService;
use App\Modules\Wims\Support\AssessmentSummary;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminAssessmentRecapPageService
{
    public function __construct(
        private readonly WimsModuleRoleService $wimsModuleRoleService,
    ) {}

    public function build(Request $request): array
    {
        $search = trim((string) $request->string('search', ''));
        $filter = (string) $request->string('filter', 'all');
        $today = now()->startOfDay();

        $baseQuery = PendaftaranMagang::query()
            ->with([
                'mahasiswa:id,name,email,nomor_induk,program_studi_id',
                'mahasiswa.programStudi:id,nama',
                'perusahaan:id,nama',
                'dosenPembimbing:id,name',
                'assessmentSubmissions' => fn ($builder) => AssessmentSummary::orderLatestFirst($builder)
                    ->select([
                        'id',
                        'pendaftaran_magang_id',
                        'assessor_role',
                        'status',
                        'total_score',
                        'submitted_at',
                        'updated_at',
                    ])
                    ->whereIn('assessor_role', ['dosen', 'mitra']),
            ])
            ->readyForAssessment($today)
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id');

        if ($search !== '') {
            $baseQuery->where(function (Builder $builder) use ($search): void {
                $builder->whereHas('mahasiswa', function (Builder $studentQuery) use ($search): void {
                    $studentQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('nomor_induk', 'like', "%{$search}%");
                })->orWhereHas('perusahaan', function (Builder $companyQuery) use ($search): void {
                    $companyQuery->where('nama', 'like', "%{$search}%");
                })->orWhereHas('dosenPembimbing', function (Builder $lecturerQuery) use ($search): void {
                    $lecturerQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        $query = clone $baseQuery;
        $this->applyFilterToQuery($query, $filter);

        $registrations = $query
            ->paginate(10)
            ->withQueryString();

        $this->wimsModuleRoleService->preloadContextRoles(
            $registrations->getCollection()->pluck('dosenPembimbing')->filter()->all(),
        );

        $registrations->through(function (PendaftaranMagang $pendaftaran): array {
            $dosenSubmission = AssessmentSummary::latestSubmission($pendaftaran->assessmentSubmissions, 'dosen');
            $mitraSubmission = AssessmentSummary::latestSubmission($pendaftaran->assessmentSubmissions, 'mitra');

            return [
                'id' => $pendaftaran->id,
                'student' => [
                    'name' => $pendaftaran->mahasiswa?->name,
                    'nim' => $pendaftaran->mahasiswa?->nomor_induk ?: $pendaftaran->mahasiswa?->nim_nip,
                    'email' => $pendaftaran->mahasiswa?->email,
                    'program_studi' => $pendaftaran->mahasiswa?->programStudi?->nama,
                ],
                'company' => [
                    'name' => $pendaftaran->perusahaan?->nama,
                ],
                'lecturer' => [
                    'id' => $pendaftaran->dosenPembimbing?->id,
                    'name' => $pendaftaran->dosenPembimbing?->name,
                    'role_context' => $pendaftaran->dosenPembimbing
                        ? $this->wimsModuleRoleService->resolveContextRoleData($pendaftaran->dosenPembimbing, 'dosen')
                        : null,
                ],
                'period_label' => $this->formatDateRange(
                    $pendaftaran->tanggal_mulai,
                    $pendaftaran->tanggal_selesai,
                ),
                'registration_status' => $pendaftaran->status,
                'dosen_assessment' => $this->transformSubmission($dosenSubmission, $pendaftaran, 'dosen'),
                'mitra_assessment' => $this->transformSubmission($mitraSubmission, $pendaftaran, 'mitra'),
            ];
        });

        return [
            'filters' => [
                'search' => $search,
                'filter' => $filter,
            ],
            'summary' => [
                'total_students' => (clone $baseQuery)->count(),
                'incomplete' => $this->countByFilter(clone $baseQuery, 'incomplete'),
                'missing_dosen_score' => $this->countByFilter(clone $baseQuery, 'missing_dosen_score'),
                'missing_mitra_score' => $this->countByFilter(clone $baseQuery, 'missing_mitra_score'),
                'complete' => $this->countByFilter(clone $baseQuery, 'complete'),
            ],
            'registrations' => $registrations,
        ];
    }

    private function transformSubmission(?AssessmentSubmission $submission, PendaftaranMagang $pendaftaran, string $role): array
    {
        $status = $submission?->status ?? 'not_assessed';
        $score = $submission?->total_score;

        return [
            'role_context' => [
                'slug' => $role,
                'label' => $this->wimsModuleRoleService->labelForRole($role),
            ],
            'status_key' => $status,
            'status_label' => $this->resolveSubmissionStatusLabel($status),
            'total_score' => $score !== null ? round((float) $score, 2) : null,
            'download_url' => $status === 'submitted'
                ? route('wims.admin.assessment-recap.download', [
                    'pendaftaran' => $pendaftaran,
                    'role' => $role,
                ])
                : null,
            'submitted_at' => $submission?->submitted_at
                ? $this->formatDateTime($submission->submitted_at)
                : null,
        ];
    }

    private function countByFilter(Builder $query, string $filter): int
    {
        $this->applyFilterToQuery($query, $filter);

        return $query->count();
    }

    private function applyFilterToQuery(Builder $query, string $filter): void
    {
        match ($filter) {
            'incomplete' => $query->where(function (Builder $builder): void {
                $builder
                    ->whereDoesntHave('assessmentSubmissions', function (Builder $submissionQuery): void {
                        $submissionQuery
                            ->where('assessor_role', 'dosen')
                            ->where('status', 'submitted');
                    })
                    ->orWhereDoesntHave('assessmentSubmissions', function (Builder $submissionQuery): void {
                        $submissionQuery
                            ->where('assessor_role', 'mitra')
                            ->where('status', 'submitted');
                    });
            }),
            'missing_dosen_score' => $query->whereDoesntHave('assessmentSubmissions', function (Builder $submissionQuery): void {
                $submissionQuery
                    ->where('assessor_role', 'dosen')
                    ->where('status', 'submitted');
            }),
            'missing_mitra_score' => $query->whereDoesntHave('assessmentSubmissions', function (Builder $submissionQuery): void {
                $submissionQuery
                    ->where('assessor_role', 'mitra')
                    ->where('status', 'submitted');
            }),
            'complete' => $query
                ->whereHas('assessmentSubmissions', function (Builder $submissionQuery): void {
                    $submissionQuery
                        ->where('assessor_role', 'dosen')
                        ->where('status', 'submitted');
                })
                ->whereHas('assessmentSubmissions', function (Builder $submissionQuery): void {
                    $submissionQuery
                        ->where('assessor_role', 'mitra')
                        ->where('status', 'submitted');
                }),
            default => null,
        };
    }

    private function resolveSubmissionStatusLabel(?string $status): string
    {
        return match ($status) {
            'submitted' => 'Sudah Dikirim',
            'draft' => 'Draft',
            default => 'Belum Dinilai',
        };
    }

    private function formatDateRange(mixed $startDate, mixed $endDate): string
    {
        if (blank($startDate) && blank($endDate)) {
            return '-';
        }

        return sprintf(
            '%s - %s',
            $this->formatDate($startDate),
            $this->formatDate($endDate),
        );
    }

    private function formatDate(mixed $date): string
    {
        if (! $date) {
            return '-';
        }

        if ($date instanceof CarbonInterface) {
            return $date->locale('id')->translatedFormat('d M Y');
        }

        return Carbon::parse($date)->locale('id')->translatedFormat('d M Y');
    }

    private function formatDateTime(CarbonInterface $date): string
    {
        return $date->locale('id')->translatedFormat('d M Y H:i');
    }
}
