<?php

namespace App\Modules\Pagi\Concerns;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Shared formatting helpers for portfolio data.
 *
 * Extracted from PagiProfileService and PagiSocialService to eliminate
 * code duplication and ensure a single source of truth for formatting logic.
 */
trait FormatsPortfolioData
{
    /**
     * Format portfolio content blocks: resolve file_path / file_paths to full storage URLs.
     */
    public function formatPortfolioContent(mixed $content): array
    {
        if (empty($content)) {
            return [];
        }
        if (is_string($content)) {
            $content = json_decode($content, true);
        }
        if (! is_array($content)) {
            return [];
        }

        foreach ($content as $key => $block) {
            if (is_array($block)) {
                if (isset($block['file_path'])) {
                    $content[$key]['preview'] = asset('storage/'.$block['file_path']);
                }
                if (isset($block['file_paths']) && is_array($block['file_paths'])) {
                    $content[$key]['previews'] = array_map(
                        fn ($path) => asset('storage/'.$path),
                        $block['file_paths']
                    );
                }
            }
        }

        return $content;
    }

    /**
     * Format a list of comment models (or raw arrays) into the frontend shape.
     *
     * Accepts either:
     * - An Eloquent Collection of PagiWorkComment models (with relations pre-loaded)
     * - A plain array / JSON-encoded string (legacy format, kept for compatibility)
     */
    public function formatComments(mixed $comments): array
    {
        if (empty($comments)) {
            return [];
        }

        // --- Eloquent Collection path (normalized DB table) ---
        if ($comments instanceof Collection || (is_array($comments) && isset($comments[0]) && is_object($comments[0]))) {
            $collection = $comments instanceof Collection ? $comments : collect($comments);

            return $collection->map(function ($c) {
                $avatar = $this->resolveAssetPath($c->user->foto_path ?? null);

                $replies = collect($c->replies ?? [])->map(function ($r) {
                    return [
                        'id' => $r->uuid,
                        'user_id' => $r->user_id,
                        'name' => $this->formatName($r->user->name ?? 'Anonymous'),
                        'avatar' => $this->resolveAssetPath($r->user->foto_path ?? null),
                        'body' => $r->body,
                        'content' => $r->body,
                        'created_at' => $r->created_at->toISOString(),
                        'time' => $r->created_at->diffForHumans(),
                        'likes' => $r->likesRelation ? $r->likesRelation->pluck('id')->toArray() : [],
                    ];
                })->toArray();

                return [
                    'id' => $c->uuid,
                    'user_id' => $c->user_id,
                    'name' => $this->formatName($c->user->name ?? 'Anonymous'),
                    'pagi_username' => $c->user?->pagi_username,
                    'avatar' => $avatar,
                    'body' => $c->body,
                    'content' => $c->body,
                    'created_at' => $c->created_at->toISOString(),
                    'time' => $c->created_at->diffForHumans(),
                    'likes' => $c->likesRelation ? $c->likesRelation->pluck('id')->toArray() : [],
                    'replies' => $replies,
                ];
            })->values()->toArray();
        }

        // --- Legacy plain-array / JSON-string path (fallback) ---
        $list = is_string($comments) ? json_decode($comments, true) : $comments;
        if (! is_array($list)) {
            return [];
        }

        return array_map(function ($c) {
            $replies = [];
            if (isset($c['replies']) && is_array($c['replies'])) {
                $replies = array_map(function ($r) {
                    return [
                        'id' => $r['id'] ?? uniqid(),
                        'user_id' => $r['user_id'] ?? null,
                        'name' => $this->formatName($r['name'] ?? 'Anonymous'),
                        'avatar' => $r['avatar'] ?? null,
                        'content' => $r['content'] ?? $r['body'] ?? '',
                        'body' => $r['content'] ?? $r['body'] ?? '',
                        'created_at' => $r['created_at'] ?? now()->toISOString(),
                        'likes' => $r['likes'] ?? [],
                    ];
                }, $c['replies']);
            }

            return [
                'id' => $c['id'] ?? uniqid(),
                'user_id' => $c['user_id'] ?? null,
                'name' => $this->formatName($c['name'] ?? 'Anonymous'),
                'avatar' => $c['avatar'] ?? null,
                'content' => $c['content'] ?? $c['body'] ?? '',
                'body' => $c['content'] ?? $c['body'] ?? '',
                'created_at' => $c['created_at'] ?? now()->toISOString(),
                'likes' => $c['likes'] ?? [],
                'replies' => $replies,
            ];
        }, $list);
    }

