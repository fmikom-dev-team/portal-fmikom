<?php

namespace App\Modules\Pagi\Services;

use App\Concerns\HandlesImageCompression;
use App\Models\Pagi\PagiFollow;
use App\Models\Pagi\PagiTag;
use App\Models\Pagi\PagiWork;
use App\Models\User;
use App\Notifications\PagiNotification;
use App\Services\VirusScannerService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mews\Purifier\Facades\Purifier;

class PortfolioService
{
    use HandlesImageCompression;

    /**
     * Save cover image securely using compression/video handlers.
     */
    public function saveCoverImage(UploadedFile $file): string
    {
        return $this->compressAndSaveBannerOrVideo($file, 'pagi/covers');
    }

    /**
     * Save gallery item cover image securely.
     */
    public function saveGalleryCover(UploadedFile $file): string
    {
        $mime = $file->getMimeType();
        if (str_starts_with($mime, 'video/')) {
            return $this->compressAndSaveBannerOrVideo($file, 'pagi/gallery');
        } else {
            return $this->compressAndSaveImage($file, 'pagi/gallery', 1920, 1920, 85);
        }
    }

    public function processContentBlocks(array $content, Request $request): array
    {
        $contentData = [];
        foreach ($content as $key => $block) {
            if (isset($block['type'])) {
                $newBlock = $block;

                // Sanitize rich HTML text for XSS
                if (isset($newBlock['value']) && is_string($newBlock['value'])) {
                    $newBlock['value'] = $this->sanitizeHtmlContent($newBlock['value']);
                }
                if (isset($newBlock['initialValue']) && is_string($newBlock['initialValue'])) {
                    $newBlock['initialValue'] = $this->sanitizeHtmlContent($newBlock['initialValue']);
                }
                if (isset($newBlock['name']) && is_string($newBlock['name'])) {
                    $newBlock['name'] = strip_tags($newBlock['name']);
                }
                if (isset($newBlock['link']) && is_string($newBlock['link'])) {
                    $newBlock['link'] = strip_tags($newBlock['link']);
                }

                // Handle single file (image, video, audio, asset)
                if ($request->hasFile("content.{$key}.file")) {
                    $newBlock['file_path'] = $this->uploadSingleBlockFile($request->file("content.{$key}.file"), $key);
                    unset($newBlock['file']); // Remove UploadedFile instance
                    unset($newBlock['preview']); // Remove blob URL
                }

                // Handle multiple files (photo_grid)
                if ($request->hasFile("content.{$key}.files")) {
                    $newBlock['file_paths'] = $this->uploadMultipleBlockFiles($request->file("content.{$key}.files"), $key, $block['file_paths'] ?? []);
                    unset($newBlock['files']);
                    unset($newBlock['previews']);
                }

                $contentData[] = $newBlock;
            }
        }

        return $contentData;
    }

    public function processAndNotifyCollaborators(PagiWork $portfolio, array $collaborators, array $existingCollabs = []): array
    {
        $existingMap = $this->buildExistingCollabsMap($existingCollabs);
        $newCollaborators = [];
        $notifiedCollaborators = [];

        foreach ($collaborators as $c) {
            $this->resolveCollaborator($c, $existingMap, $newCollaborators, $notifiedCollaborators);
        }

        $this->notifyCollaborators($portfolio, $notifiedCollaborators);

        return $newCollaborators;
    }

    /**
     * Parse and sync tags.
     */
    public function syncTags(PagiWork $portfolio, ?string $tags): void
    {
        if (empty($tags)) {
            return;
        }

        $tagNames = array_map('trim', explode(',', strip_tags($tags)));
        $tagIds = [];
        foreach ($tagNames as $name) {
            if (empty($name)) {
                continue;
            }

            $slug = Str::slug($name);
            $tag = PagiTag::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'color' => '#6366f1']
            );

            if (! $tag->wasRecentlyCreated) {
                $tag->increment('usage_count');
            }

