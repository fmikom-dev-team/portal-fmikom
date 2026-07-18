<?php

namespace App\Modules\Wims\Services\Mahasiswa\Registration;

use App\Models\Magang\PendaftaranMagang;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class StudentRegistrationActionService
{
    public function __construct(
        private readonly StudentProposalAttachmentService $proposalAttachmentService,
    ) {}

    public function buildPayload(array $input): array
    {
        return [
            'tanggal_mulai' => $input['tanggal_mulai'] ?? null,
            'tanggal_selesai' => $input['tanggal_selesai'] ?? null,
            'perusahaan_diminati_nama' => $this->nullIfBlank($input['perusahaan_diminati_nama'] ?? null),
            'perusahaan_diminati_alamat' => $this->nullIfBlank($input['perusahaan_diminati_alamat'] ?? null),
            'catatan_pengajuan' => $this->nullIfBlank($input['catatan_pengajuan'] ?? null),
            'catatan_revisi_admin' => null,
            'perusahaan_id' => null,
            'dosen_pembimbing_id' => null,
            'status' => 'pending',
        ];
    }

    public function resubmitRevision(PendaftaranMagang $registration, array $payload, ?UploadedFile $proposalFile = null): void
    {
        $registration = $registration->fresh();
        $oldPath = $registration->proposal_pkl_path;
        $newPath = $oldPath;
        $newOriginalName = $registration->proposal_pkl_original_name;
        $newUploadedAt = $registration->proposal_pkl_uploaded_at;

        if ($proposalFile) {
            $newPath = $this->proposalAttachmentService->store($proposalFile);
            $newOriginalName = $proposalFile->getClientOriginalName();
            $newUploadedAt = now();
        }

        try {
            DB::transaction(function () use ($registration, $payload, $newPath, $newOriginalName, $newUploadedAt): void {
                $locked = PendaftaranMagang::where('id', $registration->id)
                    ->lockForUpdate()
                    ->first();

                if (! $locked || $locked->status !== 'revisi') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'registration' => 'Status pendaftaran telah berubah atau tidak valid untuk perbaikan.',
                    ]);
                }

                $locked->update([
                    ...$payload,
                    'proposal_pkl_path' => $newPath,
                    'proposal_pkl_original_name' => $newOriginalName,
                    'proposal_pkl_uploaded_at' => $newUploadedAt,
                ]);
            });
        } catch (Throwable $throwable) {
            if ($proposalFile) {
                $this->proposalAttachmentService->deleteIfExists($newPath);
            }

            throw $throwable;
        }

        if ($proposalFile && $oldPath !== $newPath) {
            $this->proposalAttachmentService->deleteIfExists($oldPath);
        }
    }

    public function create(User $user, array $payload, UploadedFile $proposalFile): void
    {
        $newPath = $this->proposalAttachmentService->store($proposalFile);

        try {
            DB::transaction(function () use ($user, $payload, $proposalFile, $newPath): void {
                $latestRegistration = PendaftaranMagang::where('mahasiswa_id', $user->id)
                    ->orderByDesc('tanggal_mulai')
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();

                if ($latestRegistration && ! in_array($latestRegistration->status, ['revisi', 'rejected', 'selesai'], true)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'registration' => 'Pendaftaran aktif atau pending sudah ada. Selesaikan atau tunggu review kampus.',
                    ]);
                }

                PendaftaranMagang::create([
                    'mahasiswa_id' => $user->id,
                    ...$payload,
                    'proposal_pkl_path' => $newPath,
                    'proposal_pkl_original_name' => $proposalFile->getClientOriginalName(),
                    'proposal_pkl_uploaded_at' => now(),
                ]);
            });
        } catch (Throwable $throwable) {
            $this->proposalAttachmentService->deleteIfExists($newPath);

            throw $throwable;
        }
    }

    private function nullIfBlank(?string $value): ?string
    {
        return blank($value) ? null : $value;
    }
}
