<?php

namespace App\Modules\Pagi\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Module;
use App\Models\UserModuleRole;
use Illuminate\Support\Str;
use App\Modules\Pagi\Services\PagiProfileService;
use App\Modules\Pagi\Services\PagiSocialService;
use App\Modules\Pagi\Services\PagiNotificationService;
use App\Modules\Pagi\Services\PagiCertificateService;
use App\Modules\Pagi\Actions\FollowUserAction;
use App\Modules\Pagi\Actions\LikeWorkAction;
use App\Modules\Pagi\Actions\CreateCommentAction;
use App\Modules\Pagi\Actions\ReplyCommentAction;
use App\Modules\Pagi\Actions\LikeCommentAction;
use App\Modules\Pagi\Actions\LikeReplyAction;

class PagiDashboardController extends Controller
{
    private const ADMIN_ROLES = ['super-admin', 'admin', 'admin-universitas', 'admin-akademik', 'prodi'];

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
        $isAdmin = in_array($role, self::ADMIN_ROLES);

        if ($isAdmin) {
            return redirect()->route('module.pagi.admin.dashboard');
        }

        $componentName = "Modules/Pagi/User/" . Str::studly($role) . "Dashboard";

        $path = resource_path("js/pages/{$componentName}.vue");
        if (!file_exists($path)) {
            $fallbackName = "Modules/Pagi/User/MahasiswaDashboard";
            if (file_exists(resource_path("js/pages/{$fallbackName}.vue"))) {
                $componentName = $fallbackName;
            } else {
                abort(404, "Dashboard Template untuk Role '{$role}' belum dibuat di {$componentName}.vue");
            }
        }

        $module = Module::where('code', 'PAGI')->first();
        $peopleYouMayKnow = $module
            ? $this->socialService->getPeopleYouMayKnow($module->id, auth()->id())
            : collect();

        $feedProjects = \App\Models\Pagi\PagiWork::with(['user', 'tags'])
            ->where('is_published', true)
            ->where(function($q) {
                $q->whereNull('visibility')
                  ->orWhere('visibility', 'Everyone');
            })
            ->latest()
            ->get()
            ->map(function ($portfolio) {
                return [
                    'id' => $portfolio->id,
                    'title' => $portfolio->title ?? 'Untitled Project',
                    'image' => $portfolio->cover_image ? (str_starts_with($portfolio->cover_image, 'http') ? $portfolio->cover_image : asset('storage/' . $portfolio->cover_image)) : 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop',
                    'author' => $portfolio->user ? (($portfolio->user->name === strtoupper($portfolio->user->name)) ? ucwords(strtolower($portfolio->user->name)) : $portfolio->user->name) : 'Unknown User',
                    'avatar' => ($portfolio->user && $portfolio->user->foto_path)
                        ? (str_starts_with($portfolio->user->foto_path, 'http') ? $portfolio->user->foto_path : asset('storage/' . $portfolio->user->foto_path))
                        : 'https://ui-avatars.com/api/?name=' . urlencode($portfolio->user ? (($portfolio->user->name === strtoupper($portfolio->user->name)) ? ucwords(strtolower($portfolio->user->name)) : $portfolio->user->name) : 'User') . '&background=random',
                    'likes' => count($portfolio->likes ?? []),
                    'liked' => auth()->check() ? in_array(auth()->id(), $portfolio->likes ?? []) : false,
                    'comments' => $this->profileService->formatComments($portfolio->comments ?? []),
                    'views' => $portfolio->views_count ?? 0,
                    'views_count' => $portfolio->views_count ?? 0,
                    'content' => $this->profileService->formatPortfolioContent($portfolio->content),
                    'tools_used' => $portfolio->tools_used,
                    'description' => $portfolio->description,
                    'category' => $portfolio->category,
                    'tags' => $portfolio->tags->map(fn($t) => $t->name)->toArray(),
                    'created_at' => $portfolio->created_at->toISOString(),
                    'resolved_collaborators' => $this->profileService->resolveCollaborators($portfolio),
                    'reported_by_me' => auth()->check() 
                        ? \App\Models\Pagi\PagiReport::where('work_id', $portfolio->id)
                            ->where('reporter_id', auth()->id())
                            ->where('status', 'pending')
                            ->exists()
                        : false,
                    'user' => $portfolio->user ? [
                        'id' => $portfolio->user->id,
                        'name' => ($portfolio->user->name === strtoupper($portfolio->user->name)) ? ucwords(strtolower($portfolio->user->name)) : $portfolio->user->name,
                        'pagi_username' => $portfolio->user->pagi_username,
                        'avatar' => $portfolio->user->foto_path ? (str_starts_with($portfolio->user->foto_path, 'http') ? $portfolio->user->foto_path : asset('storage/' . $portfolio->user->foto_path)) : null,
                        'location' => $portfolio->user->location ?? 'Banyumas, Indonesia',
                    ] : null,
                ];
            });

