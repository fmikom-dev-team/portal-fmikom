<?php

namespace App\Modules\Pagi\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Pagi\PagiFollow;
use App\Models\Pagi\PagiReport;
use App\Models\Pagi\PagiWork;
use App\Models\ProgramStudi;
use App\Models\User;
use App\Modules\Pagi\Actions\CreateCommentAction;
use App\Modules\Pagi\Actions\FollowUserAction;
use App\Modules\Pagi\Actions\LikeCommentAction;
use App\Modules\Pagi\Actions\LikeReplyAction;
use App\Modules\Pagi\Actions\LikeWorkAction;
use App\Modules\Pagi\Actions\ReplyCommentAction;
use App\Modules\Pagi\Requests\StoreCertificateRequest;
use App\Modules\Pagi\Requests\UpdateCertificateRequest;
use App\Modules\Pagi\Requests\UpdateProfileRequest;
use App\Modules\Pagi\Requests\UpdateSettingsRequest;
use App\Modules\Pagi\Services\PagiCertificateService;
use App\Modules\Pagi\Services\PagiNotificationService;
use App\Modules\Pagi\Services\PagiProfileService;
use App\Modules\Pagi\Services\PagiSocialService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PagiDashboardController extends Controller implements HasMiddleware
{
    private const ADMIN_ROLES = ['super-admin', 'admin', 'admin-universitas', 'admin-akademik', 'prodi'];

    private const USERNAME_BLACKLIST = [
        'explore', 'jobs', 'messages', 'notifications', 'portfolio', 'profile',
        'admin', 'users', 'username', 'settings', 'chat', 'auth', 'api',
        'dashboard', 'seed-dummy', 'invitations', 'images', 'berita', 'halaman', 'people',
    ];

    private const DEFAULT_PROJECT_TITLE = 'Untitled Project';

    private const DEFAULT_COVER_IMAGE = 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop';

    private const DEFAULT_LOCATION = 'Banyumas, Indonesia';

    public static function middleware(): array
    {
        return [
            new Middleware(function ($request, $next) {
                $role = $request->attributes->get('resolved_role', session('active_role'));
                $action = $request->route()->getActionMethod();
                $studentOnlyActions = [
                    'storeCertificate', 'updateCertificate',
                    'destroyCertificate', 'reorderProjects', 'uploadOrgLogo',
                ];
                if (in_array($action, $studentOnlyActions) && strtolower($role) !== 'mahasiswa') {
                    if ($request->wantsJson()) {
                        return response()->json(['message' => 'Akses ditolak: Fitur ini hanya tersedia untuk Mahasiswa.'], 403);
                    }

                    return redirect()->route('module.pagi.dashboard')
                        ->with('error', 'Akses ditolak: Fitur ini hanya tersedia untuk Mahasiswa.');
                }

                return $next($request);
            }),
        ];
    }

    public function __construct(
        protected PagiProfileService $profileService,
        protected PagiSocialService $socialService,
        protected PagiNotificationService $notificationService,
        protected PagiCertificateService $certificateService,
        protected FollowUserAction $followUserAction,
        protected LikeWorkAction $likeWorkAction,
        protected CreateCommentAction $createCommentAction,
        protected ReplyCommentAction $replyCommentAction,
        protected LikeCommentAction $likeCommentAction,
        protected LikeReplyAction $likeReplyAction
    ) {}

    public function index(Request $request)
    {
        $role = $request->attributes->get('resolved_role', session('active_role'));

        if ($this->isAdminRole($role)) {
            return redirect()->route('module.pagi.admin.dashboard');
        }

        $componentName = $this->resolveDashboardComponentName($role, 'Modules/Pagi/User/MahasiswaDashboard');

        return Inertia::render($componentName, [
            'moduleName' => 'PAGI',
            'roleName' => $role,
            'peopleYouMayKnow' => Inertia::defer(fn () => $this->loadPeopleRecommendation()),
            'feedProjects' => Inertia::defer(fn () => $this->loadFeed()),
            'followingFeedProjects' => Inertia::defer(fn () => $this->loadFollowingFeed()),
            'stats' => Inertia::defer(fn () => $this->buildVisitorStats($role)),
        ]);
    }

    private function isAdminRole(string $role): bool
    {
        return in_array($role, self::ADMIN_ROLES);
    }

    private function buildVisitorStats(string $role): array
    {
        $visitorRoles = ['dosen', 'alumni', 'mitra'];
        if (! in_array(strtolower($role), $visitorRoles)) {
            return [];
        }

        return [
            'total_portfolios' => PagiWork::query()->where('is_published', true)->count('*'),
            'total_creators' => User::query()->where('user_type', 'mahasiswa')->where('is_active', true)->count('*'),
            'total_views' => PagiWork::query()->sum('views_count') ?? 0,
        ];
    }

    private function loadPeopleRecommendation(): Collection
    {
        $module = Module::query()->where('code', 'PAGI')->first();

        return $module
            ? $this->socialService->getPeopleYouMayKnow($module->id, Auth::id())
            : collect();
    }

    private function getPreloadedFeedData(Collection $works): array
    {
        $reportedWorkIds = Auth::check()
            ? PagiReport::query()->where('reporter_id', Auth::id())
                ->where('status', 'pending')
                ->whereIn('work_id', $works->pluck('id')->toArray(), 'and', false)
                ->pluck('work_id')
                ->flip()
            : collect();

        $names = $this->profileService->extractCollaboratorNames($works);
        $preloadedUsers = [];
        if (! empty($names)) {
            $preloadedUsers = User::query()->whereIn('name', $names, 'and', false)
                ->select(['id', 'name', 'pagi_username', 'foto_path'])
                ->get()
                ->keyBy('name')
                ->all();
        }

        return [$reportedWorkIds, $preloadedUsers];
    }

    private function loadFeed(): Collection
    {
        $feedProjects = Cache::remember('pagi_feed_projects_raw', 60, function () {
            return PagiWork::query()->with([
                'user:id,name,pagi_username,foto_path,location',
                'tags',
                'likesRelation',
            ])
                ->where('is_published', true)
                ->where(function ($q) {
                    $q->whereNull('visibility')
                        ->orWhere('visibility', 'Everyone');
                })
                ->latest()
                ->limit(20)
                ->get();
        });

        [$reportedWorkIds, $preloadedUsers] = $this->getPreloadedFeedData($feedProjects);

        return $this->formatFeedProjects($feedProjects, $reportedWorkIds, $preloadedUsers);
    }

    private function loadFollowingFeed(): Collection
    {
        if (! Auth::check()) {
            return collect();
        }

        $user = Auth::user();
        $followingIds = Cache::remember("pagi_user_following_{$user->id}", 60, function () use ($user) {
            return PagiFollow::query()->where('follower_id', $user->id)
                ->pluck('following_id');
        });

        if ($followingIds->isEmpty()) {
            return collect();
        }

        $followingWorks = Cache::remember("pagi_following_feed_raw_{$user->id}", 60, function () use ($followingIds) {
            return PagiWork::query()->with([
                'user:id,name,pagi_username,foto_path,location',
                'tags',
                'likesRelation',
            ])
                ->whereIn('user_id', $followingIds)
                ->where('is_published', true)
                ->where(function ($q) {
                    $q->whereNull('visibility')
                        ->orWhere('visibility', 'Everyone');
                })
                ->latest()
                ->limit(40)
                ->get();
        });

        [$reportedWorkIds, $preloadedUsers] = $this->getPreloadedFeedData($followingWorks);

        return $this->formatFeedProjects($followingWorks, $reportedWorkIds, $preloadedUsers);
    }

    private function resolveDashboardComponentName(string $role, string $defaultFallback): string
    {
        $visitorRoles = ['dosen', 'alumni', 'mitra'];
        if (in_array(strtolower($role), $visitorRoles)) {
            $componentName = 'Modules/Pagi/User/Umum/Dashboard';
        } else {
            $componentName = 'Modules/Pagi/User/'.Str::studly($role).'Dashboard';
        }

        $path = resource_path("js/pages/{$componentName}.vue");
        if (! file_exists($path)) {
            if (file_exists(resource_path("js/pages/{$defaultFallback}.vue"))) {
                $componentName = $defaultFallback;
            } else {
                abort(404, "Dashboard Template untuk Role '{$role}' belum dibuat di {$componentName}.vue");
            }
        }

        return $componentName;
    }

    public function explorePeople(Request $request)
    {
        $role = $request->attributes->get('resolved_role', session('active_role'));
        $module = Module::query()->where('code', 'PAGI')->first();

        return Inertia::render('Modules/Pagi/User/People', [
            'moduleName' => 'PAGI',
            'roleName' => $role,
            'peopleYouMayKnow' => Inertia::defer(fn () => $module ? $this->socialService->explorePeople($module->id) : collect()),
        ]);
    }

    public function messages(Request $request)
    {
        return Inertia::render('Modules/Pagi/User/Messages', [
            'moduleName' => 'PAGI',
            'roleName' => $request->attributes->get('resolved_role', session('active_role')),
        ]);
    }

    public function toggleFollow(Request $request, int $targetUserId)
    {
        try {
            $result = $this->followUserAction->execute(Auth::user(), $targetUserId);

            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function getFollowRelations(Request $request, User $user)
    {
        return response()->json($this->socialService->getFollowRelations($user));
    }

    public function notifications(Request $request)
    {
        $role = $request->attributes->get('resolved_role', session('active_role'));
        $data = $this->notificationService->getUserNotifications(Auth::user());

        return Inertia::render('Modules/Pagi/User/Notifications', [
            'moduleName' => 'PAGI',
            'roleName' => $role,
            'notifGroups' => $data['groups'],
            'unreadCount' => $data['unreadCount'],
        ]);
    }

    public function markNotificationRead(Request $request, string $id)
    {
        $this->notificationService->markAsRead(Auth::user(), $id);

        return response()->json(['ok' => true]);
    }

    public function markAllNotificationsRead(Request $request)
    {
        $this->notificationService->markAllAsRead(Auth::user());

        return response()->json(['ok' => true]);
    }

    public function deleteNotification(Request $request, string $id)
    {
        $this->notificationService->delete(Auth::user(), $id);

        return response()->json(['ok' => true]);
    }

    public function clearAllNotifications(Request $request)
    {
        $this->notificationService->clearAll(Auth::user());

        return response()->json(['ok' => true]);
    }

    public function reorderProjects(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer',
        ]);

        $this->profileService->reorderProjects(Auth::user(), $request->order);

        return redirect()->back();
    }

    public function profile(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->pagi_username) {
            return $this->redirectProfileToUsername($user, $request);
        }

        $role = $request->attributes->get('resolved_role', session('active_role'));
        $data = $this->profileService->getProfileData($user, 1, true);
        $componentName = $this->resolveProfileComponentName($role);

        return Inertia::render($componentName, [
            'moduleName' => 'PAGI',
            'roleName' => $role,
            'profileUser' => $data['profileUser'],
            'projects' => $data['projects'],
        ])->withViewData($this->getProfileViewData($user, $request));
    }

    private function redirectProfileToUsername(User $user, Request $request): RedirectResponse
    {
        return redirect()->route('module.pagi.profile.username', array_merge(['user' => $user->pagi_username], $request->query()));
    }

    private function resolveProfileComponentName(string $role): string
    {
        return in_array(strtolower($role), ['dosen', 'alumni', 'mitra'])
            ? 'Modules/Pagi/User/Umum/Profile'
            : 'Modules/Pagi/User/Profile/Index';
    }

    public function publicProfile(Request $request, User $user, $tab = null)
    {
        $redirect = $this->checkProfileRedirects($request, $user, $tab);
        if ($redirect) {
            return $redirect;
        }

        $viewerRole = $this->resolveViewerRole($request);
        $isOwner = Auth::check() && Auth::id() === $user->id;
        $data = $this->profileService->getProfileData($user, 1, $isOwner);
        $isFollowing = $this->checkIsFollowing($user, $isOwner);

        $selectedTheme = $user->metadata['pagi_work_theme'] ?? null;
        $selectedPalette = $user->metadata['pagi_work_palette_index'] ?? 0;

        $componentName = $this->resolvePublicProfileComponentName($user, $selectedTheme, $request);

        return Inertia::render($componentName, [
            'moduleName' => 'PAGI',
            'roleName' => $viewerRole,
            'profileUser' => $data['profileUser'],
            'projects' => $data['projects'],
            'isFollowing' => $isFollowing,
            'selectedTheme' => $selectedTheme,
            'selectedPalette' => (int) $selectedPalette,
        ])->withViewData($this->getProfileViewData($user, $request));
    }

    private function resolveViewerRole(Request $request): string
    {
        if (Auth::check()) {
            return $request->attributes->get('resolved_role', session('active_role')) ?? 'mahasiswa';
        }

        return 'guest';
    }

    private function checkIsFollowing(User $user, bool $isOwner): bool
    {
        if (Auth::check() && ! $isOwner) {
            return PagiFollow::query()->where('follower_id', Auth::id())
                ->where('following_id', $user->id)
                ->exists();
        }

        return false;
    }

    private function resolvePublicProfileComponentName(User $user, ?string $selectedTheme, Request $request): string
    {
        $forceStandard = $request->query('edit') === 'true' || $request->query('preview') === 'false';

        if (! empty($selectedTheme) && ! $forceStandard) {
            return 'Modules/Pagi/User/Works/Show';
        }

        return in_array(strtolower($user->user_type), ['dosen', 'alumni', 'mitra'])
            ? 'Modules/Pagi/User/Umum/Profile'
            : 'Modules/Pagi/User/Profile/Index';
    }

    private function checkProfileRedirects(Request $request, User $user, ?string $tab): ?RedirectResponse
    {
        $tabRedirect = $this->resolveTabRedirect($request, $user, $tab);
        if ($tabRedirect) {
            return $tabRedirect;
        }

        if ($user->pagi_username && $request->route()->getName() === 'module.pagi.profile.public') {
            return redirect()->route('module.pagi.profile.username', array_merge(['user' => $user->pagi_username, 'tab' => $tab], $request->query()));
        }

        return null;
    }

    private function resolveTabRedirect(Request $request, User $user, ?string $tab): ?RedirectResponse
    {
        if ($request->has('tab')) {
            $tabVal = strtolower($request->query('tab'));
            if ($tabVal === 'sertifikat') {
                $tabVal = 'certificates';
            }
            if (in_array($tabVal, ['work', 'gallery', 'certificates', 'about'])) {
                $queryString = $request->except('tab') ? '?'.http_build_query($request->except('tab')) : '';

                return redirect()->to("/pagi/{$user->pagi_username}/{$tabVal}".$queryString);
            }
        }

        if ($tab && strtolower($tab) === 'sertifikat') {
            $queryString = $request->query() ? '?'.http_build_query($request->query()) : '';

            return redirect()->to("/pagi/{$user->pagi_username}/certificates".$queryString);
        }

        if ($tab && preg_match('/[A-Z]/', $tab)) {
            $queryString = $request->query() ? '?'.http_build_query($request->query()) : '';

            return redirect()->to("/pagi/{$user->pagi_username}/".strtolower($tab).$queryString);
        }

        if ($tab && ! in_array(strtolower($tab), ['work', 'gallery', 'certificates', 'about'])) {
            $queryString = $request->query() ? '?'.http_build_query($request->query()) : '';

            return redirect()->to("/pagi/{$user->pagi_username}".$queryString);
        }

        return null;
    }

    public function userWorks(Request $request, User $user)
    {
        $isOwner = Auth::check() && Auth::id() === $user->id;
        $query = PagiWork::query()->with(['tags', 'user'])
            ->where('user_id', $user->id);

        if (! $isOwner) {
            $query->where('is_published', true)
                ->where(function ($q) {
                    $q->whereNull('visibility')
                        ->orWhere('visibility', 'Everyone');
                });
        }

        $projectsCollection = $query->latest()->get();
        $preloadedUsers = $this->preloadCollaboratorsForCollection($projectsCollection);
        $projects = $this->formatUserWorksList($projectsCollection, $user, $preloadedUsers);

        return response()->json(['works' => $projects]);
    }

    private function preloadCollaboratorsForCollection(Collection $works): array
    {
        $names = $this->profileService->extractCollaboratorNames($works);
        if (empty($names)) {
            return [];
        }

        return User::query()->whereIn('name', $names, 'and', false)
            ->select(['id', 'name', 'pagi_username', 'foto_path'])
            ->get()
            ->keyBy('name')
            ->all();
    }

    private function formatUserWorksList(Collection $projectsCollection, User $user, array $preloadedUsers): Collection
    {
        return $projectsCollection->map(fn ($p) => [
            'id' => $p->id,
            'user_id' => $p->user_id,
            'title' => $p->title ?? 'Untitled Project',
            'image' => $this->getStorageUrl($p->cover_image) ?? 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop',
            'content' => $this->profileService->formatPortfolioContent($p->content),
            'created_at' => $p->created_at->format('F jS Y'),
            'likes' => count($p->likes ?? []),
            'liked' => Auth::check() ? in_array(Auth::id(), $p->likes ?? []) : false,
            'comments' => $this->profileService->formatComments($p->comments ?? []),
            'views' => $p->views_count ?? 0,
            'is_published' => (bool) $p->is_published,
            'tools_used' => $p->tools_used,
            'description' => $p->description,
            'category' => $p->category,
            'tags' => $p->tags->map(fn ($t) => $t->name)->toArray(),
            'resolved_collaborators' => $this->profileService->resolveCollaborators($p, $preloadedUsers),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'pagi_username' => $user->pagi_username,
                'avatar' => $this->getStorageUrl($user->foto_path),
                'location' => $user->location ?? 'Banyumas, Indonesia',
            ],
        ]);
    }

    public function editProfile(Request $request)
    {
        return redirect()->route('module.pagi.profile');
    }

    public function checkUsername(Request $request)
    {
        $username = strtolower(trim($request->query('username', '')));

        if (strlen($username) < 3) {
            return response()->json(['available' => false, 'error' => 'Username minimal 3 karakter.', 'suggestions' => []]);
        }

        if (! preg_match('/^[a-z0-9._]+$/', $username)) {
            return response()->json(['available' => false, 'error' => 'Username hanya boleh mengandung huruf kecil, angka, titik (.), dan underscore (_).', 'suggestions' => []]);
        }

        if ($this->isUsernameBlacklisted($username)) {
            return response()->json(['available' => false, 'error' => 'Username ini tidak dapat digunakan karena merupakan kata cadangan sistem.', 'suggestions' => []]);
        }

        $result = $this->profileService->checkUsername($username, Auth::id() ?: 0);

        return response()->json($result);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = Auth::user();

        if ($request->has('pagi_username')) {
            $errorMsg = $this->checkUsernameCooldown($user, $request->input('pagi_username'));
            if ($errorMsg) {
                return back()->withErrors(['pagi_username' => $errorMsg]);
            }
        }

        $this->profileService->updateProfile($user, $request->validated(), $request);

        return redirect()->route('module.pagi.profile')->with('success', 'Profile updated successfully.');
    }

    public function settings(Request $request)
    {
        $user = Auth::user()->load('programStudi.fakultas');
        $role = $request->attributes->get('resolved_role', session('active_role'));

        $profileUser = $this->prepareSettingsData($user);

        $component = in_array(strtolower($role), ['dosen', 'alumni', 'mitra'])
            ? 'Modules/Pagi/User/Umum/Settings'
            : 'Modules/Pagi/User/Settings/Index';

        return Inertia::render($component, [
            'moduleName' => 'PAGI',
            'roleName' => $role,
            'profileUser' => $profileUser,
            'programStudis' => $this->getProgramStudisList(),
        ]);
    }

    private function buildProfileProgress(User $user): int
    {
        $progress = 0;
        if (! empty($user->name)) {
            $progress += 10;
        }
        if (! empty($user->email)) {
            $progress += 10;
        }
        if (! empty($user->foto_path)) {
            $progress += 10;
        }
        if (! empty($user->bio)) {
            $progress += 10;
        }
        if (! empty($user->location) || ! empty($user->metadata['billing_address'])) {
            $progress += 10;
        }
        if (! empty($user->tanggal_lahir)) {
            $progress += 10;
        }
        if (! empty($user->no_telepon)) {
            $progress += 10;
        }
        if (! empty($user->github) || ! empty($user->linkedin) || ! empty($user->instagram)) {
            $progress += 10;
        }
        if (! empty($user->metadata['calendar_link'])) {
            $progress += 10;
        }
        if (! empty($user->pagi_username)) {
            $progress += 10;
        }

        return min(100, $progress);
    }

    private function resolveUserAvatarUrl(User $user): string
    {
        return $user->foto_path
            ? (str_starts_with($user->foto_path, 'http') ? $user->foto_path : '/storage/'.$user->foto_path)
            : 'https://api.dicebear.com/7.x/initials/svg?seed='.urlencode($user->name).'&backgroundColor=3b82f6,6366f1,8b5cf6,ec4899,f43f5e&backgroundType=gradientLinear&bold=true';
    }

    private function prepareSettingsData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'tanggal_lahir' => $user->tanggal_lahir ? $user->tanggal_lahir->format('Y-m-d') : null,
            'calendar_link' => $user->metadata['calendar_link'] ?? null,
            'billing_address' => $user->metadata['billing_address'] ?? null,
            'legal_entity_name' => $user->metadata['legal_entity_name'] ?? null,
            'pagi_username' => $user->pagi_username,
            'progress' => $this->buildProfileProgress($user),
            'bio' => $user->bio ?? 'Through the lens, I create stories worth remembering.',
            'avatar' => $this->resolveUserAvatarUrl($user),
            'works_count' => $user->pagiWorks()->count(),
            'followers_count' => count($user->metadata['followers'] ?? []),
            'nim_nip' => $user->nomor_induk ?? null,
            'program_studi_id' => $user->program_studi_id ?? null,
            'program_studi' => $user->programStudi?->nama ?? null,
            'fakultas' => $user->programStudi?->fakultas?->nama ?? null,

            // Additional Database-backed fields
            'no_telepon' => $user->no_telepon ?? null,
            'location' => $user->location ?? null,
            'website' => $user->website ?? null,
            'twitter' => $user->twitter ?? null,
            'linkedin' => $user->linkedin ?? null,
            'github' => $user->github ?? null,
            'instagram' => $user->instagram ?? null,

            // Tab states stored in metadata
            'subscription' => $user->metadata['subscription'] ?? null,
            'trade_agent' => $user->metadata['trade_agent'] ?? null,
            'brand_branding' => $user->metadata['brand_branding'] ?? null,
            'payment_account' => $user->metadata['payment_account'] ?? null,
            'rewards' => $user->metadata['rewards'] ?? null,
            'email_preferences' => $user->metadata['email_preferences'] ?? null,
        ];
    }

    private function getProgramStudisList(): Collection
    {
        return ProgramStudi::query()->with('fakultas')->get()->map(function ($ps) {
            return [
                'id' => $ps->id,
                'nama' => $ps->nama,
                'fakultas_nama' => $ps->fakultas?->nama,
            ];
        });
    }

    public function updateSettings(UpdateSettingsRequest $request)
    {
        $user = Auth::user();

        // 1. Password update
        if ($request->filled('new_password')) {
            if (! $this->updateUserPassword($user, $request->current_password, $request->new_password)) {
                return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
            }
        }

        // 2. Username update
        if ($request->has('pagi_username')) {
            $errorMsg = $this->updatePagiUsername($user, $request->input('pagi_username'));
            if ($errorMsg) {
                return back()->withErrors(['pagi_username' => $errorMsg]);
            }
        }

        // 3. User direct fields
        $this->updateUserDirectFields($user, $request);

        // 4. User metadata
        $this->updateUserMetadataFields($user, $request);

        $user->save();

        return redirect()->back()->with('success', 'Pengaturan akun berhasil disimpan.');
    }

    private function updateUserPassword(User $user, ?string $currentPassword, string $newPassword): bool
    {
        if (! Hash::check($currentPassword, $user->password)) {
            return false;
        }
        $user->password = Hash::make($newPassword);

        return true;
    }

    private function updatePagiUsername(User $user, ?string $newUsername): ?string
    {
        $oldUsername = $user->pagi_username;
        if ($newUsername === $oldUsername) {
            return null;
        }

        $errorMsg = $this->checkUsernameCooldown($user, $newUsername);
        if ($errorMsg) {
            return $errorMsg;
        }

        $metadata = $user->metadata ?? [];
        if (! empty($oldUsername)) {
            $changesCount = $metadata['username_changes_count'] ?? 0;
            $metadata['username_changes_count'] = $changesCount + 1;
        } else {
            $metadata['username_changes_count'] = 0;
        }
        $metadata['last_username_changed_at'] = now()->toIso8601String();

        $user->metadata = $metadata;
        $user->pagi_username = strtolower(trim($newUsername));

        return null;
    }

    private function updateUserDirectFields(User $user, UpdateSettingsRequest $request): void
    {
        $textFields = [
            'tanggal_lahir', 'no_telepon', 'location', 'bio', 'website',
            'twitter', 'linkedin', 'github', 'instagram',
        ];
        foreach ($textFields as $field) {
            if ($request->has($field)) {
                $user->$field = is_string($request->$field) ? strip_tags($request->$field) : $request->$field;
            }
        }

        if ($request->has('nim_nip')) {
            $user->nomor_induk = $request->nim_nip ? strip_tags(trim($request->nim_nip)) : null;
        }
        if ($request->has('program_studi_id')) {
            $user->program_studi_id = $request->program_studi_id ?: null;
        }
    }

    private function updateUserMetadataFields(User $user, UpdateSettingsRequest $request): void
    {
        $metadata = $user->metadata ?? [];
        $metaTextFields = ['calendar_link', 'billing_address', 'legal_entity_name'];
        foreach ($metaTextFields as $field) {
            if ($request->has($field)) {
                $metadata[$field] = is_string($request->$field) ? strip_tags($request->$field) : $request->$field;
            }
        }

        $metaSanitizedFields = [
            'subscription', 'trade_agent', 'brand_branding',
            'payment_account', 'rewards', 'email_preferences',
        ];
        foreach ($metaSanitizedFields as $field) {
            if ($request->has($field)) {
                $metadata[$field] = $this->sanitizeInputRecursive($request->$field);
            }
        }

        $user->metadata = $metadata;
    }

    public function likePreview(Request $request, int $previewId)
    {
        $result = $this->likeWorkAction->execute(Auth::user(), $previewId);

        return response()->json($result);
    }

    public function commentPreview(Request $request, int $previewId)
    {
        $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $comments = $this->createCommentAction->execute(Auth::user(), $previewId, $request->body);

        return response()->json(['comments' => $this->profileService->formatComments($comments)]);
    }

    public function likeComment(Request $request, int $previewId, string $commentId)
    {
        $comments = $this->likeCommentAction->execute(Auth::user(), $previewId, $commentId);

        return response()->json(['comments' => $this->profileService->formatComments($comments)]);
    }

    public function viewPreview(Request $request, int $previewId)
    {
        // Rate-limit: max 3 view increments per visitor per work per 10 minutes
        // Uses the visitor's IP as the throttle key to prevent spam inflation
        $throttleKey = 'pagi_view:'.$previewId.':'.$request->ip();
        if (Cache::has($throttleKey)) {
            // Already counted recently — return current count without incrementing
            $portfolio = PagiWork::query()->select('id', 'views_count')->findOrFail($previewId);

            return response()->json(['views' => $portfolio->views_count]);
        }

        $portfolio = PagiWork::query()->findOrFail($previewId);
        $portfolio->increment('views_count', 1);

        // Prevent re-counting from same IP for the next 10 minutes
        Cache::put($throttleKey, 1, now()->addMinutes(10));

        return response()->json(['views' => $portfolio->views_count]);
    }

    /**
     * Lazy-load endpoint for the project modal.
     *
     * Returns the heavy payload (content blocks + comments) that is excluded from the
     * initial page load. Called once when the user first opens a project card.
     */
    public function previewData(Request $request, int $previewId)
    {
        $portfolio = PagiWork::query()
            ->with(['user:id,name,pagi_username,foto_path,location', 'tags'])
            ->findOrFail($previewId);

        // Security: only show published works to non-owners
        if (
            ! $portfolio->is_published
            && (! Auth::check() || Auth::id() !== $portfolio->user_id)
        ) {
            abort(404);
        }

        return response()->json([
            'content' => $this->profileService->formatPortfolioContent($portfolio->content),
            'comments' => $this->profileService->formatComments($portfolio->comments ?? []),
        ]);
    }

    public function replyComment(Request $request, int $previewId, string $commentId)
    {
        $request->validate([
            'body' => 'required|string|max:1000',
            'reply_to_user_id' => 'nullable|integer|exists:users,id',
        ]);

        $comments = $this->replyCommentAction->execute(
            Auth::user(),
            $previewId,
            $commentId,
            $request->body,
            $request->reply_to_user_id
        );

        return response()->json(['comments' => $this->profileService->formatComments($comments)]);
    }

    public function likeReply(Request $request, int $previewId, string $commentId, string $replyId)
    {
        $comments = $this->likeReplyAction->execute(Auth::user(), $previewId, $commentId, $replyId);

        return response()->json(['comments' => $this->profileService->formatComments($comments)]);
    }

    public function searchUsers(Request $request)
    {
        $query = $request->query('q', '');

        return response()->json($this->socialService->searchUsers($query, Auth::id() ?: 0));
    }

    public function storeCertificate(StoreCertificateRequest $request)
    {
        return $this->certificateService->store(Auth::user(), $request->validated(), $request);
    }

    public function getOrgLogo(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        return $this->certificateService->getOrgLogo($request->query('name'));
    }

    public function uploadOrgLogo(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'required|file|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
        ]);

        return $this->certificateService->uploadOrgLogo($request->input('name'), $request->file('logo'));
    }

    public function updateCertificate(UpdateCertificateRequest $request, string $id)
    {
        return $this->certificateService->update(Auth::user(), $id, $request->validated(), $request);
    }

    public function destroyCertificate(string $id)
    {
        return $this->certificateService->delete(Auth::user(), $id);
    }

    public function exploreGallery(Request $request)
    {
        $role = $request->attributes->get('resolved_role', session('active_role'));
        $data = $this->socialService->exploreGallery($request);

        return Inertia::render('Modules/Pagi/User/Gallery', [
            'moduleName' => 'PAGI',
            'roleName' => $role,
            'galleryItems' => $data['galleryItems'],
            'nextPageUrl' => $data['nextPageUrl'],
            'currentPage' => $data['currentPage'],
            'lastPage' => $data['lastPage'],
            'total' => $data['total'],
            'filters' => $data['filters'],
        ]);
    }

    private function getProfileViewData(User $user, Request $request): array
    {
        $projectId = $request->input('project') ?? $request->input('portfolio');
        $metaTitle = $user->name.' — PAGI Profile';
        $metaDescription = $user->bio ?: 'Tempat berbagi karya, portofolio, dan kolaborasi mahasiswa dan creator Fakultas Ilmu Komputer.';
        $metaImage = $this->getStorageUrl($user->foto_path) ?? asset('og-image.png');
        $metaType = 'profile';

        if ($projectId) {
            $sharedProject = PagiWork::query()->find($projectId);
            if ($sharedProject) {
                $metaTitle = $sharedProject->title.' by '.$user->name.' — PAGI Work';
                $metaType = 'article';
                if ($sharedProject->description) {
                    $metaDescription = strip_tags($sharedProject->description);
                    if (strlen($metaDescription) > 160) {
                        $metaDescription = substr($metaDescription, 0, 157).'...';
                    }
                } else {
                    $metaDescription = 'Lihat karya "'.$sharedProject->title.'" oleh '.$user->name.' di FMIKOM Portal.';
                }
                if ($sharedProject->cover_image) {
                    $metaImage = $this->getStorageUrl($sharedProject->cover_image) ?? $metaImage;
                }
            }
        }

        return [
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
            'metaImage' => $metaImage,
            'metaType' => $metaType,
        ];
    }

    /**
     * Format path media ke URL storage yang valid.
     * Mengembalikan URL absolut untuk path eksternal (http/https),
     * atau URL storage Laravel untuk path relatif. Mengembalikan null jika path kosong.
     */
    private function getStorageUrl(?string $path): ?string
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

    /**
     * Periksa apakah username termasuk dalam blacklist sistem.
     */
    private function isUsernameBlacklisted(string $username): bool
    {
        return in_array(strtolower(trim($username)), self::USERNAME_BLACKLIST);
    }

    /**
     * Validasi cooldown dan limitasi perubahan username (Maksimal 3x, cooldown 30 hari).
     * Mengembalikan pesan error jika melanggar, atau null jika valid.
     */
    private function checkUsernameCooldown(User $user, ?string $newUsername): ?string
    {
        if ($newUsername === $user->pagi_username) {
            return null;
        }

        if ($newUsername && $this->isUsernameBlacklisted($newUsername)) {
            return 'Username ini tidak dapat digunakan karena merupakan kata cadangan sistem.';
        }

        if (! empty($user->pagi_username)) {
            $metadata = $user->metadata ?? [];
            $changesCount = $metadata['username_changes_count'] ?? 0;
            $lastChangedAtStr = $metadata['last_username_changed_at'] ?? null;

            if ($changesCount >= 3) {
                return 'Batas perubahan username Anda telah habis (Maksimal 3 kali).';
            }

            if ($lastChangedAtStr) {
                $lastChangedAt = Carbon::parse($lastChangedAtStr);
                if ($lastChangedAt->diffInDays(now()) < 30) {
                    $daysLeft = 30 - $lastChangedAt->diffInDays(now());

                    return "Anda baru saja mengubah username. Silakan tunggu {$daysLeft} hari lagi untuk mengubahnya kembali.";
                }
            }
        }

        return null;
    }

    /**
     * Recursively sanitize input to prevent HTML/Script tag injection.
     */
    private function sanitizeInputRecursive(mixed $value)
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

    private function getAuthorName(?User $user): string
    {
        if (! $user) {
            return 'Unknown User';
        }

        $name = $user->name;
        if ($name === strtoupper($name)) {
            return ucwords(strtolower($name));
        }

        return $name;
    }

    private function getAvatarUrl(?User $user): string
    {
        if (! $user) {
            return 'https://ui-avatars.com/api/?name=User&background=random';
        }

        $avatar = $this->getStorageUrl($user->foto_path);
        if ($avatar) {
            return $avatar;
        }

        $name = $user->name;
        if ($name === strtoupper($name)) {
            $name = ucwords(strtolower($name));
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=random';
    }

    private function formatFeedProjects(Collection $projectsCollection, $reportedWorkIds, $preloadedUsers): Collection
    {
        return $projectsCollection->map(function ($portfolio) use ($reportedWorkIds, $preloadedUsers) {
            $authorName = $this->getAuthorName($portfolio->user);

            return [
                'id' => $portfolio->id,
                'title' => $portfolio->title ?? self::DEFAULT_PROJECT_TITLE,
                'image' => $this->getStorageUrl($portfolio->cover_image) ?? self::DEFAULT_COVER_IMAGE,
                'author' => $authorName,
                'avatar' => $this->getAvatarUrl($portfolio->user),
                // 'content' and 'comments' are intentionally omitted here.
                // They are heavy and only needed when the user opens the modal.
                // They are fetched on-demand via GET /pagi/preview/{id}/data.
                'likes' => count($portfolio->likes ?? []),
                'liked' => Auth::check() ? in_array(Auth::id(), $portfolio->likes ?? []) : false,
                'views' => $portfolio->views_count ?? 0,
                'tools_used' => $portfolio->tools_used,
                'description' => $portfolio->description,
                'category' => $portfolio->category,
                'tags' => $portfolio->tags->map(fn ($t) => $t->name)->toArray(),
                'created_at' => $portfolio->created_at->toISOString(),
                'resolved_collaborators' => $this->profileService->resolveCollaborators($portfolio, $preloadedUsers),
                'reported_by_me' => $reportedWorkIds->has($portfolio->id),
                'user' => $portfolio->user ? [
                    'id' => $portfolio->user->id,
                    'name' => $authorName,
                    'pagi_username' => $portfolio->user->pagi_username,
                    'avatar' => $this->getStorageUrl($portfolio->user->foto_path),
                    'location' => $portfolio->user->location ?? self::DEFAULT_LOCATION,
                ] : null,
            ];
        });
    }
}
