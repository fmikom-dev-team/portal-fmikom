<?php

namespace App\Modules\Fast\Workflow\Approvals;

use App\Models\Surat;
use App\Models\SuratApprovalFlow;
use App\Models\User;
use App\Modules\Fast\DTOs\SuratDataContract;
use App\Modules\Fast\Services\Shared\SuratDocumentGeneratorService;
use App\Modules\Fast\Services\Shared\SuratHistoryService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Illuminate\Validation\ValidationException;

class FastApprovalWorkflowService
{
    public function __construct(
        protected SuratDocumentGeneratorService $documentGenerator,
    ) {
    }

    public const ROLE_ADMIN = 'admin';

    public const ROLE_KAPRODI = 'kaprodi';

    public const ROLE_DEKAN = 'dekan';

    public function approve(Surat $surat, string $role, ?User $actor = null, ?string $notes = null): SuratApprovalFlow
    {
        $this->guardApproval($surat, $role);
        $surat->loadMissing('jenisSurat.approvalRole');

        return DB::transaction(function () use ($surat, $role, $actor, $notes): SuratApprovalFlow {
            $flow = $surat->approvalFlows()->create([
                'approver_id' => $actor?->id,
                'urutan' => $this->resolveOrder($role),
                'role' => $role,
                'status' => SuratApprovalFlow::STATUS_APPROVED,
                'keterangan' => $this->approvalLabel($role),
                'catatan' => $notes,
                'approved_at' => now(),
                'tanggal_aksi' => now(),
            ]);

            $suratUpdatePayload = [
                'status' => $this->nextStatusForSurat($surat, $role),
            ];

            if ($role === self::ROLE_ADMIN) {
                $suratUpdatePayload['validated_by_admin_id'] = $actor?->id;
                $suratUpdatePayload['validated_by_admin_at'] = now();
            }

            $surat->update($suratUpdatePayload);

            if ($this->shouldPrepareDraftAfterApproval($surat, $role)) {
                $this->documentGenerator->prepareDraft($surat->fresh());
            }

            if ($this->shouldGenerateAfterApproval($surat, $role)) {
                $this->documentGenerator->generate($surat->fresh());
            }

            if ($role === self::ROLE_ADMIN) {
                SuratHistoryService::validated($surat->id, $notes);
            } else {
                SuratHistoryService::approved($surat->id, strtoupper($role), $notes);
            }

            return $flow;
        });
    }

    public function reject(Surat $surat, string $role, ?User $actor = null, ?string $notes = null): SuratApprovalFlow
    {
        if ($role === self::ROLE_ADMIN) {
            return $this->rejectAdmin($surat, $actor, $notes);
        }

        return $this->requestRevision($surat, $role, $actor, $notes);
    }

    public function rejectAdmin(Surat $surat, ?User $actor = null, ?string $notes = null): SuratApprovalFlow
    {
        $this->guardFinalRejection($surat, self::ROLE_ADMIN);

        return DB::transaction(function () use ($surat, $actor, $notes): SuratApprovalFlow {
            $flow = $surat->approvalFlows()->create([
                'approver_id' => $actor?->id,
                'urutan' => $this->resolveOrder(self::ROLE_ADMIN),
                'role' => self::ROLE_ADMIN,
                'status' => SuratApprovalFlow::STATUS_REJECTED_FINAL,
                'keterangan' => 'Ditolak ADMIN',
                'catatan' => $notes,
                'approved_at' => now(),
                'tanggal_aksi' => now(),
            ]);

            $surat->update([
                'status' => Surat::STATUS_REJECTED_ADMIN,
                'catatan_revisi' => null,
                'rejection_reason' => $notes,
                'nomor_surat_status' => filled($surat->nomor_surat)
                    ? Surat::NOMOR_SURAT_STATUS_VOID
                    : $surat->nomor_surat_status,
            ]);

            SuratHistoryService::rejected(
                $surat->id,
                (string) $notes,
                'Ditolak Admin',
                ['role' => self::ROLE_ADMIN, 'type' => 'final_reject']
            );

            return $flow;
        });
    }

    public function requestRevision(Surat $surat, string $role, ?User $actor = null, ?string $notes = null): SuratApprovalFlow
    {
        $this->guardRevisionRequest($surat, $role);

        return DB::transaction(function () use ($surat, $role, $actor, $notes): SuratApprovalFlow {
            $nextRevisionCount = ((int) $surat->revisi_ke) + 1;
            $flow = $surat->approvalFlows()->create([
                'approver_id' => $actor?->id,
                'urutan' => $this->resolveOrder($role),
                'role' => $role,
                'status' => SuratApprovalFlow::STATUS_REVISION_REQUESTED,
                'keterangan' => 'Dikembalikan '.strtoupper($role).' untuk revisi',
                'catatan' => $notes,
                'approved_at' => now(),
                'tanggal_aksi' => now(),
            ]);

            $surat->update([
                'status' => Surat::STATUS_REVISION_REQUESTED,
                'revisi_ke' => $nextRevisionCount,
                'catatan_revisi' => $notes,
                'rejection_reason' => null,
            ]);

            SuratHistoryService::revisionRequested(
                $surat->id,
                $nextRevisionCount,
                strtoupper($role),
                $notes,
            );

            return $flow;
        });
    }

