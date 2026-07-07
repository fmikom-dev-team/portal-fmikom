<?php

namespace App\Modules\Fast\Services\Admin;

use App\Models\Surat;
use App\Models\SuratApprovalFlow;
use App\Models\SuratLampiran;
use App\Modules\Fast\Services\Shared\OutgoingLetterAttachmentService;
use App\Modules\Fast\Support\FastUserIdentitySearch;
use App\Modules\Fast\Support\TemplateAdminSupport;
use App\Modules\Fast\Workflow\Approvals\FastApprovalWorkflowService;
use App\Support\DocxPreview;
use App\Support\FastStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApprovalService
{
    public function __construct(
        protected TemplateAdminSupport $templateAdminSupport,
        protected OutgoingLetterAttachmentService $outgoingAttachmentService,
    ) {}

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
            $this->applySubjectSearch($query, $search);
        }

        $surats = $query
            ->latest()
            ->paginate(10)
            ->through(fn (Surat $surat): array => $this->serializeSuratListItem($surat))
            ->withQueryString();

        return inertia('approval/Index', [
            'context' => [
                'active_module' => 'FAST',
                'active_role' => $normalizedRole,
            ],
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
            Surat::STATUS_APPROVED_KAPRODI,
            Surat::STATUS_APPROVED_DEKAN,
            Surat::STATUS_FINISHED,
        ];

        $effectiveStatuses = match ($status) {
            '', 'pending' => [Surat::STATUS_VALIDATED_ADMIN],
            'all' => $queueStatuses,
            'finished' => [Surat::STATUS_FINISHED],
            'revision_requested' => [Surat::STATUS_REVISION_REQUESTED],
            'rejected' => [Surat::STATUS_REJECTED_ADMIN, Surat::STATUS_REJECTED_APPROVER],
            default => [Surat::STATUS_VALIDATED_ADMIN],
        };

        $query = $this->baseQueryForRole($normalizedRole)
            ->whereIn('status', $effectiveStatuses);

        if ($search !== '') {
            $this->applySubjectSearch($query, $search);
        }

        if ($categoryId > 0) {
            $query->whereHas('jenisSurat', function ($jenisQuery) use ($categoryId): void {
                $jenisQuery->where('category_id', $categoryId);
            });
        }

        $surats = $query->latest()->paginate(10)->through(fn (Surat $surat): array => $this->serializeSuratListItem($surat))->withQueryString();

        return inertia('approval/Queue', [
            'context' => [
                'active_module' => 'FAST',
                'active_role' => $normalizedRole,
            ],
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
            $this->applySubjectSearch($query, $search);
        }

        $surats = $query
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->through(fn (Surat $surat): array => $this->serializeArchiveItem($surat))
            ->withQueryString();

        return inertia('approval/Archive', [
            'context' => [
                'active_module' => 'FAST',
                'active_role' => $normalizedRole,
            ],
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
            $this->applySubjectSearch($query, $search);
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
            'context' => [
                'active_module' => 'FAST',
                'active_role' => $normalizedRole,
            ],
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
                'subjectUser',
                'jenisSurat.approvalRole',
                'lampirans',
                'dataEntries',
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

        return inertia('approval/Show', array_merge($this->serializeDetailItem($surat, $request), [
            'context' => [
                'active_module' => 'FAST',
                'active_role' => $normalizedRole,
            ],
            'role' => [
                'name' => $roleName,
                'slug' => $roleSlug,
            ],
            'back_href' => $backHref,
            'back_label' => $backLabel,
        ]));
    }

    public function show(int $id): array
    {
        $surat = Surat::query()
            ->with(['subjectUser', 'jenisSurat', 'lampirans', 'approvalFlows.approver'])
            ->findOrFail($id);

        return $this->serializeShowItem($surat);
    }

    public function previewAttachment(int $id): SymfonyResponse|StreamedResponse
    {
        $lampiran = SuratLampiran::query()->findOrFail($id);
        $located = FastStorage::locate($lampiran->file_path);

        if ($located !== null && DocxPreview::isDocx($located['relative_path'], $located['mime_type'])) {
            try {
                return response(
                    DocxPreview::renderHtml($located['absolute_path'], $lampiran->nama_file),
                    200,
                )->header('Content-Type', 'text/html; charset=UTF-8');
            } catch (\Throwable) {
                // Fall through to the file response below when DOCX parsing fails.
            }
        }

        return FastStorage::response(
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

        $resolvedRole = $request->attributes->get('resolved_role');

        if (is_string($resolvedRole) && filled($resolvedRole)) {
            $roleSlug = $resolvedRole;
            $roleName = Str::headline(str_replace('-', ' ', $roleSlug));
        } else {
            $roleName = $user->roleDisplayName() ?: 'Approval';
            $roleSlug = $user->userTypeSlug() ?: 'approval';
        }

        $normalizedRole = $this->normalizeRole($roleSlug, $roleName);

        return [$user, $roleName, $roleSlug, $normalizedRole];
    }

    protected function serializeSuratListItem(Surat $surat): array
    {
        $letterMode = $surat->serializeLetterMode();

        return [
            'id' => $surat->id,
            'type' => $surat->type,
            'status' => $surat->status,
            'tanggal_pengajuan' => optional($surat->tanggal_pengajuan ?? $surat->created_at)?->toISOString(),
            'created_at' => optional($surat->created_at)?->toISOString(),
            'subject' => $surat->serializeSubjectIdentity(),
            'letter_mode' => $letterMode['mode'],
            'letter_mode_label' => $letterMode['label'],
            'is_institution' => $letterMode['is_institution'],
            'jenisSurat' => [
                'id' => $surat->jenisSurat?->id,
                'nama' => $surat->jenisSurat?->nama,
            ],
            'download_url' => $surat->canViewFinalDocumentPreview()
                ? route('documents.surat.pdf', $surat->id, absolute: false)
                : null,
        ];
    }

    protected function serializeArchiveItem(Surat $surat): array
    {
        return array_merge($this->serializeSuratListItem($surat), [
            'nomor_surat' => $surat->nomor_surat,
            'download_url' => $surat->canViewFinalDocumentPreview()
                ? route('documents.surat.pdf', $surat->id, absolute: false)
                : null,
        ]);
    }

    protected function serializeDownloadItem(Surat $surat): array
    {
        return array_merge($this->serializeArchiveItem($surat), [
            'tanggal_selesai' => optional($surat->tanggal_selesai)?->toISOString(),
            'download_url' => $surat->generated_file_path
                ? route('documents.surat.pdf', $surat->id, absolute: false)
                : null,
        ]);
    }

    protected function serializeDetailItem(Surat $surat, Request $request): array
    {
        $isiSurat = json_decode((string) $surat->isi_surat, true);
        $latestRejectedFlow = $surat->latestRejectedFlow();
        $letterMode = $surat->serializeLetterMode();

        return [
            'id' => $surat->id,
            'type' => $surat->type,
            'nomor_surat' => $surat->nomor_surat,
            'subject' => $surat->serializeSubjectIdentity(),
            'letter_mode' => $letterMode['mode'],
            'letter_mode_label' => $letterMode['label'],
            'is_institution' => $letterMode['is_institution'],
            'jenis_surat' => $surat->jenisSurat?->nama,
            'keperluan' => $surat->keperluan,
            'isi_surat' => is_array($isiSurat) ? $isiSurat : [],
            'detail_data' => $this->buildDetailData($surat),
            'lampiran' => $surat->lampirans->map(fn ($lampiran): array => [
                'id' => $lampiran->id,
                'name' => $lampiran->nama_file,
                'url' => URL::temporarySignedRoute(
                    'documents.public.lampiran.preview',
                    now()->addMinutes(15),
                    ['id' => $lampiran->id],
                ),
                'type' => $lampiran->tipe,
            ])->values(),
            'tanggal_pengajuan' => optional($surat->tanggal_pengajuan ?? $surat->created_at)?->toISOString(),
            'status' => $surat->status,
            'pdfUrl' => $surat->canViewFinalDocumentPreview()
                ? route('documents.surat.pdf', $surat->id, absolute: false)
                : null,
            'canDownloadPdf' => $surat->canViewFinalDocumentPreview(),
            'hasAttachmentDocument' => $this->outgoingAttachmentService->hasStudentAttachment($surat),
            'attachmentPreviewUrl' => $surat->canViewFinalDocumentPreview() && $this->outgoingAttachmentService->hasStudentAttachment($surat)
                ? route('documents.surat.attachment-document', $surat->id, absolute: false)
                : null,
            'latest_rejection' => $latestRejectedFlow === null ? null : [
                'role' => $latestRejectedFlow->role,
                'label' => 'Ditolak Final',
                'type' => $latestRejectedFlow->status === Surat::STATUS_REVISION_REQUESTED ? 'revision' : 'final_reject',
                'note' => $latestRejectedFlow->catatan,
                'acted_at' => optional($latestRejectedFlow->tanggal_aksi ?? $latestRejectedFlow->created_at)?->toISOString(),
            ],
            'approval_timeline' => $surat->approvalFlows
                ->sortBy([
                    ['tanggal_aksi', 'asc'],
                    ['id', 'asc'],
                ])
                ->map(function ($flow) use ($surat): array {
                    $isAdminInitiatedInstitutionLetter =
                        $surat->type === 'surat_keluar'
                        && $surat->created_by !== null
                        && $surat->resolvedLetterMode() === 'institution';

                    $label = match (true) {
                        $flow->status === 'approved' && $flow->role === 'admin' => $isAdminInitiatedInstitutionLetter
                            ? 'Diajukan Admin'
                            : 'Divalidasi Admin',
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
                        $flow->status === SuratApprovalFlow::STATUS_NOTE => 'Catatan Approval',
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
            'previewTemplateUrl' => $surat->canViewFinalDocumentPreview()
                ? route('documents.surat.template-preview', $surat->id, absolute: false)
                : null,
            'generatedDocumentUrl' => $surat->canViewFinalDocumentPreview()
                ? route('documents.surat.generated-document', $surat->id, absolute: false)
                : null,
            'back_href' => null,
            'back_label' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildDetailData(Surat $surat): array
    {
        $entries = $surat->dataEntries
            ->mapWithKeys(function ($entry): array {
                $decoded = $this->decodeJsonPayload($entry->field_value);

                return [
                    (string) $entry->field_name => $decoded,
                ];
            })
            ->all();

        if (! empty($entries)) {
            return $this->filterDetailData($entries);
        }

        $decoded = $this->decodeJsonPayload($surat->isi_surat);
        if (is_array($decoded)) {
            return $this->filterDetailData($decoded);
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function filterDetailData(array $data): array
    {
        $labels = [];

        foreach ($data as $key => $value) {
            $name = strtolower(trim((string) $key));

            if ($this->isTechnicalDetailKey($name)) {
                continue;
            }

            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            $labels[$key] = $value;
        }

        return $labels;
    }

    protected function isTechnicalDetailKey(string $key): bool
    {
        $technical = [
            'id',
            'surat_id',
            'jenis_surat_id',
            'pemohon_id',
            'type',
            'status',
            'created_at',
            'updated_at',
            'deleted_at',
            'generated_at',
            'generated_file_path',
            'generated_file_type',
            'rendered_snapshot',
            'template_version',
            'qr_token',
            'qr_validated_at',
            'validated_by_admin_id',
            'validated_by_admin_at',
            'approved_by_id',
            'approved_at',
            'file_path',
            'path',
            'url',
            'token',
            'slug',
            'nama_file',
            'nama_asli',
            'mime_type',
            'field_name',
            'field_value',
            'approval_role',
            'approval_role_id',
            'approvalrole',
            'approval',
            'meta',
            'metadata',
            'catatan_revisi',
            'rejection_reason',
            'admin_note',
            'subject_name',
            'perihal',
            'kepada_yth',
            'nama_penanda_tangan',
            'nik_penanda_tangan',
            'jabatan_penanda_tangan',
            'nama_penandatangan',
            'nik_penandatangan',
            'jabatan_penandatangan',
            'nip_dosen',
            'nip_kaprodi',
            'nip_dekan',
            'lampiran_keterangan',
            'lampiran_judul',
            'lampiran_judul_align',
            'lampiran_judul_bold',
            'lampiran_label_no',
            'lampiran_label_nama',
            'lampiran_label_nim',
            'lampiran_label_prodi',
            'lampiran_mode',
            'lampiran_orientation',
            'lampiran_mahasiswa',
            'lampiran_columns',
            'lampiran_rows',
        ];

        if (in_array($key, $technical, true)) {
            return true;
        }

        return str_contains($key, 'tanda_tangan')
            || str_contains($key, 'penanda_tangan')
            || str_contains($key, 'penandatangan')
            || str_starts_with($key, '_')
            || $key === 'id'
            || str_ends_with($key, '_id')
            || str_contains($key, 'created_at')
            || str_contains($key, 'updated_at')
            || $key === 'status'
            || str_contains($key, 'token')
            || str_contains($key, 'path')
            || str_contains($key, 'url')
            || str_contains($key, 'file');
    }

    /**
     * @return array<string, mixed>|string|int|float|bool|null
     */
    protected function decodeJsonPayload(mixed $value): mixed
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    protected function serializeShowItem(Surat $surat): array
    {
        $isiSurat = json_decode((string) $surat->isi_surat, true);
        $letterMode = $surat->serializeLetterMode();

        return [
            'id' => $surat->id,
            'type' => $surat->type,
            'nomor_surat' => $surat->nomor_surat,
            'subject' => $surat->serializeSubjectIdentity(),
            'letter_mode' => $letterMode['mode'],
            'letter_mode_label' => $letterMode['label'],
            'is_institution' => $letterMode['is_institution'],
            'jenis_surat' => $surat->jenisSurat?->nama,
            'keperluan' => $surat->keperluan,
            'isi_surat' => is_array($isiSurat) ? $isiSurat : [],
            'lampiran' => $surat->lampirans->map(fn ($lampiran): array => [
                'id' => $lampiran->id,
                'name' => $lampiran->nama_file,
                'url' => URL::temporarySignedRoute(
                    'documents.public.lampiran.preview',
                    now()->addMinutes(15),
                    ['id' => $lampiran->id],
                ),
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
                        $flow->status === SuratApprovalFlow::STATUS_NOTE => 'Catatan Approval',
                        default => 'Catatan Approval',
                    },
                    'note' => $flow->catatan,
                    'acted_at' => optional($flow->tanggal_aksi ?? $flow->created_at)?->toISOString(),
                    'actor' => $flow->approver?->name,
                ])
                ->values(),
            'tanggal_pengajuan' => optional($surat->tanggal_pengajuan ?? $surat->created_at)?->toISOString(),
            'status' => $surat->status,
            'draft_preview_url' => $surat->canViewFinalDocumentPreview()
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
            ->with(['pemohon', 'subjectUser', 'jenisSurat.approvalRole'])
            ->whereHas('jenisSurat.approvalRole', function ($roleQuery) use ($normalizedRole): void {
                $roleQuery
                    ->where('slug', 'like', "%{$normalizedRole}%")
                    ->orWhere('nama', 'like', "%{$normalizedRole}%");
            });
    }

    protected function applySubjectSearch($query, string $search): void
    {
        $query->where(function ($builder) use ($search): void {
            $builder
                ->where(function ($typeQuery) use ($search): void {
                    $typeQuery
                        ->where('type', 'pengajuan')
                        ->whereHas('pemohon', function ($pemohonQuery) use ($search): void {
                            FastUserIdentitySearch::apply($pemohonQuery, $search);
                        });
                })
                ->orWhere(function ($typeQuery) use ($search): void {
                    $typeQuery
                        ->where('type', 'surat_keluar')
                        ->whereHas('subjectUser', function ($subjectQuery) use ($search): void {
                            FastUserIdentitySearch::apply($subjectQuery, $search);
                        });
                });
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