            $tagIds[] = $tag->id;
        }
        $portfolio->tags()->sync($tagIds);
    }

    /**
     * Notify followers about new work.
     */
    public function notifyFollowers(PagiWork $work): void
    {
        try {
            $author = $work->user ?? Auth::user();
            $avatar = null;
            if ($author->foto_path) {
                $avatar = str_starts_with($author->foto_path, 'http')
                    ? $author->foto_path
                    : asset('storage/'.$author->foto_path);
            }

            $profileLink = $author->pagi_username
                ? '/pagi/'.$author->pagi_username
                : '/pagi/profile/'.$author->id;

            $followerIds = PagiFollow::query()->where('following_id', $author->id)
                ->limit(200)
                ->pluck('follower_id');

            foreach ($followerIds as $followerId) {
                /** @var User|null $follower */
                $follower = User::query()->find($followerId);
                if ($follower) {
                    $follower->notify(new PagiNotification(
                        type: 'new_work',
                        title: $author->name,
                        message: 'mempublikasikan karya baru: "'.$work->title.'"',
                        avatar: $avatar,
                        href: $profileLink.'?project='.$work->id,
                        extra: [
                            'work_id' => $work->id,
                            'work_title' => $work->title,
                            'author_id' => $author->id,
                        ]
                    ));
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify followers: '.$e->getMessage());
        }
    }

    private function sanitizeHtmlContent($html)
    {
        if (empty($html)) {
            return $html;
        }

        return Purifier::clean($html);
    }

    /**
     * Upload single file block.
     */
    private function uploadSingleBlockFile(UploadedFile $file, string $key): string
    {
        $mime = $file->getMimeType();
        $realExt = $file->guessExtension();

        $allowedVideoMimes = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska', 'video/3gpp'];
        $allowedVideoExts = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', '3gp'];

        $allowedImageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedImageExts = ['jpeg', 'jpg', 'png', 'gif', 'webp'];

        $allowedOtherMimes = [
            'audio/mpeg', 'audio/ogg', 'audio/wav', 'audio/x-wav',
            'application/pdf',
            'application/zip', 'application/x-zip-compressed',
            'application/x-rar-compressed', 'application/x-rar',
            'application/x-tar', 'application/x-7z-compressed',
        ];
        $allowedOtherExts = ['mp3', 'wav', 'ogg', 'pdf', 'zip', 'rar', 'tar', '7z'];

        $isImage = in_array($mime, $allowedImageMimes) && in_array($realExt, $allowedImageExts);
        $isVideo = in_array($mime, $allowedVideoMimes) && in_array($realExt, $allowedVideoExts);
        $isOther = in_array($mime, $allowedOtherMimes) && in_array($realExt, $allowedOtherExts);

        if (! $isImage && ! $isVideo && ! $isOther) {
            throw ValidationException::withMessages([
                "content.{$key}.file" => 'Tipe berkas tidak diizinkan atau berbahaya.',
            ]);
        }

        if ($isImage || $isVideo) {
            return $this->compressAndSaveBannerOrVideo($file, 'pagi/works');
        }

        $scanner = app(VirusScannerService::class);
        $scanResult = $scanner->scan($file);
        if (! $scanResult['safe']) {
            throw ValidationException::withMessages([
                "content.{$key}.file" => $scanResult['reason'],
            ]);
        }

        $uuid = Str::uuid()->toString();
        $filename = $uuid.'.'.$realExt;

        return $file->storeAs('pagi/works', $filename, 'public');
    }

    /**
     * Upload multiple file blocks (photo_grid).
     */
    private function uploadMultipleBlockFiles(array $files, string $key, array $existingPaths): array
    {
        $paths = [];
        foreach ($files as $file) {
            $mime = $file->getMimeType();
            $realExt = $file->guessExtension();

            $allowedVideoMimes = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska', 'video/3gpp'];
            $allowedVideoExts = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', '3gp'];

            $allowedImageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $allowedImageExts = ['jpeg', 'jpg', 'png', 'gif', 'webp'];

            $isImage = in_array($mime, $allowedImageMimes) && in_array($realExt, $allowedImageExts);
            $isVideo = in_array($mime, $allowedVideoMimes) && in_array($realExt, $allowedVideoExts);

            if (! $isImage && ! $isVideo) {
                throw ValidationException::withMessages([
                    "content.{$key}.files" => 'Tipe berkas tidak diizinkan atau berbahaya.',
                ]);
            }

            $paths[] = $this->compressAndSaveBannerOrVideo($file, 'pagi/works');
        }

        return array_merge($existingPaths, $paths);
    }

    /**
     * Build map of existing collaborators.
     */
    private function buildExistingCollabsMap(array $existingCollabs): array
    {
        $existingMap = [];
        foreach ($existingCollabs as $ec) {
            if (is_array($ec)) {
                $existingMap[$ec['name']] = $ec['status'] ?? 'pending';
                if (isset($ec['user_id'])) {
                    $existingMap['id_'.$ec['user_id']] = $ec['status'] ?? 'pending';
                }
            } else {
                $existingMap[$ec] = 'accepted';
            }
        }

        return $existingMap;
    }

    /**
     * Resolve single collaborator details.
     */
    private function resolveCollaborator(mixed $c, array $existingMap, array &$newCollaborators, array &$notifiedCollaborators): void
    {
        if (is_array($c) && isset($c['id'])) {
            $resolvedUser = User::query()->find((int) $c['id']);
            if ($resolvedUser) {
                $status = $existingMap['id_'.$resolvedUser->id] ?? $existingMap[$resolvedUser->name] ?? 'pending';
                $newCollaborators[] = [
                    'user_id' => $resolvedUser->id,
                    'name' => strip_tags($resolvedUser->name),
                    'status' => $status,
                ];

                if ($status === 'pending' && ! isset($existingMap['id_'.$resolvedUser->id]) && ! isset($existingMap[$resolvedUser->name])) {
                    $notifiedCollaborators[] = $resolvedUser;
                }
            }
        } elseif (is_array($c) && isset($c['name'])) {
            $cName = strip_tags($c['name']);
            $status = $existingMap[$cName] ?? 'pending';
            $newCollaborators[] = [
                'name' => $cName,
                'status' => $status,
            ];

            if ($status === 'pending' && ! isset($existingMap[$cName])) {
                $resolvedUser = User::query()->where('name', $cName)->first();
                if ($resolvedUser) {
                    $notifiedCollaborators[] = $resolvedUser;
                }
            }
        } elseif (is_string($c)) {
            $cName = strip_tags($c);
            $status = $existingMap[$cName] ?? 'pending';
            $newCollaborators[] = [
                'name' => $cName,
                'status' => $status,
            ];

            if ($status === 'pending' && ! isset($existingMap[$cName])) {
                $resolvedUser = User::query()->where('name', $cName)->first();
                if ($resolvedUser) {
                    $notifiedCollaborators[] = $resolvedUser;
                }
            }
        }
    }

    /**
     * Notify newly invited collaborators.
     */
    private function notifyCollaborators(PagiWork $portfolio, array $notifiedCollaborators): void
    {
        $inviter = Auth::user();
        if (! $inviter) {
            return;
        }

        $avatar = $inviter->foto_path ? (str_starts_with($inviter->foto_path, 'http') ? $inviter->foto_path : asset('storage/'.$inviter->foto_path)) : null;

        foreach ($notifiedCollaborators as $targetUser) {
            if ($targetUser->id !== $inviter->id) {
                $targetUser->notify(new PagiNotification(
                    'collaboration',
                    $inviter->name,
                    'mengajak Anda berkolaborasi pada proyek: "'.$portfolio->title.'"',
                    $avatar,
                    '/pagi/notifications',
                    [
                        'portfolio_id' => $portfolio->id,
                        'portfolio_title' => $portfolio->title,
                        'inviter_name' => $inviter->name,
                    ]
                ));
            }
        }
    }
}