    public function finalReject(Surat $surat, string $role, ?User $actor = null, ?string $notes = null): SuratApprovalFlow
    {
        $this->guardFinalRejection($surat, $role);

        return DB::transaction(function () use ($surat, $role, $actor, $notes): SuratApprovalFlow {
            $flow = $surat->approvalFlows()->create([
                'approver_id' => $actor?->id,
                'urutan' => $this->resolveOrder($role),
                'role' => $role,
                'status' => SuratApprovalFlow::STATUS_REJECTED_FINAL,
                'keterangan' => 'Ditolak final '.strtoupper($role),
                'catatan' => $notes,
                'approved_at' => now(),
                'tanggal_aksi' => now(),
            ]);

            $surat->update([
                'status' => Surat::STATUS_REJECTED_APPROVER,
                'catatan_revisi' => null,
                'rejection_reason' => $notes,
                'nomor_surat_status' => filled($surat->nomor_surat)
                    ? Surat::NOMOR_SURAT_STATUS_VOID
                    : $surat->nomor_surat_status,
            ]);

            SuratHistoryService::rejected(
                $surat->id,
                (string) $notes,
                'Ditolak '.strtoupper($role),
                ['role' => $role, 'type' => 'final_reject']
            );

            return $flow;
        });
    }

    public function nextStatusForApproval(string $role): string
    {
        return match ($role) {
            self::ROLE_ADMIN => throw new InvalidArgumentException('Status admin harus ditentukan dari approval_role_id jenis surat.'),
            self::ROLE_KAPRODI => Surat::STATUS_APPROVED_KAPRODI,
            self::ROLE_DEKAN => Surat::STATUS_APPROVED_DEKAN,
            default => throw new InvalidArgumentException('Role approval tidak dikenali.'),
        };
    }

    public function resolveOrder(string $role): int
    {
        return match ($role) {
            self::ROLE_ADMIN => 1,
            self::ROLE_KAPRODI => 2,
            self::ROLE_DEKAN => 3,
            default => throw new InvalidArgumentException('Urutan approval tidak dikenali.'),
        };
    }

    protected function approvalLabel(string $role): string
    {
        return match ($role) {
            self::ROLE_ADMIN => 'Validasi Admin',
            self::ROLE_KAPRODI => 'Disetujui Kaprodi',
            self::ROLE_DEKAN => 'Disetujui Dekan',
            default => 'Approval',
        };
    }

    protected function guardApproval(Surat $surat, string $role): void
    {
        if ($role === self::ROLE_ADMIN && $surat->status === Surat::STATUS_PENDING) {
            $surat->loadMissing('jenisSurat', 'dataEntries');

            $payload = $surat->dataEntries
                ->mapWithKeys(fn ($entry): array => [
                    $entry->field_name => $entry->field_value,
                ])
                ->all();

            $missingCampusFields = SuratDataContract::missingRequiredCampusFields(
                $surat->jenisSurat?->field_config ?? [],
                $payload,
            );

            if ($missingCampusFields !== []) {
                throw ValidationException::withMessages([
                    'data_kampus' => 'Masih ada data kampus yang wajib dilengkapi sebelum validasi admin: '
                        .implode(', ', array_values($missingCampusFields))
                        .'.',
                ]);
            }
        }

        if (! $surat->canBeApprovedByRole($role)) {
            abort(403, 'Status surat tidak sesuai untuk approval ini.');
        }
    }

    protected function guardRejection(Surat $surat, string $role): void
    {
        if (! $surat->canBeRejectedByRole($role)) {
            abort(403, 'Status surat tidak sesuai untuk penolakan ini.');
        }
    }

    protected function guardRevisionRequest(Surat $surat, string $role): void
    {
        if (! $surat->canRequestRevisionByRole($role)) {
            abort(403, 'Status surat tidak sesuai untuk permintaan revisi ini.');
        }
    }

    protected function guardFinalRejection(Surat $surat, string $role): void
    {
        if (! $surat->canBeFinalRejectedByRole($role)) {
            abort(403, 'Status surat tidak sesuai untuk penolakan final ini.');
        }
    }

    public function nextStatusForSurat(Surat $surat, string $role): string
    {
        if ($role === self::ROLE_ADMIN) {
            return $surat->requiresFinalApproval()
                ? Surat::STATUS_VALIDATED_ADMIN
                : Surat::STATUS_FINISHED;
        }

        return $this->nextStatusForApproval($role);
    }

    protected function shouldGenerateAfterApproval(Surat $surat, string $role): bool
    {
        if ($role === self::ROLE_ADMIN) {
            return ! $surat->requiresFinalApproval();
        }

        return $surat->finalApprovalRoleSlug() === $role;
    }

    protected function shouldPrepareDraftAfterApproval(Surat $surat, string $role): bool
    {
        return $role === self::ROLE_ADMIN && $surat->requiresFinalApproval();
    }
}


