<?php

namespace App\Modules\Wims\Services\Mahasiswa\Report;

use App\Models\Magang\PendaftaranMagang;
use App\Modules\Wims\Services\Mahasiswa\Period\StudentPeriodResolverService;
use App\Modules\Wims\Services\Shared\Assessment\FinalReportAccessService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentFinalReportFileService
{
    public function __construct(
        private readonly FinalReportAccessService $finalReportAccessService,
        private readonly StudentPeriodResolverService $studentPeriodResolverService,
    ) {}

    public function resolveLatestRegistrationWithReport(int $userId): PendaftaranMagang
    {
        return $this->resolveRegistrationWithReport($userId);
    }

    public function resolveRegistrationWithReport(int $userId, ?int $registrationId = null): PendaftaranMagang
    {
        $registration = $this->studentPeriodResolverService->resolveSelectedRegistration($userId, $registrationId);

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
