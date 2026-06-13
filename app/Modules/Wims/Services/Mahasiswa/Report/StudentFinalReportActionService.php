<?php

namespace App\Modules\Wims\Services\Mahasiswa\Report;

use App\Models\Magang\PendaftaranMagang;
use App\Modules\Wims\Services\Shared\Assessment\FinalReportAccessService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class StudentFinalReportActionService
{
    public function __construct(
        private readonly FinalReportAccessService $finalReportAccessService,
    ) {
    }

    public function resolveLatestRegistration(int $userId): ?PendaftaranMagang
    {
        return PendaftaranMagang::query()
            ->forMahasiswa($userId)
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->first();
    }

    public function upload(PendaftaranMagang $registration, UploadedFile $file): void
    {
        $registration = $registration->fresh();
        $oldPath = $registration->laporan_akhir_path;
        $newPath = null;

        try {
            $newPath = $this->finalReportAccessService->storeFinalReport($file);

            DB::transaction(function () use ($registration, $file, $newPath): void {
                $registration->update([
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
