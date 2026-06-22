<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class WimsStorage
{
    private const PRIMARY_DISK = 'local';

    private const LEGACY_DISK = 'public';

    public static function storeUploadedFileAs(UploadedFile $file, string $directory, string $filename): string
    {
        $stored = Storage::disk(self::PRIMARY_DISK)->putFileAs($directory, $file, $filename);

        if (! $stored) {
            throw new RuntimeException(sprintf('Failed to store WIMS file at %s/%s.', trim($directory, '/'), $filename));
        }

        return trim($directory.'/'.$filename, '/');
    }

    /**
     * @param  string|array<int, string>|null  $paths
     */
    public static function delete(string|array|null $paths): void
    {
        foreach (Arr::wrap($paths) as $path) {
            if (! is_string($path) || trim($path) === '') {
                continue;
            }

            foreach ([self::PRIMARY_DISK, self::LEGACY_DISK] as $diskName) {
                $disk = Storage::disk($diskName);

                if ($disk->exists($path)) {
                    $disk->delete($path);
                }
            }
        }
    }

    public static function exists(?string $path): bool
    {
        return self::locate($path) !== null;
    }

    /**
     * Locate a stored WIMS file on the private disk first, then on the legacy public disk.
     *
     * @return array{disk: string, relative_path: string, absolute_path: string, mime_type: ?string}|null
     */
    public static function locate(?string $path): ?array
    {
        if (! is_string($path)) {
            return null;
        }

        $relativePath = ltrim(trim($path), '/');

        if ($relativePath === '') {
            return null;
        }

        foreach ([self::PRIMARY_DISK, self::LEGACY_DISK] as $diskName) {
            $disk = Storage::disk($diskName);

            if (! $disk->exists($relativePath)) {
                continue;
            }

            $absolutePath = $disk->path($relativePath);
            $realPath = realpath($absolutePath);
            $basePath = realpath($disk->path(''));

            if ($basePath) {
                $basePath = rtrim($basePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            }

            if (! $realPath || ! $basePath || ! str_starts_with($realPath, $basePath)) {
                continue;
            }

            return [
                'disk' => $diskName,
                'relative_path' => $relativePath,
                'absolute_path' => $absolutePath,
                'mime_type' => $disk->mimeType($relativePath) ?: null,
            ];
        }

        return null;
    }
}
