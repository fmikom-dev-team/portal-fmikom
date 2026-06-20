<?php

namespace App\Modules\Fast\Controllers\Shared\User;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use App\Models\SuratHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HistoryController extends Controller
{
    public function index(Request $request): Response
    {
        $user   = $request->user();
        abort_if($user === null, 403);

        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();

        $surats = Surat::query()
            ->with([
                'jenisSurat.approvalRole',
                'approvalFlows' => fn ($q) => $q->latest('tanggal_aksi')->latest('id'),
                'histories' => fn ($q) => $q->latest('created_at')->latest('id')->limit(8),
            ])
            ->where('pemohon_id', $user->id)
            ->when($search, fn ($q) => $q->whereHas('jenisSurat', fn ($j) => $j->where('nama', 'like', "%{$search}%"))
                ->orWhere('keperluan', 'like', "%{$search}%"))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('tanggal_pengajuan')
            ->latest('id')
            ->paginate(10)
            ->through(fn (Surat $surat): array => $this->transformSubmission($surat))
            ->withQueryString();

        return Inertia::render($this->pageName(), [
            'surats'  => $surats,
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
                'histories' => fn ($q) => $q->latest('created_at')->latest('id')->limit(8),
            ])
            ->where('pemohon_id', $user->id)
            ->findOrFail($id);

        return Inertia::render($this->detailPageName(), [
            'userType' => [
                'value' => $user->userTypeSlug(),
                'label' => $user->roleDisplayName(),
            ],
            'back_href' => $this->basePath().'/history',
            'back_label' => 'Riwayat Surat',
            'surat' => [
                'id' => $surat->id,
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
                    'url' => $lampiran->url ?? $lampiran->path_url ?? null,
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
                    'note' => $flow->catatan ?? $flow->note ?? null,
                    'description' => $flow->keterangan ?? null,
                    'acted_at' => optional($flow->tanggal_aksi)?->toISOString(),
                    'status' => $flow->status,
                    'action' => $flow->action ?? null,
                    'actor' => $flow->user?->name ?? $flow->actor_name ?? null,
                    'role' => $flow->role,
                ])->values(),
                'history_timeline' => $surat->histories->map(fn (SuratHistory $history): array => [
                    'id' => $history->id,
                    'label' => $this->historyActionLabel($history),
                    'description' => $history->keterangan,
                    'created_at' => $history->created_at?->toISOString(),
                    'action' => $history->action,
                    'actor' => $history->user?->name ?? null,
                ])->values(),
                'previewTemplateUrl' => route('documents.surat.generated-document', $surat->id, absolute: false),
                'generatedDocumentUrl' => $surat->status === Surat::STATUS_FINISHED
                    ? route('documents.surat.pdf', $surat->id, absolute: false)
                    : route('documents.surat.generated-document', $surat->id, absolute: false),
                'pdfUrl' => route('documents.surat.pdf', $surat->id, absolute: false),
                'canDownloadPdf' => $surat->status === Surat::STATUS_FINISHED,
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

    protected function detailPageName(): string
    {
        return str_replace('History', 'HistoryDetail', $this->pageName());
    }

    protected function approvalFlowLabel($flow): string
    {
        if (($flow->role ?? null) === 'admin' && ($flow->status ?? null) === 'approved') {
            return 'Validasi Admin';
        }

        return $flow->label
            ?? $flow->status_label
            ?? ucfirst(str_replace('_', ' ', (string) $flow->status));
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
        $user  = $request->user();
        abort_if($user === null, 403);

        $surat = Surat::where('pemohon_id', $user->id)->findOrFail($id);

        abort_if(
            $surat->status !== Surat::STATUS_PENDING,
            422,
            'Surat hanya bisa dibatalkan jika masih menunggu validasi.'
        );

        $surat->update(['status' => Surat::STATUS_CANCELLED]);

        return back()->with('success', 'Pengajuan surat berhasil dibatalkan.');
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
            'jenisSuratId' => $surat->jenis_surat_id,
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

        if (! $latestRevisionFlow && ! $latestFinalRejectionFlow) {
            return null;
        }

        return [
            'role' => $latestRevisionFlow?->role ?? $latestFinalRejectionFlow?->role,
            'label' => $latestRevisionFlow?->role
                ? ('Catatan revisi '.ucfirst((string) $latestRevisionFlow->role))
                : ($latestFinalRejectionFlow?->role ? 'Alasan penolakan' : 'Catatan'),
            'type' => $latestRevisionFlow ? 'revision' : 'final_reject',
            'note' => $latestRevisionFlow?->catatan ?? $latestFinalRejectionFlow?->catatan,
            'acted_at' => optional($latestRevisionFlow?->tanggal_aksi ?? $latestFinalRejectionFlow?->tanggal_aksi)?->toISOString(),
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
