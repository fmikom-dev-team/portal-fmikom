<?php

namespace App\Modules\Trace\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    /**
     * Compress and convert an uploaded image to WebP format.
     *
     * @param UploadedFile $file The uploaded image file
     * @param string $directory Storage directory (e.g., 'events', 'mitra-logos')
     * @param int $quality WebP quality (1-100, default 80)
     * @param int|null $maxWidth Maximum width in pixels (null = no resize)
     * @param int|null $maxHeight Maximum height in pixels (null = no resize)
     * @return string The stored file path relative to the disk
     */
    public static function compressToWebp(
        UploadedFile $file,
        string $directory,
        int $quality = 80,
        ?int $maxWidth = null,
        ?int $maxHeight = null,
    ): string {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getPathname());

        // Resize if dimensions specified (maintain aspect ratio)
        if ($maxWidth || $maxHeight) {
            $image->scaleDown(
                width: $maxWidth,
                height: $maxHeight,
            );
        }

        // Encode to WebP
        $encoded = $image->toWebp($quality);

        // Generate unique filename
        $filename = $directory . '/' . uniqid() . '_' . time() . '.webp';

        // Store to public disk
        Storage::disk('public')->put($filename, (string) $encoded);

        return $filename;
    }

    /**
     * Replace an existing image with a new compressed WebP version.
     * Deletes the old file if it exists.
     *
     * @param UploadedFile $file The new uploaded image
     * @param string|null $oldPath The old file path to delete
     * @param string $directory Storage directory
     * @param int $quality WebP quality
     * @param int|null $maxWidth Maximum width
     * @param int|null $maxHeight Maximum height
     * @return string The new stored file path
     */
    public static function replaceWithWebp(
        UploadedFile $file,
        ?string $oldPath,
        string $directory,
        int $quality = 80,
        ?int $maxWidth = null,
        ?int $maxHeight = null,
    ): string {
        // Delete old file
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return self::compressToWebp($file, $directory, $quality, $maxWidth, $maxHeight);
    }
}
