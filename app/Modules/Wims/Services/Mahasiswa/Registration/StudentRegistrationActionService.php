<?php

namespace App\Modules\Wims\Services\Mahasiswa\Registration;

use App\Models\Magang\PendaftaranMagang;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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
            'status_kip' => $this->nullIfBlank($input['status_kip'] ?? null),
            'sks_ditempuh' => $input['sks_ditempuh'] ?? null,
            'bidang_minat' => $this->nullIfBlank($input['bidang_minat'] ?? null),
            'bidang_minat_lainnya' => ($input['bidang_minat'] ?? null) === 'Lainnya'
                ? $this->nullIfBlank($input['bidang_minat_lainnya'] ?? null)
                : null,
            'ukuran_seragam' => $this->nullIfBlank($input['ukuran_seragam'] ?? null),
            'ukuran_seragam_custom' => ($input['ukuran_seragam'] ?? null) === 'Custom'
                ? $this->nullIfBlank($input['ukuran_seragam_custom'] ?? null)
                : null,
            'catatan_revisi_admin' => null,
            'perusahaan_id' => null,
            'dosen_pembimbing_id' => null,
            'status' => 'pending',
        ];
    }

    public function resubmitRevision(
        PendaftaranMagang $registration,
        array $payload,
        ?UploadedFile $proposalFile = null,
        ?UploadedFile $transcriptFile = null,
        ?UploadedFile $recommendationFile = null,
        bool $removeRecommendation = false,
    ): void
    {
        $registration = $registration->fresh();
        $replacements = $this->storeReplacements($registration, $proposalFile, $transcriptFile, $recommendationFile, $removeRecommendation);

        try {
            DB::transaction(function () use ($registration, $payload, $replacements): void {
                $locked = PendaftaranMagang::where('id', $registration->id)
                    ->lockForUpdate()
                    ->first();

                if (! $locked || $locked->status !== 'revisi') {
                    throw ValidationException::withMessages([
                        'registration' => 'Status pendaftaran telah berubah atau tidak valid untuk perbaikan.',
                    ]);
                }

                $locked->update([
                    ...$payload,
                    ...$replacements['attributes'],
                ]);
            });
        } catch (Throwable $throwable) {
            $this->deletePaths($replacements['new_paths']);

            throw $throwable;
        }

        $this->deletePaths($replacements['old_paths']);
    }

    public function create(
        User $user,
        array $payload,
        ?UploadedFile $proposalFile,
        ?UploadedFile $transcriptFile,
        ?UploadedFile $recommendationFile = null,
    ): void
    {
        if (! $proposalFile || ! $transcriptFile) {
            throw ValidationException::withMessages([
                'proposal_pkl' => 'Proposal PKL wajib dilampirkan saat pendaftaran baru.',
                'transkrip_nilai' => 'Transkrip nilai terakhir wajib dilampirkan saat pendaftaran baru.',
            ]);
        }

        $storedPaths = [];

        try {
            $proposalPath = $this->proposalAttachmentService->store($proposalFile);
            $storedPaths[] = $proposalPath;
            $transcriptPath = $this->proposalAttachmentService->storeTranscript($transcriptFile);
            $storedPaths[] = $transcriptPath;
            $recommendationPath = $recommendationFile
                ? $this->proposalAttachmentService->storeRecommendation($recommendationFile)
                : null;
            if ($recommendationPath) {
                $storedPaths[] = $recommendationPath;
            }

            DB::transaction(function () use ($user, $payload, $proposalFile, $transcriptFile, $recommendationFile, $proposalPath, $transcriptPath, $recommendationPath): void {
                $latestRegistration = PendaftaranMagang::where('mahasiswa_id', $user->id)
                    ->orderByDesc('tanggal_mulai')
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();

                if ($latestRegistration && ! in_array($latestRegistration->status, ['revisi', 'rejected', 'selesai'], true)) {
                    throw ValidationException::withMessages([
                        'registration' => 'Pendaftaran aktif atau pending sudah ada. Selesaikan atau tunggu review kampus.',
                    ]);
                }

                PendaftaranMagang::create([
                    'mahasiswa_id' => $user->id,
                    ...$payload,
                    'proposal_pkl_path' => $proposalPath,
                    'proposal_pkl_original_name' => $proposalFile->getClientOriginalName(),
                    'proposal_pkl_uploaded_at' => now(),
                    'transkrip_nilai_path' => $transcriptPath,
                    'transkrip_nilai_original_name' => $transcriptFile->getClientOriginalName(),
                    'transkrip_nilai_uploaded_at' => now(),
                    'surat_rekomendasi_kaprodi_path' => $recommendationPath,
                    'surat_rekomendasi_kaprodi_original_name' => $recommendationFile?->getClientOriginalName(),
                    'surat_rekomendasi_kaprodi_uploaded_at' => $recommendationFile ? now() : null,
                ]);
            });
        } catch (Throwable $throwable) {
            $this->deletePaths($storedPaths);

            throw $throwable;
        }
    }

    private function nullIfBlank(?string $value): ?string
    {
        return blank($value) ? null : $value;
    }

    private function storeReplacements(
        PendaftaranMagang $registration,
        ?UploadedFile $proposalFile,
        ?UploadedFile $transcriptFile,
        ?UploadedFile $recommendationFile,
        bool $removeRecommendation,
    ): array {
        $files = [
            'proposal' => [$proposalFile, 'proposal_pkl_path', 'proposal_pkl_original_name', 'proposal_pkl_uploaded_at', 'store'],
            'transcript' => [$transcriptFile, 'transkrip_nilai_path', 'transkrip_nilai_original_name', 'transkrip_nilai_uploaded_at', 'storeTranscript'],
            'recommendation' => [$recommendationFile, 'surat_rekomendasi_kaprodi_path', 'surat_rekomendasi_kaprodi_original_name', 'surat_rekomendasi_kaprodi_uploaded_at', 'storeRecommendation'],
        ];
        $attributes = [];
        $newPaths = [];
        $oldPaths = [];

        foreach ($files as [$file, $pathKey, $nameKey, $uploadedAtKey, $storeMethod]) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $newPath = $this->proposalAttachmentService->{$storeMethod}($file);
            $attributes[$pathKey] = $newPath;
            $attributes[$nameKey] = $file->getClientOriginalName();
            $attributes[$uploadedAtKey] = now();
            $newPaths[] = $newPath;

            if (filled($registration->{$pathKey})) {
                $oldPaths[] = $registration->{$pathKey};
            }
        }

        if ($removeRecommendation && ! $recommendationFile instanceof UploadedFile) {
            if (filled($registration->surat_rekomendasi_kaprodi_path)) {
                $oldPaths[] = $registration->surat_rekomendasi_kaprodi_path;
            }

            $attributes['surat_rekomendasi_kaprodi_path'] = null;
            $attributes['surat_rekomendasi_kaprodi_original_name'] = null;
            $attributes['surat_rekomendasi_kaprodi_uploaded_at'] = null;
        }

        return [
            'attributes' => $attributes,
            'new_paths' => $newPaths,
            'old_paths' => $oldPaths,
        ];
    }

    private function deletePaths(array $paths): void
    {
        foreach ($paths as $path) {
            $this->proposalAttachmentService->deleteIfExists($path);
        }
    }
}
