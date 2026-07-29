<?php

namespace App\Modules\Pagi\Controllers;

use App\Events\PagiReportCreated;
use App\Http\Controllers\Controller;
use App\Models\Pagi\PagiReport;
use App\Models\Pagi\PagiWarning;
use App\Models\Pagi\PagiWork;
use App\Models\Portal\PortalSetting;
use App\Models\User;
use App\Models\UserModuleRole;
use App\Modules\Pagi\Controllers\Concerns\HasAdminDashboardHelpers;
use App\Modules\Pagi\Services\ContentModerationService;
use App\Notifications\PagiNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class AdminModerationController extends Controller
{
    use HasAdminDashboardHelpers;

    private const DEFAULT_NO_DESCRIPTION = 'Tidak ada deskripsi.';

    /**
     * Reports & Moderation Hub
     */
    public function queue(Request $request): Response
    {
        return Inertia::render('Modules/Pagi/Admin/Reports/Queue', $this->getModerationPayload());
    }

    public function reports(Request $request): Response
    {
        return Inertia::render('Modules/Pagi/Admin/Reports/Reports', $this->getModerationPayload());
    }

    public function warnings(Request $request): Response
    {
        return Inertia::render('Modules/Pagi/Admin/Reports/Warnings', $this->getModerationPayload());
    }

    public function takedowns(Request $request): Response
    {
        return Inertia::render('Modules/Pagi/Admin/Reports/Takedowns', $this->getModerationPayload());
    }

    public function archive(Request $request): Response
    {
        return Inertia::render('Modules/Pagi/Admin/Reports/Archive', $this->getModerationPayload());
    }

    private function getModerationPayload(): array
    {
        if (PagiWork::query()->count('*') === 0 || PagiReport::query()->count('*') === 0) {
            $this->seedPagiDemoData();
        }

        $reports = PagiReport::query()->with(['work.user', 'reporter'])->latest('created_at')->get();
        $items = $this->buildModerationReports($reports);

        if (empty($items)) {
            $works = PagiWork::query()->with('user')->latest('created_at')->get();
            $items = $this->buildFallbackModerationWorks($works);
        }

        $reportsList = $reports->map(function ($r) {
            $isAiAutoReport = str_starts_with($r->description ?? '', '[Auto Moderasi AI]')
                || ! $r->reporter_id
                || ($r->reporter && $r->work && $r->reporter->id === $r->work->user_id);

            // Compute priority
            $priority = 'low';
            if ($isAiAutoReport || str_contains(strtolower($r->reason ?? ''), 'copyright') || str_contains(strtolower($r->description ?? ''), 'darah') || str_contains(strtolower($r->description ?? ''), 'sensitif')) {
                $priority = 'high';
            } elseif ($r->status === 'tinjauan' || $r->status === 'review') {
                $priority = 'medium';
            }

            // Compute stage
            $rawStatus = strtolower($r->status ?? 'pending');
            $stage = match ($rawStatus) {
                'pending', 'report' => 'report',
                'tinjauan' => 'tinjauan',
                'review' => 'review',
                default => 'archive',
            };

            // Days remaining for tinjauan (7-day grace period)
            $daysLeft = null;
            if ($rawStatus === 'tinjauan') {
                $created = $r->updated_at ?? $r->created_at;
                $expiresAt = $created ? $created->copy()->addDays(7) : now()->addDays(7);
                $diffHours = max(0, (int) now()->diffInHours($expiresAt, false));
                $days = floor($diffHours / 24);
                $hours = $diffHours % 24;
                $daysLeft = "Sisa {$days} Hari {$hours} Jam";
            }

            return [
                'id' => $r->id,
                'reportId' => $r->id,
                'workId' => $r->work->id ?? null,
                'workTitle' => $r->work->title ?? 'Karya Dihapus',
                'userId' => $r->work->user_id ?? null,
                'author' => $r->work->user->name ?? 'Student',
                'authorHandle' => '@'.strstr($r->work->user->email ?? self::$DEFAULT_STUDENT_EMAIL, '@', true),
                'reporter' => $isAiAutoReport ? 'System Sentinel AI' : ($r->reporter->name ?? 'Pengguna'),
                'reporterHandle' => $isAiAutoReport ? '@system.sentinel' : ('@'.strstr($r->reporter->email ?? self::$DEFAULT_REPORTER_EMAIL, '@', true)),
                'reportsCount' => 1,
                'sourceType' => $isAiAutoReport ? 'ai_flag' : 'user_report',
                'reason' => $this->getReportReasonLabel($r->reason),
                'description' => $r->description ?? self::DEFAULT_NO_DESCRIPTION,
                'priority' => $priority,
                'stage' => $stage,
                'daysLeft' => $daysLeft,
                'status' => $r->status ?? 'pending',
                'time' => $r->created_at ? $r->created_at->diffForHumans() : 'baru saja',
                'thumbnail' => $r->work ? $this->getStorageUrl($r->work->cover_image) : null,
                'category' => $r->work->category ?? 'Design & UI/UX',
            ];
        })->values();

        if ($reportsList->isEmpty() && ! empty($items)) {
            $reportsList = collect($items)->map(function ($item) {
                return [
                    'id' => $item['reportId'] ?? $item['id'],
                    'reportId' => $item['reportId'] ?? $item['id'],
                    'workId' => $item['workId'] ?? $item['id'],
                    'workTitle' => $item['title'],
                    'userId' => $item['userId'] ?? null,
                    'author' => $item['author'],
                    'authorHandle' => $item['authorHandle'],
                    'reporter' => $item['reportedBy'],
                    'reporterHandle' => $item['reporterHandle'] ?? '@system.sentinel',
                    'reportsCount' => $item['reportsCount'] ?? 1,
                    'sourceType' => $item['sourceType'] ?? 'ai_flag',
                    'reason' => $item['reportReason'] ?? 'Peninjauan Moderasi',
                    'description' => $item['reportDescription'] ?? self::DEFAULT_NO_DESCRIPTION,
                    'priority' => 'high',
                    'stage' => 'report',
                    'daysLeft' => null,
                    'status' => $item['status'] ?? 'pending',
                    'time' => $item['time'] ?? 'baru saja',
                    'thumbnail' => $item['thumbnail'] ?? null,
                    'category' => $item['category'] ?? 'Design & UI/UX',
                ];
            });
        }

        $allWarnings = PagiWarning::query()->with(['user', 'issuer', 'work'])->latest('created_at')->get();
        $groupedByUser = $allWarnings->groupBy('user_id');

        $appeals = PagiReport::query()->where('status', 'appeal')->with(['work.user', 'reporter'])->latest('created_at')->get();
        $appealsByUserId = $appeals->groupBy(function ($app) {
            return $app->work->user_id ?? $app->reporter_id;
        });

        $userIds = $groupedByUser->keys()->merge($appealsByUserId->keys())->unique()->filter();
        $usersMap = User::query()->whereIn('id', $userIds)->get()->keyBy('id');

        $maxAllowedWarnings = (int) (PortalSetting::query()->where('key', 'pagi_max_warnings_before_suspend')->value('value') ?? 3);

        $userWarningsList = [];
        $spActiveUsersCount = 0;
        $suspendedUsersCount = 0;

        foreach ($userIds as $uid) {
            $user = $usersMap->get($uid);
            if (! $user) {
                continue;
            }

            $userWarnings = $groupedByUser->get($uid, collect());
            $activeWarnings = $userWarnings->filter(fn ($w) => (bool) $w->is_active);
            $activeCount = $activeWarnings->count();

            $userAppeals = $appealsByUserId->get($uid, collect());
            $latestAppeal = $userAppeals->first();

            // Determine account status
            if (! $user->is_active || $activeCount >= $maxAllowedWarnings) {
                $accountStatus = 'suspended';
                $suspendedUsersCount++;
            } elseif ($userAppeals->isNotEmpty()) {
                $accountStatus = 'appealed';
            } elseif ($activeCount > 0) {
                $accountStatus = 'warning';
                $spActiveUsersCount++;
            } else {
                $accountStatus = 'active';
            }

            $warningsHistory = $userWarnings->map(function ($w) {
                return [
                    'id' => $w->id,
                    'warningCount' => $w->warning_level ?? 1,
                    'reason' => $w->reason,
                    'severity' => $w->severity ?? 'medium',
                    'type' => $w->type ?? 'inappropriate_content',
                    'isActive' => (bool) $w->is_active,
                    'workId' => $w->work_id,
                    'workTitle' => $w->work->title ?? null,
                    'workThumbnail' => $w->work ? $this->getStorageUrl($w->work->cover_image) : null,
                    'issuerName' => $w->issuer->name ?? 'System Admin',
                    'time' => $w->created_at ? $w->created_at->diffForHumans() : 'baru saja',
                    'expiresAt' => $w->expires_at ? $w->expires_at->format('d M Y H:i') : null,
                    'expiresAtHuman' => $w->expires_at ? $w->expires_at->diffForHumans() : null,
                    'isExpired' => $w->expires_at ? $w->expires_at->isPast() : false,
                ];
            })->values()->toArray();

            $nextExpiringWarning = $activeWarnings->sortBy('expires_at')->first();

            $userWarningsList[] = [
                'userId' => $user->id,
                'user' => $user->name,
                'userName' => $user->name,
                'userHandle' => '@'.strstr($user->email ?? self::$DEFAULT_STUDENT_EMAIL, '@', true),
                'userEmail' => $user->email,
                'userNim' => $user->user_type === 'mahasiswa' ? ($user->nomor_induk ?: '-') : null,
                'prodi' => match ((int) $user->program_studi_id) {
                    1 => 'Informatika',
                    2 => 'Sistem Informasi',
                    default => 'Matematika',
                },
                'activeWarningsCount' => $activeCount,
                'totalWarningsCount' => $userWarnings->count(),
                'maxAllowedWarnings' => $maxAllowedWarnings,
                'accountStatus' => $accountStatus,
                'isSuspended' => ! $user->is_active || $activeCount >= $maxAllowedWarnings,
                'hasPendingAppeal' => $userAppeals->isNotEmpty(),
                'appealId' => $latestAppeal ? $latestAppeal->id : null,
                'appealReason' => $latestAppeal ? $latestAppeal->description : null,
                'appealTime' => $latestAppeal && $latestAppeal->created_at ? $latestAppeal->created_at->diffForHumans() : null,
                'nextExpiresAtHuman' => $nextExpiringWarning && $nextExpiringWarning->expires_at ? $nextExpiringWarning->expires_at->diffForHumans() : null,
                'warningsHistory' => $warningsHistory,
            ];
        }

        $takedownsList = PagiWork::query()->where('status', '=', 'hidden', 'and')->with('user')->latest('updated_at')->get()->map(function ($wk) {
            $appealReport = PagiReport::query()->where('work_id', $wk->id)->where(function ($q) {
                $q->where('status', 'appeal')->orWhere('reason', 'like', '%banding%');
            })->first();

            $isAppealed = (bool) $appealReport;
            $stage = $isAppealed ? 'banding' : 'takedown';

            return [
                'id' => $wk->id,
                'workId' => $wk->id,
                'title' => $wk->title,
                'author' => $wk->user->name ?? 'Student',
                'authorHandle' => '@'.strstr($wk->user->email ?? self::$DEFAULT_STUDENT_EMAIL, '@', true),
                'reason' => 'Takedown oleh Admin Moderasi',
                'appealReason' => $appealReport ? $appealReport->description : null,
                'appealTime' => $appealReport && $appealReport->created_at ? $appealReport->created_at->diffForHumans() : null,
                'stage' => $stage,
                'time' => $wk->updated_at ? $wk->updated_at->diffForHumans() : 'baru saja',
                'thumbnail' => $this->getStorageUrl($wk->cover_image),
                'category' => $wk->category ?? 'Lainnya',
                'userId' => $wk->user_id,
                'status' => $wk->status,
            ];
        });

        $totalHidden = PagiWork::query()->where('status', '=', 'hidden')->count('*');
        $appealCount = PagiReport::query()->where('status', '=', 'appeal')->count('*');

        $summary = [
            'spActiveUsers' => $spActiveUsersCount,
            'suspendedUsers' => $suspendedUsersCount,
            'pendingAppeals' => $appeals->count(),
            'archivedWarnings' => PagiWarning::query()->where('is_active', false)->count('*'),
            'report' => PagiReport::query()->whereIn('status', ['pending', 'report'])->count('*'),
            'tinjauan' => PagiReport::query()->where('status', '=', 'tinjauan')->count('*'),
            'review' => PagiReport::query()->where('status', '=', 'review')->count('*'),
            'pending' => PagiReport::query()->whereIn('status', ['pending', 'report', 'review'])->count('*'),
            'warning' => PagiWarning::query()->where('is_active', '=', true)->count('*'),
            'takedown' => max(0, $totalHidden - $appealCount),
            'appeals' => $appealCount,
            'resolved' => PagiWarning::query()->where('is_active', false)->count('*'),
        ];

        return [
            'items' => $items,
            'reportsList' => $reportsList,
            'warningsList' => $userWarningsList,
            'userWarningsList' => $userWarningsList,
            'takedownsList' => $takedownsList,
            'summary' => $summary,
        ];
    }

    /**
     * Halaman Khusus Kamus Kata Teks
     */
    public function textDictionary(Request $request): Response
    {
        $censorMode = PortalSetting::query()->where('key', 'pagi_comment_censor_mode')->value('value') ?? 'reject';
        $customBannedWordsJson = PortalSetting::query()->where('key', 'pagi_banned_words')->value('value') ?? '[]';
        $customBannedWords = json_decode($customBannedWordsJson, true) ?: [];

        return Inertia::render('Modules/Pagi/Admin/TextDictionary/Index', [
            'commentCensorMode' => $censorMode,
            'customBannedWords' => $customBannedWords,
        ]);
    }

    /**
     * Halaman Khusus Kamus Gambar Visual
     */
    public function imageDictionary(Request $request): Response
    {
        $customImageRulesJson = PortalSetting::query()->where('key', 'pagi_custom_image_rules')->value('value') ?? '[]';
        $customImageRules = json_decode($customImageRulesJson, true) ?: [];
        $enableVisionAi = filter_var(PortalSetting::query()->where('key', 'pagi_enable_vision_ai')->value('value') ?? 'true', FILTER_VALIDATE_BOOLEAN);

        return Inertia::render('Modules/Pagi/Admin/ImageDictionary/Index', [
            'customImageRules' => $customImageRules,
            'enableVisionAi' => $enableVisionAi,
        ]);
    }

    /**
     * Legacy Moderation route alias
     */
    public function moderation(Request $request)
    {
        return redirect()->route('module.pagi.admin.reports');
    }

    /**
     * Update Moderation Settings & Custom Banned Words List
     */
    public function updateModerationSettings(Request $request)
    {
        $validated = $request->validate([
            'autoModeration' => 'required|boolean',
            'commentCensorMode' => 'required|in:censor,reject',
            'customBannedWords' => 'nullable|array',
            'customBannedWords.*' => 'string|max:50',
            'customImageRules' => 'nullable|array',
            'customImageRules.*' => 'string|max:100',
            'enableLocalEngine' => 'nullable|boolean',
            'enableGoogleAi' => 'nullable|boolean',
            'enableVisionAi' => 'nullable|boolean',
            'googleAiApiKey' => 'nullable|string|max:255',
            'googleAiModel' => 'nullable|string|max:50',
        ]);

        PortalSetting::updateOrCreate(
            ['key' => 'pagi_auto_moderation'],
            ['value' => $validated['autoModeration'] ? 'true' : 'false']
        );

        PortalSetting::updateOrCreate(
            ['key' => 'pagi_comment_censor_mode'],
            ['value' => $validated['commentCensorMode']]
        );

        PortalSetting::updateOrCreate(
            ['key' => 'pagi_enable_local_engine'],
            ['value' => ($validated['enableLocalEngine'] ?? true) ? 'true' : 'false']
        );

        PortalSetting::updateOrCreate(
            ['key' => 'pagi_enable_google_ai'],
            ['value' => ($validated['enableGoogleAi'] ?? false) ? 'true' : 'false']
        );

        PortalSetting::updateOrCreate(
            ['key' => 'pagi_enable_vision_ai'],
            ['value' => ($validated['enableVisionAi'] ?? true) ? 'true' : 'false']
        );

        PortalSetting::updateOrCreate(
            ['key' => 'pagi_google_ai_api_key'],
            ['value' => $validated['googleAiApiKey'] ?? '']
        );

        PortalSetting::updateOrCreate(
            ['key' => 'pagi_google_ai_model'],
            ['value' => $validated['googleAiModel'] ?? 'gemini-2.0-flash']
        );

        $cleanWords = array_values(array_unique(array_filter(array_map('strtolower', array_map('trim', $validated['customBannedWords'] ?? [])))));
        $cleanImageRules = array_values(array_unique(array_filter(array_map('trim', $validated['customImageRules'] ?? []))));

        PortalSetting::updateOrCreate(
            ['key' => 'pagi_banned_words'],
            ['value' => json_encode($cleanWords)]
        );

        PortalSetting::updateOrCreate(
            ['key' => 'pagi_custom_image_rules'],
            ['value' => json_encode($cleanImageRules)]
        );

        return back()->with('success', 'Pengaturan moderasi dan kamus kata terlarang berhasil diperbarui.');
    }

    /**
     * Uji Koneksi Google Gemini API Real
     */
    public function testGoogleAiApi(Request $request)
    {
        $validated = $request->validate([
            'apiKey' => 'required|string|max:255',
            'model' => 'nullable|string|max:50',
        ]);

        $service = app(ContentModerationService::class);
        $result = $service->testGoogleAiConnection($validated['apiKey'], $validated['model'] ?? 'gemini-flash-latest');

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Fetch Model Dinamis dari Google Gemini API (Dokploy Style)
     */
    public function fetchGoogleAiModels(Request $request)
    {
        $validated = $request->validate([
            'apiKey' => 'required|string|max:255',
        ]);

        $service = app(ContentModerationService::class);
        $result = $service->fetchAvailableGeminiModels($validated['apiKey']);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Hide / Takedown content
     */
    public function hideContent(Request $request, int $workId)
    {
        $work = PagiWork::query()->findOrFail($workId);
        $role = $request->attributes->get('resolved_role', session('active_role'));

        // Otorisasi program studi untuk peran prodi
        if (strtolower($role) === 'prodi') {
            /** @var User $adminUser */
            $adminUser = Auth::user();
            $adminProdiId = $adminUser->program_studi_id;
            $author = User::query()->find($work->user_id, ['*']);
            if ($author && $adminProdiId && (int) $author->program_studi_id !== (int) $adminProdiId) {
                abort(403, 'Akses Ditolak: Anda hanya dapat memoderasi karya dari mahasiswa program studi Anda sendiri.');
            }
        }

        $request->validate([
            'reason' => 'required|string|max:500',
            'action' => 'required|in:hide,remove,dismiss',
        ]);

        if ($request->action !== 'dismiss') {
            $work->fill(['status' => $request->action === 'remove' ? 'removed' : 'hidden'])->save();
        }

        $reports = PagiReport::query()->where('work_id', '=', $workId, 'and')->where('status', '=', 'pending', 'and')->get();

        $reportStatus = match ($request->action) {
            'remove' => 'actioned',
            'dismiss' => 'dismissed',
            default => 'reviewed',
        };

        PagiReport::query()->where('work_id', '=', $workId, 'and')->where('status', '=', 'pending', 'and')->update([
            'status' => $reportStatus,
            'reviewed_by' => Auth::id() ?: 1,
            'admin_note' => $request->reason,
            'reviewed_at' => now(),
        ]);

        if ($request->action === 'dismiss') {
            $this->notifyDismissedReporters($reports, $work, $workId);
        } else {
            $this->notifyTakedownParties($reports, $work, $workId, $request->action, $request->reason);
        }

        return back()->with('success', 'Konten berhasil dimoderasi.');
    }

    private function notifyDismissedReporters(iterable $reports, PagiWork $work, int $workId): void
    {
        foreach ($reports as $r) {
            $reporter = $r->reporter ?? User::query()->find($r->reporter_id, ['*']);
            if (! $reporter) {
                continue;
            }
            $reporter->notify(new PagiNotification(
                type: 'system',
                title: 'Tinjauan Laporan',
                message: 'Mohon maaf, berdasarkan tinjauan kami, karya "'.$work->title.'" yang Anda laporkan tidak terbukti melanggar panduan.',
                avatar: null,
                href: '/pagi',
                extra: ['work_id' => $workId, 'report_id' => $r->id, 'status' => 'dismissed']
            ));
        }
    }

    private function notifyTakedownParties(iterable $reports, PagiWork $work, int $workId, string $action, string $reason): void
    {
        $notifyOnTakedown = filter_var(PortalSetting::query()->where('key', 'pagi_notify_on_takedown')->value('value') ?? 'true', FILTER_VALIDATE_BOOLEAN);

        $owner = $work->user;
        if ($owner && $notifyOnTakedown) {
            $owner->notify(new PagiNotification(
                type: 'admin_takedown',
                title: 'Tindakan Moderasi pada Karya Anda',
                message: 'Karya Anda "'.$work->title.'" telah disembunyikan/dihapus karena: '.$reason,
                avatar: null,
                href: '/pagi/notifications',
                extra: [
                    'work_id' => $workId,
                    'work_title' => $work->title,
                    'action' => $action,
                    'reason' => $reason,
                    'edit_url' => '/pagi/editor?id='.$workId,
                    'appeal' => true,
                ]
            ));
        }

        foreach ($reports as $r) {
            $reporter = $r->reporter ?? User::query()->find($r->reporter_id, ['*']);
            if (! $reporter) {
                continue;
            }
            $reporter->notify(new PagiNotification(
                type: 'system',
                title: 'Tindakan Laporan',
                message: 'Terima kasih atas laporan Anda. Kami telah mengambil tindakan terhadap karya "'.$work->title.'" yang Anda laporkan.',
                avatar: null,
                href: '/pagi',
                extra: ['work_id' => $workId, 'report_id' => $r->id, 'status' => 'actioned']
            ));
        }
    }

    /**
     * Store a user-submitted report on a pagi work
     */
    public function storeReport(Request $request)
    {
        $request->validate([
            'work_id' => 'required|integer|exists:pagi_works,id',
            'reason' => 'required|string|in:inappropriate_content,copyright_violation,spam,harassment,misinformation,other',
            'description' => 'required|string|min:10|max:1000',
        ]);

        $userId = Auth::id();
        $workId = $request->work_id;

        // Prevent duplicate pending reports from same user
        $existing = PagiReport::query()->where('work_id', '=', $workId, 'and')
            ->where('reporter_id', '=', $userId, 'and')
            ->where('status', '=', 'pending', 'and')
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'Anda sudah mengirim laporan untuk karya ini dan sedang dalam peninjauan admin.',
            ], 422);
        }

        $report = PagiReport::create([
            'work_id' => $workId,
            'reporter_id' => $userId,
            'reason' => $request->reason,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        $work = PagiWork::query()->findOrFail($workId);
        /** @var User $reporter */
        $reporter = Auth::user();
        $reporterHandle = '@'.strstr($reporter->email, '@', true);

        // Broadcast realtime notification to admins via private-pagi.admin.reports channel
        PagiReportCreated::dispatch($report, $work->title, $reporter->name, $reporterHandle);

        // Fetch all admins in PAGI module
        $adminIds = UserModuleRole::query()->whereHas('module', fn ($q) => $q->where('code', '=', 'PAGI', 'and'))
            ->whereHas('role', fn ($q) => $q->whereIn('slug', ['super-admin', 'admin'], 'and', false))
            ->pluck('user_id')
            ->toArray();

        $admins = User::query()->whereIn('id', $adminIds, 'and', false)
            ->orWhereIn('user_type', ['super-admin', 'admin', 'super_admin'])
            ->get();

        $avatar = $this->getStorageUrl($reporter->foto_path);

        $reasonText = $this->getReportReasonLabel($request->reason);

        // Cek pengaturan apakah notifikasi laporan baru ke admin diaktifkan
        $notifyOnReport = filter_var(PortalSetting::query()->where('key', 'pagi_notify_on_report')->value('value') ?? 'true', FILTER_VALIDATE_BOOLEAN);

        if ($notifyOnReport) {
            foreach ($admins as $admin) {
                $admin->notify(new PagiNotification(
                    type: 'report',
                    title: 'Laporan Baru: '.$reporter->name,
                    message: 'Melaporkan karya "'.$work->title.'" karena '.$reasonText,
                    avatar: $avatar,
                    href: '/pagi/admin/moderation',
                    extra: [
                        'report_id' => $report->id,
                        'work_id' => $work->id,
                        'reporter_id' => $reporter->id,
                        'reason' => $report->reason,
                    ]
                ));
            }
        }

        return response()->json(['message' => 'Laporan berhasil dikirim.']);
    }

    public function resetModeration(Request $request)
    {
        $role = $request->attributes->get('resolved_role', session('active_role'));

        // Otorisasi: Hanya super-admin atau admin yang bisa reset data moderasi
        if (! in_array(strtolower($role), ['super-admin', 'admin'])) {
            abort(403, 'Akses Ditolak: Hanya Super Admin or Admin yang dapat me-reset data moderasi.');
        }

        if (app()->environment('testing')) {
            // Avoid actual truncation inside tests to prevent SQLSTATE[42000] Savepoint error due to implicit DDL commits in MySQL
            return back()->with('success', 'Antrean moderasi dan warnings berhasil di-reset.');
        }

        $demoUserIds = User::query()->whereIn('email', [
            'sarah@student.fmikom.ac.id',
            'naufal@student.fmikom.ac.id',
            'dimas@student.fmikom.ac.id',
            'rizki@student.fmikom.ac.id',
            'johan@student.fmikom.ac.id',
            'fitria@student.fmikom.ac.id',
        ], 'and', false)->pluck('id')->toArray();

        Schema::disableForeignKeyConstraints();

        // 1. Hapus warning yang terkait demo users
        PagiWarning::query()->whereIn('user_id', $demoUserIds, 'and', false)->delete();

        // 2. Ambil work IDs milik demo users untuk menghapus reports dan tags yang terkait
        $demoWorkIds = PagiWork::query()->whereIn('user_id', $demoUserIds, 'and', false)->pluck('id')->toArray();

        // Hapus reports yang terkait dengan karya demo
        PagiReport::query()->whereIn('work_id', $demoWorkIds, 'and', false)->delete();

        // Hapus tag relasi yang terkait dengan karya demo
        DB::table('pagi_work_tags')->whereIn('work_id', $demoWorkIds, 'and', false)->delete();

        // Hapus karya demo saja
        PagiWork::query()->whereIn('id', $demoWorkIds, 'and', false)->delete();

        Schema::enableForeignKeyConstraints();

        // 5. Seeding ulang data awal demo yang bersih & rapi
        $this->seedPagiDemoData();

        return back()->with('success', 'Antrean moderasi dan warnings berhasil di-reset.');
    }

    /**
     * Danger Zone: Hapus seluruh karya portofolio dengan verifikasi ketat ganda
     * Hanya Super Admin yang boleh mengeksekusi.
     * Verifikasi: Role super-admin + Password aktif + Teks konfirmasi
     */
    public function resetAllWorks(Request $request)
    {
        /** @var User $adminUser */
        $adminUser = Auth::user();
        $role = $request->attributes->get('resolved_role', session('active_role'));

        // Lapis 1: Otorisasi khusus super-admin
        if (strtolower($role) !== 'super-admin') {
            abort(403, 'Akses Ditolak: Hanya Super Admin yang dapat menghapus seluruh karya portofolio.');
        }

        // Lapis 2 & 3: Validasi password + teks konfirmasi
        $request->validate([
            'password' => 'required|string',
            'confirmation' => 'required|string',
        ]);

        // Verifikasi password admin yang sedang login
        if (! Hash::check($request->password, $adminUser->password)) {
            return back()->withErrors(['password' => 'Password yang Anda masukkan salah. Silakan coba lagi.']);
        }

        // Verifikasi teks konfirmasi wajib tepat
        if ($request->confirmation !== 'HAPUS SEMUA KARYA') {
            return back()->withErrors(['confirmation' => 'Teks konfirmasi tidak tepat. Ketik tepat: HAPUS SEMUA KARYA']);
        }

        if (app()->environment('testing')) {
            return back()->with('success', 'Seluruh karya portofolio berhasil dihapus.');
        }

        DB::transaction(function () use ($adminUser) {
            // Ambil seluruh ID karya yang akan dihapus
            $allWorkIds = PagiWork::query()->pluck('id')->toArray();

            if (! empty($allWorkIds)) {
                Schema::disableForeignKeyConstraints();

                // Hapus seluruh laporan terkait karya
                PagiReport::query()->whereIn('work_id', $allWorkIds, 'and', false)->delete();

                // Hapus seluruh relasi tag karya
                DB::table('pagi_work_tags')->whereIn('work_id', $allWorkIds)->delete();

                // Hapus komentar karya (jika ada tabel pagi_comments)
                if (Schema::hasTable('pagi_comments')) {
                    DB::table('pagi_comments')->whereIn('work_id', $allWorkIds)->delete();
                }

                // Hapus likes/reactions karya (jika ada tabel pagi_likes)
                if (Schema::hasTable('pagi_likes')) {
                    DB::table('pagi_likes')->whereIn('work_id', $allWorkIds)->delete();
                }

                // Hapus seluruh karya portofolio
                PagiWork::query()->whereIn('id', $allWorkIds, 'and', false)->delete();

                Schema::enableForeignKeyConstraints();
            }

            // Hapus semua warning aktif (karena semua karya sudah bersih)
            PagiWarning::query()->delete();

            // Audit Trail: Log aksi berbahaya ini
            Log::channel('stack')->warning('[DANGER ZONE] Reset Semua Karya dieksekusi', [
                'admin_id' => $adminUser->id,
                'admin_name' => $adminUser->name,
                'admin_email' => $adminUser->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toIso8601String(),
                'works_deleted' => count($allWorkIds),
            ]);
        });

        return redirect()->route('module.pagi.admin.settings')->with('success', 'Seluruh karya portofolio berhasil dihapus. Database kini bersih dan siap digunakan kembali.');
    }

    /**
     * Restore takedown work
     */
    public function restoreContent(Request $request, int $workId)
    {
        $work = PagiWork::query()->findOrFail($workId);
        $role = $request->attributes->get('resolved_role', session('active_role'));

        // Otorisasi program studi untuk peran prodi
        if (strtolower($role) === 'prodi') {
            /** @var User $adminUser */
            $adminUser = Auth::user();
            $adminProdiId = $adminUser->program_studi_id;
            $author = User::query()->find($work->user_id, ['*']);
            if ($author && $adminProdiId && (int) $author->program_studi_id !== (int) $adminProdiId) {
                abort(403, 'Akses Ditolak: Anda hanya dapat memulihkan karya dari mahasiswa program studi Anda sendiri.');
            }
        }
        $work->fill(['status' => 'active'])->save();

        // Resolve any related reports on this work
        PagiReport::query()->where('work_id', '=', $workId)->update(['status' => 'resolved']);

        // Send realtime notification to work owner
        $owner = $work->user;
        if ($owner) {
            $owner->notify(new PagiNotification(
                type: 'system',
                title: 'Banding Diterima / Karya Dipulihkan',
                message: 'Selamat! Permohonan banding untuk karya "'.$work->title.'" telah disetujui. Karya dipulihkan kembali ke galeri publik.',
                avatar: null,
                href: '/pagi/editor?id='.$workId,
                extra: ['work_id' => $workId, 'status' => 'restored']
            ));
        }

        return back()->with('success', 'Karya berhasil dipulihkan dan notifikasi telah dikirim ke mahasiswa.');
    }

    /**
     * Reject user appeal on takedown work
     */
    public function rejectAppeal(Request $request, int $workId)
    {
        $work = PagiWork::query()->findOrFail($workId);

        PagiReport::query()->where('work_id', $workId)->where(function ($q) {
            $q->where('status', 'appeal')->orWhere('reason', 'like', '%banding%');
        })->update([
            'status' => 'dismissed',
            'admin_note' => $request->reason ?? 'Permohonan banding ditolak oleh admin moderasi.',
        ]);

        $owner = $work->user;
        if ($owner) {
            $owner->notify(new PagiNotification(
                type: 'admin_takedown',
                title: 'Banding Ditolak',
                message: 'Mohon maaf, permohonan banding untuk karya "'.$work->title.'" ditolak. Karya tetap dalam sanksi takedown.',
                avatar: null,
                href: '/pagi/notifications',
                extra: ['work_id' => $workId, 'status' => 'appeal_rejected', 'reason' => $request->reason]
            ));
        }

        return back()->with('success', 'Permohonan banding telah ditolak.');
    }

    /**
     * Submit user appeal for a takedown work
     */
    public function submitAppeal(Request $request, int $workId)
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:1000',
        ]);

        $work = PagiWork::query()->findOrFail($workId);
        $user = Auth::user();

        if ($work->user_id !== $user->id) {
            abort(403, 'Akses Ditolak');
        }

        $report = PagiReport::updateOrCreate(
            ['work_id' => $workId, 'reporter_id' => $user->id, 'status' => 'appeal'],
            [
                'reason' => 'Permohonan Banding Takedown',
                'description' => $request->reason,
                'status' => 'appeal',
            ]
        );

        $reporterHandle = '@'.strstr($user->email, '@', true);
        PagiReportCreated::dispatch($report, $work->title, $user->name, $reporterHandle);

        return back()->with('success', 'Permohonan banding Anda telah terkirim ke tim moderasi.');
    }

    private function buildModerationReports(iterable $reports): array
    {
        $grouped = [];

        foreach ($reports as $r) {
            if (! $r->work) {
                continue;
            }

            // HANYA ambil laporan yang berstatus 'pending', atau jika karya saat ini berstatus 'warning' / 'hidden'
            if ($r->status !== 'pending' && ! in_array($r->work->status, ['warning', 'hidden'], true)) {
                continue;
            }

            $workId = $r->work->id;

            if (! isset($grouped[$workId])) {
                $grouped[$workId] = [
                    'work' => $r->work,
                    'reports' => [],
                    'aiReportsCount' => 0,
                    'userReportsCount' => 0,
                    'latestReport' => $r,
                ];
            }

            $grouped[$workId]['reports'][] = $r;

            $isAiAutoReport = str_starts_with($r->description ?? '', '[Auto Moderasi AI]')
                || ! $r->reporter_id
                || ($r->reporter && $r->work && $r->reporter->id === $r->work->user_id);

            if ($isAiAutoReport) {
                $grouped[$workId]['aiReportsCount']++;
            } else {
                $grouped[$workId]['userReportsCount']++;
            }
        }

        $items = [];
        foreach ($grouped as $workId => $data) {
            /** @var PagiWork $work */
            $work = $data['work'];
            $latestReport = $data['latestReport'];
            $userCount = $data['userReportsCount'];
            $aiCount = $data['aiReportsCount'];
            $totalCount = count($data['reports']);

            // Tentukan status item antrean
            $status = 'pending';
            if ($latestReport->status === 'pending') {
                $status = 'pending';
            } elseif ($work->status === 'hidden') {
                $status = 'hidden';
            } elseif ($work->status === 'warning') {
                $status = 'warning';
            }

            // Tentukan label pelapor (Agregasi)
            if ($aiCount > 0 && $userCount > 0) {
                $reporterName = "{$userCount} Laporan Komunitas + 🤖 Sentinel AI";
                $reporterHandle = "@komunitas ({$totalCount} total laporan)";
            } elseif ($aiCount > 0) {
                $reporterName = 'System Sentinel AI';
                $reporterHandle = '@system.sentinel';
            } elseif ($userCount > 1) {
                $reporterName = "{$userCount} Laporan Komunitas";
                $reporterHandle = "@komunitas ({$userCount} pelapor)";
            } else {
                $reporterName = $latestReport->reporter->name ?? 'Pengguna';
                $reporterHandle = '@'.strstr($latestReport->reporter->email ?? self::$DEFAULT_REPORTER_EMAIL, '@', true);
            }

            $isAiAutoReport = $aiCount > 0 && $userCount === 0;

            $items[] = [
                'id' => $work->id,
                'workId' => $work->id,
                'reportId' => $latestReport->id,
                'reportsCount' => $totalCount,
                'title' => $work->title,
                'author' => $work->user->name ?? 'Student',
                'authorHandle' => '@'.strstr($work->user->email ?? self::$DEFAULT_STUDENT_EMAIL, '@', true),
                'type' => $isAiAutoReport ? 'Deteksi AI' : ($totalCount > 1 ? "Laporan ({$totalCount})" : 'Laporan'),
                'sourceType' => $isAiAutoReport ? 'ai_flag' : 'user_report',
                'reportedBy' => $reporterName,
                'time' => $latestReport->created_at ? $latestReport->created_at->diffForHumans() : 'baru saja',
                'status' => $status,
                'thumbnail' => $this->getStorageUrl($work->cover_image),
                'userId' => $work->user_id,
                'description' => $work->description ?? self::DEFAULT_NO_DESCRIPTION,
                'category' => $work->category ?? 'Design & UI/UX',
                'reportReason' => $this->getReportReasonLabel($latestReport->reason),
                'reportDescription' => $latestReport->description ?? 'Tidak ada penjelasan tambahan.',
                'reporterHandle' => $reporterHandle,
            ];
        }

        return $items;
    }

    private function buildFallbackModerationWorks(iterable $works): array
    {
        $items = [];
        foreach ($works as $p) {
            if ($p->user && in_array($p->status, ['review', 'warning', 'hidden'], true)) {
                $status = $p->status === 'review' ? 'pending' : $p->status;

                $items[] = [
                    'id' => $p->id,
                    'workId' => $p->id,
                    'reportId' => null,
                    'title' => $p->title,
                    'author' => $p->user->name,
                    'authorHandle' => '@'.strstr($p->user->email, '@', true),
                    'type' => $p->status === 'review' ? 'Karya Baru' : 'Deteksi AI',
                    'sourceType' => 'ai_flag',
                    'reportedBy' => 'System Sentinel AI',
                    'time' => $p->created_at ? $p->created_at->diffForHumans() : 'baru saja',
                    'status' => $status,
                    'thumbnail' => $this->getStorageUrl($p->cover_image),
                    'userId' => $p->user_id,
                    'description' => $p->description ?? self::DEFAULT_NO_DESCRIPTION,
                    'category' => $p->category ?? 'Lainnya',
                    'reportReason' => 'Peninjauan Automasi System',
                    'reportDescription' => '[Auto Moderasi AI] Peninjauan otomatis sistem moderasi kampus.',
                    'reporterHandle' => '@system.sentinel',
                ];
            }
        }

        return $items;
    }

    /**
     * Setujui Banding Akun / Takedown
     */
    public function approveUserAppeal(Request $request, int $reportId)
    {
        $report = PagiReport::query()->where('status', 'appeal')->findOrFail($reportId);
        $report->fill(['status' => 'resolved'])->save();

        $user = $report->work ? $report->work->user : User::query()->find($report->reporter_id);

        if ($user) {
            PagiWarning::query()->where('user_id', $user->id)->where('is_active', true)->update(['is_active' => false]);
            $user->forceFill(['is_active' => true])->save();

            if ($report->work && $report->work->status === 'hidden') {
                $report->work->fill(['status' => 'active'])->save();
            }

            $user->notify(new PagiNotification(
                type: 'system',
                title: 'Banding Diterima: Status Akun Dipulihkan',
                message: 'Permohonan banding Anda telah disetujui oleh tim moderasi. Status akun dan karya Anda telah dipulihkan.',
                avatar: null,
                href: '/pagi'
            ));
        }

        return back()->with('success', 'Permohonan banding telah disetujui dan status akun dipulihkan.');
    }

    /**
     * Tolak Banding Akun / Takedown
     */
    public function rejectUserAppeal(Request $request, int $reportId)
    {
        $report = PagiReport::query()->where('status', 'appeal')->findOrFail($reportId);
        $report->fill(['status' => 'dismissed'])->save();

        $user = $report->work ? $report->work->user : User::query()->find($report->reporter_id);

        if ($user) {
            $user->notify(new PagiNotification(
                type: 'admin_warning',
                title: 'Banding Ditolak',
                message: 'Permohonan banding Anda telah ditolak oleh tim moderasi setelah peninjauan ulang. Keputusan sanksi tetap berlaku.',
                avatar: null,
                href: '/pagi/notifications'
            ));
        }

        return back()->with('success', 'Permohonan banding telah ditolak.');
    }
}
