<?php
namespace App\Modules\Fast\Controllers\Shared\User;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use App\Models\Surat;
use App\Models\SuratApprovalFlow;
use App\Models\SuratHistory;
use App\Models\SuratCategory;
use App\Modules\Fast\DTOs\SuratDataContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $roleSlug = $user->userTypeSlug();
        $isLecturer = str($roleSlug)->contains('dosen');

        $baseQuery = Surat::query()
            ->with([
                'jenisSurat.approvalRole',
                'approvalFlows' => fn ($q) => $q->with('approver:id,name')->latest('tanggal_aksi')->latest('id'),
                'histories' => fn ($q) => $q->latest('created_at')->latest('id')->limit(6),
            ])
            ->where('pemohon_id', $user->id);

        $jenisSurats = $this->visibleSubmissionJenisSuratQuery($user)
            ->with('category')
            ->orderBy('nama')
            ->get(['id', 'category_id', 'nama', 'slug', 'deskripsi', 'field_config']);

        $categoryIds = $jenisSurats->pluck('category_id')->filter()->unique()->values();

        $categories = SuratCategory::query()
            ->where('is_active', true)
            ->when($categoryIds->isNotEmpty(), fn ($q) => $q->whereIn('id', $categoryIds))
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get(['id', 'nama', 'slug', 'deskripsi']);

        // Status "diproses" = semua yang masih berjalan (belum finish/reject)
        $diprosesStatuses = [
            Surat::STATUS_PENDING,
            Surat::STATUS_VALIDATED_ADMIN,
            Surat::STATUS_REVISION_REQUESTED,
            Surat::STATUS_APPROVED_KAPRODI,
            Surat::STATUS_APPROVED_DEKAN,
        ];

        return Inertia::render($this->pageName(), [
            'summary' => (function () use ($user, $diprosesStatuses): array {
                $counts = Surat::query()
                    ->where('pemohon_id', $user->id)
                    ->selectRaw("
                        COUNT(*) as total,
                        SUM(CASE WHEN status IN (" . implode(',', array_fill(0, count($diprosesStatuses), '?')) . ") THEN 1 ELSE 0 END) as diproses,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as selesai,
                        SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END) as ditolak,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as dibatalkan
                    ", array_merge($diprosesStatuses, [Surat::STATUS_FINISHED, Surat::STATUS_REJECTED_ADMIN, Surat::STATUS_REJECTED_APPROVER, Surat::STATUS_CANCELLED]))
                    ->first();

                $total = (int) ($counts->total ?? 0);
                $dibatalkan = (int) ($counts->dibatalkan ?? 0);

                return [
                    'total'      => $total - $dibatalkan,
                    'diproses'   => (int) ($counts->diproses ?? 0),
                    'selesai'    => (int) ($counts->selesai ?? 0),
                    'ditolak'    => (int) ($counts->ditolak ?? 0),
                    'dibatalkan' => $dibatalkan,
                ];
            })(),
            'latest' => (clone $baseQuery)
                ->latest('tanggal_pengajuan')
                ->latest('id')
                ->limit(5)
                ->get()
                ->map(fn (Surat $s): array => $this->transformSubmission($s))
                ->values(),
            'categories' => $categories->map(fn (SuratCategory $c): array => [
                'id'       => $c->id,
                'nama'     => $c->nama,
                'slug'     => $c->slug,
                'deskripsi'=> $c->deskripsi,
            ])->values(),
            'jenisSurats' => $jenisSurats->map(fn (JenisSurat $j): array => [
                'id'          => $j->id,
                'categoryId'  => $j->category_id,
                'nama'        => $j->nama,
                'slug'        => $j->slug,
                'deskripsi'   => $j->deskripsi,
                'fieldConfig' => collect(SuratDataContract::filterDynamicFieldConfig($j->field_config ?? []))
                    ->map(fn (array $f): array => array_merge(
                        SuratDataContract::normalizeDynamicFieldConfigItem($f),
                        [
                            'options' => collect($f['options'] ?? [])
                                ->map(fn ($o) => is_array($o)
                                    ? ['label' => (string) ($o['label'] ?? $o['value'] ?? ''), 'value' => (string) ($o['value'] ?? '')]
                                    : ['label' => (string) $o, 'value' => (string) $o]
                                )->filter(fn ($o) => $o['value'] !== '')->values()->all(),
                        ],
                    ))->values()->all(),
            ])->values(),
            'userType' => [
                'value' => $roleSlug,
                'label' => $user->roleDisplayName(),
            ],
            'userProfile' => [
                'name'            => $user->name,
                'identifierLabel' => $isLecturer ? 'NIP' : 'NIM',
                'identifierValue' => $user->nim_nip ?: $user->nomor_induk,
            ],
            'endpoints' => [
                'submission'    => route($this->submissionRouteName(), absolute: false),
                'jenisSuratBase'=> '/jenis-surat',
                'basePath'      => $this->basePath(),
            ],
        ]);
    }

    protected function pageName(): string
    {
        return 'fast/user/Dashboard';
    }

    protected function submissionRouteName(): string
    {
        return 'fast.user.submissions.store';
    }

    protected function basePath(): string
    {
        return '/fast/user';
    }

    protected function visibleSubmissionJenisSuratQuery($user): Builder
    {
        $roleSlug = $user->userTypeSlug();

        return JenisSurat::query()
            ->where('is_active', true)
            ->where('alur_pengajuan', 'submission')
            ->whereHas('template')
            ->when($roleSlug !== '', fn (Builder $query) => $query->where(fn (Builder $roleQuery) => $roleQuery
                ->whereNull('allowed_role_id')
                ->orWhereHas('allowedRole', fn (Builder $allowedRoleQuery) => $allowedRoleQuery->where('slug', $roleSlug))
            ));
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformSubmission(Surat $surat): array
    {
        $latestRevisionFlow = $surat->latestRevisionRequestFlow();
        $latestAdminRejectionFlow = $surat->latestAdminRejectionFlow();
        $latestApproverFinalRejectionFlow = $surat->latestApproverFinalRejectionFlow();
        $latestFinalRejectionFlow = $latestAdminRejectionFlow ?? $latestApproverFinalRejectionFlow;

        return [
            'id' => $surat->id,
            'reference' => $surat->nomor_surat ?: sprintf('REQ-%05d', $surat->id),
            'jenisSurat' => $surat->jenisSurat?->nama ?? 'Surat Akademik',
            'jenisSuratId' => $surat->jenisSurat?->id,
            'approvalRole' => [
                'id' => $surat->jenisSurat?->approvalRole?->id,
                'nama' => $surat->jenisSurat?->approvalRole?->nama,
                'slug' => $surat->jenisSurat?->approvalRole?->slug,
            ],
            'approval_role_slug' => $surat->jenisSurat?->approvalRole?->slug,
            'requiresFinalApproval' => $surat->requiresFinalApproval(),
            'status' => $surat->status,
            'keperluan' => $surat->keperluan,
            'rejectionReason' => $latestFinalRejectionFlow?->catatan,
            'revisionReason' => $latestRevisionFlow?->catatan ?? $surat->catatan_revisi,
            'rejectedByRole' => $latestRevisionFlow?->role ?? $latestFinalRejectionFlow?->role,
            'needsRevision' => $surat->status === Surat::STATUS_REVISION_REQUESTED,
            'revisionCount' => (int) $surat->revisi_ke,
            'notes' => $surat->approvalFlows
                ->where('status', SuratApprovalFlow::STATUS_NOTE)
                ->map(fn ($flow) => [
                    'catatan' => $flow->catatan,
                    'oleh' => $flow->approver?->name,
                    'created_at' => $flow->tanggal_aksi?->toISOString(),
                ])->values()->all(),
            'timeline' => $surat->histories
                ->map(fn (SuratHistory $history) => [
                    'action' => $history->action,
                    'label' => $history->action_label,
                    'description' => $history->keterangan,
                    'created_at' => $history->created_at?->toISOString(),
                ])->values()->all(),
            'submittedAt' => optional($surat->tanggal_pengajuan ?? $surat->created_at)?->toISOString(),
            'neededAt' => optional($surat->tanggal_kebutuhan)?->toDateString(),
            'hasPdf' => filled($surat->generated_file_path),
        ];
    }
}


