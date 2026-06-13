<?php

namespace App\Services\Wims\Shared\Assessment;

use App\Models\PendaftaranMagang;
use Illuminate\Support\Facades\Storage;

class FinalReportAccessService
{
    public function resolveAbsolutePath(PendaftaranMagang $pendaftaran): string
    {
        return Storage::disk('public')->path($pendaftaran->laporan_akhir_path);
    }
}
