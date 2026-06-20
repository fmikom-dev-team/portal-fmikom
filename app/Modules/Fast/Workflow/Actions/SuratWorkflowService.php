<?php

namespace App\Modules\Fast\Workflow\Actions;

use App\Models\JenisSurat;
use App\Models\Surat;
use App\Models\SuratData;
use App\Models\SuratHistory;
use App\Models\SuratLampiran;
use App\Models\User;
use App\Modules\Fast\DTOs\SuratDataContract;
use App\Modules\Fast\Services\Shared\SuratDocumentGeneratorService;
use App\Modules\Fast\Workflow\Approvals\FastApprovalWorkflowService as ApprovalWorkflowService;
use App\Modules\Fast\Services\Shared\SuratHistoryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SuratWorkflowService
{
    public function __construct(
        protected ApprovalWorkflowService $approvalWorkflow,
        protected SuratDocumentGeneratorService $documentGenerator,
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, UploadedFile>  $lampirans
     */
    public function submit(User $user, array $payload, array $lampirans = []): Surat
    {
        abort_unless($user->hasFastUserRole(), 403, 'Hanya mahasiswa atau dosen yang dapat mengajukan surat.');

        $jenisSurat = JenisSurat::query()
            ->with(['allowedRole', 'approvalRole', 'template.placeholders'])
            ->findOrFail((int) $payload['jenis_surat_id']);

        abort_if(
            $jenisSurat->allowedRole !== null && ! $user->hasRole((string) $jenisSurat->allowedRole->slug),
            403,
            'Role pengguna tidak diizinkan untuk jenis surat ini.',
        );

        $dynamicData = $this->validateDynamicData($jenisSurat, Arr::wrap($payload['data'] ?? []));
        $storedPaths = [];

        try {
            return DB::transaction(function () use ($user, $payload, $jenisSurat, $dynamicData, $lampirans, &$storedPaths): Surat {
                $surat = Surat::query()->create([
                    'jenis_surat_id' => $jenisSurat->id,
                    'pemohon_id' => $user->id,
                    'type' => (string) ($payload['type'] ?? 'pengajuan'),
                    'keperluan' => (string) $payload['keperluan'],
                    'status' => Surat::STATUS_PENDING,
                    'tanggal_pengajuan' => now(),
                    'tanggal_kebutuhan' => $payload['tanggal_kebutuhan'] ?? null,
                    'isi_surat' => json_encode([
                        'jenis_surat_id' => $jenisSurat->id,
                        'jenis_surat' => $jenisSurat->nama,
                        'keperluan' => $payload['keperluan'],
                        'tanggal_kebutuhan' => $payload['tanggal_kebutuhan'] ?? null,
                        'data' => $dynamicData,
                    ], JSON_THROW_ON_ERROR),
                ]);

                $this->syncSuratData($surat, $dynamicData);
                $this->storeLampirans($surat, $lampirans, $storedPaths);
                SuratHistoryService::submitted($surat->id, (string) $jenisSurat->nama);

                return $this->freshSubmittedSurat($surat);
            });
        } catch (\Throwable $throwable) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $throwable;
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function createOutgoing(User $admin, array $payload): Surat
    {
        abort_unless($admin->hasRole('admin'), 403, 'Hanya admin yang dapat membuat surat keluar.');

        $jenisSurat = JenisSurat::query()
            ->with(['template.placeholders', 'approvalRole'])
            ->findOrFail((int) $payload['jenis_surat_id']);

        $dynamicData = $this->validateDynamicData($jenisSurat, Arr::wrap($payload['data'] ?? []));

        $surat = DB::transaction(function () use ($admin, $payload, $jenisSurat, $dynamicData): Surat {
            $surat = Surat::query()->create([
                'jenis_surat_id' => $jenisSurat->id,
                'pemohon_id' => $admin->id,
                'type' => 'surat_keluar',
                'keperluan' => (string) $payload['keperluan'],
                'status' => Surat::STATUS_PENDING,
                'tanggal_pengajuan' => now(),
                'isi_surat' => json_encode([
                    'jenis_surat_id' => $jenisSurat->id,
                    'jenis_surat' => $jenisSurat->nama,
                    'keperluan' => $payload['keperluan'],
                    'data' => $dynamicData,
                    'created_by' => [
                        'id' => $admin->id,
                        'name' => $admin->name,
                    ],
                ], JSON_THROW_ON_ERROR),
            ]);

            $this->syncSuratData($surat, $dynamicData);

            return $surat->fresh([
                'jenisSurat.template.placeholders',
                'jenisSurat.approvalRole',
                'pemohon',
                'dataEntries',
            ]);
        });

        // Admin langsung memvalidasi surat yang ia buat. Bila jenis surat memerlukan
        // persetujuan, surat diteruskan ke kaprodi/dekan (status validated_admin) dan
        // PDF baru digenerate setelah disetujui. Bila tidak, PDF langsung digenerate.
        $this->approvalWorkflow->approve($surat, ApprovalWorkflowService::ROLE_ADMIN, $admin);

        $surat->forceFill([
            'validated_by_admin_id' => $admin->id,
            'validated_by_admin_at' => now(),
        ])->save();

        return $this->freshGeneratedSurat($surat);
    }

    /**
     * Admin mengedit surat yang telah ditolak oleh kaprodi/dekan,
     * lalu meneruskannya kembali ke approval queue.
     *
     * @param  array<string, mixed>  $payload
     */
    public function editRejected(Surat $surat, User $admin, array $payload): Surat
    {
        abort_unless($admin->hasRole('admin'), 403, 'Hanya admin yang dapat mengedit surat.');
        abort_unless($surat->status === Surat::STATUS_REVISION_REQUESTED, 403, 'Hanya surat revisi approver yang dapat diedit.');
        $latestAdminApprovalFlow = $surat->approvalFlows()
            ->where('role', ApprovalWorkflowService::ROLE_ADMIN)
            ->where('status', 'approved')
            ->latest('tanggal_aksi')
            ->latest('id')
            ->first();
        abort_if(
            $surat->validated_by_admin_id === null && $latestAdminApprovalFlow === null,
            403,
            'Surat belum divalidasi admin sebelumnya.'
        );
        abort_if(
            $surat->latestRevisionRequestFlow() === null,
            403,
            'Surat ini ditolak final oleh admin dan tidak dapat dikirim ulang dari alur revisi approver.'
        );

        $jenisSurat = $surat->jenisSurat;
        $jenisSurat->loadMissing('template.placeholders', 'approvalRole');

        $dynamicData = array_replace(
            $this->extractExistingData($surat),
            Arr::wrap($payload['data'] ?? []),
        );
        $dynamicData = $this->validateDynamicData($jenisSurat, $dynamicData);

        return DB::transaction(function () use ($surat, $admin, $payload, $dynamicData, $jenisSurat, $latestAdminApprovalFlow): Surat {
            if (array_key_exists('keperluan', $payload)) {
                $surat->keperluan = (string) $payload['keperluan'];
            }

            $existingIsi = json_decode((string) $surat->isi_surat, true) ?? [];

            $surat->isi_surat = json_encode([
                'jenis_surat_id' => $surat->jenis_surat_id,
                'jenis_surat' => $jenisSurat->nama,
                'keperluan' => $surat->keperluan,
                'data' => $dynamicData,
                'created_by' => $existingIsi['created_by'] ?? [
                    'id' => $admin->id,
                    'name' => $admin->name,
                ],
            ], JSON_THROW_ON_ERROR);

            $surat->status = Surat::STATUS_VALIDATED_ADMIN;
            $surat->validated_by_admin_id = $surat->validated_by_admin_id
                ?? $latestAdminApprovalFlow?->approver_id
                ?? $admin->id;
            $surat->validated_by_admin_at = $surat->validated_by_admin_at
                ?? $latestAdminApprovalFlow?->tanggal_aksi
                ?? $latestAdminApprovalFlow?->approved_at
                ?? now();
            $surat->rejection_reason = null;
            $surat->catatan_revisi = null;
            $surat->save();

            $this->syncSuratData($surat, $dynamicData);
            $surat = $this->documentGenerator->prepareDraft($surat);

            SuratHistoryService::record(
                $surat->id,
                SuratHistory::ACTION_REVISED,
                'Admin memperbarui surat dan mengirim ulang ke approval',
                [
                    'keterangan' => 'Draft surat diperbarui setelah catatan revisi approver.',
                    'meta' => [
                        'revisi_ke' => $surat->revisi_ke,
                    ],
                ]
            );

            return $this->freshGeneratedSurat($surat);
        });
    }

    /**
     * Admin melengkapi atau mengubah surat yang masih pending sebelum validasi.
     *
     * @param  array<string, mixed>  $payload
     */
    public function editPending(Surat $surat, User $admin, array $payload): Surat
    {
        abort_unless($admin->hasRole('admin'), 403, 'Hanya admin yang dapat mengedit surat.');
        abort_unless($surat->status === Surat::STATUS_PENDING, 403, 'Hanya surat pending yang dapat diedit dengan alur validasi admin.');

        $jenisSurat = $surat->jenisSurat;
        $jenisSurat->loadMissing('template.placeholders', 'approvalRole');

        $dynamicData = array_replace(
            $this->extractExistingData($surat),
            Arr::wrap($payload['data'] ?? []),
        );
        $dynamicData = $this->validateDynamicData($jenisSurat, $dynamicData);

        return DB::transaction(function () use ($surat, $admin, $payload, $dynamicData, $jenisSurat): Surat {
            if (array_key_exists('keperluan', $payload)) {
                $surat->keperluan = (string) $payload['keperluan'];
            }

            $surat->isi_surat = json_encode([
                'jenis_surat_id' => $surat->jenis_surat_id,
                'jenis_surat' => $jenisSurat->nama,
                'keperluan' => $surat->keperluan,
                'data' => $dynamicData,
            ], JSON_THROW_ON_ERROR);

            $surat->save();

            $this->syncSuratData($surat, $dynamicData);

            $this->approvalWorkflow->approve(
                $surat->fresh(),
                ApprovalWorkflowService::ROLE_ADMIN,
                $admin,
                $payload['admin_note'] ?? null,
            );

            return $this->freshReviewedSurat($surat);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function adminReview(Surat $surat, User $admin, array $payload): Surat
    {
        abort_unless($admin->hasRole('admin'), 403, 'Hanya admin yang dapat memvalidasi pengajuan.');
        abort_unless($surat->canBeValidatedByAdmin() || $surat->canBeRejectedByAdmin(), 403, 'Status surat tidak dapat diproses admin.');

        $surat->loadMissing('jenisSurat.approvalRole', 'dataEntries');
        $dynamicData = array_replace(
            $this->extractExistingData($surat),
            Arr::wrap($payload['data'] ?? []),
        );
        $dynamicData = $this->validateDynamicData($surat->jenisSurat, $dynamicData);

        return DB::transaction(function () use ($surat, $admin, $payload, $dynamicData): Surat {
            if (array_key_exists('keperluan', $payload)) {
                $surat->keperluan = (string) $payload['keperluan'];
            }

            $surat->admin_note = $payload['admin_note'] ?? null;
            $surat->validated_by_admin_id = $admin->id;
            $surat->validated_by_admin_at = now();
            $surat->rejection_reason = $payload['decision'] === 'rejected'
                ? (string) ($payload['rejection_reason'] ?? '')
                : null;
            $surat->isi_surat = json_encode([
                'jenis_surat_id' => $surat->jenis_surat_id,
                'jenis_surat' => $surat->jenisSurat?->nama,
                'keperluan' => $surat->keperluan,
                'tanggal_kebutuhan' => optional($surat->tanggal_kebutuhan)?->toDateString(),
                'data' => $dynamicData,
            ], JSON_THROW_ON_ERROR);
            $surat->save();

            $this->syncSuratData($surat, $dynamicData);

            if ($payload['decision'] === 'rejected') {
                $this->approvalWorkflow->rejectAdmin(
                    $surat->fresh(),
                    $admin,
                    (string) ($payload['rejection_reason'] ?? null),
                );
            } else {
                $this->approvalWorkflow->approve(
                    $surat->fresh(),
                    ApprovalWorkflowService::ROLE_ADMIN,
                    $admin,
                    $payload['admin_note'] ?? null,
                );
            }

            return $this->freshReviewedSurat($surat);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function approve(Surat $surat, User $actor, array $payload): Surat
    {
        $role = $this->resolveApprovalRole($actor);
        abort_if($role === null, 403, 'Role pengguna bukan approver yang valid.');

        if ($payload['decision'] === 'revision_requested') {
            $this->approvalWorkflow->requestRevision(
                $surat->fresh(),
                $role,
                $actor,
                (string) ($payload['rejection_reason'] ?? null),
            );
        } elseif ($payload['decision'] === 'rejected_final') {
            $this->approvalWorkflow->finalReject(
                $surat->fresh(),
                $role,
                $actor,
                (string) ($payload['rejection_reason'] ?? null),
            );
        } else {
            $this->approvalWorkflow->approve(
                $surat,
                $role,
                $actor,
                $payload['notes'] ?? null,
            );

            $surat->forceFill([
                'approved_by_id' => $actor->id,
                'approved_at' => now(),
                'rejection_reason' => null,
            ])->save();
        }

        return $this->freshReviewedSurat($surat);
    }

    public function generateDocument(Surat $surat, User $admin, bool $force = false): Surat
    {
        abort_unless($admin->hasRole('admin'), 403, 'Hanya admin yang dapat generate dokumen.');
        $allowedStatuses = $surat->type === 'surat_keluar'
            ? [Surat::STATUS_PENDING, Surat::STATUS_VALIDATED_ADMIN, Surat::STATUS_APPROVED_KAPRODI, Surat::STATUS_APPROVED_DEKAN, Surat::STATUS_FINISHED]
            : [Surat::STATUS_VALIDATED_ADMIN, Surat::STATUS_APPROVED_KAPRODI, Surat::STATUS_APPROVED_DEKAN, Surat::STATUS_FINISHED];

        abort_if(
            ! $force && ! in_array($surat->status, $allowedStatuses, true),
            422,
            'Surat belum berada pada status yang siap digenerate.',
        );

        $surat->loadMissing('jenisSurat.template.placeholders', 'pemohon', 'dataEntries');

        return $this->documentGenerator->generate($surat);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function validateDynamicData(JenisSurat $jenisSurat, array $payload): array
    {
        $rules = [];
        $messages = [];

        foreach ($this->normalizeFieldConfig($jenisSurat->field_config ?? []) as $field) {
            if ((string) ($field['mode_form_pemohon'] ?? 'editable') === 'hidden') {
                continue;
            }

            $fieldRules = [($field['required'] ?? false) ? 'required' : 'nullable'];
            $options = collect($field['options'] ?? [])
                ->map(fn ($option) => is_array($option) ? ($option['value'] ?? null) : $option)
                ->filter(static fn ($option) => filled($option))
                ->values();

            switch ($field['type']) {
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'checkbox':
                    $fieldRules[] = 'boolean';
                    break;
                case 'repeatable':
                case 'checkbox-group':
                case 'multiselect':
                    $fieldRules[] = 'array';
                    if ($field['type'] === 'repeatable') {
                        $rules[$field['name'].'.*'] = ['nullable', 'string'];
                    } elseif ($options->isNotEmpty()) {
                        $rules[$field['name'].'.*'] = [Rule::in($options->all())];
                    }
                    break;
                case 'select':
                case 'radio':
                    $fieldRules[] = 'string';
                    if ($options->isNotEmpty()) {
                        $fieldRules[] = Rule::in($options->all());
                    }
                    break;
                case 'textarea':
                case 'text':
                default:
                    $fieldRules[] = 'string';
                    break;
            }

            $rules[$field['name']] = $fieldRules;
            $messages[$field['name'].'.required'] = ($field['label'] ?? $field['name']).' wajib diisi.';
        }

        $validated = Validator::make($payload, $rules, $messages)->validate();

        $validatedSpecial = Validator::make(
            SuratDataContract::extractManualDataFromValidatedPayload($payload),
            SuratDataContract::adminManualValidationRules(),
        )->validate();

        return array_replace($validated, $validatedSpecial);
    }

    /**
     * @param  array<int, UploadedFile>  $lampirans
     * @param  array<int, string>  $storedPaths
     */
    protected function storeLampirans(Surat $surat, array $lampirans, array &$storedPaths): void
    {
        Storage::disk('public')->makeDirectory('surat-lampirans');

        foreach ($lampirans as $lampiran) {
            $path = $lampiran->store('surat-lampirans', 'public');
            $storedPaths[] = $path;

            SuratLampiran::query()->create([
                'surat_id' => $surat->id,
                'nama_file' => $lampiran->getClientOriginalName(),
                'file_path' => $path,
                'tipe' => $lampiran->getClientMimeType(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function syncSuratData(Surat $surat, array $payload): void
    {
        $fieldNames = [];

        foreach ($payload as $fieldName => $fieldValue) {
            $fieldNames[] = (string) $fieldName;

            SuratData::query()->updateOrCreate(
                [
                    'surat_id' => $surat->id,
                    'field_name' => (string) $fieldName,
                ],
                [
                    'field_value' => is_array($fieldValue)
                        ? json_encode($fieldValue, JSON_THROW_ON_ERROR)
                        : (string) $fieldValue,
                ],
            );
        }

        if ($fieldNames !== []) {
            $surat->dataEntries()
                ->whereNotIn('field_name', $fieldNames)
                ->delete();
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeFieldConfig(array $fieldConfig): array
    {
        return collect(SuratDataContract::filterDynamicFieldConfig($fieldConfig))
            ->map(static fn (array $field): array => SuratDataContract::normalizeDynamicFieldConfigItem($field))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function extractExistingData(Surat $surat): array
    {
        $surat->loadMissing('jenisSurat');

        $fieldTypes = collect($this->normalizeFieldConfig($surat->jenisSurat?->field_config ?? []))
            ->mapWithKeys(fn (array $field): array => [
                $field['name'] => strtolower((string) ($field['type'] ?? 'text')),
            ])
            ->all();

        return $surat->dataEntries
            ->mapWithKeys(function (SuratData $entry) use ($fieldTypes): array {
                return [
                    $entry->field_name => $this->normalizeStoredFieldValue(
                        $entry->field_name,
                        $entry->field_value,
                        $fieldTypes[$entry->field_name] ?? null,
                    ),
                ];
            })
            ->all();
    }

    protected function normalizeStoredFieldValue(string $fieldName, ?string $storedValue, ?string $fieldType = null): mixed
    {
        if ($storedValue === null) {
            return null;
        }

        if (in_array($fieldName, SuratDataContract::adminManualArrayFields(), true)
            || in_array($fieldType, ['repeatable', 'checkbox-group', 'multiselect'], true)
        ) {
            $decoded = json_decode($storedValue, true);

            return is_array($decoded) ? $decoded : [];
        }

        if ($fieldType === 'checkbox') {
            if ($storedValue === '') {
                return false;
            }

            return filter_var($storedValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                ?? $storedValue === '1';
        }

        return $storedValue;
    }

    protected function resolveApprovalRole(User $actor): ?string
    {
        return match (true) {
            $actor->hasRole('kaprodi') => ApprovalWorkflowService::ROLE_KAPRODI,
            $actor->hasRole('dekan') => ApprovalWorkflowService::ROLE_DEKAN,
            default => null,
        };
    }

    /**
     * @return \App\Models\Surat
     */
    protected function freshSubmittedSurat(Surat $surat): Surat
    {
        return $surat->fresh([
            'jenisSurat.approvalRole',
            'pemohon',
            'dataEntries',
            'lampirans',
        ]);
    }

    /**
     * @return \App\Models\Surat
     */
    protected function freshGeneratedSurat(Surat $surat): Surat
    {
        return $surat->fresh([
            'jenisSurat.template.placeholders',
            'jenisSurat.approvalRole',
            'pemohon',
            'dataEntries',
        ]);
    }

    /**
     * @return \App\Models\Surat
     */
    protected function freshReviewedSurat(Surat $surat): Surat
    {
        return $surat->fresh([
            'jenisSurat.approvalRole',
            'pemohon',
            'dataEntries',
            'lampirans',
            'approvalFlows',
        ]);
    }
}




