<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use App\Support\WimsStorage;
use Illuminate\Support\Facades\Crypt;

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

            $relativePath = ltrim(Crypt::decryptString($base64), '/');

            $location = WimsStorage::locate($relativePath);

            if (! $location) {
                abort(404, 'File not found.');
            }

            $absolutePath = $location['absolute_path'];
            $mimeType = $location['mime_type'] ?: 'image/webp';

            if (! is_file($absolutePath)) {
                abort(403, 'Unauthorized access.');
            }

            return response()->file($absolutePath, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=86400',
            ]);
        } catch (\Throwable $e) {
            abort(404, 'Invalid image signature.');
        }
    }
}
