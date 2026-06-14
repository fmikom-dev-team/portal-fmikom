<?php

namespace App\Modules\Wims\Services\Shared\Assessment;

use App\Models\Magang\PendaftaranMagang;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class FinalReportAccessService
{
    private const DISK = 'public';

    public function resolveAbsolutePath(PendaftaranMagang $pendaftaran): string
    {
        $absolutePath = filled($pendaftaran->laporan_akhir_path)
            ? $this->normalizeLocalPath(Storage::disk(self::DISK)->path($pendaftaran->laporan_akhir_path))
            : null;

        if ($absolutePath) {
            clearstatcache(true, $absolutePath);
        }

        abort_unless(
            filled($pendaftaran->laporan_akhir_path) && Storage::disk(self::DISK)->exists($pendaftaran->laporan_akhir_path),
            404,
            'File laporan akhir tidak ditemukan.',
        );

        return $absolutePath ?: $this->normalizeLocalPath(Storage::disk(self::DISK)->path($pendaftaran->laporan_akhir_path));
    }

    public function storeFinalReport(UploadedFile $file): string
    {
        $directory = 'laporan-akhir';
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'pdf');
        $filename = Str::uuid() . '.' . $extension;
        $path = $directory . '/' . $filename;

        $contents = file_get_contents($file->getRealPath());

        if ($contents === false) {
            throw new RuntimeException('Gagal membaca file laporan akhir yang diunggah.');
        }

        if (! Storage::disk(self::DISK)->put($path, $contents)) {
            throw new RuntimeException('Gagal menyimpan file laporan akhir.');
        }

        clearstatcache(true, $this->normalizeLocalPath(Storage::disk(self::DISK)->path($path)));

        return $path;
    }

    public function deleteIfExists(?string $path): void
    {
        if (! filled($path)) {
            return;
        }

        $disk = Storage::disk(self::DISK);
        $absolutePath = $this->normalizeLocalPath(Storage::disk(self::DISK)->path($path));

        for ($attempt = 0; $attempt < 3; $attempt++) {
            clearstatcache(true, $absolutePath);

            if (! is_file($absolutePath) && ! $disk->exists($path)) {
                return;
            }

            if ($disk->delete($path)) {
                clearstatcache(true, $absolutePath);

                if (! $disk->exists($path) && ! is_file($absolutePath)) {
                    return;
                }
            }

            usleep(100000);
        }

        logger()->warning('Failed to delete replaced final report file.', [
            'disk' => self::DISK,
            'path' => $path,
            'absolute_path' => $absolutePath,
        ]);
    }

    private function normalizeLocalPath(string $path): string
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }

    public function resolveDownloadName(PendaftaranMagang $pendaftaran): string
    {
        return str($pendaftaran->finalReportDownloadName())
            ->replace(['../', '..\\', '"', "\r", "\n"], '')
            ->value();
    }
}
