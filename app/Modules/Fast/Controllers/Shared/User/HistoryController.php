<?php

namespace App\Modules\Fast\Controllers\Shared\User;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use App\Models\SuratApprovalFlow;
use App\Models\SuratHistory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

class HistoryController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        abort_if($user === null, 403);
        $this->authorize('viewAny', Surat::class);

        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();

        $surats = Surat::query()
            ->with([
                'jenisSurat.approvalRole',
                'approvalFlows' => fn ($q) => $q->latest('tanggal_aksi')->latest('id'),
                'histories' => fn ($q) => $q->latest('created_at')->latest('id')->limit(8),
            ])
            ->where('pemohon_id', $user->id)
            ->when($search, function ($q) use ($search): void {
                $q->where(function ($searchQuery) use ($search): void {
                    $searchQuery
                        ->whereHas('jenisSurat', fn ($j) => $j->where('nama', 'like', "%{$search}%"))
                        ->orWhere('keperluan', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($q) use ($status): void {
                match ($status) {
                    'pending' => $q->whereIn('status', [
                        Surat::STATUS_PENDING,
                        Surat::STATUS_VALIDATED_ADMIN,
                        Surat::STATUS_APPROVED_KAPRODI,
                        Surat::STATUS_APPROVED_DEKAN,
                    ]),
                    'finished' => $q->where('status', Surat::STATUS_FINISHED),
                    'rejected_admin' => $q->where('status', Surat::STATUS_REJECTED_ADMIN),
                    'rejected_approver' => $q->where('status', Surat::STATUS_REJECTED_APPROVER),
                    'cancelled' => $q->where('status', Surat::STATUS_CANCELLED),
                    default => $q->where('status', $status),
                };
            })
            ->latest('tanggal_pengajuan')
            ->latest('id')
            ->paginate(10)
            ->through(fn (Surat $surat): array => $this->transformSubmission($surat))
            ->withQueryString();

        return Inertia::render($this->pageName(), [
            'surats' => $surats,
            'filters' => ['search' => $search, 'status' => $status],
            'userType' => [
                'value' => $user->userTypeSlug(),
                'label' => $user->roleDisplayName(),
            ],
            'endpoints' => [
                'basePath' => $this->basePath(),
            ],
        ]);
    }

    public function show(Request $request, int $id): Response
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $surat = Surat::query()
            ->with([
                'pemohon',
                'jenisSurat.approvalRole',
                'dataEntries',
                'lampirans',
                'approvalFlows' => fn ($q) => $q->latest('tanggal_aksi')->latest('id'),
                'histories' => fn ($q) => $q->with('user:id,name,user_type')->latest('created_at')->latest('id')->limit(8),
            ])
            ->where('pemohon_id', $user->id)
            ->findOrFail($id);

        $this->authorize('view', $surat);

        return Inertia::render($this->detailPageName(), [
            'userType' => [
                'value' => $user->userTypeSlug(),
                'label' => $user->roleDisplayName(),
            ],
            'back_href' => $this->basePath().'/history',
            'back_label' => 'Riwayat Surat',
            'surat' => [
                'id' => $surat->id,
                'letter_mode' => $surat->resolvedLetterMode(),
                'letter_mode_label' => $surat->letterModeLabel(),
                'is_institution' => $surat->resolvedLetterMode() === 'institution',
                'pemohon' => [
                    'name' => $surat->pemohon?->name,
                    'nim_nip' => $surat->pemohon?->nim_nip ?? $surat->pemohon?->nomor_induk,
                ],
                'nomor_surat' => $surat->nomor_surat,
                'nomor_surat_status' => $surat->resolvedNomorSuratStatus(),
                'nomor_surat_status_label' => $surat->nomorSuratStatusLabel(),
                'reference' => $surat->nomor_surat ?: sprintf('REQ-%05d', $surat->id),
                'jenis_surat' => $surat->jenisSurat?->nama ?? 'Surat Akademik',
                'approval_role_slug' => $surat->jenisSurat?->approvalRole?->slug,
                'keperluan' => $surat->keperluan,
                'isi_surat' => $this->decodeJsonPayload($surat->isi_surat),
                'detail_data' => $this->buildDetailData($surat),
                'lampiran' => $surat->lampirans->map(fn ($lampiran): array => [
                    'id' => $lampiran->id,
                    'name' => $lampiran->nama_asli ?? $lampiran->nama_file ?? $lampiran->name ?? 'Lampiran',
                    'url' => $this->signedDocumentRoute('lampiran.preview', $lampiran->id),
                    'type' => $lampiran->mime_type ?? $lampiran->type ?? null,
                ])->values(),
                'tanggal_pengajuan' => optional($surat->tanggal_pengajuan)?->toISOString(),
                'tanggal_kebutuhan' => optional($surat->tanggal_kebutuhan)?->toDateString(),
                'tanggal_selesai' => optional($surat->tanggal_selesai)?->toISOString(),
                'status' => $surat->status,
                'latest_rejection' => $this->latestRejectionPayload($surat),
                'approval_timeline' => $surat->approvalFlows->map(fn ($flow): array => [
                    'id' => $flow->id,
                    'label' => $this->approvalFlowLabel($flow),
                    'note' => strtolower((string) $flow->role) === 'admin'
                        ? ($flow->catatan ?? $flow->note ?? null)
                        : null,
                    'description' => $flow->keterangan ?? null,
                    'acted_at' => optional($flow->tanggal_aksi)?->toISOString(),
                    'status' => $flow->status,
                    'action' => $flow->action ?? null,
                    'actor' => $flow->user?->name ?? $flow->actor_name ?? null,
                    'role' => $flow->role,
                ])->values(),
                'history_timeline' => $surat->histories->map(function (SuratHistory $history): array {
                    /** @var ?User $historyUser */
                    $historyUser = $history->user;
                    $historyUserType = $historyUser !== null ? $historyUser->user_type : null;
                    $isAdminHistory = strtolower((string) $historyUserType) === 'admin';

                    return [
                        'id' => $history->id,
                        'label' => $this->historyActionLabel($history),
                        'description' => $history->keterangan,
                        'note' => $isAdminHistory ? $history->keterangan : null,
                        'created_at' => $history->created_at?->toISOString(),
                        'action' => $history->action,
                        'actor' => $historyUser?->name ?? null,
                        'role' => $historyUserType,
                    ];
                })->values(),
                'previewTemplateUrl' => $surat->canViewFinalDocumentPreview()
                    ? $this->signedDocumentRoute('surat.template-preview', $surat->id)
                    : null,
                'generatedDocumentUrl' => $surat->canViewFinalDocumentPreview()
                    ? $this->signedDocumentRoute('surat.generated-document', $surat->id)
                    : null,
                'pdfUrl' => $surat->canViewFinalDocumentPreview()
                    ? $this->signedDocumentRoute('surat.pdf', $surat->id)
                    : null,
                'canDownloadPdf' => $surat->canViewFinalDocumentPreview(),
            ],
        ]);
    }

    protected function pageName(): string
    {
        return 'mahasiswa/History';
    }

    protected function basePath(): string
    {
        return '/fast/user';
    }

    protected function signedDocumentRoute(string $routeName, int $suratId): string
    {
        return URL::temporarySignedRoute(
            'documents.public.'.$routeName,
            now()->addMinutes(15),
            ['id' => $suratId],
        );
    }

    protected function detailPageName(): string
    {
        return str_replace('History', 'HistoryDetail', $this->pageName());
    }

    protected function approvalFlowLabel($flow): string
    {
        $role = (string) ($flow->role ?? '');
        $status = (string) ($flow->status ?? '');

        return match (true) {
            $role === 'admin' && $status === 'approved' => 'Validasi Admin',
            $status === SuratApprovalFlow::STATUS_REJECTED_FINAL && $role === 'admin' => 'Ditolak Admin',
            $status === SuratApprovalFlow::STATUS_REJECTED_FINAL && $role === 'kaprodi' => 'Ditolak Kaprodi',
            $status === SuratApprovalFlow::STATUS_REJECTED_FINAL && $role === 'dekan' => 'Ditolak Dekan',
            $status === SuratApprovalFlow::STATUS_REVISION_REQUESTED && $role === 'kaprodi' => 'Dikembalikan Kaprodi',
            $status === SuratApprovalFlow::STATUS_REVISION_REQUESTED && $role === 'dekan' => 'Dikembalikan Dekan',
            $status === SuratApprovalFlow::STATUS_NOTE => 'Catatan Approval',
            default => $flow->label
                ?? $flow->status_label
                ?? ucfirst(str_replace('_', ' ', $status)),
        };
    }

    protected function historyActionLabel(SuratHistory $history): string
    {
        $label = trim((string) $history->action_label);

        if ($label === '') {
            return 'Aktivitas surat';
        }

        return preg_replace('/^Surat\s+Surat\s+/i', 'Surat ', $label) ?? $label;
    }

    public function cancel(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $surat = Surat::where('pemohon_id', $user->id)->findOrFail($id);
        $this->authorize('cancel', $surat);

        abort_if(
            $surat->status !== Surat::STATUS_PENDING,
            422,
            'Surat hanya bisa dibatalkan jika masih menunggu validasi.'
        );

        $surat->update(['status' => Surat::STATUS_CANCELLED]);

        return back()->with('fast_success', 'Pengajuan surat berhasil dibatalkan.');
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
        $visibleRevisionFlow = $latestRevisionFlow?->role === 'admin' ? $latestRevisionFlow : null;
        $visibleFinalRejectionFlow = $latestFinalRejectionFlow?->role === 'admin' ? $latestFinalRejectionFlow : null;

        return [
            'id' => $surat->id,
            'reference' => $surat->nomor_surat ?: sprintf('REQ-%05d', $surat->id),
            'jenisSurat' => $surat->jenisSurat?->nama ?? 'Surat Akademik',
            'jenisSuratId' => $surat->jenis_surat_id,
            'letter_mode' => $surat->resolvedLetterMode(),
            'letter_mode_label' => $surat->letterModeLabel(),
            'is_institution' => $surat->resolvedLetterMode() === 'institution',
            'approvalRole' => [
                'id' => $surat->jenisSurat?->approvalRole?->id,
                'nama' => $surat->jenisSurat?->approvalRole?->nama,
                'slug' => $surat->jenisSurat?->approvalRole?->slug,
            ],
            'approval_role_slug' => $surat->jenisSurat?->approvalRole?->slug,
            'requiresFinalApproval' => $surat->requiresFinalApproval(),
            'status' => $surat->status,
            'keperluan' => $surat->keperluan,
            'rejectionReason' => $visibleFinalRejectionFlow?->catatan,
            'revisionReason' => $visibleRevisionFlow?->catatan ?? ($visibleRevisionFlow ? $surat->catatan_revisi : null),
            'rejectedByRole' => $visibleRevisionFlow?->role ?? $visibleFinalRejectionFlow?->role,
            'needsRevision' => $surat->status === Surat::STATUS_REVISION_REQUESTED,
            'revisionCount' => (int) $surat->revisi_ke,
            'submittedAt' => optional($surat->tanggal_pengajuan ?? $surat->created_at)?->toISOString(),
            'neededAt' => optional($surat->tanggal_kebutuhan)?->toDateString(),
            'nomor_surat' => $surat->nomor_surat,
            'canCancel' => $surat->status === Surat::STATUS_PENDING,
            'timeline' => $surat->histories
                ->map(fn (SuratHistory $history) => [
                    'action' => $history->action,
                    'label' => $this->historyActionLabel($history),
                    'description' => $history->keterangan,
                    'created_at' => $history->created_at?->toISOString(),
                ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function latestRejectionPayload(Surat $surat): ?array
    {
        $latestRevisionFlow = $surat->latestRevisionRequestFlow();
        $latestAdminRejectionFlow = $surat->latestAdminRejectionFlow();
        $latestApproverFinalRejectionFlow = $surat->latestApproverFinalRejectionFlow();
        $latestFinalRejectionFlow = $latestAdminRejectionFlow ?? $latestApproverFinalRejectionFlow;
        $visibleRevisionFlow = $latestRevisionFlow?->role === 'admin' ? $latestRevisionFlow : null;
        $visibleFinalRejectionFlow = $latestFinalRejectionFlow?->role === 'admin' ? $latestFinalRejectionFlow : null;

        if (! $visibleRevisionFlow && ! $visibleFinalRejectionFlow) {
            return null;
        }

        return [
            'role' => $visibleRevisionFlow?->role ?? $visibleFinalRejectionFlow?->role,
            'label' => $visibleRevisionFlow?->role
                ? ('Catatan revisi '.ucfirst((string) $visibleRevisionFlow->role))
                : 'Ditolak Final',
            'type' => $visibleRevisionFlow ? 'revision' : 'final_reject',
            'note' => $visibleRevisionFlow?->catatan ?? $visibleFinalRejectionFlow?->catatan,
            'acted_at' => optional($visibleRevisionFlow?->tanggal_aksi ?? $visibleFinalRejectionFlow?->tanggal_aksi)?->toISOString(),
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

        $priority = [
            'nama' => 1,
            'nama_lengkap' => 1,
            'nim' => 1,
            'nip' => 1,
            'nim_nip' => 1,
            'program_studi' => 2,
            'prodi' => 2,
            'semester' => 2,
            'kelas' => 2,
            'angkatan' => 2,
            'tempat' => 3,
            'tanggal' => 3,
            'tanggal_lahir' => 3,
            'alamat' => 3,
            'tujuan' => 4,
            'keperluan' => 4,
            'keterangan' => 4,
            'judul' => 4,
        ];

        uksort($labels, function (string $a, string $b) use ($priority): int {
            $pa = $priority[strtolower($a)] ?? 99;
            $pb = $priority[strtolower($b)] ?? 99;

            return $pa <=> $pb ?: strcasecmp($a, $b);
        });

        return array_slice($labels, 0, 8, true);
    }

    protected function isTechnicalDetailKey(string $key): bool
    {
        $technical = [
            // Identitas record
            'id',
            'surat_id',
            'jenis_surat_id',
            'pemohon_id',
            'subject_user_id',
            'created_by',
            'type',
            'status',
            'nomor_surat_status',
            'revisi_ke',

            // Timestamp / lifecycle
            'created_at',
            'updated_at',
            'deleted_at',
            'generated_at',
            'validated_by_admin_id',
            'validated_by_admin_at',
            'approved_by_id',
            'approved_at',

            // File / rendering / output
            'generated_file_path',
            'generated_file_type',
            'rendered_snapshot',
            'template_version',
            'file_path',
            'path',
            'url',
            'nama_file',
            'nama_asli',
            'mime_type',

            // Token / security
            'qr_token',
            'qr_validated_at',
            'token',
            'slug',

            // Workflow internal
            'approval_role',
            'approval_role_id',
            'approvalrole',
            'approval',
            'generated_by',

            // Payload / metadata internal
            'field_name',
            'field_value',
            'meta',
            'metadata',

            // Catatan internal
            'catatan_revisi',
            'rejection_reason',
            'admin_note',

            // Konfigurasi lampiran
            'lampiran_keterangan',
            'lampiran_judul',
            'lampiran_judul_align',
            'lampiran_judul_bold',
            'lampiran_orientation',
            'lampiran_mode',
            'lampiran_label_no',
            'lampiran_label_nama',
            'lampiran_label_nim',
            'lampiran_label_prodi',
            'lampiran_mahasiswa',
            'lampiran_columns',
            'lampiran_rows',
        ];

        if (in_array($key, $technical, true)) {
            return true;
        }

        return str_starts_with($key, '_')
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
}
