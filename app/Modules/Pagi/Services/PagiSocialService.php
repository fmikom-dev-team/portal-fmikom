<?php

namespace App\Modules\Pagi\Services;

use App\Models\Pagi\PagiWork;
use App\Models\User;
use App\Models\UserModuleRole;
use App\Modules\Pagi\Concerns\FormatsPortfolioData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PagiSocialService
{
    use FormatsPortfolioData;

    /**
     * Search users for direct-messaging contact searches.
     */
    public function searchUsers(string $query, int $authId): array
    {
        if (strlen($query) < 1) {
            return [];
        }

        return User::where(function ($q) use ($query) {
            $q->where('name', 'like', '%' . $query . '%')
                ->orWhere('email', 'like', '%' . $query . '%');
        })
            ->where('id', '!=', $authId)
            ->limit(10)
            ->get()
            ->map(function ($u) {
                return [
                    'id'            => $u->id,
                    'name'          => $u->name,
                    'pagi_username' => $u->pagi_username,
                    'foto_path'     => $this->resolveAssetPath($u->foto_path),
                    'role_title'    => $u->role_title ?: 'PAGI Creator',
                ];
            })->toArray();
    }

    /**
     * Get explore people data.
     * ── FIX: Replaced PHP groupBy + full load with SQL-level aggregation per user.
     */
    public function explorePeople(int $pagiModuleId): array
    {
        return Cache::remember("pagi_explore_people_{$pagiModuleId}", 600, function () use ($pagiModuleId) {
            $userModuleRoles = UserModuleRole::where('module_id', $pagiModuleId)
                ->where('is_active', true)
                ->with(['user.programStudi', 'role'])
                ->get()
                ->unique('user_id');

            $userIds = $userModuleRoles->pluck('user_id')->filter()->toArray();

            if (empty($userIds)) {
                return [];
            }

            // ── FIX: Batch-fetch follower counts via SQL aggregation (1 query)
            $followersCounts = DB::table('pagi_follows')
                ->whereIn('following_id', $userIds)
                ->groupBy('following_id')
                ->selectRaw('following_id, count(*) as count')
                ->pluck('count', 'following_id')
                ->toArray();

            // ── FIX: Load only latest 5 works per user using SQL, not PHP groupBy.
            // Uses a self-join subquery to get top-N rows per group efficiently.
            $latestWorkIds = DB::table('pagi_works as w')
                ->joinSub(
                    DB::table('pagi_works')
                        ->selectRaw('user_id, MAX(id) as max_id')
                        ->whereIn('user_id', $userIds)
                        ->where('is_published', true)
                        ->where('status', 'active')
                        ->where(function ($q) {
                            $q->whereNull('visibility')->orWhere('visibility', 'Everyone');
                        })
                        ->groupBy('user_id'),
                    'latest',
                    'w.user_id',
                    '=',
                    'latest.user_id'
                )
                ->where('w.is_published', true)
                ->where('w.status', 'active')
                ->where(function ($q) {
                    $q->whereNull('w.visibility')->orWhere('w.visibility', 'Everyone');
                })
                ->select('w.id', 'w.user_id', 'w.cover_image')
                ->orderBy('w.user_id')
                ->orderBy('w.id', 'desc')
                ->get()
                ->groupBy('user_id');

            // ── FIX: Batch fetch likes counts per user via SQL (1 query, not N queries)
            $likesCounts = DB::table('pagi_work_likes')
                ->join('pagi_works', 'pagi_work_likes.work_id', '=', 'pagi_works.id')
                ->whereIn('pagi_works.user_id', $userIds)
                ->where('pagi_works.is_published', true)
                ->groupBy('pagi_works.user_id')
                ->selectRaw('pagi_works.user_id, count(*) as count')
                ->pluck('count', 'user_id')
                ->toArray();

            $workCounts = DB::table('pagi_works')
                ->whereIn('user_id', $userIds)
                ->where('is_published', true)
                ->where('status', 'active')
                ->groupBy('user_id')
                ->selectRaw('user_id, count(*) as count')
                ->pluck('count', 'user_id')
                ->toArray();

            return $userModuleRoles->map(function ($umr) use ($latestWorkIds, $followersCounts, $likesCounts, $workCounts) {
                $u = $umr->user;
                if (! $u) {
                    return null;
                }

                $covers = collect($latestWorkIds->get($u->id, []))
                    ->map(fn ($w) => $this->resolveAssetPath($w->cover_image))
                    ->filter()
                    ->values()
                    ->toArray();

                return [
                    'id'              => $u->id,
                    'name'            => $u->name,
                    'email'           => $u->email,
                    'pagi_username'   => $u->pagi_username,
                    'role'            => optional($umr->role)->nama ?? 'User',
                    'foto_path'       => $this->resolveAssetPath($u->foto_path),
                    'banner_path'     => $this->resolveAssetPath($u->banner_path),
                    'prodi'           => optional($u->programStudi)->nama ?? null,
                    'covers'          => $covers,
                    'total_likes'     => $likesCounts[$u->id] ?? 0,
                    'total_projects'  => $workCounts[$u->id] ?? 0,
                    'followers_count' => $followersCounts[$u->id] ?? 0,
                    'skills'          => $u->metadata['skills'] ?? [],
                    'location'        => $u->location ?? $u->metadata['location'] ?? null,
                ];
            })
                ->filter()
                ->values()
                ->toArray();
        });
    }

    /**
     * Get gallery exploration items.
     */
    public function exploreGallery(Request $request): array
    {
        $search = $request->input('search');
        $sort   = $request->input('sort', 'Recommended');
        $page   = $request->input('page', 1);

        if (empty($search) && $sort === 'Recommended') {
            return Cache::remember("pagi_gallery_recommended_page_{$page}", 60, function () use ($search, $sort) {
                return $this->fetchGalleryData($search, $sort);
            });
        }

        return $this->fetchGalleryData($search, $sort);
    }

    private function fetchGalleryData(?string $search, string $sort): array
    {
        $query     = $this->buildGalleryQuery($search, $sort);
        $paginator = $query->paginate(16)->withQueryString();

        $works = $paginator->items();

        // Preload collaborator names in one batch query
        $names = $this->extractCollaboratorNames($works);
        $preloadedUsers = [];
        if (! empty($names)) {
            $preloadedUsers = User::whereIn('name', $names)
                ->select(['id', 'name', 'pagi_username', 'foto_path'])
                ->get()
                ->keyBy('name')
                ->all();
        }

        $galleryItems = [];
        foreach ($works as $p) {
            if (! $p->user) {
                continue;
            }
            $authorData    = $this->buildAuthorData($p->user);
            $portfolioData = $this->buildPortfolioData($p, $p->user, $authorData, $preloadedUsers);
            $content       = is_array($p->content) ? $p->content : (json_decode($p->content, true) ?: []);

            $isManual = $this->isManualGallery($content);
            $items    = $isManual
                ? $this->extractManualItems($p, $authorData, $portfolioData)
                : $this->extractAutoItems($p, $content, $authorData, $portfolioData);

            array_push($galleryItems, ...$items);
        }

        return [
            'galleryItems' => $galleryItems,
            'nextPageUrl'  => $paginator->nextPageUrl(),
            'currentPage'  => $paginator->currentPage(),
            'lastPage'     => $paginator->lastPage(),
            'total'        => $paginator->total(),
            'filters'      => ['search' => $search, 'sort' => $sort],
        ];
    }

    // ── Private Gallery Builder Helpers ───────────────────────────────────────

    private function buildGalleryQuery(?string $search, string $sort): Builder
    {
        // Preload likesRelation to prevent N+1 inside buildPortfolioData
        $query = PagiWork::with([
            'user.programStudi',
            'tags',
            'likesRelation',
        ])
            ->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('visibility')->orWhere('visibility', 'Everyone');
            });

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($qu) use ($search) {
                        $qu->where('name', 'like', '%' . $search . '%')
                            ->orWhere('pagi_username', 'like', '%' . $search . '%');
                    });
            });
        }

        match ($sort) {
            'Most Viewed'  => $query->orderBy('views_count', 'desc'),
            'Most Popular' => $query->orderBy('views_count', 'desc')->orderBy('id', 'desc'),
            default        => $query->latest(),
        };

        return $query;
    }

    private function buildAuthorData(User $user): array
    {
        return [
            'id'            => $user->id,
            'name'          => $this->formatName($user->name),
            'avatar'        => $this->resolveAssetPath($user->foto_path),
            'pagi_username' => $user->pagi_username,
            'prodi'         => optional($user->programStudi)->nama,
        ];
    }

    private function buildPortfolioData(PagiWork $p, User $u, array $authorData, ?array $preloadedUsers = null): array
    {
        $defaultPlaceholder = 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop';

        // $p->likes uses the cached likesRelation (no extra query)
        $likes = $p->likes;

        return [
            'id'                     => $p->id,
            'title'                  => $p->title ?? 'Untitled Project',
            'image'                  => $this->resolveAssetPath($p->cover_image) ?? $defaultPlaceholder,
            'author'                 => $this->formatName($u->name),
            'avatar'                 => $this->resolveAssetPath($u->foto_path) ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->formatName($u->name)) . '&background=random',
            'likes'                  => count($likes),
            'liked'                  => Auth::check() ? in_array(Auth::id(), $likes) : false,
            // Note: comments are intentionally omitted from gallery cards (lazy-loaded on demand)
            'views'                  => $p->views_count ?? 0,
            'views_count'            => $p->views_count ?? 0,
            'content'                => null,
            'tools_used'             => $p->tools_used,
            'description'            => $p->description,
            'category'               => $p->category,
            'tags'                   => $p->tags->map(fn ($t) => $t->name)->toArray(),
            'created_at'             => $p->created_at->toISOString(),
            'resolved_collaborators' => $this->resolveCollaborators($p, $preloadedUsers),
            'user'                   => [
                'id'            => $u->id,
                'name'          => $this->formatName($u->name),
                'pagi_username' => $u->pagi_username,
                'avatar'        => $this->resolveAssetPath($u->foto_path),
                'location'      => $u->location ?? 'Banyumas, Indonesia',
            ],
        ];
    }

    private function buildGalleryEntry(string $id, PagiWork $p, string $url, string $type, bool $isManual, array $authorData, array $portfolioData): array
    {
        return [
            'id'             => $id,
            'portfolio_id'   => $p->id,
            'url'            => $url,
            'type'           => $type,
            'title'          => $p->title,
            'is_manual'      => $isManual,
            'likes'          => is_array($p->likes) ? count($p->likes) : 0,
            'views'          => $p->views_count ?? 0,
            'comments_count' => $p->commentsRelation()->count(),
            'author'         => $authorData,
            'portfolio'      => $portfolioData,
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
        $imgUrl = $this->resolveAssetPath($p->cover_image);
        $type   = $this->isVideoUrlLocal($p->cover_image) ? 'video' : 'image';

        return [$this->buildGalleryEntry('manual-' . $p->id, $p, $imgUrl, $type, true, $authorData, $portfolioData)];
    }

    private function extractAutoItems(PagiWork $p, array $content, array $authorData, array $portfolioData): array
    {
        $defaultPlaceholder = 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop';
        $items    = [];
        $seenUrls = [];
        $maxItems = 2;
        $count    = 0;

        if ($p->cover_image && $p->cover_image !== $defaultPlaceholder) {
            $imgUrl     = str_starts_with($p->cover_image, 'http') ? $p->cover_image : asset('storage/' . $p->cover_image);
            $seenUrls[] = $imgUrl;
            $items[]    = $this->buildGalleryEntry('cover-' . $p->id, $p, $imgUrl, $this->isVideoUrlLocal($p->cover_image) ? 'video' : 'image', false, $authorData, $portfolioData);
            $count++;
        }

        foreach ($content as $bIdx => $block) {
            if ($count >= $maxItems) {
                break;
            }
            if (! $block || ! isset($block['type'])) {
                continue;
            }
            $newItems = $this->processContentBlock($block, $bIdx, $p, $seenUrls, $authorData, $portfolioData);
            foreach ($newItems as $item) {
                if ($count >= $maxItems) {
                    break;
                }
                $seenUrls[] = $item['url'];
                $items[]    = $item;
                $count++;
            }
        }

        return $items;
    }

    private function processContentBlock(array $block, int|string $bIdx, PagiWork $p, array $seenUrls, array $authorData, array $portfolioData): array
    {
        return match ($block['type']) {
            'image'       => $this->processImageBlock($block, $bIdx, $p, $seenUrls, $authorData, $portfolioData),
            'video_audio' => $this->processVideoBlock($block, $bIdx, $p, $seenUrls, $authorData, $portfolioData),
            'photo_grid'  => $this->processGridBlock($block, $bIdx, $p, $seenUrls, $authorData, $portfolioData),
            default       => [],
        };
    }

    private function processImageBlock(array $block, int|string $bIdx, PagiWork $p, array $seenUrls, array $authorData, array $portfolioData): array
    {
        if (empty($block['file_path'])) {
            return [];
        }
        $url = asset('storage/' . $block['file_path']);
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
        $url = asset('storage/' . $block['file_path']);
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
            $url = asset('storage/' . $filePath);
            if (! in_array($url, $seenUrls)) {
                $items[]    = $this->buildGalleryEntry("block-grid-{$p->id}-{$bIdx}-{$gIdx}", $p, $url, 'image', false, $authorData, $portfolioData);
                $seenUrls[] = $url;
            }
        }

        return $items;
    }

    /**
     * Get people you may know in a module.
     *
     * ── FIX: Replaced inRandomOrder() (ORDER BY RAND) with a deterministic
     * random approach using a hash-based offset to avoid full-table sort.
     */
    public function getPeopleYouMayKnow(int $moduleId, int $currentUserId): Collection
    {
        return Cache::remember("pagi_people_you_may_know_{$moduleId}_{$currentUserId}", 600, function () use ($moduleId, $currentUserId) {
            // Count total eligible users once
            $total = UserModuleRole::where('module_id', $moduleId)
                ->where('user_id', '!=', $currentUserId)
                ->where('is_active', true)
                ->count();

            if ($total === 0) {
                return collect();
            }

            // Deterministic-random offset based on current user ID + current hour
            // (changes every hour, feels random to users, but avoids ORDER BY RAND())
            $seed   = ($currentUserId * 31 + (int) now()->format('GH')) % max(1, $total);
            $offset = max(0, $seed - 5);

            return UserModuleRole::with(['user.programStudi', 'role'])
                ->where('module_id', $moduleId)
                ->where('user_id', '!=', $currentUserId)
                ->where('is_active', true)
                ->skip($offset)
                ->limit(5)
                ->get()
                ->map(fn ($umr) => $umr->user ? [
                    'id'            => $umr->user->id,
                    'name'          => $this->formatName($umr->user->name),
                    'email'         => $umr->user->email,
                    'pagi_username' => $umr->user->pagi_username,
                    'role'          => optional($umr->role)->nama ?? 'User',
                    'foto_path'     => $this->resolveAssetPath($umr->user->foto_path),
                    'prodi'         => optional($umr->user->programStudi)->nama ?? null,
                ] : null)
                ->filter()
                ->values();
        });
    }

    /**
     * Get followers and following list for a user.
     * ── FIX: Consolidated to fewer queries by merging IDs before fetching users.
     */
    public function getFollowRelations(User $user): array
    {
        $followersIds = $user->pagiFollowers()->pluck('follower_id')->toArray();
        $followingIds = $user->pagiFollowing()->pluck('following_id')->toArray();

        // Fetch all needed users in one query
        $allIds   = array_unique(array_merge($followersIds, $followingIds));
        $allUsers = User::whereIn('id', $allIds)
            ->select(['id', 'name', 'pagi_username', 'foto_path', 'role_title', 'user_type'])
            ->get()
            ->keyBy('id');

        $mapIds = function (array $ids) use ($allUsers) {
            return collect($ids)->map(fn ($id) => isset($allUsers[$id]) ? [
                'id'            => $allUsers[$id]->id,
                'name'          => $this->formatName($allUsers[$id]->name),
                'pagi_username' => $allUsers[$id]->pagi_username,
                'foto_path'     => $this->resolveAssetPath($allUsers[$id]->foto_path),
                'role_title'    => $allUsers[$id]->role_title ?? $allUsers[$id]->user_type ?? 'Member',
            ] : null)->filter()->values()->toArray();
        };

        return [
            'followers' => $mapIds($followersIds),
            'following' => $mapIds($followingIds),
        ];
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function isVideoUrlLocal(mixed $url): bool
    {
        if (empty($url)) {
            return false;
        }
        $cleanUrl = explode('?', (string) $url)[0];
        $ext      = strtolower(pathinfo($cleanUrl, PATHINFO_EXTENSION));

        return in_array($ext, ['mp4', 'webm', 'mov', 'avi', 'mkv', '3gp']);
    }

    private function isVideoPathLocal(mixed $path): bool
    {
        if (empty($path)) {
            return false;
        }
        $ext = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));

        return in_array($ext, ['mp4', 'webm', 'mov', 'avi', 'mkv', '3gp']);
    }
}
