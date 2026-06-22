<?php

namespace App\Support;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;

class PublicStorageUrl
{
    public static function signed(?string $path): ?string
    {
        if (! is_string($path)) {
            return null;
        }

        $path = trim($path);

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $normalizedPath = ltrim($path, '/');
        $encryptedPath = Crypt::encryptString($normalizedPath);
        $encryptedPath = strtr(rtrim($encryptedPath, '='), '+/', '-_');

        return URL::signedRoute('images.proxy', [
            'encrypted_path' => $encryptedPath,
        ]);
    }
}
