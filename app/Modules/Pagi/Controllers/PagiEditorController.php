<?php

namespace App\Modules\Pagi\Controllers;

use App\Events\PagiWorkCreated;
use App\Events\PagiWorkDeleted;
use App\Events\PagiWorkUpdated;
use App\Http\Controllers\Controller;
use App\Models\Pagi\PagiReport;
use App\Models\Pagi\PagiWork;
use App\Models\User;
use App\Modules\Pagi\Requests\QuickStorePortfolioRequest;
use App\Modules\Pagi\Requests\QuickUpdatePortfolioRequest;
use App\Modules\Pagi\Requests\StoreGalleryItemRequest;
use App\Modules\Pagi\Requests\StorePortfolioRequest;
use App\Modules\Pagi\Requests\UpdatePortfolioRequest;
use App\Modules\Pagi\Services\ContentModerationService;
use App\Modules\Pagi\Services\PortfolioService;
use App\Notifications\PagiNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class PagiEditorController extends Controller implements HasMiddleware
{
    protected PortfolioService $portfolioService;

    private const ERROR_ACCESS_DENIED = 'Akses ditolak: Fitur Editor Portofolio hanya tersedia untuk Mahasiswa.';

    private const ERROR_UNAUTHORIZED = 'Unauthorized action.';

    private const DATE_FORMAT_STANDARD = 'F jS Y';

    private const STORAGE_PREFIX = 'storage/';

    public function __construct(PortfolioService $portfolioService)
    {
        $this->portfolioService = $portfolioService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware(function ($request, $next) {
                $role = $request->attributes->get('resolved_role', session('active_role'));
                if (strtolower($role) !== 'mahasiswa') {
                    if ($request->wantsJson()) {
                        return response()->json(['message' => self::ERROR_ACCESS_DENIED], 403);
                    }

                    return redirect()->route('module.pagi.dashboard')
                        ->with('error', self::ERROR_ACCESS_DENIED);
                }

                // Check Restricted Access Mode for suspended users
                $user = Auth::user();
                if ($user && ! $user->is_active && ! $request->isMethod('get')) {
                    $restrictedMsg = 'Akses Anda dibatasi (Restricted Mode). Akun Anda sedang dalam penangguhan. Silakan ajukan banding melalui halaman notifikasi.';
                    if ($request->wantsJson()) {
                        return response()->json(['message' => $restrictedMsg], 403);
                    }

                    return redirect()->route('module.pagi.dashboard')->with('error', $restrictedMsg);
                }

                return $next($request);
            }),
        ];
    }

    public function editor(Request $request)
    {
        $id = $request->query('id');
        $portfolio = null;
        if ($id) {
            $portfolio = PagiWork::query()->with('tags')->findOrFail($id);
            if ($portfolio->user_id !== Auth::id()) {
                abort(403, self::ERROR_UNAUTHORIZED);
            }

            // Populate preview and previews for the frontend
            $portfolio->content = $this->populateContentPreviews($portfolio->content);
        }

        $images = [
            [
                'id' => 1,
                'url' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=1920&auto=format&fit=crop',
                'width' => 1920,
                'height' => 1080,
            ],
            [
                'id' => 2,
                'url' => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?q=80&w=1080&auto=format&fit=crop',
                'width' => 1080,
                'height' => 1350,
            ],
            [
                'id' => 3,
                'url' => 'https://images.unsplash.com/photo-1605721911519-3dfeb3be25e7?q=80&w=1080&auto=format&fit=crop',
                'width' => 1080,
                'height' => 1350,
            ],
            [
                'id' => 4,
                'url' => 'https://images.unsplash.com/photo-1526244434195-e2bd668b549b?q=80&w=1920&auto=format&fit=crop',
                'width' => 1920,
                'height' => 1080,
            ],
            [
                'id' => 5,
                'url' => 'https://images.unsplash.com/photo-1554188718-d80a69a4a29c?q=80&w=1920&auto=format&fit=crop',
                'width' => 1920,
                'height' => 1080,
            ],
        ];

        return Inertia::render('Modules/Pagi/User/Editor/Editor', [
            'editor' => $portfolio,
            'mockImages' => $images,
        ]);
    }

    public function store(StorePortfolioRequest $request)
    {
        $sanitizedTitle = strip_tags($request->title);
        $sanitizedDescription = strip_tags($request->description);
        $sanitizedCategory = strip_tags($request->category);
        $sanitizedTools = strip_tags($request->tools_used);
        $sanitizedTags = strip_tags($request->tags);
        $sanitizedVisibility = strip_tags($request->visibility ?: 'Everyone');

        $contentData = [];
        if ($request->has('content') && is_array($request->content)) {
            $contentData = $this->portfolioService->processContentBlocks($request->content, $request);
        }

        $collaborators = $request->input('collaborators') ?: [];
        if (is_string($collaborators)) {
            $collaborators = json_decode($collaborators, true) ?: [];
        }

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $this->portfolioService->saveCoverImage($request->file('cover_image'));
        }

        $portfolio = PagiWork::query()->create([
            'user_id' => Auth::id(),
            'title' => $sanitizedTitle,
            'content' => $contentData,
            'cover_image' => $coverPath,
            'category' => $sanitizedCategory,
            'tools_used' => $sanitizedTools,
            'description' => $sanitizedDescription,
            'visibility' => $sanitizedVisibility,
            'is_published' => $request->is_published ?? false,
        ]);

        // Process collaborators & notify
        $newCollaborators = $this->portfolioService->processAndNotifyCollaborators($portfolio, $collaborators);

        // Update featured details collaborator block in content
        $portfolio->update(['content' => $this->updateFeaturedDetailsCollaborators($portfolio->content, $newCollaborators)]);

        // Check Auto Moderasi AI jika aktif
        $this->checkAutoModeration($portfolio);

        // Notify followers about new published work
        if ($portfolio->is_published && $portfolio->status !== 'review') {
            $this->portfolioService->notifyFollowers($portfolio);
        }

        // Dispatch realtime event to update subscriber profiles & feeds
        PagiWorkCreated::dispatch($portfolio);

        if ($portfolio->status === 'review') {
            return redirect()->route('module.pagi.dashboard')->with('warning', 'Karya berhasil dibuat namun sedang dalam peninjauan moderasi otomatis.');
        }

        return redirect()->route('module.pagi.dashboard')->with('success', 'Portfolio created successfully!');
    }

    public function quickStore(QuickStorePortfolioRequest $request)
    {
        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $this->portfolioService->saveCoverImage($request->file('cover_image'));
        }

        // Parse lists
        $skills = json_decode($request->input('skills', '[]'), true) ?: [];
        $tools = json_decode($request->input('tools', '[]'), true) ?: [];
        $collaborators = json_decode($request->input('collaborators', '[]'), true) ?: [];

        $portfolio = PagiWork::query()->create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'content' => [],
            'cover_image' => $coverPath,
            'is_published' => true,
        ]);

        $newCollaborators = $this->portfolioService->processAndNotifyCollaborators($portfolio, $collaborators);

        // Save detailed structure into block format inside content column
        $contentData = [
            [
                'type' => 'featured_details',
                'skills' => $skills,
                'tools' => $tools,
                'completed_work_link' => $request->input('completed_work_link'),
                'collaborators' => $newCollaborators,
                'client' => $request->input('client'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'industry' => $request->input('industry'),
                'original_work_confirmed' => $request->input('original_work_confirmed') === 'true',
                'cover_fit' => $request->input('cover_fit', 'cover'),
            ],
        ];

        $portfolio->update(['content' => $contentData]);

        // Check Auto Moderasi AI jika aktif (termasuk deteksi gambar darah/gore/NSFW)
        $this->checkAutoModeration($portfolio);

        $mappedProject = [
            'id' => $portfolio->id,
            'title' => $portfolio->title ?? 'Untitled Project',
            'image' => $portfolio->cover_image ? (str_starts_with($portfolio->cover_image, 'http') ? $portfolio->cover_image : asset(self::STORAGE_PREFIX.$portfolio->cover_image)) : 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop',
            'content' => $contentData,
            'created_at' => $portfolio->created_at->format(self::DATE_FORMAT_STANDARD),
            'likes' => 0,
            'liked' => false,
            'comments' => [],
            'views' => 0,
            'status' => $portfolio->status ?? 'active',
        ];

        // Notify followers only if not flagged for review
        if ($portfolio->status !== 'review') {
            $this->portfolioService->notifyFollowers($portfolio);
        }

        // Dispatch realtime event to update subscriber profiles & feeds
        PagiWorkCreated::dispatch($portfolio);

        $message = $portfolio->status === 'review'
            ? 'Karya berhasil ditambahkan namun sedang dalam peninjauan moderasi otomatis.'
            : 'Karya berhasil ditambahkan!';

        return response()->json([
            'success' => true,
            'project' => $mappedProject,
            'message' => $message,
        ]);
    }

    public function quickUpdate(QuickUpdatePortfolioRequest $request, PagiWork $editor)
    {
        if ($editor->user_id !== Auth::id()) {
            abort(403, self::ERROR_UNAUTHORIZED);
        }

        if (in_array($editor->status, ['hidden', 'removed'], true)) {
            return redirect()->back()->with('warning', 'Karya ini sedang dalam sanksi Takedown. Anda tidak dapat mengedit karya ini, namun Anda dapat mengajukan banding.');
        }

        if ($this->isGalleryItem($editor)) {
            return $this->handleGalleryItemUpdate($request, $editor);
        }

        return $this->handleStandardItemUpdate($request, $editor);
    }

    public function update(UpdatePortfolioRequest $request, PagiWork $editor)
    {
        if ($editor->user_id !== Auth::id()) {
            abort(403, self::ERROR_UNAUTHORIZED);
        }

        if (in_array($editor->status, ['hidden', 'removed'], true)) {
            return redirect()->back()->with('warning', 'Karya ini sedang dalam sanksi Takedown. Anda tidak dapat mengedit karya ini, namun Anda dapat mengajukan banding.');
        }

        $sanitizedTitle = strip_tags($request->title);
        $sanitizedDescription = strip_tags($request->description);
        $sanitizedCategory = strip_tags($request->category);
        $sanitizedTools = strip_tags($request->tools_used);
        $sanitizedTags = strip_tags($request->tags);

        $contentData = [];
        if ($request->has('content') && is_array($request->content)) {
            $contentData = $this->portfolioService->processContentBlocks($request->content, $request);
        }

        $collaborators = $request->input('collaborators') ?: [];
        if (is_string($collaborators)) {
            $collaborators = json_decode($collaborators, true) ?: [];
        }

        $existingDetails = collect($editor->content)->firstWhere('type', 'featured_details') ?: [];
        $existingCollabs = $existingDetails['collaborators'] ?? [];

        $newCollaborators = $this->portfolioService->processAndNotifyCollaborators($editor, $collaborators, $existingCollabs);

        $contentData = $this->updateFeaturedDetailsCollaborators($contentData, $newCollaborators);

        $coverPath = $editor->cover_image;
        if ($request->hasFile('cover_image')) {
            $coverPath = $this->portfolioService->saveCoverImage($request->file('cover_image'));
        }

        $wasWarningOrHidden = in_array($editor->status, ['warning', 'hidden'], true);

        $editor->update([
            'title' => $sanitizedTitle,
            'content' => $contentData,
            'cover_image' => $coverPath,
            'category' => $sanitizedCategory,
            'tools_used' => $sanitizedTools,
            'description' => $sanitizedDescription,
            'visibility' => $request->visibility ?: 'Everyone',
            'is_published' => $request->is_published ?? false,
            // Jika karya sebelumnya ber-warning atau hidden, ubah status ke pending untuk Re-Review Admin
            'status' => $wasWarningOrHidden ? 'pending' : ($editor->status ?? 'active'),
        ]);

        // Parse and sync tags
        $this->portfolioService->syncTags($editor, $sanitizedTags);

        // Jika karya sebelumnya bermasalah dan diperbarui user, buat laporan/flag Re-Review untuk Admin
        if ($wasWarningOrHidden) {
            PagiReport::create([
                'work_id' => $editor->id,
                'reporter_id' => Auth::id(),
                'reason' => 'other',
                'description' => '[Re-Review User] Pengguna telah memperbarui karya yang sebelumnya mendapat peringatan/takedown.',
                'status' => 'pending',
            ]);
        }

        PagiWorkUpdated::dispatch($editor);

        return redirect()->route('module.pagi.profile')->with('success', 'Portfolio updated successfully!');
    }

    public function destroy(PagiWork $editor)
    {
        $userId = $editor->user_id;
        if ($userId !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $workId = $editor->id;

        // Tandai laporan pending yang terkait dengan karya ini sebagai 'actioned' (Dihapus oleh pemilik karya)
        PagiReport::query()
            ->where('work_id', '=', $editor->id, 'and')
            ->where('status', '=', 'pending', 'and')
            ->update([
                'status' => 'actioned',
                'admin_note' => 'Karya dihapus sendiri oleh pemilik karya.',
                'reviewed_at' => now(),
            ]);

        $editor->delete();

        PagiWorkDeleted::dispatch($workId, $userId);

        Cache::forget("pagi_public_profile_{$userId}");
        Cache::forget("recent_notifs_{$userId}");

        return response()->json(['success' => true]);
    }

    public function acceptCollaboration(Request $request, PagiWork $editor)
    {
        $user = Auth::user();
        $content = $editor->content ?? [];

        // content might be an empty string in edge cases
        if (! is_array($content)) {
            $content = [];
        }

        // 1. Otorisasi: Verifikasi apakah user saat ini adalah kolaborator terundang
        if (! $this->checkCollaboratorInvitation($content, $user)) {
            return response()->json(['error' => 'Akses ditolak: Anda tidak diundang sebagai kolaborator di karya ini.'], 403);
        }

        $updated = false;

        foreach ($content as $key => $block) {
            if (! is_array($block)) {
                continue;
            }
            if (isset($block['type']) && $block['type'] === 'featured_details') {
                $collaborators = $block['collaborators'] ?? [];
                foreach ($collaborators as $cKey => $collab) {
                    if ($this->isCollaboratorMatch($collab, $user)) {
                        if (is_array($collab)) {
                            $collaborators[$cKey] = array_merge($collab, ['status' => 'accepted']);
                        } else {
                            $collaborators[$cKey] = [
                                'name' => $collab,
                                'status' => 'accepted',
                            ];
                        }
                        $updated = true;
                    }
                }
                $content[$key]['collaborators'] = $collaborators;
            }
        }

        if ($updated) {
            $editor->update(['content' => $content]);

            Cache::forget("pagi_public_profile_{$user->id}");
            if ($editor->user_id) {
                Cache::forget("pagi_public_profile_{$editor->user_id}");
            }

            // Mark notification as handled in DB for the user
            $userNotifs = $user->notifications()
                ->whereIn('data->type', ['collaboration', 'collaboration_invite'])
                ->where('data->portfolio_id', $editor->id)
                ->get();
            foreach ($userNotifs as $userNotif) {
                $nData = $userNotif->data;
                $nData['collaboration_status'] = 'accept';
                $nData['collaboration_handled'] = true;
                $userNotif->data = $nData;
                $userNotif->save();
            }

            // Notify the owner (inviter) that the collaborator accepted
            $owner = $editor->user;
            if ($owner && $owner->id !== $user->id) {
                $owner->notify(new PagiNotification(
                    'collaboration_accepted',
                    $user->pagi_username ?: $user->name,
                    'menerima ajakan kolaborasi pada proyek: "'.$editor->title.'"',
                    $user->foto_path ? (str_starts_with($user->foto_path, 'http') ? $user->foto_path : asset(self::STORAGE_PREFIX.$user->foto_path)) : null,
                    '/pagi/preview/'.$editor->id,
                    [
                        'portfolio_id' => $editor->id,
                        'sender_id' => $user->id,
                        'is_invite' => false,
                    ]
                ));
            }
        }

        return response()->json(['success' => true]);
    }

    public function declineCollaboration(Request $request, PagiWork $editor)
    {
        $user = Auth::user();
        $content = $editor->content ?? [];

        // content might be an empty string in edge cases
        if (! is_array($content)) {
            $content = [];
        }

        // 1. Otorisasi: Verifikasi apakah user saat ini adalah kolaborator terundang
        if (! $this->checkCollaboratorInvitation($content, $user)) {
            return response()->json(['error' => 'Akses ditolak: Anda tidak diundang sebagai kolaborator di karya ini.'], 403);
        }

        $updated = false;

        foreach ($content as $key => $block) {
            if (! is_array($block)) {
                continue;
            }
            if (isset($block['type']) && $block['type'] === 'featured_details') {
                $collaborators = $block['collaborators'] ?? [];
                $collaborators = array_values(array_filter($collaborators, function ($collab) use ($user, &$updated) {
                    if ($this->isCollaboratorMatch($collab, $user)) {
                        $updated = true;

                        return false;
                    }

                    return true;
                }));
                $content[$key]['collaborators'] = $collaborators;
            }
        }

        if ($updated) {
            $editor->update(['content' => $content]);

            Cache::forget("pagi_public_profile_{$user->id}");
            if ($editor->user_id) {
                Cache::forget("pagi_public_profile_{$editor->user_id}");
            }

            // Mark notification as declined in DB for the user
            $userNotifs = $user->notifications()
                ->whereIn('data->type', ['collaboration', 'collaboration_invite'])
                ->where('data->portfolio_id', $editor->id)
                ->get();
            foreach ($userNotifs as $userNotif) {
                $nData = $userNotif->data;
                $nData['collaboration_status'] = 'decline';
                $nData['collaboration_handled'] = true;
                $userNotif->data = $nData;
                $userNotif->save();
            }
        }

        return response()->json(['success' => true]);
    }

    public function leaveCollaboration(Request $request, PagiWork $editor)
    {
        return $this->declineCollaboration($request, $editor);
    }

    public function storeGalleryItem(StoreGalleryItemRequest $request)
    {
        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $this->portfolioService->saveGalleryCover($request->file('cover_image'));
        }

        $portfolio = PagiWork::query()->create([
            'user_id' => Auth::id(),
            'title' => $request->title ?: 'Untitled Gallery Item',
            'content' => [
                [
                    'type' => 'gallery_item',
                    'description' => $request->description,
                ],
            ],
            'cover_image' => $coverPath,
            'is_published' => true,
        ]);

        $mapped = [
            'id' => $portfolio->id,
            'user_id' => $portfolio->user_id,
            'title' => $portfolio->title,
            'image' => asset(self::STORAGE_PREFIX.$portfolio->cover_image),
            'content' => $portfolio->content,
            'created_at' => $portfolio->created_at->format(self::DATE_FORMAT_STANDARD),
            'likes' => 0,
            'liked' => false,
            'comments' => [],
            'views' => 0,
            'is_published' => true,
            'tools_used' => null,
            'description' => $request->description,
            'category' => null,
            'tags' => [],
            'resolved_collaborators' => [],
            'user' => [
                'id' => Auth::id(),
                'name' => Auth::user()->name,
                'pagi_username' => Auth::user()->pagi_username,
                'avatar' => Auth::user()->foto_path ? (str_starts_with(Auth::user()->foto_path, 'http') ? Auth::user()->foto_path : asset(self::STORAGE_PREFIX.Auth::user()->foto_path)) : null,
                'location' => Auth::user()->location ?? 'Banyumas, Indonesia',
            ],
        ];

        return response()->json([
            'success' => true,
            'project' => $mapped,
            'message' => 'Gallery item added successfully!',
        ]);
    }

    /**
     * Map block file preview URLs for frontend.
     */
    private function populateContentPreviews(mixed $content): array
    {
        if (is_string($content)) {
            $content = json_decode($content, true);
        }
        if (! is_array($content)) {
            return [];
        }

        foreach ($content as &$block) {
            if (isset($block['file_path'])) {
                $block['preview'] = asset(self::STORAGE_PREFIX.$block['file_path']);
            }
            if (isset($block['file_paths']) && is_array($block['file_paths'])) {
                $block['previews'] = array_map(function ($path) {
                    return asset(self::STORAGE_PREFIX.$path);
                }, $block['file_paths']);
            }
        }

        return $content;
    }

    /**
     * Update or append featured details block with new collaborators.
     */
    private function updateFeaturedDetailsCollaborators(array $content, array $newCollaborators): array
    {
        $hasFeaturedDetails = false;
        foreach ($content as &$block) {
            if (isset($block['type']) && $block['type'] === 'featured_details') {
                $block['collaborators'] = $newCollaborators;
                $hasFeaturedDetails = true;
                break;
            }
        }
        if (! $hasFeaturedDetails && ! empty($newCollaborators)) {
            $content[] = [
                'type' => 'featured_details',
                'collaborators' => $newCollaborators,
                'skills' => [],
                'tools' => [],
            ];
        }

        return $content;
    }

    /**
     * Verify if the user is invited as a collaborator.
     */
    private function checkCollaboratorInvitation(array $content, User $user): bool
    {
        foreach ($content as $block) {
            if (is_array($block) && isset($block['type']) && $block['type'] === 'featured_details') {
                $collaborators = $block['collaborators'] ?? [];
                foreach ($collaborators as $collab) {
                    if ($this->isCollaboratorMatch($collab, $user)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check if a collaborator item matches the specified user.
     */
    private function isCollaboratorMatch(mixed $collab, User $user): bool
    {
        if (is_array($collab)) {
            if (isset($collab['user_id']) && $collab['user_id']) {
                if ((int) $collab['user_id'] === (int) $user->id) {
                    return true;
                }
            }

            $cName = $collab['name'] ?? '';
            $clean = ltrim($cName, '@');

            if ($user->pagi_username && strcasecmp($clean, ltrim($user->pagi_username, '@')) === 0) {
                return true;
            }

            return strcasecmp($cName, $user->name) === 0 || strcasecmp($clean, $user->name) === 0;
        }

        $cName = (string) $collab;
        $clean = ltrim($cName, '@');

        if ($user->pagi_username && strcasecmp($clean, ltrim($user->pagi_username, '@')) === 0) {
            return true;
        }

        return strcasecmp($cName, $user->name) === 0 || strcasecmp($clean, $user->name) === 0;
    }

    /**
     * Check if the work contains a gallery item block.
     */
    private function isGalleryItem(PagiWork $work): bool
    {
        if (is_array($work->content)) {
            foreach ($work->content as $block) {
                if (isset($block['type']) && $block['type'] === 'gallery_item') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Handle updating of gallery item.
     */
    private function handleGalleryItemUpdate(QuickUpdatePortfolioRequest $request, PagiWork $editor): JsonResponse
    {
        $content = $editor->content;
        if (is_array($content)) {
            foreach ($content as &$block) {
                if (isset($block['type']) && $block['type'] === 'gallery_item') {
                    $block['description'] = $request->description;
                }
            }
        }

        $editor->update([
            'title' => $request->title,
            'content' => $content,
        ]);

        return response()->json([
            'success' => true,
            'project' => [
                'id' => $editor->id,
                'title' => $editor->title,
                'content' => $content,
            ],
            'message' => 'Gallery item updated successfully!',
        ]);
    }

    /**
     * Handle updating of standard portfolio item.
     */
    private function handleStandardItemUpdate(QuickUpdatePortfolioRequest $request, PagiWork $editor): JsonResponse
    {
        $coverPath = $editor->cover_image;
        if ($request->hasFile('cover_image')) {
            $coverPath = $this->portfolioService->saveCoverImage($request->file('cover_image'));
        }

        // Parse lists
        $skills = json_decode($request->input('skills', '[]'), true) ?: [];
        $tools = json_decode($request->input('tools', '[]'), true) ?: [];
        $collaborators = json_decode($request->input('collaborators', '[]'), true) ?: [];

        $existingDetails = collect($editor->content)->firstWhere('type', 'featured_details') ?: [];
        $existingCollabs = $existingDetails['collaborators'] ?? [];

        $newCollaborators = $this->portfolioService->processAndNotifyCollaborators($editor, $collaborators, $existingCollabs);

        // Save detailed structure into block format inside content column
        $contentData = [
            [
                'type' => 'featured_details',
                'skills' => $skills,
                'tools' => $tools,
                'completed_work_link' => $request->input('completed_work_link'),
                'collaborators' => $newCollaborators,
                'client' => $request->input('client'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'industry' => $request->input('industry'),
                'original_work_confirmed' => $request->input('original_work_confirmed') === 'true',
                'cover_fit' => $request->input('cover_fit', 'cover'),
            ],
        ];

        $editor->update([
            'title' => $request->title,
            'content' => $contentData,
            'cover_image' => $coverPath,
        ]);

        $mappedProject = [
            'id' => $editor->id,
            'title' => $editor->title ?? 'Untitled Project',
            'image' => $editor->cover_image ? (str_starts_with($editor->cover_image, 'http') ? $editor->cover_image : asset(self::STORAGE_PREFIX.$editor->cover_image)) : 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop',
            'content' => $contentData,
            'created_at' => $editor->created_at->format(self::DATE_FORMAT_STANDARD),
            'likes' => $editor->likes ?? 0,
            'liked' => false,
            'comments' => [],
            'views' => $editor->views ?? 0,
        ];

        return response()->json([
            'success' => true,
            'project' => $mappedProject,
            'message' => 'Karya berhasil diperbarui!',
        ]);
    }

    /**
     * Memeriksa dan mengeksekusi Auto Moderasi Konten (Teks & Gambar Cover via Google Vision AI)
     */
    private function checkAutoModeration(PagiWork $work): void
    {
        $moderationService = app(ContentModerationService::class);
        $textToScan = trim(($work->title ?? '').' '.($work->description ?? ''));

        // Scan teks (judul + deskripsi)
        $textScan = $moderationService->scan($textToScan);
        $imageScan = ['is_flagged' => false];

        // Scan gambar cover jika ada
        if (! empty($work->cover_image)) {
            $imagePath = storage_path('app/public/'.ltrim($work->cover_image, '/'));
            if (file_exists($imagePath)) {
                $imageScan = $moderationService->scanImage($imagePath, $textToScan);
            }
        }

        if ($textScan['is_flagged'] || $imageScan['is_flagged']) {
            $work->fill([
                'status' => 'review',
                'is_published' => false,
            ])->save();

            $reasons = array_merge($textScan['matched_words'] ?? [], $imageScan['matched_words'] ?? []);
            $reasonText = implode(', ', array_unique($reasons));

            PagiReport::create([
                'work_id' => $work->id,
                'reporter_id' => $work->user_id,
                'reason' => 'inappropriate_content',
                'description' => '[Auto Moderasi AI] Terdeteksi konten berisiko: '.($reasonText ?: 'Gambar / Teks Sensitif'),
                'status' => 'pending',
            ]);
        }
    }
}
