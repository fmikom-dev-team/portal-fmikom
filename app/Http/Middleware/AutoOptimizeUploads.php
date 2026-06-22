<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class AutoOptimizeUploads
{
    /**
     * Keep track of temporary files created during the request to clean them up when the script finishes.
     */
    private static array $tempFiles = [];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $filesArray = $request->files->all();
        if (empty($filesArray)) {
            return $next($request);
        }

        $modified = false;
        $this->processFileArray($filesArray, '', $modified);

        if ($modified) {
            $request->files->replace($filesArray);
        }

        return $next($request);
    }

    /**
     * Recursively traverse files array to process single and nested file uploads.
     */
    private function processFileArray(array &$array, string $prefix, bool &$modified)
    {
        foreach ($array as $key => &$value) {
            $currentKey = $prefix === '' ? $key : "{$prefix}.{$key}";
            if ($value instanceof UploadedFile) {
                if ($value->isValid()) {
                    $optimized = $this->optimize($currentKey, $value);
                    if ($optimized) {
                        $value = $optimized;
                        $modified = true;
                    }
                }
            } elseif (is_array($value)) {
                $this->processFileArray($value, $currentKey, $modified);
            }
        }
    }

    /**
     * Optimize an uploaded file based on its field name/key and mime type.
     */
    private function optimize(string $key, UploadedFile $file): ?UploadedFile
    {
        $mime = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());

        // 1. Logo Web Check (converts raster logos to SVG)
        $isLogo = (str_contains(strtolower($key), 'brand_logo') || str_contains(strtolower($key), 'logo'))
            && str_starts_with($mime, 'image/');

        if ($isLogo) {
            return $this->convertToSvg($file);
        }

        // 2. Image Optimization (converts non-webp images to WebP)
        if (str_starts_with($mime, 'image/')) {
            return $this->convertToWebp($file);
        }

        return null;
    }

    /**
     * Convert PNG/JPG/GIF to a highly optimized SVG wrapper containing embedded WebP logo data.
     */
    private function convertToSvg(UploadedFile $file): ?UploadedFile
    {
        $mime = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());

        // Already an SVG, no optimization needed
        if ($extension === 'svg' || $mime === 'image/svg+xml') {
            return null;
        }

        if (! function_exists('imagewebp')) {
            Log::warning('[AutoOptimizeUploads] GD imagewebp is not available for logo conversion.');

            return null;
        }

        try {
            $realPath = $file->getRealPath();
            if ($extension === 'jpeg' || $extension === 'jpg' || $mime === 'image/jpeg') {
                $image = imagecreatefromjpeg($realPath);
            } elseif ($extension === 'png' || $mime === 'image/png') {
                $image = imagecreatefrompng($realPath);
            } elseif ($extension === 'gif' || $mime === 'image/gif') {
                $image = imagecreatefromgif($realPath);
            } else {
                return null;
            }

            if (! $image) {
                return null;
            }

            $origWidth = imagesx($image);
            $origHeight = imagesy($image);

            // Resize to standard logo size (max width 250px, keeping aspect ratio)
            $newWidth = 250;
            $newHeight = (int) (($origHeight / $origWidth) * $newWidth);

            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

            // Retain transparency for PNGs and GIFs
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);

            imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

            // Save WebP to memory via buffering
            ob_start();
            imagewebp($resizedImage, null, 80);
            $webpData = ob_get_clean();

            imagedestroy($image);
            imagedestroy($resizedImage);

            $base64 = base64_encode($webpData);

            // Construct secure SVG layout wrapping the WebP base64 logo
            $svgContent = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>'."\n";
            $svgContent .= '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 '.$newWidth.' '.$newHeight.'" width="'.$newWidth.'" height="'.$newHeight.'">'."\n";
            $svgContent .= '  <image width="'.$newWidth.'" height="'.$newHeight.'" xlink:href="data:image/webp;base64,'.$base64.'" />'."\n";
            $svgContent .= '</svg>';

            $tempDir = sys_get_temp_dir();
            $tempPath = tempnam($tempDir, 'svg').'.svg';
            file_put_contents($tempPath, $svgContent);

            self::$tempFiles[] = $tempPath;

            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME).'.svg';

            return new UploadedFile(
                $tempPath,
                $originalName,
                'image/svg+xml',
                UPLOAD_ERR_OK,
                true // Test mode allows handling custom temp path files
            );
        } catch (\Throwable $e) {
            Log::error('[AutoOptimizeUploads] SVG logo conversion failed: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Convert PNG/JPG/GIF to WebP format.
     */
    private function convertToWebp(UploadedFile $file): ?UploadedFile
    {
        $mime = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());

        // Already WebP or SVG
        if ($extension === 'webp' || $mime === 'image/webp' || $extension === 'svg' || $mime === 'image/svg+xml') {
            return null;
        }

        if (! function_exists('imagewebp')) {
            Log::warning('[AutoOptimizeUploads] GD imagewebp is not available for image optimization.');

            return null;
        }

        try {
            $realPath = $file->getRealPath();
            if ($extension === 'jpeg' || $extension === 'jpg' || $mime === 'image/jpeg') {
                $image = imagecreatefromjpeg($realPath);
            } elseif ($extension === 'png' || $mime === 'image/png') {
                $image = imagecreatefrompng($realPath);
                if ($image) {
                    imagepalettetotruecolor($image);
                    imagealphablending($image, false);
                    imagesavealpha($image, true);
                }
            } elseif ($extension === 'gif' || $mime === 'image/gif') {
                $image = imagecreatefromgif($realPath);
            } else {
                return null;
            }

            if (! $image) {
                return null;
            }

            $tempDir = sys_get_temp_dir();
            $tempPath = tempnam($tempDir, 'webp').'.webp';

            if (imagewebp($image, $tempPath, 80)) {
                imagedestroy($image);
                self::$tempFiles[] = $tempPath;

                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME).'.webp';

                return new UploadedFile(
                    $tempPath,
                    $originalName,
                    'image/webp',
                    UPLOAD_ERR_OK,
                    true
                );
            }
            imagedestroy($image);
        } catch (\Throwable $e) {
            Log::error('[AutoOptimizeUploads] WebP conversion failed: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Clean up temporary files created during this request.
     */
    public function __destruct()
    {
        foreach (self::$tempFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }
    }
}
