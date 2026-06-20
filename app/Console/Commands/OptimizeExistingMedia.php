<?php

namespace App\Console\Commands;

use App\Models\Pagi\PagiWork;
use App\Models\Portal\PortalSetting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class OptimizeExistingMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:optimize-existing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize all existing uploaded images, videos, and logos to WebP, WebM, and SVG format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting media optimization for existing files...');

        // 1. Optimize Web branding settings (Logo to SVG, Favicon to WebP, Hero Gallery to WebP)
        $this->optimizeBrandingSettings();

        // 2. Optimize PagiWorks (cover images and nested content blocks)
        $this->optimizePagiWorks();

        // 3. Optimize User avatars (foto_path)
        $this->optimizeUserAvatars();

        $this->info('Media optimization completed successfully!');
    }

    /**
     * Optimize system branding settings.
     */
    private function optimizeBrandingSettings()
    {
        $this->comment('Optimizing system branding settings...');

        // Brand Logo to SVG
        $logoSetting = PortalSetting::where('key', 'brand_logo')->first();
        if ($logoSetting && $logoSetting->value) {
            $path = str_replace('/storage/', '', $logoSetting->value);
            $newPath = $this->convertLogoToSvg($path);
            if ($newPath) {
                $logoSetting->update(['value' => '/storage/'.$newPath]);
                $this->info("Logo web optimized to SVG: {$newPath}");
            }
        }

        // Brand Favicon to WebP
        $faviconSetting = PortalSetting::where('key', 'brand_favicon')->first();
        if ($faviconSetting && $faviconSetting->value) {
            $path = str_replace('/storage/', '', $faviconSetting->value);
            $newPath = $this->convertFileToWebp($path);
            if ($newPath) {
                $faviconSetting->update(['value' => '/storage/'.$newPath]);
                $this->info("Favicon web optimized to WebP: {$newPath}");
            }
        }

        // Hero Gallery images to WebP
        $gallerySetting = PortalSetting::where('key', 'hero_gallery')->first();
        if ($gallerySetting && $gallerySetting->value) {
            $images = json_decode($gallerySetting->value, true);
            if (is_array($images)) {
                $updated = false;
                foreach ($images as &$img) {
                    if (str_starts_with($img, 'http')) {
                        continue;
                    }
                    $path = str_replace('/storage/', '', $img);
                    $newPath = $this->convertFileToWebp($path);
                    if ($newPath) {
                        $img = str_contains($img, '/storage/') ? '/storage/'.$newPath : $newPath;
                        $updated = true;
                    }
                }
                if ($updated) {
                    $gallerySetting->update(['value' => json_encode($images)]);
                    $this->info('Hero gallery images optimized to WebP.');
                }
            }
        }
    }

    /**
     * Optimize PagiWork cover images and content block media.
     */
    private function optimizePagiWorks()
    {
        $this->comment('Optimizing PagiWork entries...');

        $works = PagiWork::all();
        foreach ($works as $work) {
            // Cover Image
            if ($work->cover_image && ! str_starts_with($work->cover_image, 'http')) {
                $path = str_replace('/storage/', '', $work->cover_image);
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                if (in_array($extension, ['mp4', 'mov', 'avi', 'mkv'])) {
                    $newPath = $this->convertVideoToWebm($path);
                } else {
                    $newPath = $this->convertFileToWebp($path);
                }

                if ($newPath) {
                    $work->update(['cover_image' => $newPath]);
                    $this->info("PagiWork #{$work->id} cover_image optimized: {$newPath}");
                }
            }

            // Content Blocks
            if (is_array($work->content)) {
                $content = $work->content;
                $modified = false;
                $this->optimizeContentBlocks($content, $modified);
                if ($modified) {
                    $work->update(['content' => $content]);
                    $this->info("PagiWork #{$work->id} content media optimized.");
                }
            }
        }
    }

    /**
     * Recursively optimize nested blocks in PagiWork content.
     */
    private function optimizeContentBlocks(array &$blocks, bool &$modified)
    {
        foreach ($blocks as &$block) {
            if (! isset($block['type'])) {
                continue;
            }

            if ($block['type'] === 'image' && ! empty($block['file_path'])) {
                if (str_starts_with($block['file_path'], 'http')) {
                    continue;
                }
                $path = str_replace('/storage/', '', $block['file_path']);
                $newPath = $this->convertFileToWebp($path);
                if ($newPath) {
                    $block['file_path'] = str_contains($block['file_path'], '/storage/') ? '/storage/'.$newPath : $newPath;
                    $modified = true;
                }
            } elseif ($block['type'] === 'video_audio' && ! empty($block['file_path'])) {
                if (str_starts_with($block['file_path'], 'http')) {
                    continue;
                }
                $path = str_replace('/storage/', '', $block['file_path']);
                $newPath = $this->convertVideoToWebm($path);
                if ($newPath) {
                    $block['file_path'] = str_contains($block['file_path'], '/storage/') ? '/storage/'.$newPath : $newPath;
                    $modified = true;
                }
            } elseif ($block['type'] === 'photo_grid' && ! empty($block['images'])) {
                foreach ($block['images'] as &$gridImg) {
                    if (! empty($gridImg['file_path'])) {
                        if (str_starts_with($gridImg['file_path'], 'http')) {
                            continue;
                        }
                        $path = str_replace('/storage/', '', $gridImg['file_path']);
                        $newPath = $this->convertFileToWebp($path);
                        if ($newPath) {
                            $gridImg['file_path'] = str_contains($gridImg['file_path'], '/storage/') ? '/storage/'.$newPath : $newPath;
                            $modified = true;
                        }
                    }
                }
            }
        }
    }

    /**
     * Optimize all user profile pictures.
     */
    private function optimizeUserAvatars()
    {
        $this->comment('Optimizing User profile avatars...');

        $users = User::all();
        foreach ($users as $user) {
            if ($user->foto_path && ! str_starts_with($user->foto_path, 'http')) {
                $path = str_replace('/storage/', '', $user->foto_path);
                $newPath = $this->convertFileToWebp($path);
                if ($newPath) {
                    $user->update(['foto_path' => $newPath]);
                    $this->info("User #{$user->id} avatar optimized: {$newPath}");
                }
            }
        }
    }

    /**
     * Convert PNG/JPG/GIF to WebP format.
     */
    private function convertFileToWebp(string $relativePath): ?string
    {
        if (! Storage::disk('public')->exists($relativePath)) {
            $newRelativePath = preg_replace('/\.(jpeg|jpg|png)$/i', '.webp', $relativePath);
            if (Storage::disk('public')->exists($newRelativePath)) {
                return $newRelativePath;
            }
            return null;
        }

        $absolutePath = Storage::disk('public')->path($relativePath);
        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        if (in_array($extension, ['webp', 'svg', 'gif'])) {
            return null;
        }

        if (! function_exists('imagewebp')) {
            return null;
        }

        try {
            if ($extension === 'jpeg' || $extension === 'jpg') {
                $image = imagecreatefromjpeg($absolutePath);
            } elseif ($extension === 'png') {
                $image = imagecreatefrompng($absolutePath);
                if ($image) {
                    imagepalettetotruecolor($image);
                    imagealphablending($image, false);
                    imagesavealpha($image, true);
                }
            } else {
                return null;
            }

            if (! $image) {
                return null;
            }

            $newRelativePath = preg_replace('/\.(jpeg|jpg|png)$/i', '.webp', $relativePath);
            $newAbsolutePath = Storage::disk('public')->path($newRelativePath);

            if (imagewebp($image, $newAbsolutePath, 80)) {
                imagedestroy($image);
                Storage::disk('public')->delete($relativePath);

                return $newRelativePath;
            }
            imagedestroy($image);
        } catch (\Throwable $e) {
            $this->error("Failed to convert image {$relativePath}: ".$e->getMessage());
        }

        return null;
    }

    /**
     * Convert PNG/JPG/GIF logo into optimized SVG.
     */
    private function convertLogoToSvg(string $relativePath): ?string
    {
        if (! Storage::disk('public')->exists($relativePath)) {
            $newRelativePath = preg_replace('/\.(jpeg|jpg|png)$/i', '.svg', $relativePath);
            if (Storage::disk('public')->exists($newRelativePath)) {
                return $newRelativePath;
            }
            return null;
        }

        $absolutePath = Storage::disk('public')->path($relativePath);
        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        if ($extension === 'svg') {
            return null;
        }

        if (! function_exists('imagewebp')) {
            return null;
        }

        try {
            if ($extension === 'jpeg' || $extension === 'jpg') {
                $image = imagecreatefromjpeg($absolutePath);
            } elseif ($extension === 'png') {
                $image = imagecreatefrompng($absolutePath);
            } else {
                return null;
            }

            if (! $image) {
                return null;
            }

            $origWidth = imagesx($image);
            $origHeight = imagesy($image);

            $newWidth = 250;
            $newHeight = (int) (($origHeight / $origWidth) * $newWidth);

            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
            imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

            ob_start();
            imagewebp($resizedImage, null, 80);
            $webpData = ob_get_clean();

            imagedestroy($image);
            imagedestroy($resizedImage);

            $base64 = base64_encode($webpData);

            $svgContent = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>'."\n";
            $svgContent .= '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 '.$newWidth.' '.$newHeight.'" width="'.$newWidth.'" height="'.$newHeight.'">'."\n";
            $svgContent .= '  <image width="'.$newWidth.'" height="'.$newHeight.'" xlink:href="data:image/webp;base64,'.$base64.'" />'."\n";
            $svgContent .= '</svg>';

            $newRelativePath = preg_replace('/\.(jpeg|jpg|png)$/i', '.svg', $relativePath);

            Storage::disk('public')->put($newRelativePath, $svgContent);
            Storage::disk('public')->delete($relativePath);

            return $newRelativePath;
        } catch (\Throwable $e) {
            $this->error("Failed to convert logo {$relativePath} to SVG: ".$e->getMessage());
        }

        return null;
    }

    /**
     * Convert videos into optimized WebM using FFmpeg.
     */
    private function convertVideoToWebm(string $relativePath): ?string
    {
        if (! Storage::disk('public')->exists($relativePath)) {
            $newRelativePath = preg_replace('/\.(mp4|mov|avi|mkv|flv)$/i', '.webm', $relativePath);
            if (Storage::disk('public')->exists($newRelativePath)) {
                return $newRelativePath;
            }
            return null;
        }

        $absolutePath = Storage::disk('public')->path($relativePath);
        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        if ($extension === 'webm') {
            return null;
        }

        try {
            $ffmpegBin = env('FFMPEG_BINARIES', 'ffmpeg');
            $newRelativePath = preg_replace('/\.(mp4|mov|avi|mkv|flv)$/i', '.webm', $relativePath);
            $newAbsolutePath = Storage::disk('public')->path($newRelativePath);

            $cmd = escapeshellcmd($ffmpegBin).' -i '.escapeshellarg($absolutePath).' -c:v libvpx -crf 32 -b:v 1M -c:a libopus -quality good -cpu-used 4 -y '.escapeshellarg($newAbsolutePath).' 2>&1';

            exec($cmd, $output, $resultCode);

            if ($resultCode === 0 && file_exists($newAbsolutePath) && filesize($newAbsolutePath) > 0) {
                Storage::disk('public')->delete($relativePath);

                return $newRelativePath;
            } else {
                $this->warn("FFmpeg conversion failed for {$relativePath}. Output: ".implode("\n", $output));
            }
        } catch (\Throwable $e) {
            $this->error("Failed to convert video {$relativePath} to WebM: ".$e->getMessage());
        }

        return null;
    }
}
