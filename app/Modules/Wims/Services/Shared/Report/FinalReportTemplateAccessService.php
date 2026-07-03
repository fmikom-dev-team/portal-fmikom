<?php

namespace App\Modules\Wims\Services\Shared\Report;

use App\Models\Magang\FinalReportTemplate;
use App\Support\WimsStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class FinalReportTemplateAccessService
{
    private const DISK = 'local';

    private const DIRECTORY = 'laporan-template-akhir';

    public function storeTemplateFile(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'pdf');
        $filename = Str::uuid().'.'.$extension;
        $path = self::DIRECTORY.'/'.$filename;

        $contents = file_get_contents($file->getRealPath());

        if ($contents === false) {
            throw new RuntimeException('Gagal membaca file template laporan akhir yang diunggah.');
        }

        if (! Storage::disk(self::DISK)->put($path, $contents)) {
            throw new RuntimeException('Gagal menyimpan template laporan akhir.');
        }

        clearstatcache(true, $this->normalizePath(Storage::disk(self::DISK)->path($path)));

        return $path;
    }

    public function resolveActiveTemplateDownloadName(FinalReportTemplate $template): string
    {
        $safeName = str($template->original_name ?: $template->title ?: 'template-laporan-akhir')
            ->replace(['../', '..\\', '"', "\r", "\n"], '')
            ->value();

        if (pathinfo($safeName, PATHINFO_EXTENSION) !== '') {
            return $safeName;
        }

        $extension = pathinfo($template->file_path, PATHINFO_EXTENSION);

        return $extension ? $safeName.'.'.$extension : $safeName;
    }

    public function resolveAbsolutePath(FinalReportTemplate $template): string
    {
        $location = WimsStorage::locate($template->file_path);
        $absolutePath = $location['absolute_path'] ?? null;

        if ($absolutePath) {
            clearstatcache(true, $absolutePath);
        }

        abort_unless($location !== null, 404, 'File template laporan akhir tidak ditemukan.');

        return $absolutePath ?: $this->normalizePath(Storage::disk(self::DISK)->path($template->file_path));
    }

    public function deleteIfExists(?string $path): void
    {
        if (! filled($path)) {
            return;
        }

        $location = WimsStorage::locate($path);
        $absolutePath = $location['absolute_path'] ?? $this->normalizePath(Storage::disk(self::DISK)->path($path));

        for ($attempt = 0; $attempt < 3; $attempt++) {
            clearstatcache(true, $absolutePath);

            if (! is_file($absolutePath) && ! WimsStorage::exists($path)) {
                return;
            }

            WimsStorage::delete($path);

            clearstatcache(true, $absolutePath);

            if (! WimsStorage::exists($path) && ! is_file($absolutePath)) {
                return;
            }

            usleep(100000);
        }

        logger()->warning('Failed to delete replaced final report template file.', [
            'disk' => self::DISK,
            'path' => $path,
            'absolute_path' => $absolutePath,
        ]);
    }

    private function normalizePath(string $path): string
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }
}