        return Inertia::render($componentName, [
            'moduleName' => 'PAGI',
            'roleName' => $role,
            'peopleYouMayKnow' => $peopleYouMayKnow,
            'feedProjects' => $feedProjects,
        ]);
    }

    public function explorePeople(Request $request)
    {
        $role = $request->attributes->get('resolved_role', session('active_role'));
        $module = Module::where('code', 'PAGI')->first();
        $people = $module ? $this->socialService->explorePeople($module->id) : collect();

        return Inertia::render('Modules/Pagi/User/People', [
            'moduleName' => 'PAGI',
            'roleName' => $role,
            'peopleYouMayKnow' => $people,
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
            $result = $this->followUserAction->execute(auth()->user(), $targetUserId);
            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function getFollowRelations(Request $request, \App\Models\User $user)
    {
        return response()->json($this->socialService->getFollowRelations($user));
    }

    public function notifications(Request $request)
    {
        $role = $request->attributes->get('resolved_role', session('active_role'));
        $data = $this->notificationService->getUserNotifications(auth()->user());

        return Inertia::render('Modules/Pagi/User/Notifications', [
            'moduleName'  => 'PAGI',
            'roleName'    => $role,
            'notifGroups' => $data['groups'],
            'unreadCount' => $data['unreadCount'],
        ]);
    }

    public function markNotificationRead(Request $request, string $id)
    {
        $this->notificationService->markAsRead(auth()->user(), $id);
        return response()->json(['ok' => true]);
    }

    public function markAllNotificationsRead(Request $request)
    {
        $this->notificationService->markAllAsRead(auth()->user());
        return response()->json(['ok' => true]);
    }

    public function deleteNotification(Request $request, string $id)
    {
        $this->notificationService->delete(auth()->user(), $id);
        return response()->json(['ok' => true]);
    }

    public function clearAllNotifications(Request $request)
    {
        $this->notificationService->clearAll(auth()->user());
        return response()->json(['ok' => true]);
    }

    public function reorderProjects(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer'
        ]);

        $this->profileService->reorderProjects(auth()->user(), $request->order);
        return redirect()->back();
    }

    public function profile(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->pagi_username) {
            return redirect()->route('module.pagi.profile.username', array_merge(['user' => $user->pagi_username], $request->query()));
        }

        $role = $request->attributes->get('resolved_role', session('active_role'));
        $data = $this->profileService->getProfileData($user, 1, true);

        return Inertia::render('Modules/Pagi/User/Profile/Index', [
            'moduleName'  => 'PAGI',
            'roleName'    => $role,
            'profileUser' => $data['profileUser'],
            'projects'    => $data['projects'],
        ])->withViewData($this->getProfileViewData($user, $request));
    }

    public function publicProfile(Request $request, \App\Models\User $user, $tab = null)
    {
        if ($request->has('tab')) {
            $tabVal = strtolower($request->query('tab'));
            if ($tabVal === 'sertifikat') $tabVal = 'certificates';
            if (in_array($tabVal, ['work', 'gallery', 'certificates', 'about'])) {
                return redirect()->to("/pagi/{$user->pagi_username}/{$tabVal}" . ($request->except('tab') ? '?' . http_build_query($request->except('tab')) : ''));
            }
        }

        if ($tab && strtolower($tab) === 'sertifikat') {
            return redirect()->to("/pagi/{$user->pagi_username}/certificates" . ($request->query() ? '?' . http_build_query($request->query()) : ''));
        }

        if ($tab && preg_match('/[A-Z]/', $tab)) {
            return redirect()->to("/pagi/{$user->pagi_username}/" . strtolower($tab) . ($request->query() ? '?' . http_build_query($request->query()) : ''));
        }

        if ($tab && !in_array(strtolower($tab), ['work', 'gallery', 'certificates', 'about'])) {
            return redirect()->to("/pagi/{$user->pagi_username}" . ($request->query() ? '?' . http_build_query($request->query()) : ''));
        }

        if ($user->pagi_username && $request->route()->getName() === 'module.pagi.profile.public') {
            return redirect()->route('module.pagi.profile.username', array_merge(['user' => $user->pagi_username, 'tab' => $tab], $request->query()));
        }

        $viewerRole = auth()->check()
            ? ($request->attributes->get('resolved_role', session('active_role')) ?? 'mahasiswa')
            : 'guest';

        $isOwner = auth()->check() && auth()->id() === $user->id;
        $data = $this->profileService->getProfileData($user, 1, $isOwner);

        $isFollowing = false;
        if (auth()->check() && !$isOwner) {
            $authMeta = auth()->user()->metadata ?? [];
            $isFollowing = in_array($user->id, $authMeta['following'] ?? []);
        }

        $selectedTheme = $user->metadata['pagi_work_theme'] ?? null;
        $selectedPalette = $user->metadata['pagi_work_palette_index'] ?? 0;
        $forceStandard = $request->query('edit') === 'true' || $request->query('preview') === 'false';

        $componentName = (!empty($selectedTheme) && !$forceStandard) 
            ? 'Modules/Pagi/User/Works/Show' 
            : 'Modules/Pagi/User/Profile/Index';

        return Inertia::render($componentName, [
            'moduleName'      => 'PAGI',
            'roleName'        => $viewerRole,
            'profileUser'     => $data['profileUser'],
            'projects'        => $data['projects'],
            'isFollowing'     => $isFollowing,
            'selectedTheme'   => $selectedTheme,
            'selectedPalette' => (int)$selectedPalette,
        ])->withViewData($this->getProfileViewData($user, $request));
    }

    public function userWorks(Request $request, \App\Models\User $user)
    {
        $isOwner = auth()->check() && auth()->id() === $user->id;
        $query = \App\Models\Pagi\PagiWork::with(['tags', 'user'])
            ->where('user_id', $user->id);

        if (!$isOwner) {
            $query->where('is_published', true)
                  ->where(function($q) {
                      $q->whereNull('visibility')
                        ->orWhere('visibility', 'Everyone');
                  });
        }

        $projects = $query->latest()
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'user_id' => $p->user_id,
                'title' => $p->title ?? 'Untitled Project',
                'image' => $p->cover_image ? (str_starts_with($p->cover_image, 'http') ? $p->cover_image : asset('storage/' . $p->cover_image)) : 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop',
                'content' => $this->profileService->formatPortfolioContent($p->content),
                'created_at' => $p->created_at->format('F jS Y'),
                'likes' => count($p->likes ?? []),
                'liked' => auth()->check() ? in_array(auth()->id(), $p->likes ?? []) : false,
                'comments' => $this->profileService->formatComments($p->comments ?? []),
                'views' => $p->views_count ?? 0,
                'is_published' => (bool)$p->is_published,
                'tools_used' => $p->tools_used,
                'description' => $p->description,
                'category' => $p->category,
                'tags' => $p->tags->map(fn($t) => $t->name)->toArray(),
                'resolved_collaborators' => $this->profileService->resolveCollaborators($p),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'pagi_username' => $user->pagi_username,
                    'avatar' => $user->foto_path ? (str_starts_with($user->foto_path, 'http') ? $user->foto_path : asset('storage/' . $user->foto_path)) : null,
                    'location' => $user->location ?? 'Banyumas, Indonesia',
                ],
            ]);

        return response()->json(['works' => $projects]);
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

        if (!preg_match('/^[a-z0-9._]+$/', $username)) {
            return response()->json(['available' => false, 'error' => 'Username hanya boleh mengandung huruf kecil, angka, titik (.), dan underscore (_).', 'suggestions' => []]);
        }

        $blacklist = ['explore', 'jobs', 'messages', 'notifications', 'portfolio', 'profile', 'admin', 'users', 'username', 'settings', 'chat', 'auth', 'api', 'dashboard', 'seed-dummy', 'invitations', 'images', 'berita', 'halaman', 'people'];
        if (in_array($username, $blacklist)) {
            return response()->json(['available' => false, 'error' => 'Username ini tidak dapat digunakan karena merupakan kata cadangan sistem.', 'suggestions' => []]);
        }

        $result = $this->profileService->checkUsername($username, auth()->id() ?: 0);
        return response()->json($result);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'role_title'       => 'nullable|string|max:100',
            'banner'           => [
                'nullable',
                'file',
                'mimes:jpeg,png,jpg,gif,webp,mp4,webm,ogg',
                'max:102400',
                function ($attribute, $value, $fail) {
                    $mime = $value->getMimeType();
                    if (str_starts_with($mime, 'video/')) {
                        $path = $value->getRealPath();
                        $escapedPath = escapeshellarg($path);
                        $command = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 {$escapedPath} 2>&1";
                        $output = shell_exec($command);
                        if ($output !== null && is_numeric(trim($output))) {
                            $duration = (float) trim($output);
                            if ($duration > 60.5) {
                                $fail('Durasi video maksimal adalah 1 menit (60 detik).');
                            }
                        }
                    }
                }
            ],
            'foto'             => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'bio'              => 'nullable|string|max:1000',
            'location'         => 'nullable|string|max:100',
            'website'          => 'nullable|string|max:255',
            'twitter'          => 'nullable|string|max:100',
            'linkedin'         => 'nullable|string|max:100',
            'github'           => 'nullable|string|max:100',
            'instagram'        => 'nullable|string|max:100',
            'tanggal_lahir'    => 'nullable|date',
            'is_message_enabled' => 'nullable|boolean',
            'skills'           => 'nullable|array',
            'timezone'         => 'nullable|string|max:100',
            'timezone_extended'=> 'nullable|string|max:100',
            'languages'        => 'nullable|array',
            'pagi_username'    => [
                'nullable',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9._]+$/',
                \Illuminate\Validation\Rule::unique('users', 'pagi_username')->ignore(auth()->id()),
            ],
        ]);

        if ($request->has('pagi_username')) {
            $oldUsername = $user->pagi_username;
            $newUsername = $request->input('pagi_username');
            if ($newUsername !== $oldUsername) {
                $blacklist = ['explore', 'jobs', 'messages', 'notifications', 'portfolio', 'profile', 'admin', 'users', 'username', 'settings', 'chat', 'auth', 'api', 'dashboard', 'seed-dummy', 'invitations', 'images', 'berita', 'halaman', 'people'];
                if ($newUsername && in_array($newUsername, $blacklist)) {
                    return back()->withErrors(['pagi_username' => 'Username ini tidak dapat digunakan karena merupakan kata cadangan sistem.']);
                }

                if (!empty($oldUsername)) {
                    $metadata = $user->metadata ?? [];
                    $changesCount = $metadata['username_changes_count'] ?? 0;
                    $lastChangedAtStr = $metadata['last_username_changed_at'] ?? null;

                    if ($changesCount >= 3) {
                        return back()->withErrors(['pagi_username' => 'Batas perubahan username Anda telah habis (Maksimal 3 kali).']);
                    }

                    if ($lastChangedAtStr) {
                        $lastChangedAt = \Carbon\Carbon::parse($lastChangedAtStr);
                        if ($lastChangedAt->diffInDays(now()) < 30) {
                            $daysLeft = 30 - $lastChangedAt->diffInDays(now());
                            return back()->withErrors(['pagi_username' => "Anda baru saja mengubah username. Silakan tunggu {$daysLeft} hari lagi untuk mengubahnya kembali."]);
                        }
                    }
                }
            }
        }

        $this->profileService->updateProfile($user, $request->all(), $request);

        return redirect()->route('module.pagi.profile')->with('success', 'Profile updated successfully.');
    }

    public function settings(Request $request)
    {
        $user = auth()->user()->load('programStudi.fakultas');
        $role = $request->attributes->get('resolved_role', session('active_role'));

        // Recalculate progress:
        $progress = 0;
        if (!empty($user->name)) $progress += 10;
        if (!empty($user->email)) $progress += 10;
        if (!empty($user->foto_path)) $progress += 10;
        if (!empty($user->bio)) $progress += 10;
        if (!empty($user->location) || !empty($user->metadata['billing_address'])) $progress += 10;
        if (!empty($user->tanggal_lahir)) $progress += 10;
        if (!empty($user->no_telepon)) $progress += 10;
        if (!empty($user->github) || !empty($user->linkedin) || !empty($user->instagram)) $progress += 10;
        if (!empty($user->metadata['calendar_link'])) $progress += 10;
        if (!empty($user->pagi_username)) $progress += 10;

        $profileUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'tanggal_lahir' => $user->tanggal_lahir ? $user->tanggal_lahir->format('Y-m-d') : null,
            'calendar_link' => $user->metadata['calendar_link'] ?? null,
            'billing_address' => $user->metadata['billing_address'] ?? null,
            'legal_entity_name' => $user->metadata['legal_entity_name'] ?? null,
            'pagi_username' => $user->pagi_username,
            'progress' => min(100, $progress),
            'bio' => $user->bio ?? 'Through the lens, I create stories worth remembering.',
            'avatar' => $user->foto_path 
                ? (str_starts_with($user->foto_path, 'http') ? $user->foto_path : '/storage/' . $user->foto_path)
                : 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($user->name) . '&backgroundColor=3b82f6,6366f1,8b5cf6,ec4899,f43f5e&backgroundType=gradientLinear&bold=true',
            'works_count' => $user->pagiWorks()->count(),
            'followers_count' => count($user->metadata['followers'] ?? []),
            'nim_nip' => $user->nim_nip ?? null,
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

        return Inertia::render('Modules/Pagi/User/Settings/Index', [
            'moduleName' => 'PAGI',
            'roleName' => $role,
            'profileUser' => $profileUser,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id)],
            'name' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'no_telepon' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'website' => ['nullable', 'string', 'max:255'],
            'twitter' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'string', 'max:255'],
            'github' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'calendar_link' => ['nullable', 'string', 'max:255'],
            'billing_address' => ['nullable', 'string', 'max:500'],
            'legal_entity_name' => ['nullable', 'string', 'max:255'],
            'pagi_username' => [
                'nullable',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9._]+$/',
                \Illuminate\Validation\Rule::unique('users', 'pagi_username')->ignore($user->id),
            ],
            // Password change inputs
            'current_password' => ['nullable', 'required_with:new_password', 'string'],
            'new_password' => ['nullable', 'string', 'min:8'],
        ]);

        // Validate current password if changing password
        if ($request->filled('new_password')) {
            if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
            }
            $user->password = \Illuminate\Support\Facades\Hash::make($request->new_password);
        }

        // Handle Pagi Username changes limits
        if ($request->has('pagi_username')) {
            $oldUsername = $user->pagi_username;
            $newUsername = $request->input('pagi_username');
            if ($newUsername !== $oldUsername) {
                $blacklist = ['explore', 'jobs', 'messages', 'notifications', 'portfolio', 'profile', 'admin', 'users', 'username', 'settings', 'chat', 'auth', 'api', 'dashboard', 'seed-dummy', 'invitations', 'images', 'berita', 'halaman', 'people'];
                if ($newUsername && in_array(strtolower($newUsername), $blacklist)) {
                    return back()->withErrors(['pagi_username' => 'Username ini tidak dapat digunakan karena merupakan kata cadangan sistem.']);
                }

                if (!empty($oldUsername)) {
                    $metadata = $user->metadata ?? [];
                    $changesCount = $metadata['username_changes_count'] ?? 0;
                    $lastChangedAtStr = $metadata['last_username_changed_at'] ?? null;

                    if ($changesCount >= 3) {
                        return back()->withErrors(['pagi_username' => 'Batas perubahan username Anda telah habis (Maksimal 3 kali).']);
                    }

                    if ($lastChangedAtStr) {
                        $lastChangedAt = \Carbon\Carbon::parse($lastChangedAtStr);
                        if ($lastChangedAt->diffInDays(now()) < 30) {
                            $daysLeft = 30 - $lastChangedAt->diffInDays(now());
                            return back()->withErrors(['pagi_username' => "Anda baru saja mengubah username. Silakan tunggu {$daysLeft} hari lagi untuk mengubahnya kembali."]);
                        }
                    }
                    $metadata['username_changes_count'] = $changesCount + 1;
                } else {
                    $metadata['username_changes_count'] = 0;
                }
                $metadata['last_username_changed_at'] = now()->toIso8601String();
                $user->pagi_username = strtolower(trim($newUsername));
            }
        }

        // Direct columns - email and name updates are disabled to keep them tied to portal credentials
        if ($request->has('tanggal_lahir')) $user->tanggal_lahir = $request->tanggal_lahir;
        if ($request->has('no_telepon')) $user->no_telepon = strip_tags($request->no_telepon);
        if ($request->has('location')) $user->location = strip_tags($request->location);
        if ($request->has('bio')) $user->bio = strip_tags($request->bio);
        if ($request->has('website')) $user->website = strip_tags($request->website);
        if ($request->has('twitter')) $user->twitter = strip_tags($request->twitter);
        if ($request->has('linkedin')) $user->linkedin = strip_tags($request->linkedin);
        if ($request->has('github')) $user->github = strip_tags($request->github);
        if ($request->has('instagram')) $user->instagram = strip_tags($request->instagram);

        // Metadata updates
        $metadata = $user->metadata ?? [];
        if ($request->has('calendar_link')) $metadata['calendar_link'] = strip_tags($request->calendar_link);
        if ($request->has('billing_address')) $metadata['billing_address'] = strip_tags($request->billing_address);
        if ($request->has('legal_entity_name')) $metadata['legal_entity_name'] = strip_tags($request->legal_entity_name);
        if ($request->has('subscription')) $metadata['subscription'] = $this->sanitizeInputRecursive($request->subscription);
        if ($request->has('trade_agent')) $metadata['trade_agent'] = $this->sanitizeInputRecursive($request->trade_agent);
        if ($request->has('brand_branding')) $metadata['brand_branding'] = $this->sanitizeInputRecursive($request->brand_branding);
        if ($request->has('payment_account')) $metadata['payment_account'] = $this->sanitizeInputRecursive($request->payment_account);
        if ($request->has('rewards')) $metadata['rewards'] = $this->sanitizeInputRecursive($request->rewards);
        if ($request->has('email_preferences')) $metadata['email_preferences'] = $this->sanitizeInputRecursive($request->email_preferences);
        
        $user->metadata = $metadata;

        $user->save();

        return redirect()->back()->with('success', 'Pengaturan akun berhasil disimpan.');
    }

    public function likePreview(Request $request, int $previewId)
    {
        $result = $this->likeWorkAction->execute(auth()->user(), $previewId);
        return response()->json($result);
    }

    public function commentPreview(Request $request, int $previewId)
    {
        $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $comments = $this->createCommentAction->execute(auth()->user(), $previewId, $request->body);
        return response()->json(['comments' => $this->profileService->formatComments($comments)]);
    }

    public function likeComment(Request $request, int $previewId, string $commentId)
    {
        $comments = $this->likeCommentAction->execute(auth()->user(), $previewId, $commentId);
        return response()->json(['comments' => $this->profileService->formatComments($comments)]);
    }

    public function viewPreview(Request $request, int $previewId)
    {
        $portfolio = \App\Models\Pagi\PagiWork::findOrFail($previewId);
        $portfolio->increment('views_count');

        return response()->json(['views' => $portfolio->views_count]);
    }

    public function replyComment(Request $request, int $previewId, string $commentId)
    {
        $request->validate([
            'body' => 'required|string|max:1000',
            'reply_to_user_id' => 'nullable|integer|exists:users,id',
        ]);

        $comments = $this->replyCommentAction->execute(
            auth()->user(), 
            $previewId, 
            $commentId, 
            $request->body, 
            $request->reply_to_user_id
        );
        return response()->json(['comments' => $this->profileService->formatComments($comments)]);
    }

    public function likeReply(Request $request, int $previewId, string $commentId, string $replyId)
    {
        $comments = $this->likeReplyAction->execute(auth()->user(), $previewId, $commentId, $replyId);
        return response()->json(['comments' => $this->profileService->formatComments($comments)]);
    }

    public function searchUsers(Request $request)
    {
        $query = $request->query('q', '');
        return response()->json($this->socialService->searchUsers($query, auth()->id() ?: 0));
    }

    public function storeCertificate(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'issuer' => 'required|string|max:255',
            'date' => 'required|string|max:100',
            'expirationDate' => 'nullable|string|max:100',
            'credentialId' => 'nullable|string|max:255',
            'credentialUrl' => 'nullable|url|max:2083',
            'skills' => 'nullable|string',
            'newMedia' => 'nullable|array|max:3',
            'newMedia.*' => 'file|mimes:jpeg,png,jpg,gif,webp,pdf|max:20480',
        ]);

        return $this->certificateService->store(auth()->user(), $validated, $request);
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
            'logo' => 'required|file|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
        ]);

        return $this->certificateService->uploadOrgLogo($request->input('name'), $request->file('logo'));
    }

    public function updateCertificate(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'issuer' => 'required|string|max:255',
            'date' => 'required|string|max:100',
            'expirationDate' => 'nullable|string|max:100',
            'credentialId' => 'nullable|string|max:255',
            'credentialUrl' => 'nullable|url|max:2083',
            'skills' => 'nullable|string',
            'existingMedia' => 'nullable|string',
            'newMedia' => 'nullable|array|max:3',
            'newMedia.*' => 'file|mimes:jpeg,png,jpg,gif,webp,pdf|max:20480',
        ]);

        return $this->certificateService->update(auth()->user(), $id, $validated, $request);
    }

    public function destroyCertificate($id)
    {
        return $this->certificateService->delete(auth()->user(), $id);
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

    private function getProfileViewData($user, Request $request): array
    {
        $projectId = $request->input('project') ?? $request->input('portfolio');
        $metaTitle = $user->name . ' — PAGI Profile';
        $metaDescription = $user->bio ?: 'Tempat berbagi karya, portofolio, dan kolaborasi mahasiswa dan creator Fakultas Ilmu Komputer.';
        $metaImage = $user->foto_path 
            ? (str_starts_with($user->foto_path, 'http') ? $user->foto_path : asset('storage/' . $user->foto_path))
            : asset('og-image.png');
        $metaType = 'profile';

        if ($projectId) {
            $sharedProject = \App\Models\Pagi\PagiWork::find($projectId);
            if ($sharedProject) {
                $metaTitle = $sharedProject->title . ' by ' . $user->name . ' — PAGI Work';
                $metaType = 'article';
                if ($sharedProject->description) {
                    $metaDescription = strip_tags($sharedProject->description);
                    if (strlen($metaDescription) > 160) {
                        $metaDescription = substr($metaDescription, 0, 157) . '...';
                    }
                } else {
                    $metaDescription = 'Lihat karya "' . $sharedProject->title . '" oleh ' . $user->name . ' di FMIKOM Portal.';
                }
                if ($sharedProject->cover_image) {
                    $metaImage = str_starts_with($sharedProject->cover_image, 'http') 
                        ? $sharedProject->cover_image 
                        : asset('storage/' . $sharedProject->cover_image);
                }
            }
        }

        return [
            'metaTitle'       => $metaTitle,
            'metaDescription' => $metaDescription,
            'metaImage'       => $metaImage,
            'metaType'        => $metaType,
        ];
    }

    /**
     * Recursively sanitize input to prevent HTML/Script tag injection.
     */
    private function sanitizeInputRecursive($value)
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
