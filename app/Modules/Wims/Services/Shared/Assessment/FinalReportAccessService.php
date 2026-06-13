<?php

namespace App\Modules\Wims\Services\Shared\Assessment;

use App\Models\Magang\PendaftaranMagang;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FinalReportAccessService
{
    private const DISK = 'public';

    public function resolveAbsolutePath(PendaftaranMagang $pendaftaran): string
    {
        abort_unless(
            filled($pendaftaran->laporan_akhir_path) && Storage::disk(self::DISK)->exists($pendaftaran->laporan_akhir_path),
            404,
            'File laporan akhir tidak ditemukan.',
        );

        return Storage::disk(self::DISK)->path($pendaftaran->laporan_akhir_path);
    }

    public function storeFinalReport(UploadedFile $file): string
    {
        $directory = 'laporan-akhir';
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'pdf');
        $filename = Str::uuid() . '.' . $extension;

        Storage::disk(self::DISK)->putFileAs($directory, $file, $filename);

        return $directory . '/' . $filename;
    }

    public function deleteIfExists(?string $path): void
    {
        if (! filled($path) || ! Storage::disk(self::DISK)->exists($path)) {
            return;
        }

        Storage::disk(self::DISK)->delete($path);
    }

    public function resolveDownloadName(PendaftaranMagang $pendaftaran): string
    {
        return str($pendaftaran->finalReportDownloadName())
            ->replace(['../', '..\\', '"', "\r", "\n"], '')
            ->value();
    }
}
