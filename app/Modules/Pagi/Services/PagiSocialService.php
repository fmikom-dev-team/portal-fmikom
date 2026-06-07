<?php

namespace App\Modules\Pagi\Services;

use App\Models\Pagi\PagiWork;
use App\Models\User;
use App\Models\UserModuleRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PagiSocialService
{
    /**
     * Resolve asset URL.
     */
    public function resolveAssetUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return str_starts_with($path, 'http') ? $path : asset('storage/'.$path);
    }

    /**
     * Search users for direct-messaging contact searches.
     */
    public function searchUsers(string $query, int $authId): array
    {
        if (strlen($query) < 1) {
            return [];
        }

        return User::where(function ($q) use ($query) {
            $q->where('name', 'like', '%'.$query.'%')
                ->orWhere('email', 'like', '%'.$query.'%');
        })
            ->where('id', '!=', $authId)
            ->limit(10)
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'pagi_username' => $u->pagi_username,
                    'foto_path' => $this->resolveAssetUrl($u->foto_path),
                    'role_title' => $u->role_title ?: 'PAGI Creator',
                ];
            })->toArray();
    }

    /**
     * Get explore people data.
     */
    public function explorePeople(int $pagiModuleId): array
    {
        $userModuleRoles = UserModuleRole::where('module_id', $pagiModuleId)
            ->where('is_active', true)
            ->with(['user.programStudi', 'role'])
            ->get()
            ->unique('user_id');

        $userIds = $userModuleRoles->pluck('user_id')->filter()->toArray();

        $allWorks = PagiWork::whereIn('user_id', $userIds)
            ->where('is_published', true)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('visibility')
                    ->orWhere('visibility', 'Everyone');
            })
            ->latest()
            ->get()
            ->groupBy('user_id');

        return $userModuleRoles->map(function ($umr) use ($allWorks) {
            $u = $umr->user;
            if (! $u) {
                return null;
            }

            $allPortfolios = $allWorks->get($u->id) ?? collect();

            $covers = $allPortfolios->map(function ($p) {
                return $this->resolveAssetUrl($p->cover_image);
            })->filter()->values()->toArray();

            $totalLikes = $allPortfolios->sum(fn ($p) => is_array($p->likes) ? count($p->likes) : 0);
            $totalProjects = $allPortfolios->count();

            $followers = $u->metadata['followers'] ?? [];
            $followersCount = is_array($followers) ? count($followers) : 0;

            $skills = $u->metadata['skills'] ?? [];
            $location = $u->location ?? $u->metadata['location'] ?? null;

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'pagi_username' => $u->pagi_username,
                'role' => optional($umr->role)->nama ?? 'User',
                'foto_path' => $this->resolveAssetUrl($u->foto_path),
                'banner_path' => $this->resolveAssetUrl($u->banner_path),
                'prodi' => optional($u->programStudi)->nama ?? null,
                'covers' => $covers,
                'total_likes' => $totalLikes,
                'total_projects' => $totalProjects,
                'followers_count' => $followersCount,
                'skills' => $skills,
                'location' => $location,
            ];
        })
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Get gallery exploration items.
     */
    public function exploreGallery(Request $request): array
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'Recommended');

        $query = $this->buildGalleryQuery($search, $sort);
        $paginator = $query->paginate(16)->withQueryString();

        $galleryItems = [];
        foreach ($paginator->items() as $p) {
            if (! $p->user) {
                continue;
            }
            $authorData = $this->buildAuthorData($p->user);
            $portfolioData = $this->buildPortfolioData($p, $p->user, $authorData);
            $content = is_array($p->content) ? $p->content : (json_decode($p->content, true) ?: []);

            $isManual = $this->isManualGallery($content);
            $items = $isManual
                ? $this->extractManualItems($p, $authorData, $portfolioData)
                : $this->extractAutoItems($p, $content, $authorData, $portfolioData);

            array_push($galleryItems, ...$items);
        }

        return [
            'galleryItems' => $galleryItems,
            'nextPageUrl' => $paginator->nextPageUrl(),
            'currentPage' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'filters' => ['search' => $search, 'sort' => $sort],
        ];
    }

    // --- Private Gallery Builder Helpers ---

    private function buildGalleryQuery(?string $search, string $sort): Builder
    {
        $query = PagiWork::with(['user.programStudi', 'tags'])
            ->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('visibility')->orWhere('visibility', 'Everyone');
            });

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function ($qu) use ($search) {
                        $qu->where('name', 'like', '%'.$search.'%')
                            ->orWhere('pagi_username', 'like', '%'.$search.'%');
                    });
            });
        }

        match ($sort) {
            'Most Viewed' => $query->orderBy('views_count', 'desc'),
            'Most Popular' => $query->orderBy('views_count', 'desc')->orderBy('id', 'desc'),
            default => $query->latest(),
        };

        return $query;
    }

    private function buildAuthorData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $this->formatName($user->name),
            'avatar' => $this->resolveAssetUrl($user->foto_path),
            'pagi_username' => $user->pagi_username,
            'prodi' => optional($user->programStudi)->nama,
        ];
    }

    private function buildPortfolioData(PagiWork $p, User $u, array $authorData): array
    {
        $defaultPlaceholder = 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop';

        return [
            'id' => $p->id,
            'title' => $p->title ?? 'Untitled Project',
            'image' => $this->resolveAssetUrl($p->cover_image) ?? $defaultPlaceholder,
            'author' => $this->formatName($u->name),
            'avatar' => $this->resolveAssetUrl($u->foto_path) ?? 'https://ui-avatars.com/api/?name='.urlencode($this->formatName($u->name)).'&background=random',
            'likes' => is_array($p->likes) ? count($p->likes) : 0,
            'liked' => auth()->check() ? in_array(auth()->id(), $p->likes ?? []) : false,
            'comments' => $this->formatComments($p->comments ?? []),
            'views' => $p->views_count ?? 0,
            'views_count' => $p->views_count ?? 0,
            'content' => $this->formatPortfolioContent($p->content),
            'tools_used' => $p->tools_used,
            'description' => $p->description,
            'category' => $p->category,
            'tags' => $p->tags->map(fn ($t) => $t->name)->toArray(),
            'created_at' => $p->created_at->toISOString(),
            'resolved_collaborators' => $this->resolveCollaborators($p),
            'user' => [
                'id' => $u->id,
                'name' => $this->formatName($u->name),
                'pagi_username' => $u->pagi_username,
                'avatar' => $this->resolveAssetUrl($u->foto_path),
                'location' => $u->location ?? 'Banyumas, Indonesia',
            ],
        ];
    }

    private function buildGalleryEntry(string $id, PagiWork $p, string $url, string $type, bool $isManual, array $authorData, array $portfolioData): array
    {
        return [
            'id' => $id,
            'portfolio_id' => $p->id,
            'url' => $url,
            'type' => $type,
            'title' => $p->title,
            'is_manual' => $isManual,
            'likes' => is_array($p->likes) ? count($p->likes) : 0,
            'views' => $p->views_count ?? 0,
            'comments_count' => is_array($p->comments) ? count($p->comments) : 0,
            'author' => $authorData,
            'portfolio' => $portfolioData,
        ];
    }

    private function isManualGallery(array $content): bool
    {
        foreach ($content as $block) {
            if (isset($block['type']) && $block['type'] === 'gallery_item') {
                return true;
            }
        }

        return false;
    }

    private function extractManualItems(PagiWork $p, array $authorData, array $portfolioData): array
    {
        if (! $p->cover_image) {
            return [];
        }
        $imgUrl = $this->resolveAssetUrl($p->cover_image);
        $type = $this->isVideoUrlLocal($p->cover_image) ? 'video' : 'image';

        return [$this->buildGalleryEntry('manual-'.$p->id, $p, $imgUrl, $type, true, $authorData, $portfolioData)];
    }

    private function extractAutoItems(PagiWork $p, array $content, array $authorData, array $portfolioData): array
    {
        $defaultPlaceholder = 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop';
        $items = [];
        $seenUrls = [];

        if ($p->cover_image && $p->cover_image !== $defaultPlaceholder) {
            $imgUrl = str_starts_with($p->cover_image, 'http') ? $p->cover_image : asset('storage/'.$p->cover_image);
            $seenUrls[] = $imgUrl;
            $items[] = $this->buildGalleryEntry('cover-'.$p->id, $p, $imgUrl, $this->isVideoUrlLocal($p->cover_image) ? 'video' : 'image', false, $authorData, $portfolioData);
        }

        foreach ($content as $bIdx => $block) {
            if (! $block || ! isset($block['type'])) {
                continue;
            }
            $newItems = $this->processContentBlock($block, $bIdx, $p, $seenUrls, $authorData, $portfolioData);
            foreach ($newItems as $item) {
                $seenUrls[] = $item['url'];
                $items[] = $item;
            }
        }

        return $items;
    }

    private function processContentBlock(array $block, int|string $bIdx, PagiWork $p, array $seenUrls, array $authorData, array $portfolioData): array
    {
        return match ($block['type']) {
            'image' => $this->processImageBlock($block, $bIdx, $p, $seenUrls, $authorData, $portfolioData),
            'video_audio' => $this->processVideoBlock($block, $bIdx, $p, $seenUrls, $authorData, $portfolioData),
            'photo_grid' => $this->processGridBlock($block, $bIdx, $p, $seenUrls, $authorData, $portfolioData),
            default => [],
        };
    }

    private function processImageBlock(array $block, int|string $bIdx, PagiWork $p, array $seenUrls, array $authorData, array $portfolioData): array
    {
        if (empty($block['file_path'])) {
            return [];
        }
        $url = asset('storage/'.$block['file_path']);
        if (in_array($url, $seenUrls)) {
            return [];
        }

        return [$this->buildGalleryEntry("block-image-{$p->id}-{$bIdx}", $p, $url, 'image', false, $authorData, $portfolioData)];
    }

    private function processVideoBlock(array $block, int|string $bIdx, PagiWork $p, array $seenUrls, array $authorData, array $portfolioData): array
    {
        if (empty($block['file_path']) || ! $this->isVideoPathLocal($block['file_path'])) {
            return [];
        }
        $url = asset('storage/'.$block['file_path']);
        if (in_array($url, $seenUrls)) {
            return [];
        }

        return [$this->buildGalleryEntry("block-video-{$p->id}-{$bIdx}", $p, $url, 'video', false, $authorData, $portfolioData)];
    }

    private function processGridBlock(array $block, int|string $bIdx, PagiWork $p, array $seenUrls, array $authorData, array $portfolioData): array
    {
        if (empty($block['file_paths']) || ! is_array($block['file_paths'])) {
            return [];
        }
        $items = [];
        foreach ($block['file_paths'] as $gIdx => $filePath) {
            if (empty($filePath)) {
                continue;
            }
            $url = asset('storage/'.$filePath);
            if (! in_array($url, $seenUrls)) {
                $items[] = $this->buildGalleryEntry("block-grid-{$p->id}-{$bIdx}-{$gIdx}", $p, $url, 'image', false, $authorData, $portfolioData);
                $seenUrls[] = $url;
            }
        }

        return $items;
    }

    /**
     * Get people you may know in a module.
     */
    public function getPeopleYouMayKnow(int $moduleId, int $currentUserId): Collection
    {
        return UserModuleRole::with(['user.programStudi', 'role'])
            ->where('module_id', $moduleId)
            ->where('user_id', '!=', $currentUserId)
            ->where('is_active', true)
            ->inRandomOrder()
            ->limit(5)
            ->get()
            ->map(fn ($umr) => $umr->user ? [
                'id' => $umr->user->id,
                'name' => $this->formatName($umr->user->name),
                'email' => $umr->user->email,
                'pagi_username' => $umr->user->pagi_username,
                'role' => optional($umr->role)->nama ?? 'User',
                'foto_path' => $this->resolveAssetUrl($umr->user->foto_path),
                'prodi' => optional($umr->user->programStudi)->nama ?? null,
            ] : null)
            ->filter()
            ->values();
    }

    /**
     * Get followers and following list for a user.
     */
    public function getFollowRelations(User $user): array
    {
        $followersIds = $user->metadata['followers'] ?? [];
        $followingIds = $user->metadata['following'] ?? [];

        $mapUsers = function ($ids) {
            return User::whereIn('id', $ids)
                ->get()
                ->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $this->formatName($u->name),
                    'pagi_username' => $u->pagi_username,
                    'foto_path' => $this->resolveAssetUrl($u->foto_path),
                    'role_title' => $u->role_title ?? $u->user_type ?? 'Member',
                ])
                ->values()
                ->toArray();
        };

        return [
            'followers' => $mapUsers($followersIds),
            'following' => $mapUsers($followingIds),
        ];
    }

    // --- Private Helper Formatting Methods ---

    private function isVideoUrlLocal($url)
    {
        if (empty($url)) {
            return false;
        }
        $cleanUrl = explode('?', $url)[0];
        $ext = strtolower(pathinfo($cleanUrl, PATHINFO_EXTENSION));

        return in_array($ext, ['mp4', 'webm', 'mov', 'avi', 'mkv', '3gp']);
    }

    private function isVideoPathLocal($path)
    {
        if (empty($path)) {
            return false;
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($ext, ['mp4', 'webm', 'mov', 'avi', 'mkv', '3gp']);
    }

    private function formatPortfolioContent($content)
    {
        if (empty($content)) {
            return [];
        }
        if (is_string($content)) {
            $decoded = json_decode($content, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($content) ? $content : [];
    }

    private function formatComments($comments)
    {
        $result = [];
        if (! empty($comments)) {
            $list = is_string($comments) ? json_decode($comments, true) : $comments;
            if (is_array($list)) {
                $result = array_map(function ($c) {
                    $replies = [];
                    if (isset($c['replies']) && is_array($c['replies'])) {
                        $replies = array_map(function ($r) {
                            return [
                                'id' => $r['id'] ?? uniqid(),
                                'user_id' => $r['user_id'] ?? null,
                                'name' => isset($r['name']) ? $this->formatName($r['name']) : 'Anonymous',
                                'avatar' => $r['avatar'] ?? null,
                                'content' => $r['content'] ?? '',
                                'created_at' => $r['created_at'] ?? now()->toISOString(),
                                'likes' => $r['likes'] ?? [],
                            ];
                        }, $c['replies']);
                    }

                    return [
                        'id' => $c['id'] ?? uniqid(),
                        'user_id' => $c['user_id'] ?? null,
                        'name' => isset($c['name']) ? $this->formatName($c['name']) : 'Anonymous',
                        'avatar' => $c['avatar'] ?? null,
                        'content' => $c['content'] ?? '',
                        'created_at' => $c['created_at'] ?? now()->toISOString(),
                        'likes' => $c['likes'] ?? [],
                        'replies' => $replies,
                    ];
                }, $list);
            }
        }

        return $result;
    }

    private function resolveCollaborators($portfolio)
    {
        if (! $portfolio || ! $portfolio->content) {
            return [];
        }
        $content = is_string($portfolio->content) ? json_decode($portfolio->content, true) : $portfolio->content;
        if (! is_array($content)) {
            return [];
        }
        foreach ($content as $block) {
            if ($block && isset($block['type']) && $block['type'] === 'featured_details') {
                $collaborators = $block['data']['collaborators'] ?? $block['collaborators'] ?? [];
                if (! empty($collaborators)) {
                    $names = [];
                    $statusMap = [];
                    if (is_array($collaborators)) {
                        foreach ($collaborators as $c) {
                            if (is_array($c)) {
                                $cName = $c['name'] ?? '';
                                $cStatus = $c['status'] ?? 'pending';
                            } else {
                                $cName = (string) $c;
                                $cStatus = 'accepted';
                            }
                            if (! empty(trim($cName))) {
                                $names[] = $cName;
                                $statusMap[$cName] = $cStatus;
                            }
                        }
                    } else {
                        $names = array_map('trim', explode(',', $collaborators));
                        foreach ($names as $name) {
                            $statusMap[$name] = 'accepted';
                        }
                    }
                    $names = array_filter($names);

                    return User::whereIn('name', $names)
                        ->select(['id', 'name', 'pagi_username', 'foto_path'])
                        ->get()
                        ->map(fn ($u) => [
                            'id' => $u->id,
                            'name' => $this->formatName($u->name),
                            'pagi_username' => $u->pagi_username,
                            'avatar' => $this->resolveAssetUrl($u->foto_path),
                            'status' => $statusMap[$u->name] ?? 'pending',
                        ])->toArray();
                }
            }
        }

        return [];
    }

    private function formatName(?string $name): string
    {
        if (! $name) {
            return '';
        }
        if ($name === strtoupper($name)) {
            return ucwords(strtolower($name));
        }

        return $name;
    }
}
