<?php

namespace App\Modules\Wims\Services\Shared\Placement;

use App\Models\Magang\PendaftaranMagang;
use App\Models\Magang\PerusahaanMitra;
use App\Models\User;
use App\Modules\Wims\Services\Shared\Portal\WimsModuleRoleService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PlacementIndexService
{
    public function __construct(
        private readonly WimsModuleRoleService $wimsModuleRoleService,
    ) {
    }

    public function buildIndexQuery(Request $request, bool $withRelations = false): Builder
    {
        $status = (string) $request->string('status', 'all');
        $search = trim((string) $request->string('search', ''));
        $period = trim((string) $request->string('period', ''));
        $registrationId = $request->integer('pendaftaran');
        $allowedStatuses = ['approved', 'aktif', 'selesai'];

        $query = PendaftaranMagang::query()
            ->when($withRelations, fn (Builder $builder) => $builder->with(['mahasiswa', 'perusahaan', 'suratPenetapan']))
            ->whereIn('status', $allowedStatuses);

        if ($status !== '' && $status !== 'all' && in_array($status, $allowedStatuses, true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder->whereHas('mahasiswa', function (Builder $mahasiswaQuery) use ($search): void {
                    $mahasiswaQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nim_nip', 'like', "%{$search}%")
                        ->orWhere('nomor_induk', 'like', "%{$search}%");
                })->orWhereHas('perusahaan', function (Builder $perusahaanQuery) use ($search): void {
                    $perusahaanQuery->where('nama', 'like', "%{$search}%");
                });
            });
        }

        if ($period !== '') {
            [$start, $end] = array_pad(explode('__', $period, 2), 2, null);

            if (filled($start) && filled($end)) {
                $query
                    ->whereDate('tanggal_mulai', $start)
                    ->whereDate('tanggal_selesai', $end);
            }
        }

        if ($registrationId > 0) {
            $query->whereKey($registrationId);
        }

        return $query;
    }

    public function buildPlacements(Request $request): LengthAwarePaginator
    {
        $placements = $this->buildIndexQuery($request, true)
            ->orderByRaw("CASE WHEN status = 'approved' THEN 0 WHEN status = 'aktif' THEN 1 WHEN status = 'selesai' THEN 2 ELSE 3 END")
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $this->wimsModuleRoleService->preloadContextRoles(
            $placements->getCollection()->pluck('mahasiswa')->filter()->all(),
        );

        return $placements->through(fn (PendaftaranMagang $pendaftaran) => $this->transformPlacement($pendaftaran));
    }

    public function buildFilters(Request $request): array
    {
        $registrationId = $request->integer('pendaftaran');

        return [
            'status' => (string) $request->string('status', 'all'),
            'search' => trim((string) $request->string('search', '')),
            'period' => trim((string) $request->string('period', '')),
            'pendaftaran' => $registrationId > 0 ? $registrationId : null,
        ];
    }

    public function buildSummary(): array
    {
        $allowedStatuses = ['approved', 'aktif', 'selesai'];
        $approvedOrActiveQuery = PendaftaranMagang::query()->whereIn('status', $allowedStatuses);
        $activeCompaniesQuery = PerusahaanMitra::query()->where('is_active', true);

        return [
            'all' => (clone $approvedOrActiveQuery)->count(),
            'approved_or_active' => PendaftaranMagang::query()
                ->whereIn('status', ['approved', 'aktif'])
                ->count(),
            'completed' => PendaftaranMagang::query()
                ->where('status', 'selesai')
                ->count(),
            'assigned_company' => PendaftaranMagang::query()
                ->whereIn('status', $allowedStatuses)
                ->whereNotNull('perusahaan_id')
                ->count(),
            'assigned_dosen' => PendaftaranMagang::query()
                ->whereIn('status', $allowedStatuses)
                ->whereNotNull('dosen_pembimbing_id')
                ->count(),
            'available_companies' => (clone $activeCompaniesQuery)->count(),
            'fully_assigned' => PendaftaranMagang::query()
                ->whereIn('status', $allowedStatuses)
                ->whereNotNull('perusahaan_id')
                ->whereNotNull('dosen_pembimbing_id')
                ->count(),
        ];
    }

    public function buildBatchActions(Request $request, LengthAwarePaginator $placements): array
    {
        return [
            'eligible_on_current_page' => collect($placements->items())
                ->where('can_complete_now', true)
                ->count(),
            'eligible_with_current_filters' => $this->buildIndexQuery($request)
                ->where('status', 'aktif')
                ->get()
                ->filter(fn (PendaftaranMagang $pendaftaran) => $pendaftaran->canBeMarkedComplete())
                ->count(),
        ];
    }

    public function buildOptions(): array
    {
        $allowedStatuses = ['approved', 'aktif', 'selesai'];
        $activeCompaniesQuery = PerusahaanMitra::query()->where('is_active', true);

        return [
            'companies' => (clone $activeCompaniesQuery)
                ->orderBy('nama')
                ->get(['id', 'nama'])
                ->map(fn (PerusahaanMitra $company) => [
                    'id' => $company->id,
                    'label' => $company->nama,
                ])
                ->values()
                ->all(),
            'dosen' => User::query()
                ->whereIn('users.id', $this->wimsModuleRoleService->usersForRole('dosen')->select('users.id'))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (User $dosen) => [
                    'id' => $dosen->id,
                    'label' => $dosen->name,
                ])
                ->values()
                ->all(),
            'periods' => PendaftaranMagang::query()
                ->whereIn('status', $allowedStatuses)
                ->whereNotNull('tanggal_mulai')
                ->whereNotNull('tanggal_selesai')
                ->orderByDesc('tanggal_selesai')
                ->get(['tanggal_mulai', 'tanggal_selesai'])
                ->unique(fn (PendaftaranMagang $pendaftaran) => sprintf(
                    '%s__%s',
                    optional($pendaftaran->tanggal_mulai)->toDateString(),
                    optional($pendaftaran->tanggal_selesai)->toDateString(),
                ))
                ->map(function (PendaftaranMagang $pendaftaran): array {
                    $start = $pendaftaran->tanggal_mulai?->toDateString();
                    $end = $pendaftaran->tanggal_selesai?->toDateString();

                    return [
                        'value' => sprintf('%s__%s', $start, $end),
                        'label' => sprintf(
                            '%s - %s',
                            $this->formatDate($pendaftaran->tanggal_mulai),
                            $this->formatDate($pendaftaran->tanggal_selesai),
                        ),
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    private function transformPlacement(PendaftaranMagang $pendaftaran): array
    {
        $surat = $pendaftaran->suratPenetapan;
        $canAssign = $pendaftaran->status === 'approved';
        $hasPlacementData = filled($pendaftaran->perusahaan_id)
            && filled($pendaftaran->dosen_pembimbing_id)
            && filled($pendaftaran->tanggal_mulai)
            && filled($pendaftaran->tanggal_selesai);
        $hasGeneratedRequest = $surat !== null
            && in_array($surat->status, ['requested', 'generated'], true);
        $canCompleteNow = $pendaftaran->canBeMarkedComplete();

        return [
            'id' => $pendaftaran->id,
            'student' => [
                'id' => $pendaftaran->mahasiswa?->id,
                'name' => $pendaftaran->mahasiswa?->name,
                'email' => $pendaftaran->mahasiswa?->email,
                'identity' => $pendaftaran->mahasiswa?->nim_nip ?: $pendaftaran->mahasiswa?->nomor_induk,
                'role_context' => $pendaftaran->mahasiswa
                    ? $this->wimsModuleRoleService->resolveContextRoleData($pendaftaran->mahasiswa, 'mahasiswa')
                    : null,
            ],
            'company_id' => $pendaftaran->perusahaan_id,
            'company' => [
                'id' => $pendaftaran->perusahaan?->id,
                'name' => $pendaftaran->perusahaan?->nama,
            ],
            'dosen_pembimbing_id' => $pendaftaran->dosen_pembimbing_id,
            'tanggal_mulai' => $this->formatDate($pendaftaran->tanggal_mulai),
            'tanggal_selesai' => $this->formatDate($pendaftaran->tanggal_selesai),
            'status' => $pendaftaran->status,
            'can_assign' => $canAssign,
            'can_generate_surat' => $pendaftaran->status === 'approved' && $hasPlacementData,
            'can_activate' => $pendaftaran->status === 'approved' && $hasPlacementData && $hasGeneratedRequest,
            'can_complete' => $pendaftaran->status === 'aktif',
            'can_complete_now' => $canCompleteNow,
            'surat' => [
                'status' => $surat?->status ?? 'belum_dibuat',
                'provider' => $surat?->provider,
                'nomor_surat' => $surat?->nomor_surat,
                'requested_at' => $this->formatDateTime($surat?->requested_at),
                'generated_at' => $this->formatDateTime($surat?->generated_at),
                'file_url' => $surat?->file_url,
                'error_message' => $surat?->error_message,
            ],
        ];
    }

    private function formatDate(mixed $date): ?string
    {
        if (blank($date)) {
            return null;
        }

        if ($date instanceof Carbon) {
            return $date->translatedFormat('d M Y');
        }

        return Carbon::parse($date)->translatedFormat('d M Y');
    }

    private function formatDateTime(mixed $date): ?string
    {
        if (blank($date)) {
            return null;
        }

        if ($date instanceof Carbon) {
            return $date->translatedFormat('d M Y H:i');
        }

        return Carbon::parse($date)->translatedFormat('d M Y H:i');
    }
}
