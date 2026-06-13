<?php

namespace App\Modules\Wims\Services\Mahasiswa\Report;

use App\Models\PendaftaranMagang;
use Illuminate\Http\UploadedFile;

class StudentFinalReportActionService
{
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
        $path = $file->store('laporan-akhir', 'public');

        $registration->update([
            'laporan_akhir_path' => $path,
            'laporan_akhir_original_name' => $file->getClientOriginalName(),
            'laporan_akhir_uploaded_at' => now(),
        ]);
    }
}
