<?php

namespace App\Modules\Wims\Services\Mahasiswa\Report;

use App\Models\Magang\PendaftaranMagang;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentFinalReportFileService
{
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
        $absolutePath = Storage::disk('public')->path($registration->laporan_akhir_path);
        abort_unless(is_file($absolutePath), 404);

        return response()->file($absolutePath, [
            'Content-Disposition' => 'inline; filename="' . $registration->finalReportDownloadName() . '"',
        ]);
    }

    public function download(PendaftaranMagang $registration): BinaryFileResponse
    {
        $absolutePath = Storage::disk('public')->path($registration->laporan_akhir_path);
        abort_unless(is_file($absolutePath), 404);

        return response()->download($absolutePath, $registration->finalReportDownloadName());
    }
}
