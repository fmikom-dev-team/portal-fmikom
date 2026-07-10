<?php

namespace App\Models;

use App\Modules\Fast\DTOs\SuratDataContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * @property string|null $status
 * @property string|null $nomor_surat
 * @property string|null $nomor_surat_status
 * @property string|null $qr_token
 */
class Surat extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_VALIDATED_ADMIN = 'validated_admin';

    public const STATUS_REVISION_REQUESTED = 'revision_requested';

    public const STATUS_APPROVED_KAPRODI = 'approved_kaprodi';

    public const STATUS_APPROVED_DEKAN = 'approved_dekan';

    public const STATUS_FINISHED = 'finished';

    public const STATUS_REJECTED_ADMIN = 'rejected_admin';

    public const STATUS_REJECTED_APPROVER = 'rejected_approver';

    public const STATUS_REJECTED = self::STATUS_REJECTED_ADMIN;

    public const STATUS_CANCELLED = 'cancelled';

    public const NOMOR_SURAT_STATUS_RESERVED = 'reserved';

    public const NOMOR_SURAT_STATUS_ISSUED = 'issued';

    public const NOMOR_SURAT_STATUS_VOID = 'void';

    public const WORKFLOW_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_VALIDATED_ADMIN,
        self::STATUS_REVISION_REQUESTED,
        self::STATUS_APPROVED_KAPRODI,
        self::STATUS_APPROVED_DEKAN,
        self::STATUS_FINISHED,
        self::STATUS_REJECTED_ADMIN,
        self::STATUS_REJECTED_APPROVER,
    ];

    protected $fillable = [
        'jenis_surat_id',
        'pemohon_id',
        'created_by',
        'subject_user_id',
        'type',
        'nomor_surat',
        'nomor_surat_status',
        'keperluan',
        'status',
        'isi_surat',
        'generated_file_path',
        'generated_file_type',
        'generated_at',
        'template_version',
        'rendered_snapshot',
        'revisi_ke',
        'catatan_revisi',
        'tanggal_pengajuan',
        'tanggal_kebutuhan',
        'tanggal_selesai',
        'qr_token',
        'generated_by',
        'qr_validated_at',
        'admin_note',
        'rejection_reason',
        'validated_by_admin_id',
        'validated_by_admin_at',
        'approved_by_id',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'datetime',
            'tanggal_kebutuhan' => 'date',
            'tanggal_selesai' => 'datetime',
            'generated_at' => 'datetime',
            'revisi_ke' => 'integer',
            'qr_validated_at' => 'datetime',
            'validated_by_admin_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $surat): void {
            if ($surat->wasRecentlyCreated || $surat->wasChanged('status')) {
                Cache::forget('notif_count_pending_admin');
                Cache::forget('notif_count_revision_admin');
                Cache::forget('notif_count_approval_queue_kaprodi');
                Cache::forget('notif_count_approval_queue_dekan');
            }
        });
    }

    public function qrCode(): HasOne
    {
        return $this->hasOne(SuratQrCode::class)->latestOfMany();
    }

    /**
     * @return BelongsTo<JenisSurat, $this>
     */
    public function jenisSurat(): BelongsTo
    {
        return $this->belongsTo(JenisSurat::class)->withTrashed();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemohon_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function subjectUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_user_id');
    }

    public function resolvedSubjectUser(): ?User
    {
        if (
            $this->type === 'surat_keluar' &&
            $this->created_by !== null &&
            $this->subject_user_id === null
        ) {
            return null;
        }

        return $this->subjectUser ?? $this->pemohon;
    }

    /**
     * @return array{id: int|null, name: string|null, nim: string|null, nomor_induk: string|null, email: string|null}
     */
    public function serializeSubjectIdentity(): array
    {
        $user = $this->resolvedSubjectUser();
        $manualSubject = $this->extractManualSubjectIdentity();

        if ($user === null) {
            return [
                'id' => null,
                'name' => Arr::get($manualSubject, 'name'),
                'nim' => Arr::get($manualSubject, 'nim'),
                'nomor_induk' => Arr::get($manualSubject, 'nomor_induk'),
                'email' => Arr::get($manualSubject, 'email'),
            ];
        }

        return [
            'id' => $user->id,
            'name' => $user->name ?? Arr::get($manualSubject, 'name'),
            'nim' => $user->nim_nip ?? $user->nomor_induk ?? Arr::get($manualSubject, 'nim'),
            'nomor_induk' => $user->nomor_induk ?? Arr::get($manualSubject, 'nomor_induk'),
            'email' => $user->email ?? Arr::get($manualSubject, 'email'),
        ];
    }

    /**
     * @return array{id: int|null, name: string|null, nim: string|null, nomor_induk: string|null, email: string|null}
     */
    public function serializePemohonIdentity(): array
    {
        $user = $this->pemohon;

        if ($user === null) {
            return [
                'id' => null,
                'name' => null,
                'nim' => null,
                'nomor_induk' => null,
                'email' => null,
            ];
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'nim' => $user->nim_nip ?? $user->nomor_induk,
            'nomor_induk' => $user->nomor_induk,
            'email' => $user->email,
        ];
    }

    public function resolvedLetterMode(): string
    {
        $jenisSurat = $this->jenisSurat;
        $storedMode = strtolower(trim((string) ($jenisSurat ? $jenisSurat->letter_mode : '')));

        if (in_array($storedMode, ['personal', 'institution'], true)) {
            return $storedMode;
        }

        if (
            $this->type === 'surat_keluar' &&
            $this->resolvedSubjectUser() === null &&
            blank($this->extractManualSubjectIdentity()['name'] ?? null)
        ) {
            return 'institution';
        }

        return 'personal';
    }

    public function letterModeLabel(): string
    {
        return $this->resolvedLetterMode() === 'institution'
            ? 'Surat Institusi'
            : 'Surat Personal';
    }

    /**
     * @return array{mode: string, label: string, is_institution: bool}
     */
    public function serializeLetterMode(): array
    {
        $mode = $this->resolvedLetterMode();

        return [
            'mode' => $mode,
            'label' => $this->letterModeLabel(),
            'is_institution' => $mode === 'institution',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function validatedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by_admin_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    /**
     * @return HasMany<SuratLampiran, $this>
     */
    public function lampirans(): HasMany
    {
        return $this->hasMany(SuratLampiran::class);
    }

    /**
     * @return HasMany<SuratData, $this>
     */
    public function dataEntries(): HasMany
    {
        return $this->hasMany(SuratData::class);
    }

    /**
     * @return HasMany<SuratApprovalFlow, $this>
     */
    public function approvalFlows(): HasMany
    {
        return $this->hasMany(SuratApprovalFlow::class)->orderBy('urutan')->orderBy('id');
    }

    /**
     * @return HasMany<SuratHistory, $this>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(SuratHistory::class)->latest('created_at')->latest('id');
    }

    public function canBeValidatedByAdmin(): bool
    {
        return $this->status === self::STATUS_PENDING
            && ! $this->hasIncompleteCampusData();
    }

    public function canBeRejectedByAdmin(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeApprovedByRole(string $role): bool
    {
        $finalApprovalRole = $this->finalApprovalRoleSlug();

        return match ($role) {
            'admin' => $this->canBeValidatedByAdmin(),
            'kaprodi' => $this->status === self::STATUS_VALIDATED_ADMIN && $finalApprovalRole === 'kaprodi',
            'dekan' => $this->status === self::STATUS_VALIDATED_ADMIN && $finalApprovalRole === 'dekan',
            default => throw new InvalidArgumentException('Role approval tidak dikenali.'),
        };
    }

    public function canBeRejectedByRole(string $role): bool
    {
        return $this->canBeFinalRejectedByRole($role);
    }

    public function canRequestRevisionByRole(string $role): bool
    {
        $finalApprovalRole = $this->finalApprovalRoleSlug();

        return match ($role) {
            'kaprodi' => $this->status === self::STATUS_VALIDATED_ADMIN && $finalApprovalRole === 'kaprodi',
            'dekan' => $this->status === self::STATUS_VALIDATED_ADMIN && $finalApprovalRole === 'dekan',
            default => false,
        };
    }

    public function canBeFinalRejectedByRole(string $role): bool
    {
        $finalApprovalRole = $this->finalApprovalRoleSlug();

        return match ($role) {
            'admin' => $this->status === self::STATUS_PENDING,
            'kaprodi' => $this->status === self::STATUS_VALIDATED_ADMIN && $finalApprovalRole === 'kaprodi',
            'dekan' => $this->status === self::STATUS_VALIDATED_ADMIN && $finalApprovalRole === 'dekan',
            default => throw new InvalidArgumentException('Role approval tidak dikenali.'),
        };
    }

    public function finalApprovalRoleSlug(): ?string
    {
        $this->loadMissing('jenisSurat.approvalRole');

        $slug = (string) ($this->jenisSurat?->approvalRole?->slug ?? '');
        $name = (string) ($this->jenisSurat?->approvalRole?->nama ?? '');

        $normalizedSlug = Str::slug($slug);
        $normalizedName = Str::slug($name);

        if (Str::contains($normalizedSlug, 'kaprodi') || Str::contains($normalizedName, 'kaprodi')) {
            return 'kaprodi';
        }

        if (Str::contains($normalizedSlug, 'dekan') || Str::contains($normalizedName, 'dekan')) {
            return 'dekan';
        }

        return null;
    }

    public function requiresFinalApproval(): bool
    {
        $this->loadMissing('jenisSurat');

        return (bool) ($this->jenisSurat?->perlu_approval ?? false)
            && $this->finalApprovalRoleSlug() !== null;
    }

    public function resolvedNomorSuratStatus(): ?string
    {
        if (blank($this->nomor_surat)) {
            return null;
        }

        $storedStatus = strtolower(trim((string) ($this->nomor_surat_status ?? '')));
        if (in_array($storedStatus, [
            self::NOMOR_SURAT_STATUS_RESERVED,
            self::NOMOR_SURAT_STATUS_ISSUED,
            self::NOMOR_SURAT_STATUS_VOID,
        ], true)) {
            return $storedStatus;
        }

        if (in_array($this->status, [self::STATUS_REJECTED_ADMIN, self::STATUS_REJECTED_APPROVER], true)) {
            return self::NOMOR_SURAT_STATUS_VOID;
        }

        if ($this->status === self::STATUS_FINISHED) {
            return self::NOMOR_SURAT_STATUS_ISSUED;
        }

        return self::NOMOR_SURAT_STATUS_RESERVED;
    }

    public function nomorSuratStatusLabel(): ?string
    {
        return match ($this->resolvedNomorSuratStatus()) {
            self::NOMOR_SURAT_STATUS_RESERVED => 'Tercatat',
            self::NOMOR_SURAT_STATUS_ISSUED => 'Final',
            self::NOMOR_SURAT_STATUS_VOID => 'Void',
            default => null,
        };
    }

    public function latestRejectedFlow(): ?SuratApprovalFlow
    {
        $rejectionStatuses = [
            SuratApprovalFlow::STATUS_REVISION_REQUESTED,
            SuratApprovalFlow::STATUS_REJECTED_FINAL,
        ];

        if ($this->relationLoaded('approvalFlows')) {
            return $this->approvalFlows
                ->whereIn('status', $rejectionStatuses)
                ->sortByDesc(fn (SuratApprovalFlow $flow) => $flow->tanggal_aksi?->getTimestamp() ?? 0)
                ->sortByDesc('id')
                ->first();
        }

        return $this->approvalFlows()
            ->whereIn('status', $rejectionStatuses)
            ->latest('tanggal_aksi')
            ->latest('id')
            ->first();
    }

    public function latestRevisionRequestFlow(): ?SuratApprovalFlow
    {
        if ($this->relationLoaded('approvalFlows')) {
            return $this->approvalFlows
                ->where('status', SuratApprovalFlow::STATUS_REVISION_REQUESTED)
                ->sortByDesc(fn (SuratApprovalFlow $flow) => $flow->tanggal_aksi?->getTimestamp() ?? 0)
                ->sortByDesc('id')
                ->first();
        }

        return $this->approvalFlows()
            ->where('status', SuratApprovalFlow::STATUS_REVISION_REQUESTED)
            ->latest('tanggal_aksi')
            ->latest('id')
            ->first();
    }

    public function latestAdminRejectionFlow(): ?SuratApprovalFlow
    {
        if ($this->relationLoaded('approvalFlows')) {
            return $this->approvalFlows
                ->where('status', SuratApprovalFlow::STATUS_REJECTED_FINAL)
                ->where('role', 'admin')
                ->sortByDesc(fn (SuratApprovalFlow $flow) => $flow->tanggal_aksi?->getTimestamp() ?? 0)
                ->sortByDesc('id')
                ->first();
        }

        return $this->approvalFlows()
            ->where('status', SuratApprovalFlow::STATUS_REJECTED_FINAL)
            ->where('role', 'admin')
            ->latest('tanggal_aksi')
            ->latest('id')
            ->first();
    }

    public function latestApproverFinalRejectionFlow(): ?SuratApprovalFlow
    {
        if ($this->relationLoaded('approvalFlows')) {
            return $this->approvalFlows
                ->where('status', SuratApprovalFlow::STATUS_REJECTED_FINAL)
                ->whereIn('role', ['kaprodi', 'dekan'])
                ->sortByDesc(fn (SuratApprovalFlow $flow) => $flow->tanggal_aksi?->getTimestamp() ?? 0)
                ->sortByDesc('id')
                ->first();
        }

        return $this->approvalFlows()
            ->where('status', SuratApprovalFlow::STATUS_REJECTED_FINAL)
            ->whereIn('role', ['kaprodi', 'dekan'])
            ->latest('tanggal_aksi')
            ->latest('id')
            ->first();
    }

    public function isRevisionRequested(): bool
    {
        return $this->status === self::STATUS_REVISION_REQUESTED;
    }

    public function isFinalRejected(): bool
    {
        return in_array($this->status, [self::STATUS_REJECTED_ADMIN, self::STATUS_REJECTED_APPROVER], true);
    }

    public function canBeEditedByAdmin(): bool
    {
        if ($this->status === self::STATUS_PENDING) {
            return true;
        }

        if ($this->status !== self::STATUS_REVISION_REQUESTED) {
            return false;
        }

        return $this->validated_by_admin_id !== null
            || $this->approvalFlows()->where('role', 'admin')->where('status', 'approved')->exists();
    }

    public function canViewFinalDocumentPreview(): bool
    {
        $this->loadMissing('qrCode');

        if ($this->status !== self::STATUS_FINISHED) {
            return false;
        }

        $hasValidation = $this->validated_by_admin_id !== null
            && $this->validated_by_admin_at !== null;
        $hasFinalApproval = ($this->approved_by_id !== null && $this->approved_at !== null)
            || $this->approvalFlows()->where('status', 'approved')->exists();

        if (! $hasValidation || ! $hasFinalApproval || blank($this->qr_token)) {
            return false;
        }

        $qrCode = $this->qrCode;

        if ($qrCode !== null && data_get($qrCode, 'status') !== SuratQrCode::STATUS_ACTIVE) {
            return false;
        }

        return true;
    }

    /**
     * @return array{name: string|null, nim: string|null, nomor_induk: string|null, email: string|null}
     */
    protected function extractManualSubjectIdentity(): array
    {
        $data = [];

        if ($this->relationLoaded('dataEntries')) {
            $data = $this->dataEntries
                ->mapWithKeys(fn (SuratData $entry): array => [
                    $entry->field_name => $entry->field_value,
                ])
                ->all();
        }

        if ($data === [] && filled($this->isi_surat)) {
            $decoded = json_decode((string) $this->isi_surat, true);
            $data = is_array($decoded) ? Arr::wrap(Arr::get($decoded, 'data', [])) : [];
        }

        return [
            'name' => Arr::get($data, 'subject_name')
                ?? Arr::get($data, 'nama')
                ?? Arr::get($data, 'nama_pemohon')
                ?? Arr::get($data, 'nama_mahasiswa')
                ?? Arr::get($data, 'nama_dosen'),
            'nim' => Arr::get($data, 'nim')
                ?? Arr::get($data, 'nim_pemohon')
                ?? Arr::get($data, 'nim_mahasiswa'),
            'nomor_induk' => Arr::get($data, 'nomor_induk')
                ?? Arr::get($data, 'nomor_induk_pemohon')
                ?? Arr::get($data, 'nomor_induk_mahasiswa'),
            'email' => Arr::get($data, 'email')
                ?? Arr::get($data, 'email_pemohon'),
        ];
    }

    public function hasIncompleteCampusData(): bool
    {
        $this->loadMissing('jenisSurat', 'dataEntries');

        $payload = $this->dataEntries
            ->mapWithKeys(fn (SuratData $entry): array => [
                $entry->field_name => $entry->field_value,
            ])
            ->all();

        return SuratDataContract::missingRequiredCampusFields(
            $this->jenisSurat?->field_config ?? [],
            $payload,
        ) !== [];
    }

    /**
     * @return array<string, string>
     */
    public function missingCampusDataFields(): array
    {
        $this->loadMissing('jenisSurat', 'dataEntries');

        $payload = $this->dataEntries
            ->mapWithKeys(fn (SuratData $entry): array => [
                $entry->field_name => $entry->field_value,
            ])
            ->all();

        return SuratDataContract::missingRequiredCampusFields(
            $this->jenisSurat?->field_config ?? [],
            $payload,
        );
    }
}
