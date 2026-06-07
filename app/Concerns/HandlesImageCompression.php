<?php

namespace App\Concerns;

use Illuminate\Http\UploadedFile;

trait HandlesImageCompression
{
    /**
     * Compress and resize an uploaded image, converting it to WebP.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param int $maxWidth
     * @param int $maxHeight
     * @param int $quality
     * @return string|null The relative storage path of the saved file
     */
    protected function compressAndSaveImage(UploadedFile $file, string $directory = 'profile_photos', int $maxWidth = 400, int $maxHeight = 400, int $quality = 80): ?string
    {
        // Allocate more memory dynamically for processing large images
        @ini_set('memory_limit', '512M');

        // Generate a unique filename with .webp extension
        $filename = uniqid('img_', true) . '.webp';
        $targetDir = storage_path('app/public/' . $directory);

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetPath = $targetDir . '/' . $filename;
        $sourcePath = $file->getRealPath();

        // Load image based on mime type
        $mime = $file->getClientMimeType();
        $sourceImage = null;

        switch ($mime) {
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

        if (!$sourceImage) {
            // If GD loading failed or unsupported format, just store normally as fallback
            return $file->store($directory, 'public');
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

        if (!$success) {
            // Fallback to JPEG
            $filename = uniqid('img_', true) . '.jpg';
            $targetPath = $targetDir . '/' . $filename;
            $success = @imagejpeg($destImage, $targetPath, $quality);
        }

        // Free memory
        imagedestroy($sourceImage);
        imagedestroy($destImage);

        if ($success) {
            return $directory . '/' . $filename;
        }

        // Fallback to normal store if anything failed
        return $file->store($directory, 'public');
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

        $realPath = $file->getRealPath();
        $mime = $file->getClientMimeType();
        
        // 1. Strict Content validation (check binary/content for script sequences)
        // Skip for image and video files as they are binary and can contain random matching byte sequences.
        if (!str_starts_with($mime, 'image/') && !str_starts_with($mime, 'video/')) {
            $contents = file_get_contents($realPath);
            if (
                str_contains($contents, '<?php') || 
                str_contains($contents, '<?=') || 
                str_contains($contents, '<script') ||
                preg_match('/<script\b[^>]*>(.*?)<\/script>/is', $contents) ||
                preg_match('/<\?php\b(.*?)(\?>|$)/is', $contents)
            ) {
                // Throw exception or abort immediately if script injection detected
                abort(422, 'Security Warning: Dangerous script signatures detected in file content.');
            }
        }
        $targetDir = storage_path('app/public/' . $directory);
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // 2. Video processing — transcode and compress to WebM using FFmpeg
        if (str_starts_with($mime, 'video/')) {
            $filename = uniqid('vid_', true) . '.webm';
            $destPath = $targetDir . '/' . $filename;

            $ffmpegPath = '/opt/homebrew/bin/ffmpeg';
            if (!file_exists($ffmpegPath)) {
                $ffmpegPath = 'ffmpeg';
            }

            $escapedSource = escapeshellarg($realPath);
            $escapedDest = escapeshellarg($destPath);

            // Use VP8 with realtime speed settings to compress and save as WebM
            $command = "{$ffmpegPath} -y -i {$escapedSource} -c:v libvpx -crf 32 -b:v 1M -deadline realtime -cpu-used 4 -c:a libvorbis {$escapedDest} 2>&1";

            \Illuminate\Support\Facades\Log::info("Converting video to WebM: " . $command);
            exec($command, $output, $resultCode);

            if ($resultCode === 0 && file_exists($destPath)) {
                return $directory . '/' . $filename;
            }

            \Illuminate\Support\Facades\Log::error("FFmpeg video transcode failed with code {$resultCode}. Output: " . implode("\n", $output));

            // Fallback to original storage if FFmpeg fails
            $ext = $file->getClientOriginalExtension() ?: 'mp4';
            $filename = uniqid('vid_fallback_', true) . '.' . $ext;
            return $file->storeAs($directory, $filename, 'public');
        }


        // 3. GIF processing (preserve animation, store securely)
        if ($mime === 'image/gif') {
            $filename = uniqid('gif_', true) . '.gif';
            return $file->storeAs($directory, $filename, 'public');
        }

        // 4. Static Image compression (convert to WebP)
        return $this->compressAndSaveImage($file, $directory, 3200, 410, 80);
    }
}
