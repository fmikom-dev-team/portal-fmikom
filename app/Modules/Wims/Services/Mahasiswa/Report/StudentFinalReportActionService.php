<?php

namespace App\Modules\Wims\Services\Mahasiswa\Report;

use App\Models\Magang\PendaftaranMagang;
use App\Modules\Wims\Services\Mahasiswa\Period\StudentPeriodResolverService;
use App\Modules\Wims\Services\Shared\Assessment\FinalReportAccessService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class StudentFinalReportActionService
{
    public function __construct(
        private readonly FinalReportAccessService $finalReportAccessService,
        private readonly StudentPeriodResolverService $studentPeriodResolverService,
    ) {}

    public function resolveLatestRegistration(int $userId): ?PendaftaranMagang
    {
        return $this->studentPeriodResolverService->resolveSelectedRegistration($userId);
    }

    public function resolveRegistration(int $userId, ?int $registrationId = null): ?PendaftaranMagang
    {
        return $this->studentPeriodResolverService->resolveSelectedRegistration($userId, $registrationId);
    }

    public function upload(PendaftaranMagang $registration, UploadedFile $file): void
    {
        $registration = $registration->fresh();
        $oldPath = $registration->laporan_akhir_path;
        $newPath = null;

        try {
            $newPath = $this->finalReportAccessService->storeFinalReport($file);

            DB::transaction(function () use ($registration, $file, $newPath): void {
                $lockedRegistration = PendaftaranMagang::query()
                    ->lockForUpdate()
                    ->findOrFail($registration->id);

                if (! $lockedRegistration->isPostInternshipPhase()) {
                    throw ValidationException::withMessages([
                        'laporan_akhir' => 'Laporan akhir hanya dapat diunggah setelah periode PKL berakhir.',
                    ]);
                }

                $lockedRegistration->update([
                    'laporan_akhir_path' => $newPath,
                    'laporan_akhir_original_name' => $file->getClientOriginalName(),
                    'laporan_akhir_uploaded_at' => now(),
                ]);
            });
        } catch (Throwable $throwable) {
            $this->finalReportAccessService->deleteIfExists($newPath);

            throw $throwable;
        }

        if ($oldPath !== $newPath) {
            $this->finalReportAccessService->deleteIfExists($oldPath);
        }
    }
}
