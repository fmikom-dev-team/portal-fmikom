<?php

namespace App\Modules\Pagi\Services;

use App\Concerns\HandlesImageCompression;
use App\Models\User;
use App\Services\VirusScannerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PagiCertificateService
{
    use HandlesImageCompression;

    /**
     * Store a new certificate.
     */
    public function store(User $user, array $validatedData, Request $request): JsonResponse
    {
        $metadata = $user->metadata ?? [];

        if (! array_key_exists('certificates', $metadata)) {
            $metadata['certificates'] = [
                ['id' => 1, 'title' => 'Google UX Design Professional Certificate', 'issuer' => 'Coursera', 'date' => '2026-01', 'expirationDate' => '', 'credentialId' => 'G-18A8B2C3', 'credentialUrl' => '', 'skills' => ['UX Design', 'Figma'], 'media' => []],
                ['id' => 2, 'title' => 'Figma UI/UX Advanced Design Course', 'issuer' => 'FMIKOM Academy', 'date' => '2025-12', 'expirationDate' => '', 'credentialId' => 'FM-882143', 'credentialUrl' => '', 'skills' => ['UI/UX Design', 'Prototyping'], 'media' => []],
            ];
        }

        $newId = count($metadata['certificates']) > 0
            ? max(array_column($metadata['certificates'], 'id')) + 1
            : 1;

        $mediaList = [];
        if ($request->hasFile('newMedia')) {
            $newMedia = $request->file('newMedia');
            $newMediaThumbs = $request->file('newMediaThumb') ?: [];

            foreach ($newMedia as $index => $file) {
                if ($file->isValid()) {
                    $mime = $file->getMimeType();
                    $realExt = $file->guessExtension();
                    $origName = $file->getClientOriginalName();

                    $allowedImageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $allowedImageExts = ['jpeg', 'jpg', 'png', 'gif', 'webp'];

                    $isImage = in_array($mime, $allowedImageMimes) && in_array($realExt, $allowedImageExts);
                    $isPdf = $mime === 'application/pdf' && $realExt === 'pdf';

                    if (! $isImage && ! $isPdf) {
                        return response()->json(['success' => false, 'message' => 'Tipe file tidak didukung. Hanya gambar (jpeg, png, gif, webp) atau PDF yang diperbolehkan.'], 422);
                    }

                    // Scan binary content for injection signatures
                    $realPath = $file->getRealPath();
                    $contents = file_get_contents($realPath);
                    if (
                        str_contains($contents, '<?php') ||
                        str_contains($contents, '<?=') ||
                        str_contains($contents, '<script') ||
                        preg_match('/<script\b[^>]*>(.*?)<\/script>/is', $contents) ||
                        preg_match('/<\?php\b(.*?)(\?>|$)/is', $contents)
                    ) {
                        return response()->json(['success' => false, 'message' => 'Security Error: Script signature detected in file content.'], 422);
                    }

                    $path = null;
                    $thumbPath = null;

                    if ($isImage) {
                        $path = $this->compressAndSaveImage($file, 'pagi/certificates', 1200, 1200, 85);
                        $thumbPath = $path;
                    } else {
                        // Scan non-image file (PDF) with ClamAV before storing
                        $scanner = app(VirusScannerService::class);
                        $scanResult = $scanner->scan($file);
                        if (! $scanResult['safe']) {
                            return response()->json(['success' => false, 'message' => $scanResult['reason']], 422);
                        }

                        $uuid = Str::uuid()->toString();
                        $filename = $uuid.'.pdf';
                        $path = $file->storeAs('pagi/certificates', $filename, 'public');
                        if (isset($newMediaThumbs[$index]) && $newMediaThumbs[$index]->isValid()) {
                            $thumbFile = $newMediaThumbs[$index];
                            $thumbPath = $this->compressAndSaveImage($thumbFile, 'pagi/certificates/thumbs', 400, 400, 80);
                        }
                    }

                    if ($path) {
                        $mediaList[] = [
                            'name' => $origName,
                            'path' => $path,
                            'type' => $isImage ? 'image' : 'pdf',
                            'thumbnail_path' => $thumbPath,
                        ];
                    }
                }
            }
        }

        $skills = json_decode($request->input('skills', '[]'), true) ?: [];

        $newCert = [
            'id' => $newId,
            'title' => $validatedData['title'],
            'issuer' => $validatedData['issuer'],
            'date' => $validatedData['date'],
            'expirationDate' => $validatedData['expirationDate'] ?? '',
            'credentialId' => $validatedData['credentialId'] ?? '',
            'credentialUrl' => $validatedData['credentialUrl'] ?? '',
            'skills' => $skills,
            'media' => $mediaList,
        ];

        $metadata['certificates'][] = $newCert;
        $user->metadata = $metadata;
        $user->save();

        return response()->json([
            'success' => true,
            'certificates' => $this->resolveCertificateLogos($metadata['certificates']),
            'message' => 'Certificate uploaded successfully!',
        ]);
    }

    /**
     * Get and resolve organization logo.
     */
    public function getOrgLogo(string $name): JsonResponse
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));

        if (empty($slug)) {
            return response()->json(['success' => false, 'message' => 'Invalid name.']);
        }

        $storageDir = storage_path('app/public/org-logos');
        if (! file_exists($storageDir)) {
            @mkdir($storageDir, 0755, true);
        }

        $extensions = ['svg', 'png', 'jpg', 'jpeg', 'webp', 'gif'];
        foreach ($extensions as $ext) {
            $cachedFile = "{$storageDir}/{$slug}.{$ext}";
            if (file_exists($cachedFile)) {
                return response()->json([
                    'success' => true,
                    'cached' => true,
                    'url' => asset("storage/org-logos/{$slug}.{$ext}"),
                ]);
            }
        }

        $theSvg = $this->findTheSvgIcon($slug, $name);
        if ($theSvg) {
            $svgPath = $theSvg['path'];
            $content = @file_get_contents($svgPath);
            if ($content) {
                $svg = null;
                if (preg_match('/"default":\s*`([^`]+)`/s', $content, $m)) {
                    $svg = $m[1];
                } elseif (preg_match('/export const svg\s*=\s*`([^`]+)`/s', $content, $m)) {
                    $svg = $m[1];
                } elseif (preg_match('/exports.svg\s*=\s*`([^`]+)`/s', $content, $m)) {
                    $svg = $m[1];
                }

                if ($svg) {
                    @file_put_contents("{$storageDir}/{$slug}.svg", $svg);

                    return response()->json([
                        'success' => true,
                        'cached' => false,
                        'source' => 'thesvg',
                        'url' => asset("storage/org-logos/{$slug}.svg"),
                    ]);
                }
            }
        }

        return response()->json([
            'success' => false,
            'show_upload' => true,
            'message' => 'Logo not found. You can upload one.',
        ]);
    }

    /**
     * Upload custom logo for organization.
     */
    public function uploadOrgLogo(string $name, $file): JsonResponse
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));

        if (empty($slug)) {
            return response()->json(['success' => false, 'message' => 'Invalid name.'], 422);
        }

        if ($file && $file->isValid()) {
            $realMime = $file->getMimeType();
            $realExt = $file->guessExtension();

            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
            $allowedExts = ['jpeg', 'jpg', 'png', 'gif', 'webp', 'svg'];

            if (! in_array($realMime, $allowedMimes) || ! in_array($realExt, $allowedExts)) {
                return response()->json(['success' => false, 'message' => 'Tipe gambar logo tidak valid.'], 422);
            }

            // Scan organization logo with ClamAV
            $scanner = app(VirusScannerService::class);
            $scanResult = $scanner->scan($file);
            if (! $scanResult['safe']) {
                return response()->json(['success' => false, 'message' => $scanResult['reason']], 422);
            }

            // Delete existing logos from public disk using Storage facade
            $extensions = ['svg', 'png', 'jpg', 'jpeg', 'webp', 'gif'];
            foreach ($extensions as $ext) {
                $existingPath = "org-logos/{$slug}.{$ext}";
                if (Storage::disk('public')->exists($existingPath)) {
                    Storage::disk('public')->delete($existingPath);
                }
            }

            $ext = $realExt ?: 'png';
            $filename = "{$slug}.{$ext}";

            if ($ext === 'svg') {
                $contents = file_get_contents($file->getRealPath());
                $sanitizedSvg = $this->sanitizeSvg($contents);
                Storage::disk('public')->put("org-logos/{$filename}", $sanitizedSvg);
            } else {
                $file->storeAs('org-logos', $filename, 'public');
            }

            return response()->json([
                'success' => true,
                'url' => asset("storage/org-logos/{$filename}"),
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Failed to upload logo.'], 400);
    }

    /**
     * Update an existing certificate.
     */
    public function update(User $user, $id, array $validatedData, Request $request): JsonResponse
    {
        $metadata = $user->metadata ?? [];

        if (! array_key_exists('certificates', $metadata)) {
            $metadata['certificates'] = [
                ['id' => 1, 'title' => 'Google UX Design Professional Certificate', 'issuer' => 'Coursera', 'date' => '2026-01', 'expirationDate' => '', 'credentialId' => 'G-18A8B2C3', 'credentialUrl' => '', 'skills' => ['UX Design', 'Figma'], 'media' => []],
                ['id' => 2, 'title' => 'Figma UI/UX Advanced Design Course', 'issuer' => 'FMIKOM Academy', 'date' => '2025-12', 'expirationDate' => '', 'credentialId' => 'FM-882143', 'credentialUrl' => '', 'skills' => ['UI/UX Design', 'Prototyping'], 'media' => []],
            ];
        }

        $foundIndex = -1;
        foreach ($metadata['certificates'] as $idx => $c) {
            if ($c['id'] == $id) {
                $foundIndex = $idx;
                break;
            }
        }

        if ($foundIndex === -1) {
            return response()->json(['success' => false, 'message' => 'Certificate not found.'], 404);
        }

        $oldCert = $metadata['certificates'][$foundIndex];
        $existingMedia = json_decode($request->input('existingMedia', '[]'), true) ?: [];

        if (isset($oldCert['media']) && is_array($oldCert['media'])) {
            $keepPaths = array_column($existingMedia, 'path');
            foreach ($oldCert['media'] as $oldM) {
                if (! in_array($oldM['path'], $keepPaths)) {
                    $oldFilePath = storage_path('app/public/'.$oldM['path']);
                    if (file_exists($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                    if (isset($oldM['thumbnail_path']) && $oldM['thumbnail_path'] && $oldM['thumbnail_path'] !== $oldM['path']) {
                        $oldThumbPath = storage_path('app/public/'.$oldM['thumbnail_path']);
                        if (file_exists($oldThumbPath)) {
                            @unlink($oldThumbPath);
                        }
                    }
                }
            }
        }

        $mediaList = $existingMedia;

        if ($request->hasFile('newMedia')) {
            $newMedia = $request->file('newMedia');
            $newMediaThumbs = $request->file('newMediaThumb') ?: [];

            foreach ($newMedia as $index => $file) {
                if ($file->isValid()) {
                    $mime = $file->getMimeType();
                    $realExt = $file->guessExtension();
                    $origName = $file->getClientOriginalName();

                    $allowedImageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $allowedImageExts = ['jpeg', 'jpg', 'png', 'gif', 'webp'];

                    $isImage = in_array($mime, $allowedImageMimes) && in_array($realExt, $allowedImageExts);
                    $isPdf = $mime === 'application/pdf' && $realExt === 'pdf';

                    if (! $isImage && ! $isPdf) {
                        return response()->json(['success' => false, 'message' => 'Tipe file tidak didukung. Hanya gambar (jpeg, png, gif, webp) atau PDF yang diperbolehkan.'], 422);
                    }

                    // Scan binary content for injection signatures
                    $realPath = $file->getRealPath();
                    $contents = file_get_contents($realPath);
                    if (
                        str_contains($contents, '<?php') ||
                        str_contains($contents, '<?=') ||
                        str_contains($contents, '<script') ||
                        preg_match('/<script\b[^>]*>(.*?)<\/script>/is', $contents) ||
                        preg_match('/<\?php\b(.*?)(\?>|$)/is', $contents)
                    ) {
                        return response()->json(['success' => false, 'message' => 'Security Error: Script signature detected in file content.'], 422);
                    }

                    $path = null;
                    $thumbPath = null;

                    if ($isImage) {
                        $path = $this->compressAndSaveImage($file, 'pagi/certificates', 1200, 1200, 85);
                        $thumbPath = $path;
                    } else {
                        // Scan non-image file (PDF) with ClamAV before storing
                        $scanner = app(VirusScannerService::class);
                        $scanResult = $scanner->scan($file);
                        if (! $scanResult['safe']) {
                            return response()->json(['success' => false, 'message' => $scanResult['reason']], 422);
                        }

                        $uuid = Str::uuid()->toString();
                        $filename = $uuid.'.pdf';
                        $path = $file->storeAs('pagi/certificates', $filename, 'public');
                        if (isset($newMediaThumbs[$index]) && $newMediaThumbs[$index]->isValid()) {
                            $thumbFile = $newMediaThumbs[$index];
                            $thumbPath = $this->compressAndSaveImage($thumbFile, 'pagi/certificates/thumbs', 400, 400, 80);
                        }
                    }

                    if ($path) {
                        $mediaList[] = [
                            'name' => $origName,
                            'path' => $path,
                            'type' => $isImage ? 'image' : 'pdf',
                            'thumbnail_path' => $thumbPath,
                        ];
                    }
                }
            }
        }

        if (count($mediaList) > 3) {
            return response()->json(['success' => false, 'message' => 'You can upload a maximum of 3 certificate proof files.'], 422);
        }

        $skills = json_decode($request->input('skills', '[]'), true) ?: [];

        $metadata['certificates'][$foundIndex] = [
            'id' => (int) $id,
            'title' => $validatedData['title'],
            'issuer' => $validatedData['issuer'],
            'date' => $validatedData['date'],
            'expirationDate' => $validatedData['expirationDate'] ?? '',
            'credentialId' => $validatedData['credentialId'] ?? '',
            'credentialUrl' => $validatedData['credentialUrl'] ?? '',
            'skills' => $skills,
            'media' => $mediaList,
        ];

        $user->metadata = $metadata;
        $user->save();

        return response()->json([
            'success' => true,
            'certificates' => $this->resolveCertificateLogos($metadata['certificates']),
            'message' => 'Certificate updated successfully!',
        ]);
    }

    /**
     * Delete an existing certificate.
     */
    public function delete(User $user, $id): JsonResponse
    {
        $metadata = $user->metadata ?? [];

        if (! array_key_exists('certificates', $metadata)) {
            $metadata['certificates'] = [
                ['id' => 1, 'title' => 'Google UX Design Professional Certificate', 'issuer' => 'Coursera', 'date' => '2026-01', 'expirationDate' => '', 'credentialId' => 'G-18A8B2C3', 'credentialUrl' => '', 'skills' => ['UX Design', 'Figma'], 'media' => []],
                ['id' => 2, 'title' => 'Figma UI/UX Advanced Design Course', 'issuer' => 'FMIKOM Academy', 'date' => '2025-12', 'expirationDate' => '', 'credentialId' => 'FM-882143', 'credentialUrl' => '', 'skills' => ['UI/UX Design', 'Prototyping'], 'media' => []],
            ];
        }

        $certToDelete = null;
        foreach ($metadata['certificates'] as $c) {
            if ($c['id'] == $id) {
                $certToDelete = $c;
                break;
            }
        }

        if ($certToDelete && isset($certToDelete['media']) && is_array($certToDelete['media'])) {
            foreach ($certToDelete['media'] as $m) {
                $filePath = storage_path('app/public/'.$m['path']);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
                if (isset($m['thumbnail_path']) && $m['thumbnail_path'] && $m['thumbnail_path'] !== $m['path']) {
                    $thumbPath = storage_path('app/public/'.$m['thumbnail_path']);
                    if (file_exists($thumbPath)) {
                        @unlink($thumbPath);
                    }
                }
            }
        }

        $filtered = array_filter($metadata['certificates'], function ($cert) use ($id) {
            return $cert['id'] != $id;
        });

        $metadata['certificates'] = array_values($filtered);
        $user->metadata = $metadata;
        $user->save();

        return response()->json([
            'success' => true,
            'certificates' => $this->resolveCertificateLogos($metadata['certificates']),
            'message' => 'Certificate deleted successfully!',
        ]);
    }

    // --- Private Helper Methods ---

    private function resolveCertificateLogos(?array $certificates): array
    {
        if (empty($certificates)) {
            return [];
        }

        $storageDir = storage_path('app/public/org-logos');
        $extensions = ['svg', 'png', 'jpg', 'jpeg', 'webp', 'gif'];

        return array_map(function ($cert) use ($storageDir, $extensions) {
            if (! empty($cert['issuer'])) {
                $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $cert['issuer']), '-'));
                foreach ($extensions as $ext) {
                    if (file_exists("{$storageDir}/{$slug}.{$ext}")) {
                        $cert['logo_url'] = asset("storage/org-logos/{$slug}.{$ext}");
                        break;
                    }
                }
            }

            return $cert;
        }, $certificates);
    }

    private function findTheSvgIcon(string $slug, string $name): ?array
    {
        $slugsToCheck = [$slug];
        if (preg_match('/^([^(]+)\(([^)]+)\)/', $name, $matches)) {
            $before = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $matches[1]), '-'));
            $inside = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $matches[2]), '-'));
            if ($before) {
                $slugsToCheck[] = $before;
            }
            if ($inside) {
                $slugsToCheck[] = $inside;
            }
        }

        $cleanName = preg_replace('/\b(inc|corp|ltd|co|corporation|academy|university|institute)\b$/i', '', $name);
        $cleanSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $cleanName), '-'));
        if ($cleanSlug && ! in_array($cleanSlug, $slugsToCheck)) {
            $slugsToCheck[] = $cleanSlug;
        }

        foreach (array_unique($slugsToCheck) as $s) {
            if (empty($s)) {
                continue;
            }
            $jsPath = base_path("node_modules/@thesvg/icons/dist/{$s}.js");
            $cjsPath = base_path("node_modules/@thesvg/icons/dist/{$s}.cjs");

            if (file_exists($jsPath)) {
                return ['slug' => $s, 'path' => $jsPath];
            } elseif (file_exists($cjsPath)) {
                return ['slug' => $s, 'path' => $cjsPath];
            }
        }

        return null;
    }

    private function sanitizeSvg(string $svgContent): string
    {
        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);

        if (! @$dom->loadXML($svgContent, LIBXML_NONET)) {
            return '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"/>';
        }

        $xpath = new \DOMXPath($dom);

        $blockedElements = [
            'script', 'iframe', 'object', 'embed', 'set', 'animate',
            'animatetransform', 'animatecolor', 'animatemotion', 'handler', 'listener',
        ];

        foreach ($blockedElements as $element) {
            $nodes = $xpath->query("//*[local-name()='{$element}']");
            foreach ($nodes as $node) {
                $node->parentNode->removeChild($node);
            }
        }

        $nodes = $xpath->query('//*');
        foreach ($nodes as $node) {
            if ($node instanceof \DOMElement && $node->hasAttributes()) {
                $attrsToRemove = [];
                foreach ($node->attributes as $attr) {
                    $name = strtolower($attr->name);
                    $val = strtolower(trim($attr->value));

                    if (str_starts_with($name, 'on')) {
                        $attrsToRemove[] = $attr->name;
                    } elseif (str_starts_with($val, 'javascript:') || str_starts_with($val, 'data:text/html')) {
                        $attrsToRemove[] = $attr->name;
                    }
                }
                foreach ($attrsToRemove as $attrName) {
                    $node->removeAttribute($attrName);
                }
            }
        }

        return $dom->saveXML($dom->documentElement);
    }
}
