<?php

namespace App\Concerns;

use App\Jobs\OptimizeVideoJob;
use App\Services\VirusScannerService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

trait HandlesImageCompression
{
    /**
     * Compress and resize an uploaded image, converting it to WebP.
     *
     * @return string|null The relative storage path of the saved file
     */
    protected function compressAndSaveImage(UploadedFile $file, string $directory = 'profile_photos', int $maxWidth = 400, int $maxHeight = 400, int $quality = 80): ?string
    {
        // Allocate more memory dynamically for processing large images
        @ini_set('memory_limit', '512M');

        // Validate actual mime type and extension
        $realMime = $file->getMimeType();
        $clientExt = strtolower($file->getClientOriginalExtension());
        $realExt = $file->guessExtension();
        if (! $realExt || $realExt === 'bin') {
            $realExt = $clientExt;
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/octet-stream'];
        $allowedExts = ['jpeg', 'jpg', 'png', 'gif', 'webp'];

        $isValidImage = (in_array($realMime, $allowedMimes) || str_starts_with($realMime, 'image/')) && in_array($realExt, $allowedExts);

        if (! $isValidImage) {
            Log::warning("[HandlesImageCompression] Image type rejected. MIME: {$realMime}, Ext: {$realExt}, ClientExt: {$clientExt}");
            $key = $this->getUploadedFileKey($file);
            throw ValidationException::withMessages([
                $key => 'Unggah berkas gagal: Tipe berkas gambar tidak valid atau terindikasi berbahaya.',
            ]);
        }

        // Scan for virus signature using ClamAV
        $scanner = app(VirusScannerService::class);
        $scanResult = $scanner->scan($file);
        if (! $scanResult['safe']) {
            $key = $this->getUploadedFileKey($file);
            throw ValidationException::withMessages([
                $key => $scanResult['reason'],
            ]);
        }

        // Generate a unique filename with .webp extension
        $filename = uniqid('img_', true).'.webp';
        $targetDir = storage_path('app/public/'.$directory);

        if (! file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetPath = $targetDir.'/'.$filename;
        $sourcePath = $file->getRealPath();

        $sourceImage = null;

        switch ($realMime) {
            case 'image/jpeg':
            case 'image/jpg':
                $sourceImage = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = @imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = @imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                $sourceImage = @imagecreatefromwebp($sourcePath);
                break;
        }

        if (! $sourceImage) {
            // Throw exception to prevent storing raw fallback (neutralize fake/corrupted images)
            $key = $this->getUploadedFileKey($file);
            throw ValidationException::withMessages([
                $key => 'Unggah berkas gagal: Kerusakan berkas gambar terdeteksi.',
            ]);
        }

        // Get original dimensions
        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        // Calculate new dimensions keeping aspect ratio
        $ratio = $origWidth / $origHeight;
        if ($origWidth > $maxWidth || $origHeight > $maxHeight) {
            if ($ratio > 1) {
                $newWidth = $maxWidth;
                $newHeight = round($maxWidth / $ratio);
            } else {
                $newHeight = $maxHeight;
                $newWidth = round($maxHeight * $ratio);
            }
        } else {
            $newWidth = $origWidth;
            $newHeight = $origHeight;
        }

        // Create true color image for destination
        $destImage = imagecreatetruecolor($newWidth, $newHeight);

        // Handle transparency for PNG/WebP/GIF
        imagealphablending($destImage, false);
        imagesavealpha($destImage, true);
        $transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 127);
        imagefilledrectangle($destImage, 0, 0, $newWidth, $newHeight, $transparent);

        // Resample original image into destination
        imagecopyresampled($destImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // Save as WebP if supported, fallback to JPEG if not
        $success = false;
        if (function_exists('imagewebp')) {
            $success = @imagewebp($destImage, $targetPath, $quality);
        }

        if (! $success) {
            // Fallback to JPEG
            $filename = uniqid('img_', true).'.jpg';
            $targetPath = $targetDir.'/'.$filename;
            $success = @imagejpeg($destImage, $targetPath, $quality);
        }

        // Free memory
        imagedestroy($sourceImage);
        imagedestroy($destImage);

        if ($success) {
            return $directory.'/'.$filename;
        }

        $key = $this->getUploadedFileKey($file);
        throw ValidationException::withMessages([
            $key => 'Unggah berkas gagal: Gagal memproses gambar.',
        ]);
    }

    /**
     * Securely validate, compress, and save banner files.
     * Static images are compressed to WebP.
     * Videos are compressed to WebM via FFmpeg if available.
     * Strict content scan is run to prevent execution injection.
     */
    protected function compressAndSaveBannerOrVideo(UploadedFile $file, string $directory = 'pagi/banners'): ?string
    {
        // Allocate more memory dynamically for processing large images
        @ini_set('memory_limit', '512M');

        // Scan for virus signature using ClamAV
        $scanner = app(VirusScannerService::class);
        $scanResult = $scanner->scan($file);
        if (! $scanResult['safe']) {
            $key = $this->getUploadedFileKey($file);
            throw ValidationException::withMessages([
                $key => $scanResult['reason'],
            ]);
        }

        $realMime = $file->getMimeType();
        $clientExt = strtolower($file->getClientOriginalExtension());
        $realExt = $file->guessExtension();
        if (! $realExt || $realExt === 'bin') {
            $realExt = $clientExt;
        }

        $allowedVideoMimes = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska', 'video/3gpp', 'application/octet-stream'];
        $allowedVideoExts = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', '3gp'];

        $allowedImageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/octet-stream'];
        $allowedImageExts = ['jpeg', 'jpg', 'png', 'gif', 'webp'];

        $isImage = (in_array($realMime, $allowedImageMimes) || str_starts_with($realMime, 'image/')) && in_array($realExt, $allowedImageExts);
        $isVideo = (in_array($realMime, $allowedVideoMimes) || str_starts_with($realMime, 'video/')) && in_array($realExt, $allowedVideoExts);

        if (! $isImage && ! $isVideo) {
            Log::warning("[HandlesImageCompression] File type rejected. MIME: {$realMime}, Ext: {$realExt}, ClientExt: {$clientExt}");
            $key = $this->getUploadedFileKey($file);
            throw ValidationException::withMessages([
                $key => 'Unggah berkas gagal: Tipe berkas tidak didukung atau terindikasi berbahaya.',
            ]);
        }

        $targetDir = storage_path('app/public/'.$directory);
        if (! file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // 2. Video processing — transcode and compress in background queue
        if ($isVideo) {
            $ext = in_array($realExt, $allowedVideoExts) ? $realExt : 'mp4';
            $filename = uniqid('vid_', true).'.'.$ext;

            // Move the uploaded file to the target location immediately
            $file->move($targetDir, $filename);

            // Dispatch OptimizeVideoJob to queue to compress in background sequentially (prevent CPU overload)
            OptimizeVideoJob::dispatch($directory.'/'.$filename, $ext);

            return $directory.'/'.$filename;
        }

        // 3. GIF processing (preserve animation, store securely)
        if ($realMime === 'image/gif') {
            $filename = uniqid('gif_', true).'.gif';

            return $file->storeAs($directory, $filename, 'public');
        }

        // 4. Static Image compression (convert to WebP)
        return $this->compressAndSaveImage($file, $directory, 3200, 410, 80);
    }

    protected function getUploadedFileKey(UploadedFile $file): string
    {
        $request = request();
        foreach ($request->allFiles() as $inputKey => $uploadedFile) {
            if (is_array($uploadedFile)) {
                foreach (Arr::dot($uploadedFile) as $dotKey => $subFile) {
                    if ($subFile->getRealPath() === $file->getRealPath()) {
                        return $inputKey.'.'.$dotKey;
                    }
                }
            } else {
                if ($uploadedFile->getRealPath() === $file->getRealPath()) {
                    return $inputKey;
                }
            }
        }

        return 'file';
    }
}
