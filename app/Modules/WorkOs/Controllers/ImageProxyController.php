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

            // Ensure the path is strictly within the public storage of profile photos
            if (str_contains($relativePath, '..') || ! str_starts_with($relativePath, 'profile_photos/')) {
                abort(403, 'Unauthorized access.');
            }

            if (! Storage::disk('public')->exists($relativePath)) {
                abort(404, 'File not found.');
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
