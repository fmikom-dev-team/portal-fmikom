<?php

namespace App\Modules\Wims\Services\Shared\Assessment;

use App\Models\Magang\PendaftaranMagang;
use App\Models\Magang\PerusahaanMitra;
use App\Models\User;
use App\Modules\Wims\Support\AssessmentSummary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AssessmentIndexService
{
    private const PER_PAGE = 20;

    public function buildLecturerData(User $user, Request $request): array
    {
        return $this->buildData($user, $request, 'dosen', null);
    }

    public function buildCompanyData(User $user, ?PerusahaanMitra $company, Request $request): array
    {
        return $company
            ? $this->buildData($user, $request, 'mitra', $company)
            : $this->emptyPayload();
    }

    private function buildData(User $user, Request $request, string $role, ?PerusahaanMitra $company): array
    {
        $query = PendaftaranMagang::query()
            ->when($role === 'dosen', fn (Builder $builder) => $builder->where('dosen_pembimbing_id', $user->id))
            ->when($role === 'mitra', fn (Builder $builder) => $builder->where('perusahaan_id', $company?->id))
            ->readyForAssessment(now());

        $this->applyFilters($query, $request, $user->id, $role);

        $page = (clone $query)
            ->with([
                'mahasiswa:id,name,email,nomor_induk',
                'perusahaan:id,nama',
                'assessmentSubmissions' => fn ($submissionQuery) => AssessmentSummary::orderLatestFirst($submissionQuery)
                    ->where('assessor_id', $user->id)
                    ->where('assessor_role', $role)
                    ->with('template:id,name'),
            ])
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $page->through(fn (PendaftaranMagang $pendaftaran) => $this->transformRegistration($pendaftaran, $user->id, $role));

        return $this->buildIndexPayload($page, $query, $request, $user->id, $role);
    }

    private function transformRegistration(PendaftaranMagang $pendaftaran, int $assessorId, string $role): array
    {
        $submission = AssessmentSummary::latestSubmission($pendaftaran->assessmentSubmissions, $role, $assessorId);
        $statusKey = $submission?->status ?? 'not_assessed';

        return [
            'id' => $pendaftaran->id,
            'student' => [
                'name' => $pendaftaran->mahasiswa?->name,
                'nim' => $pendaftaran->mahasiswa?->nomor_induk,
                'email' => $pendaftaran->mahasiswa?->email,
            ],
            'company' => [
                'id' => $pendaftaran->perusahaan?->id,
                'name' => $pendaftaran->perusahaan?->nama,
            ],
            'period' => [
                'start' => $pendaftaran->tanggal_mulai?->toDateString(),
                'end' => $pendaftaran->tanggal_selesai?->toDateString(),
                'label' => $this->formatPeriodLabel($pendaftaran),
            ],
            'registration_status' => $pendaftaran->status,
            'dashboard_phase' => $pendaftaran->isReadyForAssessment(now()) ? 'completed' : ($pendaftaran->status === 'aktif' ? 'active' : 'assigned'),
            'assessment' => [
                'status_key' => $statusKey,
                'status_label' => $this->resolveSubmissionStatusLabel($submission?->status),
                'total_score' => $submission?->total_score !== null ? round((float) $submission->total_score, 2) : null,
                'submitted_at' => $submission?->submitted_at?->translatedFormat('d M Y H:i'),
                'template_name' => $submission?->template?->name,
            ],
        ];
    }

    private function applyFilters(Builder $query, Request $request, int $assessorId, string $role): void
    {
        $search = trim((string) $request->string('search', ''));
        $status = (string) $request->string('status', 'all');

        if ($search !== '') {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
            $query->where(function (Builder $builder) use ($escaped): void {
                $builder->whereHas('mahasiswa', function (Builder $student) use ($escaped): void {
                    $student->where('name', 'like', "%{$escaped}%")
                        ->orWhere('email', 'like', "%{$escaped}%")
                        ->orWhere('nomor_induk', 'like', "%{$escaped}%");
                })->orWhereHas('perusahaan', fn (Builder $company) => $company->where('nama', 'like', "%{$escaped}%"));
            });
        }

        if (! in_array($status, ['all', 'not_assessed', 'draft', 'submitted'], true)) {
            $status = 'all';
        }

        $submission = fn (Builder $builder) => $builder
            ->where('assessor_id', $assessorId)
            ->where('assessor_role', $role);

        if ($status === 'not_assessed') {
            $query->whereDoesntHave('assessmentSubmissions', $submission);
        } elseif ($status === 'draft') {
            // AssessmentSummary prioritizes an already submitted entry over a
            // draft from another template. Mirror that rule in the SQL filter
            // so the list cannot label an item "submitted" under "draft".
            $query
                ->whereHas('assessmentSubmissions', fn (Builder $builder) => $submission($builder)->where('status', 'draft'))
                ->whereDoesntHave('assessmentSubmissions', fn (Builder $builder) => $submission($builder)->where('status', 'submitted'));
        } elseif ($status === 'submitted') {
            $query->whereHas('assessmentSubmissions', fn (Builder $builder) => $submission($builder)->where('status', 'submitted'));
        }
    }

    private function buildIndexPayload($page, Builder $filteredQuery, Request $request, int $assessorId, string $role): array
    {
        $summaryQuery = clone $filteredQuery;
        $submission = fn (Builder $builder) => $builder
            ->where('assessor_id', $assessorId)
            ->where('assessor_role', $role);

        return [
            'summary' => [
                'total_students' => (clone $summaryQuery)->count(),
                'not_assessed' => (clone $summaryQuery)->whereDoesntHave('assessmentSubmissions', $submission)->count(),
                'draft' => (clone $summaryQuery)->whereHas('assessmentSubmissions', fn (Builder $builder) => $submission($builder)->where('status', 'draft'))->count(),
                'submitted' => (clone $summaryQuery)->whereHas('assessmentSubmissions', fn (Builder $builder) => $submission($builder)->where('status', 'submitted'))->count(),
            ],
            'students' => $page->getCollection()->all(),
            'pagination' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'from' => $page->firstItem(),
                'to' => $page->lastItem(),
                'total' => $page->total(),
            ],
            'filters' => [
                'search' => trim((string) $request->string('search', '')),
                'status' => (string) $request->string('status', 'all'),
            ],
        ];
    }

    private function emptyPayload(): array
    {
        return [
            'summary' => ['total_students' => 0, 'not_assessed' => 0, 'draft' => 0, 'submitted' => 0],
            'students' => [],
            'pagination' => ['current_page' => 1, 'last_page' => 1, 'from' => null, 'to' => null, 'total' => 0],
            'filters' => ['search' => '', 'status' => 'all'],
        ];
    }

    private function formatPeriodLabel(PendaftaranMagang $pendaftaran): string
    {
        if (! $pendaftaran->tanggal_mulai || ! $pendaftaran->tanggal_selesai) {
            return 'Periode belum ditentukan';
        }

        return sprintf(
            '%s - %s',
            Carbon::parse($pendaftaran->tanggal_mulai)->translatedFormat('d M Y'),
            Carbon::parse($pendaftaran->tanggal_selesai)->translatedFormat('d M Y'),
        );
    }

    private function resolveSubmissionStatusLabel(?string $status): string
    {
        return match ($status) {
            'submitted' => 'Sudah Dikirim',
            'draft' => 'Draft',
            default => 'Belum Dinilai',
        };
    }
}
