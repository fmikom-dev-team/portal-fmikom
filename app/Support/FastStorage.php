<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FastStorage
{
    private const PRIMARY_DISK = 'local';

    private const LEGACY_DISK = 'public';

    /**
     * @return array{path: string, preferred_disks: array<int, string>}
     */
    protected static function normalizePath(string $path): array
    {
        $path = trim($path);

        if (str_starts_with($path, '/private/')) {
            return [
                'path' => ltrim(substr($path, strlen('/private/')), '/'),
                'preferred_disks' => [self::PRIMARY_DISK, self::LEGACY_DISK],
            ];
        }

        if (str_starts_with($path, 'private/')) {
            return [
                'path' => ltrim(substr($path, strlen('private/')), '/'),
                'preferred_disks' => [self::PRIMARY_DISK, self::LEGACY_DISK],
            ];
        }

        return [
            'path' => ltrim($path, '/'),
            'preferred_disks' => [self::PRIMARY_DISK, self::LEGACY_DISK],
        ];
    }

    /**
     * @return array{disk: string, relative_path: string, absolute_path: string, mime_type: ?string}|null
     */
    public static function locate(?string $path): ?array
    {
        if (! is_string($path)) {
            return null;
        }

        $normalized = self::normalizePath($path);
        $relativePath = $normalized['path'];

        if ($relativePath === '') {
            return null;
        }

        foreach ($normalized['preferred_disks'] as $diskName) {
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

    public static function exists(?string $path): bool
    {
        return self::locate($path) !== null;
    }

    /**
     * @param  array<string, string>  $headers
     */
    public static function response(string $path, string $filename, array $headers = []): SymfonyResponse|StreamedResponse
    {
        $located = self::locate($path);

        if ($located === null) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk($located['disk'])->response(
            $located['relative_path'],
            $filename,
            $headers,
        );
    }

    public static function delete(string|array|null $paths): void
    {
        foreach ((array) $paths as $path) {
            if (! is_string($path) || trim($path) === '') {
                continue;
            }

            $normalized = self::normalizePath($path);

            foreach ($normalized['preferred_disks'] as $diskName) {
                $disk = Storage::disk($diskName);

                if ($disk->exists($normalized['path'])) {
                    $disk->delete($normalized['path']);
                }
            }
        }
    }

    public static function makeDirectory(string $directory, string $disk = self::PRIMARY_DISK): void
    {
        Storage::disk($disk)->makeDirectory($directory);
    }

    public static function put(string $path, string $contents, string $disk = self::PRIMARY_DISK): void
    {
        $result = Storage::disk($disk)->put($path, $contents);

        if (! $result) {
            throw new RuntimeException(sprintf('Failed to store FAST file at %s on disk %s.', $path, $disk));
        }
    }
}
