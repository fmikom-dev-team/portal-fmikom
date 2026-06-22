<?php

namespace App\Modules\Wims\Services\Mahasiswa\Report;

use App\Models\Magang\PendaftaranMagang;
use App\Modules\Wims\Services\Shared\Assessment\FinalReportAccessService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentFinalReportFileService
{
    public function __construct(
        private readonly FinalReportAccessService $finalReportAccessService,
    ) {}

    public function resolveLatestRegistrationWithReport(int $userId): PendaftaranMagang
    {
        $registration = PendaftaranMagang::query()
            ->forMahasiswa($userId)
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->first();

        abort_unless($registration && filled($registration->laporan_akhir_path), 404);

        return $registration;
    }

    public function view(PendaftaranMagang $registration): BinaryFileResponse
    {
        $absolutePath = $this->finalReportAccessService->resolveAbsolutePath($registration);

        return response()->file($absolutePath, [
            'Content-Disposition' => 'inline; filename="'.$this->finalReportAccessService->resolveDownloadName($registration).'"',
        ]);
    }

    public function download(PendaftaranMagang $registration): BinaryFileResponse
    {
        $absolutePath = $this->finalReportAccessService->resolveAbsolutePath($registration);

        return response()->download($absolutePath, $this->finalReportAccessService->resolveDownloadName($registration));
    }
}
