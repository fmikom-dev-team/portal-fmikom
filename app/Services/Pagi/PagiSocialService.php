<?php

namespace App\Services\Pagi;

use App\Models\User;
use App\Models\PagiWork;
use App\Models\UserModuleRole;
use Illuminate\Http\Request;

class PagiSocialService
{
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
                'id' => $u->id,
                'name' => $u->name,
                'pagi_username' => $u->pagi_username,
                'foto_path' => $u->foto_path ? (str_starts_with($u->foto_path, 'http') ? $u->foto_path : asset('storage/' . $u->foto_path)) : null,
                'role_title' => $u->role_title ?: 'PAGI Creator',
            ];
        })->toArray();
    }

    /**
     * Get explore people data.
     */
    public function explorePeople(int $pagiModuleId): array
    {
        return UserModuleRole::where('module_id', $pagiModuleId)
            ->where('is_active', true)
            ->with(['user.programStudi', 'role'])
            ->get()
            ->unique('user_id')
            ->map(function ($umr) {
                $u = $umr->user;
                if (!$u) {
                    return null;
                }

                $allPortfolios = PagiWork::where('user_id', $u->id)
                    ->where('is_published', true)
                    ->where('status', 'active')
                    ->where(function ($q) {
                        $q->whereNull('visibility')
                          ->orWhere('visibility', 'Everyone');
                    })
                    ->latest()
                    ->get();

                $covers = $allPortfolios->map(function ($p) {
                    return $p->cover_image
                        ? (str_starts_with($p->cover_image, 'http') ? $p->cover_image : asset('storage/' . $p->cover_image))
                        : null;
                })->filter()->values()->toArray();

                $totalLikes = $allPortfolios->sum(fn($p) => is_array($p->likes) ? count($p->likes) : 0);
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
                    'foto_path' => $u->foto_path
                        ? (str_starts_with($u->foto_path, 'http') ? $u->foto_path : asset('storage/' . $u->foto_path))
                        : null,
                    'banner_path' => $u->banner_path
                        ? (str_starts_with($u->banner_path, 'http') ? $u->banner_path : asset('storage/' . $u->banner_path))
                        : null,
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

        $query = PagiWork::with(['user.programStudi'])
            ->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('visibility')
                  ->orWhere('visibility', 'Everyone');
            });

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function ($qu) use ($search) {
                      $qu->where('name', 'like', '%' . $search . '%')
                         ->orWhere('pagi_username', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($sort === 'Most Viewed') {
            $query->orderBy('views_count', 'desc');
        } elseif ($sort === 'Most Popular') {
            $query->orderBy('views_count', 'desc')->orderBy('id', 'desc');
        } else {
            $query->latest();
        }

        $paginator = $query->paginate(16)->withQueryString();
        $galleryItems = [];
        $defaultPlaceholder = 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop';

        foreach ($paginator->items() as $p) {
            $u = $p->user;
            if (!$u) continue;

            $authorData = [
                'id' => $u->id,
                'name' => $u->name,
                'avatar' => $u->foto_path ? (str_starts_with($u->foto_path, 'http') ? $u->foto_path : asset('storage/' . $u->foto_path)) : null,
                'pagi_username' => $u->pagi_username,
                'prodi' => optional($u->programStudi)->nama,
            ];

            $portfolioData = [
                'id' => $p->id,
                'title' => $p->title ?? 'Untitled Project',
                'image' => $p->cover_image ? (str_starts_with($p->cover_image, 'http') ? $p->cover_image : asset('storage/' . $p->cover_image)) : $defaultPlaceholder,
                'author' => $u->name,
                'avatar' => $u->foto_path ? (str_starts_with($u->foto_path, 'http') ? $u->foto_path : asset('storage/' . $u->foto_path)) : 'https://ui-avatars.com/api/?name=' . urlencode($u->name) . '&background=random',
                'likes' => is_array($p->likes) ? count($p->likes) : 0,
                'liked' => auth()->check() ? in_array(auth()->id(), $p->likes ?? []) : false,
                'comments' => $this->formatComments($p->comments ?? []),
                'views' => $p->views_count ?? 0,
                'views_count' => $p->views_count ?? 0,
                'content' => $this->formatPortfolioContent($p->content),
                'tools_used' => $p->tools_used,
                'description' => $p->description,
                'category' => $p->category,
                'tags' => $p->tags->map(fn($t) => $t->name)->toArray(),
                'created_at' => $p->created_at->toISOString(),
                'resolved_collaborators' => $this->resolveCollaborators($p),
                'user' => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'pagi_username' => $u->pagi_username,
                    'avatar' => $u->foto_path ? (str_starts_with($u->foto_path, 'http') ? $u->foto_path : asset('storage/' . $u->foto_path)) : null,
                    'location' => $u->location ?? 'Banyumas, Indonesia',
                ],
            ];

            $projectUrls = [];
            $isManual = false;
            $content = is_array($p->content) ? $p->content : (json_decode($p->content, true) ?: []);
            foreach ($content as $block) {
                if (isset($block['type']) && $block['type'] === 'gallery_item') {
                    $isManual = true;
                    break;
                }
            }

            if ($isManual) {
                if ($p->cover_image) {
                    $imgUrl = str_starts_with($p->cover_image, 'http') ? $p->cover_image : asset('storage/' . $p->cover_image);
                    $galleryItems[] = [
                        'id' => 'manual-' . $p->id,
                        'portfolio_id' => $p->id,
                        'url' => $imgUrl,
                        'type' => $this->isVideoUrlLocal($p->cover_image) ? 'video' : 'image',
                        'title' => $p->title,
                        'is_manual' => true,
                        'likes' => is_array($p->likes) ? count($p->likes) : 0,
                        'views' => $p->views_count ?? 0,
                        'comments_count' => is_array($p->comments) ? count($p->comments) : 0,
                        'author' => $authorData,
                        'portfolio' => $portfolioData,
                    ];
                }
            } else {
                if ($p->cover_image && $p->cover_image !== $defaultPlaceholder) {
                    $imgUrl = str_starts_with($p->cover_image, 'http') ? $p->cover_image : asset('storage/' . $p->cover_image);
                    $projectUrls[] = $imgUrl;
                    $galleryItems[] = [
                        'id' => 'cover-' . $p->id,
                        'portfolio_id' => $p->id,
                        'url' => $imgUrl,
                        'type' => $this->isVideoUrlLocal($p->cover_image) ? 'video' : 'image',
                        'title' => $p->title,
                        'is_manual' => false,
                        'likes' => is_array($p->likes) ? count($p->likes) : 0,
                        'views' => $p->views_count ?? 0,
                        'comments_count' => is_array($p->comments) ? count($p->comments) : 0,
                        'author' => $authorData,
                        'portfolio' => $portfolioData,
                    ];
                }

                foreach ($content as $bIdx => $block) {
                    if (!$block) continue;
                    if (isset($block['type'])) {
                        if ($block['type'] === 'image' && !empty($block['file_path'])) {
                            $url = asset('storage/' . $block['file_path']);
                            if (!in_array($url, $projectUrls)) {
                                $projectUrls[] = $url;
                                $galleryItems[] = [
                                    'id' => "block-image-{$p->id}-{$bIdx}",
                                    'portfolio_id' => $p->id,
                                    'url' => $url,
                                    'type' => 'image',
                                    'title' => $p->title,
                                    'is_manual' => false,
                                    'likes' => is_array($p->likes) ? count($p->likes) : 0,
                                    'views' => $p->views_count ?? 0,
                                    'comments_count' => is_array($p->comments) ? count($p->comments) : 0,
                                    'author' => $authorData,
                                    'portfolio' => $portfolioData,
                                ];
                            }
                        } elseif ($block['type'] === 'video_audio' && !empty($block['file_path']) && $this->isVideoPathLocal($block['file_path'])) {
                            $url = asset('storage/' . $block['file_path']);
                            if (!in_array($url, $projectUrls)) {
                                $projectUrls[] = $url;
                                $galleryItems[] = [
                                    'id' => "block-video-{$p->id}-{$bIdx}",
                                    'portfolio_id' => $p->id,
                                    'url' => $url,
                                    'type' => 'video',
                                    'title' => $p->title,
                                    'is_manual' => false,
                                    'likes' => is_array($p->likes) ? count($p->likes) : 0,
                                    'views' => $p->views_count ?? 0,
                                    'comments_count' => is_array($p->comments) ? count($p->comments) : 0,
                                    'author' => $authorData,
                                    'portfolio' => $portfolioData,
                                ];
                            }
                        } elseif ($block['type'] === 'photo_grid' && !empty($block['file_paths']) && is_array($block['file_paths'])) {
                            foreach ($block['file_paths'] as $gIdx => $filePath) {
                                if (empty($filePath)) continue;
                                $url = asset('storage/' . $filePath);
                                if (!in_array($url, $projectUrls)) {
                                    $projectUrls[] = $url;
                                    $galleryItems[] = [
                                        'id' => "block-grid-{$p->id}-{$bIdx}-{$gIdx}",
                                        'portfolio_id' => $p->id,
                                        'url' => $url,
                                        'type' => 'image',
                                        'title' => $p->title,
                                        'is_manual' => false,
                                        'likes' => is_array($p->likes) ? count($p->likes) : 0,
                                        'views' => $p->views_count ?? 0,
                                        'comments_count' => is_array($p->comments) ? count($p->comments) : 0,
                                        'author' => $authorData,
                                        'portfolio' => $portfolioData,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        return [
            'galleryItems' => $galleryItems,
            'nextPageUrl' => $paginator->nextPageUrl(),
            'currentPage' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'filters' => [
                'search' => $search,
                'sort' => $sort,
            ],
        ];
    }

    // --- Private Helper Formatting Methods ---

    private function isVideoUrlLocal($url)
    {
        if (empty($url)) return false;
        $cleanUrl = explode('?', $url)[0];
        $ext = strtolower(pathinfo($cleanUrl, PATHINFO_EXTENSION));
        return in_array($ext, ['mp4', 'webm', 'mov', 'avi', 'mkv', '3gp']);
    }

    private function isVideoPathLocal($path)
    {
        if (empty($path)) return false;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($ext, ['mp4', 'webm', 'mov', 'avi', 'mkv', '3gp']);
    }

    private function formatPortfolioContent($content)
    {
        if (empty($content)) return [];
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($content) ? $content : [];
    }

    private function formatComments($comments)
    {
        if (empty($comments)) return [];
        $list = is_string($comments) ? json_decode($comments, true) : $comments;
        if (!is_array($list)) return [];
        return array_map(function ($c) {
            return [
                'id' => $c['id'] ?? uniqid(),
                'user_id' => $c['user_id'] ?? null,
                'name' => $c['name'] ?? 'Anonymous',
                'avatar' => $c['avatar'] ?? null,
                'content' => $c['content'] ?? '',
                'created_at' => $c['created_at'] ?? now()->toISOString(),
                'likes' => $c['likes'] ?? [],
                'replies' => isset($c['replies']) ? array_map(function ($r) {
                    return [
                        'id' => $r['id'] ?? uniqid(),
                        'user_id' => $r['user_id'] ?? null,
                        'name' => $r['name'] ?? 'Anonymous',
                        'avatar' => $r['avatar'] ?? null,
                        'content' => $r['content'] ?? '',
                        'created_at' => $r['created_at'] ?? now()->toISOString(),
                        'likes' => $r['likes'] ?? [],
                    ];
                }, $c['replies']) : [],
            ];
        }, $list);
    }

    private function resolveCollaborators($portfolio)
    {
        if (!$portfolio || !$portfolio->content) return [];
        $content = is_string($portfolio->content) ? json_decode($portfolio->content, true) : $portfolio->content;
        if (!is_array($content)) return [];
        foreach ($content as $block) {
            if ($block && isset($block['type']) && $block['type'] === 'featured_details') {
                $collaborators = $block['data']['collaborators'] ?? $block['collaborators'] ?? [];
                if (!empty($collaborators)) {
                    $names = is_array($collaborators) ? $collaborators : array_map('trim', explode(',', $collaborators));
                    return User::whereIn('name', $names)
                        ->select(['id', 'name', 'pagi_username', 'foto_path'])
                        ->get()
                        ->map(fn($u) => [
                            'id' => $u->id,
                            'name' => $u->name,
                            'pagi_username' => $u->pagi_username,
                            'avatar' => $u->foto_path ? (str_starts_with($u->foto_path, 'http') ? $u->foto_path : asset('storage/' . $u->foto_path)) : null,
                        ])->toArray();
                }
            }
        }
        return [];
    }
}