    /**
     * Resolve collaborator User models from a portfolio content JSON.
     *
     * Uses $preloadedUsers map (name → User) when available to avoid extra queries.
     */
    public function resolveCollaborators(mixed $portfolio, ?array $preloadedUsers = null): array
    {
        if (! $portfolio || ! $portfolio->content) {
            return [];
        }
        $content = is_string($portfolio->content) ? json_decode($portfolio->content, true) : $portfolio->content;
        if (! is_array($content)) {
            return [];
        }

        foreach ($content as $block) {
            if (! ($block && isset($block['type']) && $block['type'] === 'featured_details')) {
                continue;
            }

            $collaborators = $block['data']['collaborators'] ?? $block['collaborators'] ?? [];
            if (empty($collaborators)) {
                continue;
            }

            [$names, $statusMap] = $this->parseCollaboratorList($collaborators);

            if (empty($names)) {
                return [];
            }

            if ($preloadedUsers !== null) {
                $users = collect();
                foreach ($names as $name) {
                    if (isset($preloadedUsers[$name])) {
                        $users->push($preloadedUsers[$name]);
                    }
                }
            } else {
                $users = User::whereIn('name', $names)
                    ->select(['id', 'name', 'pagi_username', 'foto_path'])
                    ->get();
            }

            return $users->map(fn ($u) => [
                'id' => $u->id,
                'name' => $this->formatName($u->name),
                'pagi_username' => $u->pagi_username,
                'avatar' => $this->resolveAssetPath($u->foto_path),
                'status' => $statusMap[$u->name] ?? 'pending',
            ])->toArray();
        }

        return [];
    }

    /**
     * Extract all collaborator names from a collection of portfolios (for bulk preloading).
     */
    public function extractCollaboratorNames(iterable $portfolios): array
    {
        $names = [];
        foreach ($portfolios as $portfolio) {
            if (! $portfolio || ! $portfolio->content) {
                continue;
            }
            $content = is_string($portfolio->content) ? json_decode($portfolio->content, true) : $portfolio->content;
            if (! is_array($content)) {
                continue;
            }
            foreach ($content as $block) {
                if (! ($block && isset($block['type']) && $block['type'] === 'featured_details')) {
                    continue;
                }
                $collaborators = $block['data']['collaborators'] ?? $block['collaborators'] ?? [];
                if (! empty($collaborators)) {
                    [$blockNames] = $this->parseCollaboratorList($collaborators);
                    array_push($names, ...$blockNames);
                }
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * Format a user's display name (convert all-caps to title case).
     */
    public function formatName(?string $name): string
    {
        if (! $name) {
            return '';
        }
        if ($name === strtoupper($name)) {
            return ucwords(strtolower($name));
        }

        return $name;
    }

    /**
     * Resolve a storage-relative or absolute path to a full URL.
     */
    public function resolveAssetPath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
            $webpPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $path);
            if (file_exists(public_path('storage/'.$webpPath))) {
                $path = $webpPath;
            }
        } elseif (strtolower($extension) === 'mp4') {
            $webmPath = preg_replace('/\.mp4$/i', '.webm', $path);
            if (file_exists(public_path('storage/'.$webmPath))) {
                $path = $webmPath;
            }
        }

        return asset('storage/'.$path);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Parse a collaborators array into [names[], statusMap[name => status]].
     *
     * @return array{0: string[], 1: array<string, string>}
     */
    private function parseCollaboratorList(mixed $collaborators): array
    {
        $names = [];
        $statusMap = [];

        if (is_array($collaborators)) {
            foreach ($collaborators as $c) {
                if (is_array($c)) {
                    $rawName = $c['name'] ?? '';
                    $cName = is_scalar($rawName) ? trim((string) $rawName) : '';
                    $cStatus = is_scalar($c['status'] ?? 'pending') ? ($c['status'] ?? 'pending') : 'pending';
                } else {
                    $cName = is_scalar($c) ? trim((string) $c) : '';
                    $cStatus = 'accepted';
                }
                if ($cName !== '') {
                    $names[] = $cName;
                    $statusMap[$cName] = $cStatus;
                }
            }
        } else {
            $split = array_map('trim', explode(',', (string) $collaborators));
            foreach ($split as $name) {
                if ($name !== '') {
                    $names[] = $name;
                    $statusMap[$name] = 'accepted';
                }
            }
        }

        $names = array_values(array_filter($names, fn ($n) => is_string($n) && $n !== ''));

        return [$names, $statusMap];
    }
}
