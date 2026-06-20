<?php

namespace App\Modules\Fast\Services\Admin;

use App\Models\Surat;
use App\Models\SuratApprovalFlow;
use App\Models\SuratLampiran;
use App\Modules\Fast\Support\TemplateAdminSupport;
use App\Modules\Fast\Workflow\Approvals\FastApprovalWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApprovalService
{
    public function __construct(
        protected TemplateAdminSupport $templateAdminSupport,
    ) {
    }

    public function index(Request $request): Response
    {
        [$user, $roleName, $roleSlug, $normalizedRole] = $this->resolveRoleContext($request);

        $status = $request->string('status')->toString();
        $defaultStatus = $this->waitingStatusesForRole($normalizedRole)[0] ?? '';
        $effectiveStatus = $status !== '' ? $status : $defaultStatus;
        $search = $request->string('search')->trim()->toString();
        $categoryId = $request->integer('category_id');

        $query = $this->baseQueryForRole($normalizedRole);

        if ($effectiveStatus !== '') {
            $query->where('status', $effectiveStatus);
        } else {
            $query->whereIn('status', $this->waitingStatusesForRole($normalizedRole));
        }

        if ($categoryId > 0) {
            $query->whereHas('jenisSurat', function ($jenisQuery) use ($categoryId): void {
                $jenisQuery->where('category_id', $categoryId);
            });
        }

        if ($search !== '') {
            $query->whereHas('pemohon', function ($pemohonQuery) use ($search): void {
                $pemohonQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('nim_nip', 'like', "%{$search}%")
                    ->orWhere('nomor_induk', 'like', "%{$search}%");
            });
        }

        $surats = $query
            ->latest()
            ->paginate(10)
            ->through(fn (Surat $surat): array => $this->serializeSuratListItem($surat))
            ->withQueryString();

        return inertia('approval/Index', [
            'role' => [
                'name' => $roleName,
                'slug' => $roleSlug,
            ],
            'surats' => $surats,
            'summary' => [
                'waiting' => $this->baseSummaryQuery($normalizedRole)->whereIn('status', $this->waitingStatusesForRole($normalizedRole))->count(),
                'approved' => $this->baseSummaryQuery($normalizedRole)->whereIn('status', $this->approvedStatusesForRole($normalizedRole))->count(),
                'revision_requested' => $this->baseSummaryQuery($normalizedRole)->where('status', Surat::STATUS_REVISION_REQUESTED)->count(),
                'final_rejected' => $this->baseSummaryQuery($normalizedRole)->where('status', Surat::STATUS_REJECTED_APPROVER)->count(),
            ],
            'filters' => [
                'status' => $effectiveStatus,
                'search' => $search,
                'category_id' => $categoryId > 0 ? (string) $categoryId : '',
            ],
            'categories' => $this->templateAdminSupport->listCategories(),
        ]);
    }

    public function queue(Request $request): Response
    {
        [, $roleName, $roleSlug, $normalizedRole] = $this->resolveRoleContext($request);

        $status = $request->string('status')->toString();
        $search = $request->string('search')->trim()->toString();
        $categoryId = $request->integer('category_id');
        $queueStatuses = [
            Surat::STATUS_VALIDATED_ADMIN,
            Surat::STATUS_REVISION_REQUESTED,
            Surat::STATUS_REJECTED_ADMIN,
            Surat::STATUS_REJECTED_APPROVER,
        ];

        $effectiveStatuses = match ($status) {
            '', 'pending' => [Surat::STATUS_VALIDATED_ADMIN],
            'all' => $queueStatuses,
            'revision_requested' => [Surat::STATUS_REVISION_REQUESTED],
            'rejected' => [Surat::STATUS_REJECTED_ADMIN, Surat::STATUS_REJECTED_APPROVER],
            default => [Surat::STATUS_VALIDATED_ADMIN],
        };

        $query = $this->baseQueryForRole($normalizedRole)
            ->whereIn('status', $effectiveStatuses);

        if ($search !== '') {
            $query->whereHas('pemohon', function ($pemohonQuery) use ($search): void {
                $pemohonQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('nim_nip', 'like', "%{$search}%")
                    ->orWhere('nomor_induk', 'like', "%{$search}%");
            });
        }

        if ($categoryId > 0) {
            $query->whereHas('jenisSurat', function ($jenisQuery) use ($categoryId): void {
                $jenisQuery->where('category_id', $categoryId);
            });
        }

        $surats = $query->latest()->paginate(10)->through(fn (Surat $surat): array => $this->serializeSuratListItem($surat))->withQueryString();

        return inertia('approval/Queue', [
            'role' => ['name' => $roleName, 'slug' => $roleSlug],
            'surats' => $surats,
            'filters' => [
                'status' => $status !== '' ? $status : 'pending',
                'search' => $search,
                'category_id' => $categoryId > 0 ? (string) $categoryId : '',
            ],
            'categories' => $this->templateAdminSupport->listCategories(),
        ]);
    }

    public function archive(Request $request): Response
    {
        [, $roleName, $roleSlug, $normalizedRole] = $this->resolveRoleContext($request);

        $status = $request->string('status')->toString();
        $search = $request->string('search')->trim()->toString();
        $categoryId = $request->integer('category_id');

        $approvedStatuses = $this->approvedStatusesForRole($normalizedRole);
        $archiveStatuses = [
            ...$approvedStatuses,
            Surat::STATUS_REVISION_REQUESTED,
            Surat::STATUS_REJECTED_APPROVER,
        ];

        $query = $this->baseQueryForRole($normalizedRole)
            ->whereIn('status', $archiveStatuses);

        if ($status === 'approved') {
            $query->whereIn('status', $approvedStatuses);
        } elseif ($status !== '' && in_array($status, $archiveStatuses, true)) {
            $query->where('status', $status);
        }

        if ($categoryId > 0) {
            $query->whereHas('jenisSurat', function ($jenisQuery) use ($categoryId): void {
                $jenisQuery->where('category_id', $categoryId);
            });
        }

        if ($search !== '') {
            $query->whereHas('pemohon', function ($pemohonQuery) use ($search): void {
                $pemohonQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('nim_nip', 'like', "%{$search}%")
                    ->orWhere('nomor_induk', 'like', "%{$search}%");
            });
        }

        $surats = $query
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->through(fn (Surat $surat): array => $this->serializeArchiveItem($surat))
            ->withQueryString();

        return inertia('approval/Archive', [
            'role' => ['name' => $roleName, 'slug' => $roleSlug],
            'surats' => $surats,
            'filters' => [
                'status' => $status,
                'search' => $search,
                'category_id' => $categoryId > 0 ? (string) $categoryId : '',
            ],
            'statusOptions' => $this->archiveStatusOptions($normalizedRole),
            'categories' => $this->templateAdminSupport->listCategories(),
        ]);
    }

    public function download(Request $request): Response
    {
        [, $roleName, $roleSlug, $normalizedRole] = $this->resolveRoleContext($request);

        $search = $request->string('search')->trim()->toString();
        $categoryId = $request->integer('category_id');
        $tanggalMulai = $request->string('tanggal_mulai')->trim()->toString();
        $tanggalAkhir = $request->string('tanggal_akhir')->trim()->toString();

        $query = $this->baseQueryForRole($normalizedRole)
            ->where('status', Surat::STATUS_FINISHED)
            ->whereNotNull('generated_file_path');

        if ($search !== '') {
            $query->whereHas('pemohon', function ($pemohonQuery) use ($search): void {
                $pemohonQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('nim_nip', 'like', "%{$search}%")
                    ->orWhere('nomor_induk', 'like', "%{$search}%");
            });
        }

        if ($categoryId > 0) {
            $query->whereHas('jenisSurat', function ($jenisQuery) use ($categoryId): void {
                $jenisQuery->where('category_id', $categoryId);
            });
        }

        if ($tanggalMulai !== '') {
            $query->whereDate('tanggal_selesai', '>=', $tanggalMulai);
        }

        if ($tanggalAkhir !== '') {
            $query->whereDate('tanggal_selesai', '<=', $tanggalAkhir);
        }

        $surats = $query
            ->latest('tanggal_selesai')
            ->paginate(10)
            ->through(fn (Surat $surat): array => $this->serializeDownloadItem($surat))
            ->withQueryString();

        return inertia('approval/Download', [
            'role' => ['name' => $roleName, 'slug' => $roleSlug],
            'surats' => $surats,
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId > 0 ? (string) $categoryId : '',
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_akhir' => $tanggalAkhir,
            ],
            'categories' => $this->templateAdminSupport->listCategories(),
        ]);
    }

    public function detail(Request $request, int $id): Response
    {
        [, $roleName, $roleSlug, $normalizedRole] = $this->resolveRoleContext($request);

        $surat = Surat::query()
            ->with([
                'pemohon',
                'jenisSurat.approvalRole',
                'lampirans',
                'approvalFlows.approver',
                'histories.user',
            ])
            ->findOrFail($id);

        $source = $request->string('from')->toString();
        $backHref = match ($source) {
            'dashboard' => route($normalizedRole === FastApprovalWorkflowService::ROLE_KAPRODI ? 'kaprodi.dashboard' : 'dekan.dashboard', absolute: false),
            'antrian' => route($normalizedRole === FastApprovalWorkflowService::ROLE_KAPRODI ? 'kaprodi.antrian' : 'dekan.antrian', absolute: false),
            default => route($normalizedRole === FastApprovalWorkflowService::ROLE_KAPRODI ? 'kaprodi.arsip' : 'dekan.arsip', absolute: false),
        };
        $backLabel = match ($source) {
            'dashboard' => 'Dashboard',
            'antrian' => 'Antrian Approval',
            default => 'Riwayat Approval',
        };

        return inertia('approval/Show', array_merge([
            'role' => [
                'name' => $roleName,
                'slug' => $roleSlug,
            ],
            'back_href' => $backHref,
            'back_label' => $backLabel,
        ], $this->serializeDetailItem($surat, $request)));
    }

    public function show(int $id): array
    {
        $surat = Surat::query()
            ->with(['pemohon', 'jenisSurat', 'lampirans', 'approvalFlows.approver'])
            ->findOrFail($id);

        return $this->serializeShowItem($surat);
    }

    public function previewAttachment(int $id): StreamedResponse
    {
        $lampiran = SuratLampiran::query()->findOrFail($id);

        abort_unless(Storage::disk('public')->exists($lampiran->file_path), 404);

        return Storage::disk('public')->response(
            $lampiran->file_path,
            $lampiran->nama_file,
            [
                'Content-Type' => $lampiran->tipe ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="'.addslashes($lampiran->nama_file).'"',
            ],
        );
    }

    protected function resolveRoleContext(Request $request): array
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $roleName = $user->roleDisplayName() ?: 'Approval';
        $roleSlug = $user->userTypeSlug() ?: 'approval';
        $normalizedRole = $this->normalizeRole($roleSlug, $roleName);

        return [$user, $roleName, $roleSlug, $normalizedRole];
    }

    protected function serializeSuratListItem(Surat $surat): array
    {
        return [
            'id' => $surat->id,
            'status' => $surat->status,
            'tanggal_pengajuan' => optional($surat->tanggal_pengajuan ?? $surat->created_at)?->toISOString(),
            'created_at' => optional($surat->created_at)?->toISOString(),
            'pemohon' => [
                'name' => $surat->pemohon?->name,
                'nim' => $surat->pemohon?->nim_nip ?? $surat->pemohon?->nomor_induk,
            ],
            'jenisSurat' => [
                'id' => $surat->jenisSurat?->id,
                'nama' => $surat->jenisSurat?->nama,
            ],
        ];
    }

    protected function serializeArchiveItem(Surat $surat): array
    {
        return array_merge($this->serializeSuratListItem($surat), [
            'nomor_surat' => $surat->nomor_surat,
        ]);
    }

    protected function serializeDownloadItem(Surat $surat): array
    {
        return array_merge($this->serializeArchiveItem($surat), [
            'tanggal_selesai' => optional($surat->tanggal_selesai)?->toISOString(),
            'download_url' => $surat->generated_file_path ? Storage::disk('public')->url($surat->generated_file_path) : null,
        ]);
    }

    protected function serializeDetailItem(Surat $surat, Request $request): array
    {
        $isiSurat = json_decode((string) $surat->isi_surat, true);
        $latestRejectedFlow = $surat->latestRejectedFlow();

        return [
            'id' => $surat->id,
            'nomor_surat' => $surat->nomor_surat,
            'pemohon' => [
                'name' => $surat->pemohon?->name,
                'nim' => $surat->pemohon?->nim_nip ?? $surat->pemohon?->nomor_induk,
            ],
            'jenis_surat' => $surat->jenisSurat?->nama,
            'keperluan' => $surat->keperluan,
            'isi_surat' => is_array($isiSurat) ? $isiSurat : [],
            'lampiran' => $surat->lampirans->map(fn ($lampiran): array => [
                'id' => $lampiran->id,
                'name' => $lampiran->nama_file,
                'url' => null,
                'type' => $lampiran->tipe,
            ])->values(),
            'tanggal_pengajuan' => optional($surat->tanggal_pengajuan ?? $surat->created_at)?->toISOString(),
            'status' => $surat->status,
            'latest_rejection' => $latestRejectedFlow === null ? null : [
                'role' => $latestRejectedFlow->role,
                'label' => match ($latestRejectedFlow->role) {
                    'admin' => 'Ditolak Admin',
                    'kaprodi' => $latestRejectedFlow->status === Surat::STATUS_REVISION_REQUESTED ? 'Dikembalikan Kaprodi' : 'Ditolak Kaprodi',
                    'dekan' => $latestRejectedFlow->status === Surat::STATUS_REVISION_REQUESTED ? 'Dikembalikan Dekan' : 'Ditolak Dekan',
                    default => 'Riwayat Penolakan',
                },
                'type' => $latestRejectedFlow->status === Surat::STATUS_REVISION_REQUESTED ? 'revision' : 'final_reject',
                'note' => $latestRejectedFlow->catatan,
                'acted_at' => optional($latestRejectedFlow->tanggal_aksi ?? $latestRejectedFlow->created_at)?->toISOString(),
            ],
            'approval_timeline' => $surat->approvalFlows
                ->sortBy([
                    ['tanggal_aksi', 'asc'],
                    ['id', 'asc'],
                ])
                ->map(function ($flow): array {
                    $label = match (true) {
                        $flow->status === 'approved' && $flow->role === 'admin' => 'Divalidasi Admin',
                        $flow->status === 'approved' && $flow->role === 'kaprodi' => 'Disetujui Kaprodi',
                        $flow->status === 'approved' && $flow->role === 'dekan' => 'Disetujui Dekan',
                        $flow->status === 'rejected_final' && $flow->role === 'admin' => 'Ditolak Admin',
                        $flow->status === 'revision_requested' && $flow->role === 'kaprodi' => 'Dikembalikan Kaprodi',
                        $flow->status === 'revision_requested' && $flow->role === 'dekan' => 'Dikembalikan Dekan',
                        $flow->status === 'rejected_final' && $flow->role === 'kaprodi' => 'Ditolak Kaprodi',
                        $flow->status === 'rejected_final' && $flow->role === 'dekan' => 'Ditolak Dekan',
                        $flow->status === 'note' => 'Catatan Approval',
                        default => (string) ($flow->keterangan ?? 'Riwayat Approval'),
                    };

                    return [
                        'id' => $flow->id,
                        'role' => $flow->role,
                        'status' => $flow->status,
                        'label' => $label,
                        'note' => $flow->catatan,
                        'acted_at' => optional($flow->tanggal_aksi ?? $flow->created_at)?->toISOString(),
                        'actor' => $flow->approver?->name,
                    ];
                })
                ->values(),
            'history_timeline' => $surat->histories()
                ->with('user:id,name')
                ->latest('created_at')
                ->latest('id')
                ->get()
                ->map(function ($history): array {
                    return [
                        'id' => $history->id,
                        'action' => $history->action,
                        'label' => match ($history->action) {
                            'rejected' => (string) ($history->action_label ?: 'Ditolak'),
                            'revised' => (string) ($history->action_label ?: 'Dikembalikan untuk Revisi'),
                            default => $history->action_label,
                        },
                        'description' => $history->keterangan,
                        'actor' => $history->user?->name,
                        'created_at' => optional($history->created_at)?->toISOString(),
                    ];
                })
                ->values(),
            'approval_notes' => $surat->approvalFlows
                ->filter(fn ($flow) => filled($flow->catatan))
                ->sortBy([
                    ['tanggal_aksi', 'asc'],
                    ['id', 'asc'],
                ])
                ->map(fn ($flow): array => [
                    'role' => $flow->role,
                    'status' => $flow->status,
                    'label' => match (true) {
                        $flow->status === Surat::STATUS_REVISION_REQUESTED && $flow->role === 'kaprodi' => 'Catatan Revisi Kaprodi',
                        $flow->status === Surat::STATUS_REVISION_REQUESTED && $flow->role === 'dekan' => 'Catatan Revisi Dekan',
                        $flow->status === SuratApprovalFlow::STATUS_REJECTED_FINAL && $flow->role === 'kaprodi' => 'Catatan Penolakan Kaprodi',
                        $flow->status === SuratApprovalFlow::STATUS_REJECTED_FINAL && $flow->role === 'dekan' => 'Catatan Penolakan Dekan',
                        $flow->status === Surat::STATUS_NOTE => 'Catatan Approval',
                        default => 'Catatan Approval',
                    },
                    'note' => $flow->catatan,
                    'acted_at' => optional($flow->tanggal_aksi ?? $flow->created_at)?->toISOString(),
                    'actor' => $flow->approver?->name,
                ])
                ->values(),
            'can_approve' => $surat->canBeApprovedByRole($this->normalizeRole($request->user()?->userTypeSlug(), $request->user()?->roleDisplayName())),
            'can_request_revision' => $surat->canRequestRevisionByRole($this->normalizeRole($request->user()?->userTypeSlug(), $request->user()?->roleDisplayName())),
            'can_final_reject' => $surat->canBeFinalRejectedByRole($this->normalizeRole($request->user()?->userTypeSlug(), $request->user()?->roleDisplayName())),
            'previewTemplateUrl' => route('documents.surat.template-preview', $surat->id, absolute: false),
            'generatedDocumentUrl' => filled($surat->nomor_surat) || filled($surat->rendered_snapshot)
                ? (
                    $surat->status === Surat::STATUS_FINISHED
                        ? route('documents.surat.pdf', $surat->id, absolute: false)
                        : route('documents.surat.generated-document', $surat->id, absolute: false)
                )
                : null,
            'back_href' => null,
            'back_label' => null,
        ];
    }

    protected function serializeShowItem(Surat $surat): array
    {
        $isiSurat = json_decode((string) $surat->isi_surat, true);

        return [
            'id' => $surat->id,
            'nomor_surat' => $surat->nomor_surat,
            'pemohon' => [
                'name' => $surat->pemohon?->name,
                'nim' => $surat->pemohon?->nim_nip ?? $surat->pemohon?->nomor_induk,
            ],
            'jenis_surat' => $surat->jenisSurat?->nama,
            'keperluan' => $surat->keperluan,
            'isi_surat' => is_array($isiSurat) ? $isiSurat : [],
            'lampiran' => $surat->lampirans->map(fn ($lampiran): array => [
                'id' => $lampiran->id,
                'name' => $lampiran->nama_file,
                'url' => route('approval.lampiran.preview', $lampiran->id, absolute: false),
                'type' => $lampiran->tipe,
            ])->values(),
            'approval_notes' => $surat->approvalFlows
                ->filter(fn ($flow) => filled($flow->catatan))
                ->sortBy([
                    ['tanggal_aksi', 'asc'],
                    ['id', 'asc'],
                ])
                ->map(fn ($flow): array => [
                    'role' => $flow->role,
                    'status' => $flow->status,
                    'label' => match (true) {
                        $flow->status === Surat::STATUS_REVISION_REQUESTED && $flow->role === 'kaprodi' => 'Catatan Revisi Kaprodi',
                        $flow->status === Surat::STATUS_REVISION_REQUESTED && $flow->role === 'dekan' => 'Catatan Revisi Dekan',
                        $flow->status === SuratApprovalFlow::STATUS_REJECTED_FINAL && $flow->role === 'kaprodi' => 'Catatan Penolakan Kaprodi',
                        $flow->status === SuratApprovalFlow::STATUS_REJECTED_FINAL && $flow->role === 'dekan' => 'Catatan Penolakan Dekan',
                        $flow->status === Surat::STATUS_NOTE => 'Catatan Approval',
                        default => 'Catatan Approval',
                    },
                    'note' => $flow->catatan,
                    'acted_at' => optional($flow->tanggal_aksi ?? $flow->created_at)?->toISOString(),
                    'actor' => $flow->approver?->name,
                ])
                ->values(),
            'tanggal_pengajuan' => optional($surat->tanggal_pengajuan ?? $surat->created_at)?->toISOString(),
            'status' => $surat->status,
            'draft_preview_url' => filled($surat->nomor_surat) || filled($surat->rendered_snapshot)
                ? route('documents.surat.generated-document', $surat->id, absolute: false)
                : null,
        ];
    }

    protected function normalizeRole(?string $slug, ?string $name): string
    {
        $normalizedSlug = Str::slug((string) $slug);
        $normalizedName = Str::slug((string) $name);

        if (Str::contains($normalizedSlug, 'kaprodi') || Str::contains($normalizedName, 'kaprodi')) {
            return FastApprovalWorkflowService::ROLE_KAPRODI;
        }

        return FastApprovalWorkflowService::ROLE_DEKAN;
    }

    protected function approvedStatusForRole(string $role): string
    {
        return $role === FastApprovalWorkflowService::ROLE_KAPRODI
            ? Surat::STATUS_APPROVED_KAPRODI
            : Surat::STATUS_APPROVED_DEKAN;
    }

    /**
     * @return array<int, string>
     */
    protected function approvedStatusesForRole(string $role): array
    {
        return [
            $this->approvedStatusForRole($role),
            Surat::STATUS_FINISHED,
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    protected function archiveStatusOptions(string $normalizedRole): array
    {
        return [
            ['value' => 'approved', 'label' => 'Disetujui'],
            ['value' => Surat::STATUS_REVISION_REQUESTED, 'label' => 'Revisi Diminta'],
            ['value' => Surat::STATUS_REJECTED_APPROVER, 'label' => 'Ditolak Final'],
        ];
    }

    protected function baseSummaryQuery(string $normalizedRole)
    {
        return $this->baseQueryForRole($normalizedRole);
    }

    protected function baseQueryForRole(string $normalizedRole)
    {
        return Surat::query()
            ->with(['pemohon', 'jenisSurat.approvalRole'])
            ->whereHas('jenisSurat.approvalRole', function ($roleQuery) use ($normalizedRole): void {
                $roleQuery
                    ->where('slug', 'like', "%{$normalizedRole}%")
                    ->orWhere('nama', 'like', "%{$normalizedRole}%");
            });
    }

    /**
     * @return array<int, string>
     */
    protected function waitingStatusesForRole(string $normalizedRole): array
    {
        return [Surat::STATUS_VALIDATED_ADMIN];
    }
}
