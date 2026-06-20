<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class ImageProxyController extends Controller
{
    /**
     * Decrypt path and serve profile image.
     */
    public function serve(string $encryptedPath)
    {
        try {
            // Restore standard base64 characters
            $base64 = str_replace(['-', '_'], ['+', '/'], $encryptedPath);
            $mod4 = strlen($base64) % 4;
            if ($mod4) {
                $base64 .= substr('====', $mod4);
            }

            $relativePath = Crypt::decryptString($base64);

            // First check if the file exists using Storage
            if (! Storage::disk('public')->exists($relativePath)) {
                abort(404, 'File not found.');
            }

            // Ensure the path is strictly within the public storage of profile photos
            // Resolve real path and base path to prevent URL-encoded path traversal bypasses
            $absolutePath = Storage::disk('public')->path($relativePath);
            $realPath = realpath($absolutePath);
            $basePath = realpath(Storage::disk('public')->path('profile_photos'));

            if ($basePath) {
                $basePath = rtrim($basePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            }

            if (! $realPath || ! $basePath || ! str_starts_with($realPath, $basePath)) {
                abort(403, 'Unauthorized access.');
            }

            $absolutePath = Storage::disk('public')->path($relativePath);
            $mimeType = Storage::disk('public')->mimeType($relativePath) ?: 'image/webp';

            return response()->file($absolutePath, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=86400',
            ]);
        } catch (\Throwable $e) {
            abort(404, 'Invalid image signature.');
        }
    }
}
