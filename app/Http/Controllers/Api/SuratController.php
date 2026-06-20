<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AdminReviewSuratRequest;
use App\Http\Requests\Api\ApproveSuratRequest;
use App\Http\Requests\Api\GenerateSuratDocumentRequest;
use App\Http\Requests\Api\StoreSuratRequest;
use App\Models\Surat;
use App\Modules\Fast\Workflow\Actions\SuratWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuratController extends Controller
{
    public function __construct(
        protected SuratWorkflowService $workflow,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $query = Surat::query()->with([
            'pemohon',
            'jenisSurat.approvalRole',
            'dataEntries',
            'lampirans',
        ]);

        if ($user->hasFastUserRole()) {
            $query->where('pemohon_id', $user->id);
        } elseif ($user->isApprover()) {
            $approvalRoleSlug = $user->hasRole('kaprodi') ? 'kaprodi' : 'dekan';

            $query
                ->whereIn('status', [Surat::STATUS_VALIDATED_ADMIN])
                ->whereHas('jenisSurat.approvalRole', fn ($roleQuery) => $roleQuery->where('slug', $approvalRoleSlug));
        } elseif (! $user->hasStaffAccess()) {
            abort(403, 'Anda tidak memiliki akses ke data surat.');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('jenis_surat_id')) {
            $query->where('jenis_surat_id', $request->integer('jenis_surat_id'));
        }

        return response()->json([
            'data' => $query->latest()->paginate(15)->through(
                fn (Surat $surat): array => $this->transformSurat($surat),
            ),
        ]);
    }

    public function store(StoreSuratRequest $request): JsonResponse
    {
        $surat = $this->workflow->submit(
            $request->user(),
            $request->validated(),
            $request->file('lampiran', []),
        );

        return response()->json([
            'message' => 'Pengajuan surat berhasil dibuat.',
            'data' => $this->transformSurat($surat),
        ], 201);
    }

    public function show(Request $request, Surat $surat): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        if ($user->hasFastUserRole()) {
            abort_unless($surat->pemohon_id === $user->id, 403, 'Anda tidak dapat melihat surat ini.');
        } elseif ($user->isApprover()) {
            $surat->loadMissing('jenisSurat.approvalRole');

            abort_unless(
                $surat->status === Surat::STATUS_VALIDATED_ADMIN
                && $surat->jenisSurat?->approvalRole !== null
                && $user->hasRole((string) $surat->jenisSurat->approvalRole->slug),
                403,
                'Anda tidak dapat melihat surat ini.',
            );
        } elseif (! $user->hasStaffAccess()) {
            abort(403, 'Anda tidak memiliki akses ke data surat ini.');
        }

        $surat->loadMissing([
            'pemohon',
            'jenisSurat.approvalRole',
            'dataEntries',
            'lampirans',
            'approvalFlows',
            'validatedByAdmin',
            'approvedBy',
        ]);

        return response()->json([
            'data' => $this->transformSurat($surat, true),
        ]);
    }

    public function adminValidate(AdminReviewSuratRequest $request, Surat $surat): JsonResponse
    {
        $updated = $this->workflow->adminReview($surat, $request->user(), $request->validated());

        return response()->json([
            'message' => $request->string('decision')->toString() === 'rejected'
                ? 'Pengajuan ditolak oleh admin.'
                : 'Pengajuan divalidasi oleh admin.',
            'data' => $this->transformSurat($updated, true),
        ]);
    }

    public function approve(ApproveSuratRequest $request, Surat $surat): JsonResponse
    {
        $updated = $this->workflow->approve($surat, $request->user(), $request->validated());

        return response()->json([
            'message' => match ($request->string('decision')->toString()) {
                'revision_requested' => 'Pengajuan dikembalikan ke admin untuk revisi.',
                'rejected_final' => 'Pengajuan ditolak final oleh approver.',
                default => 'Pengajuan berhasil disetujui.',
            },
            'data' => $this->transformSurat($updated, true),
        ]);
    }

    public function generateDocument(GenerateSuratDocumentRequest $request, Surat $surat): JsonResponse
    {
        $generated = $this->workflow->generateDocument(
            $surat,
            $request->user(),
            (bool) $request->boolean('force'),
        );

        return response()->json([
            'message' => 'Dokumen surat berhasil digenerate.',
            'data' => $this->transformSurat($generated, true),
        ]);
    }

    protected function transformSurat(Surat $surat, bool $includeAudit = false): array
    {
        $payload = [
            'id' => $surat->id,
            'type' => $surat->type,
            'status' => $surat->status,
            'nomor_surat' => $surat->nomor_surat,
            'keperluan' => $surat->keperluan,
            'tanggal_pengajuan' => optional($surat->tanggal_pengajuan)?->toISOString(),
            'tanggal_kebutuhan' => optional($surat->tanggal_kebutuhan)?->toDateString(),
            'tanggal_selesai' => optional($surat->tanggal_selesai)?->toISOString(),
            'generated_file_path' => $surat->generated_file_path,
            'generated_file_type' => $surat->generated_file_type,
            'jenis_surat' => [
                'id' => $surat->jenisSurat?->id,
                'nama' => $surat->jenisSurat?->nama,
                'approval_role' => $surat->jenisSurat?->approvalRole?->slug,
            ],
            'pemohon' => [
                'id' => $surat->pemohon?->id,
                'name' => $surat->pemohon?->name,
                'nomor_induk' => $surat->pemohon?->nim_nip ?? $surat->pemohon?->nomor_induk,
            ],
            'data' => $surat->dataEntries
                ->mapWithKeys(function ($entry): array {
                    $decoded = json_decode((string) $entry->field_value, true);

                    return [
                        $entry->field_name => json_last_error() === JSON_ERROR_NONE
                            ? $decoded
                            : $entry->field_value,
                    ];
                })
                ->all(),
            'lampiran' => $surat->lampirans
                ->map(fn ($lampiran): array => [
                    'id' => $lampiran->id,
                    'nama_file' => $lampiran->nama_file,
                    'file_path' => $lampiran->file_path,
                    'tipe' => $lampiran->tipe,
                ])
                ->values()
                ->all(),
        ];

        if (! $includeAudit) {
            return $payload;
        }

        $payload['audit'] = [
            'admin_note' => $surat->admin_note,
            'rejection_reason' => $surat->rejection_reason,
            'validated_by_admin' => $surat->validatedByAdmin?->name,
            'validated_by_admin_at' => optional($surat->validated_by_admin_at)?->toISOString(),
            'approved_by' => $surat->approvedBy?->name,
            'approved_at' => optional($surat->approved_at)?->toISOString(),
            'approval_flows' => $surat->approvalFlows->map(fn ($flow): array => [
                'id' => $flow->id,
                'role' => $flow->role,
                'status' => $flow->status,
                'keterangan' => $flow->keterangan,
                'catatan' => $flow->catatan,
                'approved_at' => optional($flow->approved_at)?->toISOString(),
            ])->values()->all(),
        ];

        return $payload;
    }
}
