<?php

namespace App\Modules\Pagi\Services;

use App\Models\User;
use App\Models\PagiWork;
use App\Concerns\HandlesImageCompression;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PagiProfileService
{
    use HandlesImageCompression;

    /**
     * Get profile data formatted for frontend.
     */
    public function getProfileData(User $user, $requestPagiModuleId = 1, bool $isOwner = true): array
    {
        if (!$isOwner) {
            return \Illuminate\Support\Facades\Cache::remember(
                "pagi_public_profile_{$user->id}",
                600,
                fn() => $this->fetchProfileData($user, $requestPagiModuleId, false)
            );
        }

        return $this->fetchProfileData($user, $requestPagiModuleId, true);
    }

    /**
     * Fetch raw profile data formatted for frontend.
     */
    private function fetchProfileData(User $user, $requestPagiModuleId = 1, bool $isOwner = true): array
    {
        $projectOrder = $user->metadata['pagi_project_order'] ?? null;
        $projectsQuery = PagiWork::with(['tags', 'user'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('content', 'like', '%' . $user->name . '%');
            });

        if (!$isOwner) {
            $projectsQuery->where('is_published', true)
                ->where(function ($q) {
                    $q->whereNull('visibility')
                      ->orWhere('visibility', 'Everyone');
                });
        }

        $projectsCollection = $projectsQuery->get();
        if (is_array($projectOrder)) {
            $orderMap = array_flip($projectOrder);
            $projectsCollection = $projectsCollection->sortBy(function ($p) use ($orderMap) {
                return $orderMap[$p->id] ?? (-$p->id);
            })->values();
        } else {
            $projectsCollection = $projectsCollection->sortByDesc('created_at')->values();
        }

        $projects = $projectsCollection->map(function ($p) use ($user) {
            $creator = $p->user ?? $user;
            return [
                'id' => $p->id,
                'user_id' => $p->user_id,
                'title' => $p->title ?? 'Untitled Project',
                'image' => $p->cover_image ? (str_starts_with($p->cover_image, 'http') ? $p->cover_image : asset('storage/' . $p->cover_image)) : 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop',
                'content' => $this->formatPortfolioContent($p->content),
                'created_at' => $p->created_at->format('F jS Y'),
                'likes' => count($p->likes ?? []),
                'liked' => auth()->check() ? in_array(auth()->id(), $p->likes ?? []) : false,
                'comments' => $this->formatComments($p->comments ?? []),
                'views' => $p->views_count ?? 0,
                'is_published' => (bool)$p->is_published,
                'tools_used' => $p->tools_used,
                'description' => $p->description,
                'category' => $p->category,
                'tags' => $p->tags->map(fn($t) => $t->name)->toArray(),
                'resolved_collaborators' => $this->resolveCollaborators($p),
                'user' => [
                    'id' => $creator->id,
                    'name' => $creator->name,
                    'pagi_username' => $creator->pagi_username,
                    'avatar' => $creator->foto_path ? (str_starts_with($creator->foto_path, 'http') ? $creator->foto_path : asset('storage/' . $creator->foto_path)) : null,
                    'location' => $creator->location ?? 'Banyumas, Indonesia',
                ],
            ];
        })->toArray();

        $pagiRoles = DB::table('user_module_roles')
            ->join('roles', 'user_module_roles.role_id', '=', 'roles.id')
            ->where('user_module_roles.user_id', $user->id)
            ->where('user_module_roles.module_id', $requestPagiModuleId)
            ->where('user_module_roles.is_active', true)
            ->select('roles.nama', 'roles.slug')
            ->get();

        $pagiRoleLabel = null;
        if ($pagiRoles->isNotEmpty()) {
            $nonAdmin = $pagiRoles->first(fn($r) => !in_array($r->slug, ['super-admin', 'admin']));
            $chosen = $nonAdmin ?? $pagiRoles->first();
            $pagiRoleLabel = $chosen?->nama;
        }

        $profileUser = [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'pagi_username' => $user->pagi_username,
            'role_title'    => $user->role_title,
            'pagi_role'     => $pagiRoleLabel,
            'user_type'     => $user->user_type,
            'bio'           => $user->bio,
            'location'      => $user->location,
            'website'       => $user->website,
            'twitter'       => $user->twitter,
            'linkedin'      => $user->linkedin,
            'github'        => $user->github,
            'instagram'     => $user->instagram,
            'foto_path'     => $user->foto_path,
            'banner_path'   => $user->banner_path,
            'tanggal_lahir' => $user->tanggal_lahir ? $user->tanggal_lahir->format('Y-m-d') : null,
            'skills'        => $user->metadata['skills'] ?? ['Figma', 'UI/UX Design', 'Vue.js'],
            'timezone'      => $user->metadata['timezone'] ?? null,
            'timezone_extended' => $user->metadata['timezone_extended'] ?? null,
            'languages'     => $user->metadata['languages'] ?? [],
            'followers_count' => count($user->metadata['followers'] ?? []),
            'following_count' => count($user->metadata['following'] ?? []),
            'certificates' => $this->resolveCertificateLogos(
                array_key_exists('certificates', $user->metadata ?? [])
                    ? $user->metadata['certificates']
                    : [
                        ['id' => 1, 'title' => 'Google UX Design Professional Certificate', 'issuer' => 'Coursera', 'date' => 'Januari 2026', 'credentialId' => 'G-18A8B2C3'],
                        ['id' => 2, 'title' => 'Figma UI/UX Advanced Design Course', 'issuer' => 'FMIKOM Academy', 'date' => 'Desember 2025', 'credentialId' => 'FM-882143']
                    ]
            ),
        ];

        return [
            'profileUser' => $profileUser,
            'projects' => $projects,
        ];
    }

    /**
     * Update profile details.
     */
    public function updateProfile(User $user, array $validatedData, Request $request): void
    {
        if (isset($validatedData['pagi_username'])) {
            $oldUsername = $user->pagi_username;
            $newUsername = $validatedData['pagi_username'];
            if ($newUsername !== $oldUsername) {
                $metadata = $user->metadata ?? [];
                if (!empty($oldUsername)) {
                    $changesCount = $metadata['username_changes_count'] ?? 0;
                    $metadata['username_changes_count'] = $changesCount + 1;
                } else {
                    $metadata['username_changes_count'] = 0;
                }
                $metadata['last_username_changed_at'] = now()->toIso8601String();
                $user->metadata = $metadata;
            }
        }

        $textFields = ['name', 'role_title', 'bio', 'location', 'website', 'twitter', 'linkedin', 'github', 'instagram', 'tanggal_lahir', 'pagi_username'];
        $dataToUpdate = [];
        foreach ($textFields as $field) {
            if ($request->has($field)) {
                if ($field === 'name') {
                    $val = $validatedData[$field] ?? null;
                    if (!empty($val)) {
                        $dataToUpdate[$field] = $val;
                    }
                } elseif ($field === 'pagi_username') {
                    $val = $validatedData[$field] ?? null;
                    $dataToUpdate[$field] = $val ? strtolower(trim($val)) : null;
                } else {
                    $dataToUpdate[$field] = $validatedData[$field] ?? null;
                }
            }
        }
        if (!empty($dataToUpdate)) {
            $user->fill($dataToUpdate);
        }

        $metaFields = ['is_message_enabled', 'skills', 'timezone', 'timezone_extended', 'languages'];
        $metadata = $user->metadata ?? [];
        $metadataChanged = false;
        foreach ($metaFields as $field) {
            if ($request->has($field)) {
                if ($field === 'is_message_enabled') {
                    $metadata['is_message_enabled'] = $request->boolean('is_message_enabled');
                } else {
                    $metadata[$field] = $validatedData[$field] ?? null;
                }
                $metadataChanged = true;
            }
        }
        if ($metadataChanged) {
            $user->metadata = $metadata;
        }

        if ($request->hasFile('banner')) {
            $path = $this->compressAndSaveBannerOrVideo($request->file('banner'), 'pagi/banners');
            if ($path) {
                if ($user->banner_path) {
                    $oldBannerPath = storage_path('app/public/' . $user->banner_path);
                    if (file_exists($oldBannerPath)) {
                        @unlink($oldBannerPath);
                    }
                }
                $user->banner_path = $path;
            }
        }

        if ($request->hasFile('foto')) {
            $path = $this->compressAndSaveImage($request->file('foto'), 'profile_photos', 400, 400, 80);
            if ($path) {
                if ($user->foto_path && !str_starts_with($user->foto_path, 'http')) {
                    $oldPath = storage_path('app/public/' . $user->foto_path);
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                $user->foto_path = $path;
            }
        } elseif ($request->has('avatar_url') && !empty($validatedData['avatar_url'])) {
            if ($user->foto_path && !str_starts_with($user->foto_path, 'http')) {
                $oldPath = storage_path('app/public/' . $user->foto_path);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $user->foto_path = $validatedData['avatar_url'];
        } elseif ($request->boolean('remove_foto')) {
            if ($user->foto_path) {
                if (!str_starts_with($user->foto_path, 'http')) {
                    $oldPath = storage_path('app/public/' . $user->foto_path);
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
            }
            $user->foto_path = null;
        }

        $user->save();
    }

    /**
     * Check username availability.
     */
    public function checkUsername(string $username, int $currentUserId): array
    {
        $exists = User::where('pagi_username', $username)
            ->where('id', '!=', $currentUserId)
            ->exists();

        if (!$exists) {
            return ['available' => true];
        }

        $suggestions = [];
        $bases = [
            $username . rand(10, 99),
            $username . '_' . rand(1, 9),
            str_replace(['.', '_'], '', $username) . rand(100, 999)
        ];
        foreach ($bases as $sugg) {
            if (!User::where('pagi_username', $sugg)->exists()) {
                $suggestions[] = $sugg;
            }
        }
        if (count($suggestions) < 3) {
            $suggestions[] = $username . rand(100, 999);
        }

        return [
            'available' => false,
            'error' => 'Username ini sudah digunakan.',
            'suggestions' => array_values(array_unique($suggestions))
        ];
    }

    /**
     * Reorder projects.
     */
    public function reorderProjects(User $user, array $order): void
    {
        $metadata = $user->metadata ?? [];
        $metadata['pagi_project_order'] = $order;
        $user->metadata = $metadata;
        $user->save();
    }

    // --- Private Helper Formatting Methods from ModuleDashboardController ---

    public function formatPortfolioContent($content)
    {
        if (empty($content)) return [];
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($content) ? $content : [];
    }

    public function formatComments($comments)
    {
        if (empty($comments)) return [];
        $list = is_string($comments) ? json_decode($comments, true) : $comments;
        if (!is_array($list)) return [];
        return array_map(function($c) {
            return [
                'id' => $c['id'] ?? uniqid(),
                'user_id' => $c['user_id'] ?? null,
                'name' => $c['name'] ?? 'Anonymous',
                'avatar' => $c['avatar'] ?? null,
                'content' => $c['content'] ?? '',
                'created_at' => $c['created_at'] ?? now()->toISOString(),
                'likes' => $c['likes'] ?? [],
                'replies' => isset($c['replies']) ? array_map(function($r) {
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

    public function resolveCollaborators($portfolio)
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

    private function resolveCertificateLogos($certificates)
    {
        if (!is_array($certificates)) return [];
        return array_map(function($cert) {
            $issuer = strtolower($cert['issuer'] ?? '');
            $logo = null;
            if (str_contains($issuer, 'google')) {
                $logo = 'https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_color_logo_Google_2015.svg';
            } elseif (str_contains($issuer, 'dicoding')) {
                $logo = 'https://images.credential.net/embed/logo/5a420b922d9c0250df7b9bb652b0a3c9e6bb07d0f98e72782b1c8f1e6b359f13.png';
            } elseif (str_contains($issuer, 'hacker')) {
                $logo = 'https://upload.wikimedia.org/wikipedia/commons/4/40/HackerRank_Icon-1000px.png';
            } elseif (str_contains($issuer, 'coursera')) {
                $logo = 'https://upload.wikimedia.org/wikipedia/commons/9/97/Coursera-Logo_600x600.svg';
            } else {
                $logo = asset('premium.svg');
            }
            $cert['logo'] = $logo;
            return $cert;
        }, $certificates);
    }
}
