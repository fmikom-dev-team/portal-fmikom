<?php

namespace App\Modules\Fast\Services\Admin;

use App\Models\Surat;
use App\Models\SuratApprovalFlow;
use App\Modules\Fast\Services\Shared\OutgoingLetterAttachmentService;
use App\Modules\Fast\Support\FastUserIdentitySearch;
use App\Modules\Fast\Support\TemplateAdminSupport;
use App\Modules\Fast\Template\Renderers\SuratTemplateRendererService;
use App\Support\FastStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardService
{
    public function __construct(
        protected TemplateAdminSupport $templateAdminSupport,
        protected SuratTemplateRendererService $templateRenderer,
        protected ApprovalService $approvalService,
        protected OutgoingLetterAttachmentService $outgoingAttachmentService,
    ) {}

    public function index(Request $request): Response
    {
        $query = Surat::query()
            ->with(['pemohon', 'subjectUser', 'jenisSurat', 'dataEntries'])
            ->where('type', 'pengajuan')
            ->whereIn('status', [
                Surat::STATUS_PENDING,
            ]);

        $search = $request->string('search')->trim()->toString();
        $categoryId = $request->integer('category_id');

        if ($categoryId > 0) {
            $query->whereHas('jenisSurat', function ($jenisSuratQuery) use ($categoryId): void {
                $jenisSuratQuery->where('category_id', $categoryId);
            });
        }

        if ($search !== '') {
            $query->whereHas('pemohon', function ($pemohonQuery) use ($search): void {
                FastUserIdentitySearch::apply($pemohonQuery, $search);
                $pemohonQuery->orWhere('email', 'like', "%{$search}%");
            });
        }

        $surats = $query
            ->orderByDesc('tanggal_pengajuan')
            ->orderByDesc('created_at')
            ->paginate(6)
            ->through(fn (Surat $surat): array => [
                'id' => $surat->id,
                'type' => $surat->type,
                'status' => $surat->status,
                'can_approve' => $surat->canBeValidatedByAdmin(),
                'can_edit' => $surat->canBeEditedByAdmin(),
                'needs_admin_completion' => $surat->hasIncompleteCampusData(),
                'tanggal_pengajuan' => optional($surat->tanggal_pengajuan ?? $surat->created_at)?->toISOString(),
                'created_at' => optional($surat->created_at)?->toISOString(),
                'subject' => $surat->serializeSubjectIdentity(),
                'pemohon' => $surat->serializePemohonIdentity(),
                'jenisSurat' => [
                    'id' => $surat->jenisSurat?->id,
                    'nama' => $surat->jenisSurat?->nama,
                ],
            ])
            ->withQueryString();

        $adminActivityHistory = Surat::query()
            ->with(['subjectUser', 'jenisSurat'])
            ->where('type', 'surat_keluar')
            ->where('status', Surat::STATUS_FINISHED)
            ->orderByDesc('tanggal_selesai')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get()
            ->map(fn (Surat $surat): array => [
                'id' => $surat->id,
                'type' => $surat->type,
                'nomor_surat' => $surat->nomor_surat,
                'keperluan' => $surat->keperluan,
                'tanggal_selesai' => optional($surat->tanggal_selesai ?? $surat->created_at)?->toISOString(),
                'subject' => $surat->serializeSubjectIdentity(),
                'jenisSurat' => [
                    'nama' => $surat->jenisSurat?->nama,
                ],
            ]);

        return Inertia::render('admin/dashboard/Index', [
            'surats' => $surats,
            'summary' => (function (): array {
                $counts = Surat::query()
                    ->where('type', 'pengajuan')
                    ->selectRaw('
                        COUNT(*) as total,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as validated,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as finished,
                        SUM(CASE WHEN status IN (?,?) THEN 1 ELSE 0 END) as rejected,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled
                    ', [
                        Surat::STATUS_PENDING,
                        Surat::STATUS_VALIDATED_ADMIN,
                        Surat::STATUS_FINISHED,
                        Surat::STATUS_REJECTED_ADMIN,
                        Surat::STATUS_REJECTED_APPROVER,
                        Surat::STATUS_CANCELLED,
                    ])
                    ->first();

                $total = (int) ($counts->total ?? 0);
                $cancelled = (int) ($counts->cancelled ?? 0);

                return [
                    'total' => $total - $cancelled,
                    'pending' => (int) ($counts->pending ?? 0),
                    'validated' => (int) ($counts->validated ?? 0),
                    'finished' => (int) ($counts->finished ?? 0),
                    'rejected' => (int) ($counts->rejected ?? 0),
                    'cancelled' => $cancelled,
                ];
            })(),
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId > 0 ? (string) $categoryId : '',
            ],
            'categories' => $this->templateAdminSupport->listCategories(),
            'adminActivity' => [
                'recent' => $adminActivityHistory,
            ],
            'links' => [
                'submissionsIndex' => $this->adminBasePath().'/surat',
                'archiveIndex' => $this->adminBasePath().'/archive',
            ],
        ]);
    }

    public function show(int $id): Response
    {
        $surat = Surat::query()
            ->with(['pemohon', 'subjectUser', 'jenisSurat', 'lampirans', 'approvalFlows'])
            ->findOrFail($id);

        $isiSurat = json_decode((string) $surat->isi_surat, true);

        return Inertia::render('admin/dashboard/Show', [
            'id' => $surat->id,
            'type' => $surat->type,
            'nomor_surat' => $surat->nomor_surat,
            'letter_mode' => $surat->resolvedLetterMode(),
            'letter_mode_label' => $surat->letterModeLabel(),
            'is_institution' => $surat->resolvedLetterMode() === 'institution',
            'subject' => $surat->serializeSubjectIdentity(),
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
            'hasAttachmentDocument' => $this->outgoingAttachmentService->hasStudentAttachment($surat),
            'latest_rejection' => (function () use ($surat): ?array {
                $latestRevisionFlow = $surat->latestRevisionRequestFlow();
                $latestAdminRejectionFlow = $surat->latestAdminRejectionFlow();
                $latestApproverFinalRejectionFlow = $surat->latestApproverFinalRejectionFlow();
                $latestRejectedFlow = $latestRevisionFlow ?? $latestAdminRejectionFlow ?? $latestApproverFinalRejectionFlow;

                if ($latestRejectedFlow === null) {
                    return null;
                }

                $isRevisionRequest = $latestRejectedFlow->status === SuratApprovalFlow::STATUS_REVISION_REQUESTED;

                return [
                    'role' => $latestRejectedFlow->role,
                    'label' => 'Ditolak Final',
                    'type' => $isRevisionRequest ? 'revision' : 'final_reject',
                    'note' => $latestRejectedFlow->catatan,
                    'acted_at' => optional($latestRejectedFlow->tanggal_aksi ?? $latestRejectedFlow->created_at)?->toISOString(),
                ];
            })(),
            'approval_timeline' => $surat->approvalFlows
                ->sortBy([
                    ['tanggal_aksi', 'asc'],
                    ['id', 'asc'],
                ])
                ->map(function ($flow) use ($surat): array {
                    $isInstitutionLetter = $surat->resolvedLetterMode() === 'institution';
                    $label = match (true) {
                        $flow->status === 'approved' && $flow->role === 'admin' => $isInstitutionLetter ? 'Diajukan Admin' : 'Divalidasi Admin',
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
            'can_approve' => $surat->canBeValidatedByAdmin(),
            'can_edit' => $surat->canBeEditedByAdmin(),
            'previewTemplateUrl' => $surat->canViewFinalDocumentPreview()
                ? route('documents.surat.template-preview', $surat->id, absolute: false)
                : null,
            'generatedDocumentUrl' => $surat->canViewFinalDocumentPreview()
                ? route('documents.surat.generated-document', $surat->id, absolute: false)
                : null,
            'attachmentPreviewUrl' => $surat->canViewFinalDocumentPreview() && $this->outgoingAttachmentService->hasStudentAttachment($surat)
                ? route('documents.surat.attachment-document', $surat->id, absolute: false)
                : null,
        ]);
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
            'size',
            'original_name',
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
            || str_contains($key, 'penandatangan');
    }

    protected function decodeJsonPayload(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    protected function adminBasePath(): string
    {
        $role = strtolower((string) (auth()->user()?->getResolvedRoleSlug() ?? auth()->user()?->getGlobalRoleSlug() ?? 'admin'));

        return in_array($role, ['kaprodi', 'dekan'], true) ? "/{$role}/admin" : '/admin';
    }

    public function previewTemplate(int $id): SymfonyResponse
    {
        $surat = Surat::query()
            ->with(['pemohon', 'jenisSurat.template.placeholders', 'dataEntries'])
            ->findOrFail($id);
        abort_unless($surat->canViewFinalDocumentPreview(), 404, 'Pratinjau surat hanya tersedia setelah surat final.');
        $jenisSurat = $surat->jenisSurat;
        $jenisSuratName = $jenisSurat ? $jenisSurat->nama : 'Surat';
        $template = $jenisSurat?->template;

        if ($template === null && filled($surat->rendered_snapshot)) {
            return response(
                $this->templateRenderer->wrapDocumentHtml(
                    'Preview '.$jenisSuratName,
                    (string) $surat->rendered_snapshot,
                    null,
                ),
                200,
            )->header('Content-Type', 'text/html; charset=UTF-8');
        }

        $rendered = $this->templateRenderer->renderForSurat($surat, true, 'pdf');

        return response(
            $this->templateRenderer->wrapDocumentHtml('Preview '.$jenisSuratName, $rendered['html'], $template),
            200,
        )->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function previewGeneratedDocument(int $id): SymfonyResponse|StreamedResponse
    {
        $surat = Surat::query()
            ->with(['pemohon', 'jenisSurat.template.placeholders', 'dataEntries'])
            ->findOrFail($id);
        abort_unless($surat->canViewFinalDocumentPreview(), 404, 'Pratinjau surat hanya tersedia setelah surat final.');
        $jenisSurat = $surat->jenisSurat;
        $jenisSuratName = $jenisSurat ? $jenisSurat->nama : 'surat';
        $template = $jenisSurat?->template;

        $filename = sprintf(
            '%s-%d.pdf',
            str_replace(' ', '-', strtolower($jenisSuratName)),
            $surat->id,
        );

        if (
            $surat->status === Surat::STATUS_FINISHED &&
            filled($surat->generated_file_path) &&
            FastStorage::exists($surat->generated_file_path)
        ) {
            return FastStorage::response(
                $surat->generated_file_path,
                $filename,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="'.addslashes($filename).'"',
                    'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                ],
            );
        }

        abort_if($surat->status === Surat::STATUS_FINISHED, 404, 'File PDF final tidak ditemukan.');

        if (filled($surat->rendered_snapshot)) {
            return response(
                $this->templateRenderer->wrapDocumentHtml(
                    ucfirst($jenisSuratName).' - '.($surat->nomor_surat ?? ''),
                    (string) $surat->rendered_snapshot,
                    $template,
                ),
                200,
            )->header('Content-Type', 'text/html; charset=UTF-8');
        }

        $rendered = $this->templateRenderer->renderForSurat($surat, true, 'pdf');

        return response(
            $this->templateRenderer->wrapDocumentHtml(
                ucfirst($jenisSuratName).' - '.($surat->nomor_surat ?? ''),
                $rendered['html'],
                $template,
            ),
            200,
        )->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function previewAttachment(int $id): SymfonyResponse|StreamedResponse
    {
        return $this->approvalService->previewAttachment($id);
    }

    public function previewAttachmentDocument(int $id): SymfonyResponse|StreamedResponse
    {
        $surat = Surat::query()
            ->with(['jenisSurat', 'dataEntries'])
            ->findOrFail($id);
        abort_unless($surat->canViewFinalDocumentPreview(), 404, 'Pratinjau lampiran hanya tersedia setelah surat final.');

        $columns = $this->outgoingAttachmentService->extractAttachmentColumnsFromSurat($surat);
        $rows = $this->outgoingAttachmentService->extractAttachmentRowsFromSurat($surat);
        abort_if($this->outgoingAttachmentService->extractAttachmentMode($surat) !== 'student_list' || $columns === [] || $rows === [], 404, 'Lampiran daftar mahasiswa tidak tersedia.');

        $path = $this->outgoingAttachmentService->ensureGeneratedPdf($surat);
        $filename = $this->outgoingAttachmentService->attachmentFileName($surat);

        if ($path !== null && FastStorage::exists($path)) {
            return FastStorage::response(
                $path,
                $filename,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="'.addslashes($filename).'"',
                    'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                ],
            );
        }

        return response(
            $this->outgoingAttachmentService->buildPreviewHtmlForSurat($surat, $columns, $rows),
            200,
        )->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function downloadPdf(Request $request, int $id): SymfonyResponse
    {
        $user = $request->user();
        $surat = Surat::query()
            ->with(['pemohon', 'jenisSurat.template.placeholders', 'dataEntries'])
            ->findOrFail($id);

        $filename = sprintf(
            '%s-%d.pdf',
            str_replace(' ', '-', strtolower((string) ($surat->jenisSurat?->nama ?: 'surat'))),
            $surat->id,
        );
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.addslashes($filename).'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        if (
            $surat->status === Surat::STATUS_FINISHED &&
            filled($surat->generated_file_path) &&
            FastStorage::exists($surat->generated_file_path)
        ) {
            return FastStorage::response(
                $surat->generated_file_path,
                $filename,
                $headers,
            );
        }

        abort_if($user === null, 403);
        abort(404, 'PDF final belum tersedia.');
    }

    public function downloadAttachmentPdf(Request $request, int $id): SymfonyResponse
    {
        return $this->downloadPdf($request, $id);
    }
}
