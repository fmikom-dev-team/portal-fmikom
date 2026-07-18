<?php

namespace App\Modules\Pagi\Services;

use App\Concerns\HandlesImageCompression;
use App\Models\Pagi\PagiWork;
use App\Models\User;
use App\Modules\Pagi\Concerns\FormatsPortfolioData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PagiProfileService
{
    use FormatsPortfolioData;
    use HandlesImageCompression;

    /**
     * Get profile data formatted for frontend.
     * Public profiles are cached for 10 minutes to avoid repeated heavy queries.
     */
    public function getProfileData(User $user, $requestPagiModuleId = 1, bool $isOwner = true): array
    {
        if (! $isOwner) {
            return Cache::remember(
                "pagi_public_profile_{$user->id}",
                600,
                fn () => $this->fetchProfileData($user, $requestPagiModuleId, false)
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

        // ── FIX #1: Removed `orWhere('content', 'like', '%name%')` full-table scan.
        // Collaborators are resolved separately via preloaded user map—no DB scan needed.
        $projectsQuery = PagiWork::with([
            'tags',
            'user:id,name,pagi_username,foto_path,location',
            // Preload likes & comments to prevent N+1 inside ->map()
            'likesRelation',
            'commentsRelation' => fn ($q) => $q->whereNull('parent_id'),
            'commentsRelation.user:id,name,pagi_username,foto_path',
            'commentsRelation.likesRelation',
            'commentsRelation.replies.user:id,name,pagi_username,foto_path',
            'commentsRelation.replies.likesRelation',
        ])->where('user_id', $user->id);

        if (! $isOwner) {
            $projectsQuery->where('is_published', true)
                ->where(function ($q) {
                    $q->whereNull('visibility')
                        ->orWhere('visibility', 'Everyone');
                });
        }

        // ── FIX #2: Added limit to prevent loading hundreds of works into memory
        $projectsCollection = $projectsQuery->latest()->limit(60)->get();

        if (is_array($projectOrder)) {
            $orderMap = array_flip($projectOrder);
            $projectsCollection = $projectsCollection->sortBy(function ($p) use ($orderMap) {
                return $orderMap[$p->id] ?? (-$p->id);
            })->values();
        } else {
            $projectsCollection = $projectsCollection->sortByDesc('created_at')->values();
        }

        // Preload collaborator user data in one batch query (avoid N+1 in resolveCollaborators)
        $names = $this->extractCollaboratorNames($projectsCollection);
        $preloadedUsers = [];
        if (! empty($names)) {
            $preloadedUsers = User::query()->whereIn('name', $names, 'and', false)
                ->select(['id', 'name', 'pagi_username', 'foto_path'])
                ->get()
                ->keyBy('name')
                ->all();
        }

        $projects = $projectsCollection->map(function ($p) use ($user, $preloadedUsers) {
            $creator = $p->user ?? $user;

            $image = 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop';
            if ($p->cover_image) {
                $image = str_starts_with($p->cover_image, 'http')
                    ? $p->cover_image
                    : asset('storage/'.$p->cover_image);
            }

            $avatar = null;
            if ($creator->foto_path) {
                $avatar = str_starts_with($creator->foto_path, 'http')
                    ? $creator->foto_path
                    : asset('storage/'.$creator->foto_path);
            }

            // $p->likes and $p->comments use the cached relations (no extra queries)
            return [
                'id' => $p->id,
                'user_id' => $p->user_id,
                'title' => $p->title ?? 'Untitled Project',
                'image' => $image,
                'content' => $this->formatPortfolioContent($p->content),
                'created_at' => $p->created_at->format('F jS Y'),
                'likes' => count($p->likes),
                'liked' => Auth::check() ? in_array(Auth::id(), $p->likes) : false,
                'comments' => $this->formatComments($p->comments),
                'views' => $p->views_count ?? 0,
                'is_published' => (bool) $p->is_published,
                'tools_used' => $p->tools_used,
                'description' => $p->description,
                'category' => $p->category,
                'tags' => $p->tags->map(fn ($t) => $t->name)->toArray(),
                'resolved_collaborators' => $this->resolveCollaborators($p, $preloadedUsers),
                'user' => [
                    'id' => $creator->id,
                    'name' => $this->formatName($creator->name),
                    'pagi_username' => $creator->pagi_username,
                    'avatar' => $avatar,
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
            $nonAdmin = $pagiRoles->first(fn ($r) => ! in_array($r->slug, ['super-admin', 'admin']));
            $chosen = $nonAdmin ?? $pagiRoles->first();
            $pagiRoleLabel = $chosen?->nama;
        }

        $profileUser = [
            'id' => $user->id,
            'name' => $this->formatName($user->name),
            'email' => $user->email,
            'pagi_username' => $user->pagi_username,
            'role_title' => $user->role_title,
            'pagi_role' => $pagiRoleLabel,
            'user_type' => $user->user_type,
            'bio' => $user->bio,
            'location' => $user->location,
            'website' => $user->website,
            'twitter' => $user->twitter,
            'linkedin' => $user->linkedin,
            'github' => $user->github,
            'instagram' => $user->instagram,
            'foto_path' => $user->foto_path,
            'banner_path' => $user->banner_path,
            'tanggal_lahir' => $user->tanggal_lahir ? $user->tanggal_lahir->format('Y-m-d') : null,
            'skills' => $user->metadata['skills'] ?? ['Figma', 'UI/UX Design', 'Vue.js'],
            'timezone' => $user->metadata['timezone'] ?? null,
            'timezone_extended' => $user->metadata['timezone_extended'] ?? null,
            'languages' => $user->metadata['languages'] ?? [],
            // ── FIX #3: Use single COUNT query from DB instead of reading JSON metadata
            'followers_count' => $user->pagiFollowers()->count(),
            'following_count' => $user->pagiFollowing()->count(),
            'followed_by_user' => ($firstFollower = $user->pagiFollowers()->first()) ? [
                'id' => $firstFollower->id,
                'name' => $this->formatName($firstFollower->name),
                'pagi_username' => $firstFollower->pagi_username,
                'foto_path' => $this->resolveAssetPath($firstFollower->foto_path),
            ] : null,
            'certificates' => $this->resolveCertificateLogos(
                array_key_exists('certificates', $user->metadata ?? [])
                    ? $user->metadata['certificates']
                    : [
                        ['id' => 1, 'title' => 'Google UX Design Professional Certificate', 'issuer' => 'Coursera', 'date' => 'Januari 2026', 'credentialId' => 'G-18A8B2C3'],
                        ['id' => 2, 'title' => 'Figma UI/UX Advanced Design Course', 'issuer' => 'FMIKOM Academy', 'date' => 'Desember 2025', 'credentialId' => 'FM-882143'],
                    ]
            ),
            'educations' => array_key_exists('educations', $user->metadata ?? [])
                ? $user->metadata['educations']
                : [
                    ['id' => 1, 'level' => 'S1', 'institution' => 'Fakultas Ilmu Komputer, Universitas Amikom Purwokerto', 'major' => 'Teknologi Informasi', 'start_date' => '2022', 'end_date' => 'Present', 'description' => 'IPK 3.85. Aktif dalam organisasi mahasiswa dan riset AI.'],
                ],
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
                if (! empty($oldUsername)) {
                    $changesCount = $metadata['username_changes_count'] ?? 0;
                    $metadata['username_changes_count'] = $changesCount + 1;
                } else {
                    $metadata['username_changes_count'] = 0;
                }
                $metadata['last_username_changed_at'] = now()->toIso8601String();
                $user->metadata = $metadata;
            }
        }

        $textFields = ['role_title', 'bio', 'location', 'website', 'twitter', 'linkedin', 'github', 'instagram', 'tanggal_lahir', 'pagi_username'];
        $dataToUpdate = [];
        foreach ($textFields as $field) {
            if ($request->has($field)) {
                if ($field === 'pagi_username') {
                    $val = $validatedData[$field] ?? null;
                    $dataToUpdate[$field] = $val ? strip_tags(strtolower(trim($val))) : null;
                } else {
                    $val = $validatedData[$field] ?? null;
                    $dataToUpdate[$field] = is_string($val) ? strip_tags($val) : $val;
                }
            }
        }
        if (! empty($dataToUpdate)) {
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
                    $metadata[$field] = $this->sanitizeInputRecursive($validatedData[$field] ?? null);
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
                if ($user->banner_path && file_exists(storage_path('app/public/'.$user->banner_path))) {
                    @unlink(storage_path('app/public/'.$user->banner_path));
                }
                $user->banner_path = $path;
            }
        }

        if ($request->hasFile('foto')) {
            $path = $this->compressAndSaveImage($request->file('foto'), 'profile_photos', 400, 400, 80);
            if ($path) {
                if ($user->foto_path && ! str_starts_with($user->foto_path, 'http') && file_exists(storage_path('app/public/'.$user->foto_path))) {
                    @unlink(storage_path('app/public/'.$user->foto_path));
                }
                $user->foto_path = $path;
            }
        } elseif ($request->has('avatar_url') && ! empty($validatedData['avatar_url'])) {
            if ($user->foto_path && ! str_starts_with($user->foto_path, 'http') && file_exists(storage_path('app/public/'.$user->foto_path))) {
                @unlink(storage_path('app/public/'.$user->foto_path));
            }
            $user->foto_path = $validatedData['avatar_url'];
        } elseif ($request->boolean('remove_foto')) {
            if ($user->foto_path && ! str_starts_with($user->foto_path, 'http') && file_exists(storage_path('app/public/'.$user->foto_path))) {
                @unlink(storage_path('app/public/'.$user->foto_path));
            }
            $user->foto_path = null;
        }

        $user->save();
    }

    /**
     * Check username availability.
     * ── FIX #4: Use a single whereIn query for suggestions instead of N separate exists() calls.
     */
    public function checkUsername(string $username, int $currentUserId): array
    {
        $exists = User::query()->where('pagi_username', $username)
            ->where('id', '!=', $currentUserId)
            ->exists();

        if (! $exists) {
            return ['available' => true];
        }

        // Generate 3 candidate suggestions
        $candidates = [
            $username.rand(10, 99),
            $username.'_'.rand(1, 9),
            str_replace(['.', '_'], '', $username).rand(100, 999),
            $username.rand(100, 999),
        ];

        // One query to check which candidates are already taken
        $taken = User::query()->whereIn('pagi_username', $candidates, 'and', false)
            ->pluck('pagi_username')
            ->flip()
            ->all();

        $suggestions = array_values(array_filter($candidates, fn ($c) => ! isset($taken[$c])));

        return [
            'available' => false,
            'error' => 'Username ini sudah digunakan.',
            'suggestions' => array_values(array_unique(array_slice($suggestions, 0, 3))),
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

    // ──────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function resolveCertificateLogos($certificates): array
    {
        if (! is_array($certificates)) {
            return [];
        }

        return array_map(function ($cert) {
            $issuer = strtolower($cert['issuer'] ?? '');
            if (str_contains($issuer, 'google')) {
                $logo = asset('images/partners/google.svg');
            } elseif (str_contains($issuer, 'dicoding')) {
                $logo = asset('images/issuers/dicoding.png');
            } elseif (str_contains($issuer, 'hacker')) {
                $logo = asset('images/issuers/hackerrank.png');
            } elseif (str_contains($issuer, 'coursera')) {
                $logo = asset('images/issuers/coursera.svg');
            } else {
                $logo = asset('premium.svg');
            }
            $cert['logo'] = $logo;

            return $cert;
        }, $certificates);
    }

    private function sanitizeInputRecursive(mixed $value): mixed
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->sanitizeInputRecursive($val);
            }

            return $value;
        }

        if (is_string($value)) {
            return strip_tags($value);
        }

        return $value;
    }
}
