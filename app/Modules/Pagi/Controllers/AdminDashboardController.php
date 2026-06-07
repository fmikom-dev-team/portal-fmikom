<?php

namespace App\Modules\Pagi\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;
use App\Models\Pagi\PagiWork;
use App\Models\Pagi\PagiReport;
use App\Models\Pagi\PagiWarning;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use App\Events\PagiReportCreated;
use App\Notifications\PagiNotification;

/**
 * PAGI Admin Dashboard Controller
 *
 * Security: Semua route dilindungi oleh:
 * - auth middleware
 * - module.context:pagi middleware
 * - admin role check
 */
class AdminDashboardController extends Controller
{
    /**
     * Dashboard Overview
     * Menampilkan statistik ringkasan untuk admin
     */
    public function index(Request $request): Response
    {
        // Auto-seed real database data if pagi_works is empty to guarantee populated metrics
        if (PagiWork::count() === 0) {
            $this->seedPagiDemoData();
        }

        // 1. Real Stats from Database (with % change calculation)
        $stats = $this->buildStats();

        // 2. Real Moderation Summary from Database
        $moderationSummary = $this->buildModerationSummary();

        // 3. Real Recent Activities from Database
        $recentActivities = [];

        // Work publications
        $latestWorks = PagiWork::with('user')->latest()->take(3)->get();
        foreach ($latestWorks as $p) {
            if ($p->user) {
                $recentActivities[] = [
                    'id' => 'p_' . $p->id,
                    'type' => 'publish',
                    'title' => 'Karya baru dipublikasikan',
                    'description' => '"' . $p->title . '" oleh @' . strstr($p->user->email, '@', true),
                    'actor' => strstr($p->user->email, '@', true),
                    'avatar' => $this->getStorageUrl($p->user->foto_path),
                    'time' => $p->created_at->diffForHumans(),
                    'timestamp' => $p->created_at->timestamp,
                ];
            }
        }

        // Reports submitted
        $latestReports = PagiReport::with(['work', 'reporter'])->latest()->take(2)->get();
        foreach ($latestReports as $r) {
            if ($r->reporter && $r->work) {
                $recentActivities[] = [
                    'id' => 'r_' . $r->id,
                    'type' => 'report',
                    'title' => 'Laporan baru dari @' . strstr($r->reporter->email, '@', true),
                    'description' => 'Melaporkan karya "' . $r->work->title . '"',
                    'actor' => strstr($r->reporter->email, '@', true),
                    'avatar' => $this->getStorageUrl($r->reporter->foto_path),
                    'time' => $r->created_at->diffForHumans(),
                    'timestamp' => $r->created_at->timestamp,
                ];
            }
        }

        // Warnings issued
        $latestWarnings = PagiWarning::with('user')->latest()->take(2)->get();
        foreach ($latestWarnings as $w) {
            if ($w->user) {
                $recentActivities[] = [
                    'id' => 'w_' . $w->id,
                    'type' => 'warning',
                    'title' => 'Peringatan dikirim ke @' . strstr($w->user->email, '@', true),
                    'description' => 'Alasan: ' . $w->reason,
                    'actor' => strstr($w->user->email, '@', true),
                    'avatar' => $this->getStorageUrl($w->user->foto_path),
                    'time' => $w->created_at->diffForHumans(),
                    'timestamp' => $w->created_at->timestamp,
                ];
            }
        }

        // Sort by timestamp descending
        usort($recentActivities, function ($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        // Limit to 5
        $recentActivities = array_slice($recentActivities, 0, 5);

        // 4. Real Moderation Items from Database
        $moderationItems = [];
        $reports = PagiReport::with(['work.user', 'reporter'])->latest()->take(10)->get();
        foreach ($reports as $r) {
            if ($r->work) {
                $moderationItems[] = [
                    'id' => $r->work->id,
                    'title' => $r->work->title,
                    'author' => $r->work->user->name ?? 'Student',
                    'authorHandle' => '@' . strstr($r->work->user->email ?? self::DEFAULT_STUDENT_EMAIL, '@', true),
                    'type' => 'Laporan',
                    'reportedBy' => '@' . strstr($r->reporter->email ?? self::DEFAULT_REPORTER_EMAIL, '@', true),
                    'time' => $r->created_at->diffForHumans(),
                    'status' => $r->status === 'pending' ? 'pending' : ($r->work->status === 'hidden' ? 'hidden' : 'active'),
                    'thumbnail' => $this->getStorageUrl($r->work->cover_image),
                    'userId' => $r->work->user_id,
                    'description' => $r->work->description ?? 'Tidak ada deskripsi.',
                    'category' => $r->work->category ?? 'Design & UI/UX',
                    'reportReason' => $this->getReportReasonLabel($r->reason),
                    'reportDescription' => $r->description ?? 'Tidak ada penjelasan tambahan.',
                    'reporterHandle' => '@' . strstr($r->reporter->email ?? self::DEFAULT_REPORTER_EMAIL, '@', true),
                ];
            }
        }

        // If no reports exist, load review-status works as new items
        if (empty($moderationItems)) {
            $reviewWorks = PagiWork::with('user')->where('status', 'review')->latest()->take(10)->get();
            foreach ($reviewWorks as $p) {
                $moderationItems[] = [
                    'id' => $p->id,
                    'title' => $p->title,
                    'author' => $p->user->name ?? 'Student',
                    'authorHandle' => '@' . strstr($p->user->email ?? self::DEFAULT_STUDENT_EMAIL, '@', true),
                    'type' => 'Karya Baru',
                    'reportedBy' => '@' . strstr($p->user->email ?? self::DEFAULT_STUDENT_EMAIL, '@', true),
                    'time' => $p->created_at->diffForHumans(),
                    'status' => 'pending',
                    'thumbnail' => $this->getStorageUrl($p->cover_image),
                    'userId' => $p->user_id,
                    'description' => $p->description ?? 'Tidak ada deskripsi.',
                    'category' => $p->category ?? 'Lainnya',
                    'reportReason' => 'Peninjauan Karya Baru',
                    'reportDescription' => 'Karya baru dipublikasikan dan memerlukan persetujuan admin.',
                    'reporterHandle' => '@' . strstr($p->user->email ?? self::DEFAULT_STUDENT_EMAIL, '@', true),
                ];
            }
        }

        // 5. Real Popular Works ordered by views count
        $popularWorksRaw = PagiWork::with('user')
            ->where('is_published', true)
            ->orderBy('views_count', 'desc')
            ->take(5)
            ->get();

        $popularWorks = [];
        $rank = 1;
        foreach ($popularWorksRaw as $work) {
            if ($work->user) {
                $popularWorks[] = [
                    'id' => $work->id,
                    'rank' => $rank++,
                    'title' => $work->title,
                    'author' => '@' . strstr($work->user->email, '@', true),
                    'views' => $work->views_count,
                    'thumbnail' => $this->getStorageUrl($work->cover_image),
                ];
            }
        }

        return Inertia::render('Modules/Pagi/Admin/Dashboard', [
            'stats'              => $stats,
            'moderationSummary'  => $moderationSummary,
            'recentActivities'   => $recentActivities,
            'moderationItems'    => $moderationItems,
            'popularWorks'       => $popularWorks,
            'chartData'          => $this->buildChartData('7d'),
        ]);
    }

    /**
     * Moderation Queue
     */
    public function moderation(Request $request): Response
    {
        if (PagiWork::count() === 0) {
            $this->seedPagiDemoData();
        }

        $items = [];
        $reports = PagiReport::with(['work.user', 'reporter'])->latest()->get();
        foreach ($reports as $r) {
            if ($r->work) {
                $items[] = [
                    'id' => $r->work->id,
                    'title' => $r->work->title,
                    'author' => $r->work->user->name ?? 'Student',
                    'authorHandle' => '@' . strstr($r->work->user->email ?? self::DEFAULT_STUDENT_EMAIL, '@', true),
                    'type' => 'Laporan',
                    'reportedBy' => '@' . strstr($r->reporter->email ?? self::DEFAULT_REPORTER_EMAIL, '@', true),
                    'time' => $r->created_at->diffForHumans(),
                    'status' => $r->status === 'pending' ? 'pending' : ($r->work->status === 'hidden' ? 'hidden' : 'active'),
                    'thumbnail' => $this->getStorageUrl($r->work->cover_image),
                    'userId' => $r->work->user_id,
                    'description' => $r->work->description ?? 'Tidak ada deskripsi.',
                    'category' => $r->work->category ?? 'Design & UI/UX',
                    'reportReason' => $this->getReportReasonLabel($r->reason),
                    'reportDescription' => $r->description ?? 'Tidak ada penjelasan tambahan.',
                    'reporterHandle' => '@' . strstr($r->reporter->email ?? self::DEFAULT_REPORTER_EMAIL, '@', true),
                ];
            }
        }

        // Fallback to active/review works if no reports exist
        if (empty($items)) {
            $works = PagiWork::with('user')->latest()->get();
            foreach ($works as $p) {
                if ($p->user) {
                    $items[] = [
                        'id' => $p->id,
                        'title' => $p->title,
                        'author' => $p->user->name,
                        'authorHandle' => '@' . strstr($p->user->email, '@', true),
                        'type' => $p->status === 'review' ? 'Karya Baru' : 'Komentar',
                        'reportedBy' => '@' . strstr($p->user->email, '@', true),
                        'time' => $p->created_at->diffForHumans(),
                        'status' => $p->status === 'review' ? 'pending' : ($p->status === 'hidden' ? 'hidden' : 'active'),
                        'thumbnail' => $this->getStorageUrl($p->cover_image),
                        'userId' => $p->user_id,
                        'description' => $p->description ?? 'Tidak ada deskripsi.',
                        'category' => $p->category ?? 'Lainnya',
                        'reportReason' => 'Peninjauan Karya Baru',
                        'reportDescription' => 'Karya baru dipublikasikan and memerlukan persetujuan admin.',
                        'reporterHandle' => '@' . strstr($p->user->email, '@', true),
                    ];
                }
            }
        }

        // Summary badges counted dynamically
        $summary = [
            'pending'  => PagiReport::where('status', 'pending')->count(),
            'warning'  => PagiWarning::where('is_active', true)->count(),
            'takedown' => PagiWork::where('status', 'hidden')->count(),
            'resolved' => PagiReport::whereIn('status', ['reviewed', 'dismissed', 'actioned'])->count(),
        ];

        return Inertia::render('Modules/Pagi/Admin/Moderation/Index', [
            'items' => $items,
            'summary' => $summary,
        ]);
    }

    /**
     * Mahasiswa Management
     */
    public function mahasiswa(Request $request): Response
    {
        if (PagiWork::count() === 0) {
            $this->seedPagiDemoData();
        }

        $users = User::where('user_type', 'mahasiswa')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'handle' => '@' . strstr($u->email, '@', true),
                    'email' => $u->email,
                    'nim' => $u->nomor_induk ?: '-',
                    'prodi' => match ((int)$u->program_studi_id) {
                        1 => 'Informatika',
                        2 => 'Sistem Informasi',
                        default => 'Matematika',
                    },
                    'status' => PagiWarning::where('user_id', $u->id)->where('is_active', true)->exists() ? 'warning' : ($u->is_active ? 'active' : 'suspended'),
                    'karyaCount' => PagiWork::where('user_id', $u->id)->count(),
                    'joinDate' => $u->created_at->format('d M Y'),
                ];
            });

        return Inertia::render('Modules/Pagi/Admin/Users/Mahasiswa', [
            'users' => $users
        ]);
    }

    /**
     * Mitra Management
     */
    public function mitra(Request $request): Response
    {
        if (PagiWork::count() === 0) {
            $this->seedPagiDemoData();
        }

        $mitras = User::where('user_type', 'mitra')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'pic' => $u->metadata['pic'] ?? 'PIC Perusahaan',
                    'status' => $u->is_active ? 'active' : 'suspended',
                    'karyaCount' => PagiWork::where('user_id', $u->id)->count(),
                    'joinDate' => $u->created_at->format('d M Y'),
                ];
            });

        // Fallback demo partners if database contains no partners yet
        if ($mitras->isEmpty()) {
            $mitras = collect([
                [
                    'id' => 101,
                    'name' => "PT Telkom Indonesia",
                    'email' => "hr@telkom.co.id",
                    'pic' => "Budi Santoso",
                    'status' => "active",
                    'karyaCount' => 5,
                    'joinDate' => "12 Sep 2021",
                ],
                [
                    'id' => 102,
                    'name' => "Gojek Tokopedia (GoTo)",
                    'email' => "partners@goto.com",
                    'pic' => "Andi Wijaya",
                    'status' => "active",
                    'karyaCount' => 10,
                    'joinDate' => "15 Oct 2022",
                ],
                [
                    'id' => 103,
                    'name' => "Shopee Indonesia",
                    'email' => "career@shopee.co.id",
                    'pic' => "Sinta",
                    'status' => "warning",
                    'karyaCount' => 2,
                    'joinDate' => "20 Nov 2022",
                ],
                [
                    'id' => 104,
                    'name' => "Ruangguru",
                    'email' => "info@ruangguru.com",
                    'pic' => "Deni",
                    'status' => "active",
                    'karyaCount' => 8,
                    'joinDate' => "10 Jan 2023",
                ]
            ]);
        }

        return Inertia::render('Modules/Pagi/Admin/Users/Mitra', [
            'mitras' => $mitras
        ]);
    }

    /**
     * Warn a user
     */
    public function warnUser(Request $request, int $userId)
    {
        $request->validate([
            'reason'  => 'required|string|max:500',
            'content_id' => 'nullable|integer|exists:pagi_works,id',
        ]);

        $warning = PagiWarning::create([
            'user_id' => $userId,
            'work_id' => $request->content_id,
            'issued_by' => auth()->id() ?: 1,
            'severity' => 'medium',
            'type' => 'inappropriate_content',
            'reason' => $request->reason,
            'is_active' => true,
            'expires_at' => now()->addDays(30),
        ]);

        // Send notification to the user warned
        $user = User::findOrFail($userId);
        $work = $request->content_id ? PagiWork::find($request->content_id) : null;
        
        $user->notify(new PagiNotification(
            type: 'admin_warning',
            title: 'Peringatan Akun',
            message: 'Anda menerima peringatan dari admin: ' . $request->reason . ($work ? ' (terkait karya "' . $work->title . '")' : ''),
            avatar: null,
            href: '/pagi/notifications',
            extra: [
                'warning_id' => $warning->id,
                'work_id' => $request->content_id,
                'reason' => $request->reason,
                'edit_url' => $request->content_id ? '/pagi/editor?id=' . $request->content_id : null,
                'appeal' => true
            ]
        ));

        // If it was related to a work, we should also resolve any pending reports on that work and notify reporters
        if ($request->content_id) {
            $workId = $request->content_id;
            
            // Get all pending reports for this work
            $reports = PagiReport::where('work_id', $workId)
                ->where('status', 'pending')
                ->get();

            PagiReport::where('work_id', $workId)
                ->where('status', 'pending')
                ->update([
                    'status' => 'actioned',
                    'reviewed_by' => auth()->id() ?: 1,
                    'admin_note' => 'Peringatan dikirim ke pengguna: ' . $request->reason,
                    'reviewed_at' => now(),
                ]);

            foreach ($reports as $r) {
                $reporter = $r->reporter ?? User::find($r->reporter_id);
                if ($reporter) {
                    $reporter->notify(new PagiNotification(
                        type: 'system',
                        title: 'Tindakan Laporan',
                        message: 'Terima kasih atas laporan Anda. Kami telah mengambil tindakan terhadap karya "' . ($work->title ?? 'terkait') . '" yang Anda laporkan.',
                        avatar: null,
                        href: '/pagi',
                        extra: [
                            'work_id' => $workId,
                            'report_id' => $r->id,
                            'status' => 'actioned',
                        ]
                    ));
                }
            }
        }

        return back()->with('success', 'Peringatan berhasil dikirim.');
    }

    /**
     * Hide / Takedown content
     */
    public function hideContent(Request $request, int $workId)
    {
        $request->validate([
            'reason'  => 'required|string|max:500',
            'action'  => 'required|in:hide,remove,dismiss',
        ]);

        $work = PagiWork::findOrFail($workId);

        if ($request->action !== 'dismiss') {
            $work->update(['status' => $request->action === 'remove' ? 'removed' : 'hidden']);
        }

        $reports = PagiReport::where('work_id', $workId)->where('status', 'pending')->get();

        $reportStatus = match ($request->action) {
            'remove'  => 'actioned',
            'dismiss' => 'dismissed',
            default   => 'reviewed',
        };

        PagiReport::where('work_id', $workId)->where('status', 'pending')->update([
            'status'      => $reportStatus,
            'reviewed_by' => auth()->id() ?: 1,
            'admin_note'  => $request->reason,
            'reviewed_at' => now(),
        ]);

        if ($request->action === 'dismiss') {
            $this->notifyDismissedReporters($reports, $work, $workId);
        } else {
            $this->notifyTakedownParties($reports, $work, $workId, $request->action, $request->reason);
        }

        return back()->with('success', 'Konten berhasil dimoderasi.');
    }

    private function notifyDismissedReporters($reports, PagiWork $work, int $workId): void
    {
        foreach ($reports as $r) {
            $reporter = $r->reporter ?? User::find($r->reporter_id);
            if (!$reporter) {
                continue;
            }
            $reporter->notify(new PagiNotification(
                type: 'system',
                title: 'Tinjauan Laporan',
                message: 'Mohon maaf, berdasarkan tinjauan kami, karya "' . $work->title . '" yang Anda laporkan tidak terbukti melanggar panduan.',
                avatar: null,
                href: '/pagi',
                extra: ['work_id' => $workId, 'report_id' => $r->id, 'status' => 'dismissed']
            ));
        }
    }

    private function notifyTakedownParties($reports, PagiWork $work, int $workId, string $action, string $reason): void
    {
        $owner = $work->user;
        if ($owner) {
            $owner->notify(new PagiNotification(
                type: 'admin_takedown',
                title: 'Tindakan Moderasi pada Karya Anda',
                message: 'Karya Anda "' . $work->title . '" telah disembunyikan/dihapus karena: ' . $reason,
                avatar: null,
                href: '/pagi/notifications',
                extra: [
                    'work_id'   => $workId,
                    'work_title'=> $work->title,
                    'action'    => $action,
                    'reason'    => $reason,
                    'edit_url'  => '/pagi/editor?id=' . $workId,
                    'appeal'    => true,
                ]
            ));
        }

        foreach ($reports as $r) {
            $reporter = $r->reporter ?? User::find($r->reporter_id);
            if (!$reporter) {
                continue;
            }
            $reporter->notify(new PagiNotification(
                type: 'system',
                title: 'Tindakan Laporan',
                message: 'Terima kasih atas laporan Anda. Kami telah mengambil tindakan terhadap karya "' . $work->title . '" yang Anda laporkan.',
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
            'work_id'     => 'required|integer|exists:pagi_works,id',
            'reason'      => 'required|string|in:inappropriate_content,copyright_violation,spam,harassment,misinformation,other',
            'description' => 'required|string|min:10|max:1000',
        ]);

        $userId = auth()->id();
        $workId = $request->work_id;

        // Prevent duplicate pending reports from same user
        $existing = PagiReport::where('work_id', $workId)
            ->where('reporter_id', $userId)
            ->where('status', 'pending')
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'Anda sudah mengirim laporan untuk karya ini dan sedang dalam peninjauan admin.',
            ], 422);
        }

        $report = PagiReport::create([
            'work_id'     => $workId,
            'reporter_id' => $userId,
            'reason'      => $request->reason,
            'description' => $request->description,
            'status'      => 'pending',
        ]);

        $work = PagiWork::findOrFail($workId);
        $reporter = auth()->user();
        $reporterHandle = '@' . strstr($reporter->email, '@', true);

        // Broadcast realtime notification to admins via private-pagi.admin.reports channel
        PagiReportCreated::dispatch($report, $work->title, $reporter->name, $reporterHandle);

        // Fetch all admins in PAGI module
        $adminIds = \App\Models\UserModuleRole::whereHas('module', fn($q) => $q->where('code', 'PAGI'))
            ->whereHas('role', fn($q) => $q->whereIn('slug', ['super-admin', 'admin']))
            ->pluck('user_id')
            ->toArray();

        $admins = User::whereIn('id', $adminIds)
            ->orWhereIn('user_type', ['super-admin', 'admin', 'super_admin'])
            ->get();

        $avatar = $this->getStorageUrl($reporter->foto_path);

        $reasonText = $this->getReportReasonLabel($request->reason);

        foreach ($admins as $admin) {
            $admin->notify(new PagiNotification(
                type: 'report',
                title: 'Laporan Baru: ' . $reporter->name,
                message: 'Melaporkan karya "' . $work->title . '" karena ' . $reasonText,
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

        return response()->json(['message' => 'Laporan berhasil dikirim.']);
    }

    /**
     * Admin sends a notification to the owner of a reported work
     * action: 'warn' | 'takedown' | 'message'
     */
    public function sendNotificationToUser(Request $request, int $userId)
    {
        $request->validate([
            'message' => 'required|string|min:5|max:1000',
            'action'  => 'required|in:warn,takedown,message',
            'work_id' => 'nullable|integer|exists:pagi_works,id',
        ]);

        $targetUser = User::findOrFail($userId);
        $admin = auth()->user();

        $actionLabel = match($request->action) {
            'warn'     => 'Peringatan',
            'takedown' => 'Takedown',
            default    => 'Pesan Admin',
        };

        // Store notification in the DB using Laravel's notification system
        $targetUser->notifications()->create([
            'id'              => \Illuminate\Support\Str::uuid(),
            'type'            => 'App\\Notifications\\PagiNotification',
            'notifiable_type' => User::class,
            'notifiable_id'   => $userId,
            'data'            => json_encode([
                'type'       => $request->action === 'takedown' ? 'admin_takedown' : ($request->action === 'warn' ? 'admin_warning' : 'admin_message'),
                'title'      => $actionLabel . ' dari Admin',
                'message'    => $request->message,
                'work_id'    => $request->work_id,
                'admin_name' => $admin->name ?? 'Admin',
                'action'     => $request->action,
                'options'    => $request->action === 'takedown' ? ['takedown', 'delete'] : [],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // If takedown action, hide the work
        if ($request->action === 'takedown' && $request->work_id) {
            PagiWork::where('id', $request->work_id)->update(['status' => 'hidden']);
            PagiReport::where('work_id', $request->work_id)
                ->where('status', 'pending')
                ->update(['status' => 'actioned', 'reviewed_by' => $admin->id, 'reviewed_at' => now()]);
        }

        // If warn, also create a PagiWarning
        if ($request->action === 'warn' && $request->work_id) {
            PagiWarning::create([
                'user_id'   => $userId,
                'work_id'   => $request->work_id,
                'issued_by' => $admin->id,
                'severity'  => 'medium',
                'type'      => 'inappropriate_content',
                'reason'    => $request->message,
                'is_active' => true,
                'expires_at'=> now()->addDays(30),
            ]);
        }

        return back()->with('success', 'Notifikasi berhasil dikirim ke pengguna.');
    }

    /**
     * Build real stats with % change (current month vs previous month)
     */
    private function buildStats(): array
    {
        $now       = Carbon::now();
        $startThis = $now->copy()->startOfMonth();
        $startPrev = $now->copy()->subMonth()->startOfMonth();
        $endPrev   = $now->copy()->subMonth()->endOfMonth();

        $mahasiswaAktif    = User::where('user_type', 'mahasiswa')->where('is_active', true)->count();
        $karyaPublish      = PagiWork::where('is_published', true)->count();
        $laporanMasuk      = PagiReport::where('status', 'pending')->count();
        $warningAktif      = PagiWarning::where('is_active', true)->count();
        $karyaDitinjau     = PagiWork::where('status', 'review')->count();

        // This month counts
        $mahasiswaThisMonth = User::where('user_type', 'mahasiswa')->where('created_at', '>=', $startThis)->count();
        $karyaThisMonth     = PagiWork::where('is_published', true)->where('created_at', '>=', $startThis)->count();
        $laporanThisMonth   = PagiReport::where('created_at', '>=', $startThis)->count();
        $warningThisMonth   = PagiWarning::where('created_at', '>=', $startThis)->count();

        // Previous month counts
        $mahasiswaPrevMonth = User::where('user_type', 'mahasiswa')->whereBetween('created_at', [$startPrev, $endPrev])->count();
        $karyaPrevMonth     = PagiWork::where('is_published', true)->whereBetween('created_at', [$startPrev, $endPrev])->count();
        $laporanPrevMonth   = PagiReport::whereBetween('created_at', [$startPrev, $endPrev])->count();
        $warningPrevMonth   = PagiWarning::whereBetween('created_at', [$startPrev, $endPrev])->count();

        $calcChange = function (int $cur, int $prev): array {
            if ($prev === 0) {
                return ['value' => $cur > 0 ? '+' . $cur : '0', 'trend' => $cur > 0 ? 'up' : 'neutral'];
            }
            $pct = round((($cur - $prev) / $prev) * 100, 1);
            return [
                'value' => ($pct >= 0 ? '+' : '') . $pct . '%',
                'trend' => $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'neutral'),
            ];
        };

        return [
            'mahasiswaAktif' => $mahasiswaAktif,
            'karyaPublish'   => $karyaPublish,
            'laporanMasuk'   => $laporanMasuk,
            'warningAktif'   => $warningAktif,
            'karyaDitinjau'  => $karyaDitinjau,
            'changes' => [
                'mahasiswaAktif' => $calcChange($mahasiswaThisMonth, $mahasiswaPrevMonth),
                'karyaPublish'   => $calcChange($karyaThisMonth, $karyaPrevMonth),
                'laporanMasuk'   => $calcChange($laporanThisMonth, $laporanPrevMonth),
                'warningAktif'   => $calcChange($warningThisMonth, $warningPrevMonth),
                'karyaDitinjau'  => ['value' => $karyaDitinjau . ' menunggu', 'trend' => 'neutral'],
            ],
        ];
    }

    /**
     * Build real moderation summary
     */
    private function buildModerationSummary(): array
    {
        return [
            'total'    => PagiReport::count(),
            'pending'  => PagiReport::where('status', 'pending')->count(),
            'warning'  => PagiWarning::where('is_active', true)->count(),
            'takedown' => PagiWork::where('status', 'hidden')->count(),
            'rejected' => PagiReport::where('status', 'dismissed')->count(),
            'safe'     => PagiReport::whereIn('status', ['reviewed', 'actioned'])->count(),
        ];
    }

    /**
     * Build real chart data grouped by day for given range
     */
    private function buildChartData(string $range = '7d'): array
    {
        $days = match ($range) {
            '30d'  => 30,
            '90d'  => 90,
            default => 7,
        };

        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();

        $karyaCounts = PagiWork::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $laporanCounts = PagiReport::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $warningCounts = PagiWarning::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $labels   = [];
        $karya    = [];
        $laporan  = [];
        $warnings = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $carbonDate = Carbon::now()->subDays($i);
            $dateStr    = $carbonDate->toDateString();

            $labels[]   = $this->formatChartLabel($carbonDate, $i, $days);
            $karya[]    = $karyaCounts[$dateStr] ?? 0;
            $laporan[]  = $laporanCounts[$dateStr] ?? 0;
            $warnings[] = $warningCounts[$dateStr] ?? 0;
        }

        return [
            'categories' => $labels,
            'karya'      => $karya,
            'laporan'    => $laporan,
            'warnings'   => $warnings,
        ];
    }

    /**
     * JSON API: Realtime stats polling
     */
    public function apiStats(Request $request): JsonResponse
    {
        if (PagiWork::count() === 0) {
            $this->seedPagiDemoData();
        }

        return response()->json([
            'stats'             => $this->buildStats(),
            'moderationSummary' => $this->buildModerationSummary(),
            'timestamp'         => now()->toISOString(),
        ]);
    }

    /**
     * JSON API: Realtime chart data
     */
    public function apiChart(Request $request): JsonResponse
    {
        $range = $request->input('range', '7d');
        if (!in_array($range, ['7d', '30d', '90d'])) {
            $range = '7d';
        }

        return response()->json([
            'chartData' => $this->buildChartData($range),
            'range'     => $range,
        ]);
    }

    /**
     * JSON API: Fetch real admin notifications from DB
     */
    public function apiAdminNotifications(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $notifications = $user->notifications()
            ->where('type', 'App\\Notifications\\PagiNotification')
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($notif) {
                $data = $notif->data;
                // Sometimes notification data is auto-cast to array, sometimes it needs decoding
                if (is_string($data)) {
                    $data = json_decode($data, true) ?: [];
                }
                return [
                    'id'          => $notif->id,
                    'type'        => $data['type'] ?? 'system',
                    'title'       => $data['title'] ?? 'PAGI Admin System',
                    'message'     => $data['message'] ?? '',
                    'avatar'      => $data['avatar'] ?? null,
                    'href'        => $data['href'] ?? '/pagi/admin',
                    'unread'      => is_null($notif->read_at),
                    'time'        => $notif->created_at->diffForHumans(),
                    'created_at'  => $notif->created_at->toISOString(),
                    'extra'       => $data,
                ];
            });

        $unreadCount = $user->unreadNotifications()
            ->where('type', 'App\\Notifications\\PagiNotification')
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ]);
    }

    /**
     * Auto-seed real portfolio & moderation data linked together in the database
     */
    private function seedPagiDemoData(): void
    {
        $studentsData = [
            [
                'name' => 'Sarah Aulia',
                'email' => 'sarah@student.fmikom.ac.id',
                'nim' => '2021010001',
                'prodi' => 1,
                'role_title' => 'UI/UX Designer',
                'bio' => 'Senang mendesain interface aplikasi mobile dan web yang fungsional dan indah.',
            ],
            [
                'name' => 'Naufal Dzaky',
                'email' => 'naufal@student.fmikom.ac.id',
                'nim' => '2021010002',
                'prodi' => 1,
                'role_title' => 'Frontend Developer',
                'bio' => 'Belajar React, Vue, dan ekosistem JS modern.',
            ],
            [
                'name' => 'Dimas Wirawan',
                'email' => 'dimas@student.fmikom.ac.id',
                'nim' => '2022010015',
                'prodi' => 2,
                'role_title' => 'Graphic Designer',
                'bio' => 'Spesialis branding, tipografi, dan ilustrasi digital.',
            ],
            [
                'name' => 'Rizki Design',
                'email' => 'rizki@student.fmikom.ac.id',
                'nim' => '2022010030',
                'prodi' => 2,
                'role_title' => 'Product Designer',
                'bio' => 'Fokus pada riset pengguna dan wireframing produk digital.',
            ],
            [
                'name' => 'Johan Triwibowo',
                'email' => 'johan@student.fmikom.ac.id',
                'nim' => '2020010045',
                'prodi' => 1,
                'role_title' => '3D Artist',
                'bio' => 'Pembuat visualisasi 3D interior dan aset game.',
            ],
            [
                'name' => 'Fitria Nur',
                'email' => 'fitria@student.fmikom.ac.id',
                'nim' => '2023010008',
                'prodi' => 1,
                'role_title' => 'Mobile Developer',
                'bio' => 'Senang memprogram aplikasi mobile native untuk iOS dan Android.',
            ]
        ];

        $users = [];
        foreach ($studentsData as $data) {
            $users[] = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password123'),
                    'user_type' => 'mahasiswa',
                    'role_title' => $data['role_title'],
                    'bio' => $data['bio'],
                    'nomor_induk' => $data['nim'],
                    'program_studi_id' => $data['prodi'],
                    'location' => 'Purwokerto, Indonesia',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        // Seed some mitra partner accounts
        $mitrasData = [
            [
                'name' => "PT Telkom Indonesia",
                'email' => "hr@telkom.co.id",
                'pic' => "Budi Santoso",
            ],
            [
                'name' => "Gojek Tokopedia (GoTo)",
                'email' => "partners@goto.com",
                'pic' => "Andi Wijaya",
            ]
        ];
        foreach ($mitrasData as $mData) {
            User::updateOrCreate(
                ['email' => $mData['email']],
                [
                    'name' => $mData['name'],
                    'password' => Hash::make('password123'),
                    'user_type' => 'mitra',
                    'metadata' => ['pic' => $mData['pic']],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        $admin = User::where('user_type', 'super_admin')->first() ?: User::first();

        // 2. Create Works
        $worksData = [
            [
                'title' => 'Perancangan UI Aplikasi EduLearn',
                'user_email' => 'sarah@student.fmikom.ac.id',
                'description' => 'Eksplorasi UI/UX aplikasi pembelajaran online interaktif untuk mahasiswa FMIKOM.',
                'category' => 'Design & UI/UX',
                'views_count' => 1200,
                'is_published' => true,
                'status' => 'active',
            ],
            [
                'title' => 'Eksplorasi Tipografi Eksperimental',
                'user_email' => 'dimas@student.fmikom.ac.id',
                'description' => 'Konsep tipografi poster yang menggabungkan elemen retro dan modern.',
                'category' => 'Graphic Design',
                'views_count' => 980,
                'is_published' => true,
                'status' => 'active',
            ],
            [
                'title' => 'Prototype Aplikasi Traveling',
                'user_email' => 'rizki@student.fmikom.ac.id',
                'description' => 'Aplikasi pencarian rute wisata lokal di Purwokerto.',
                'category' => 'Product Design',
                'views_count' => 320,
                'is_published' => true,
                'status' => 'hidden',
            ],
            [
                'title' => '3D Visualisasi Interior Cafe',
                'user_email' => 'johan@student.fmikom.ac.id',
                'description' => 'Rendering interior cafe minimalis menggunakan Blender.',
                'category' => '3D Modeling',
                'views_count' => 870,
                'is_published' => true,
                'status' => 'review',
            ],
            [
                'title' => 'Desain Poster Event Musik',
                'user_email' => 'naufal@student.fmikom.ac.id',
                'description' => 'Poster promosi konser musik kampus tahunan.',
                'category' => 'Graphic Design',
                'views_count' => 120,
                'is_published' => true,
                'status' => 'active',
            ]
        ];

        $ports = [];
        foreach ($worksData as $pData) {
            $user = User::where('email', $pData['user_email'])->first();
            if ($user) {
                $ports[] = PagiWork::create([
                    'user_id' => $user->id,
                    'title' => $pData['title'],
                    'description' => $pData['description'],
                    'category' => $pData['category'],
                    'views_count' => $pData['views_count'],
                    'is_published' => $pData['is_published'],
                    'status' => $pData['status'],
                    'content' => [['type' => 'paragraph', 'data' => ['text' => $pData['description']]]],
                ]);
            }
        }

        // 3. Create Reports
        if (count($ports) >= 4) {
            PagiReport::create([
                'work_id' => $ports[2]->id,
                'reporter_id' => $users[1]->id,
                'reason' => 'copyright_violation',
                'description' => 'Terdapat indikasi kemiripan 90% dengan karya desain di Dribbble.',
                'status' => 'pending',
            ]);

            PagiReport::create([
                'work_id' => $ports[3]->id,
                'reporter_id' => $users[0]->id,
                'reason' => 'copyright_violation',
                'description' => 'Aset model 3D menggunakan milik berbayar tanpa lisensi komersial.',
                'status' => 'pending',
            ]);
        }

        // 4. Create Warnings
        if (count($users) >= 3 && $admin) {
            PagiWarning::create([
                'user_id' => $users[2]->id,
                'issued_by' => $admin->id,
                'severity' => 'medium',
                'type' => 'inappropriate_content',
                'reason' => 'Menggunakan gambar beresolusi rendah dan mengandung watermark berbayar.',
                'is_active' => true,
                'expires_at' => now()->addDays(30),
            ]);
        }
    }

    /**
     * Analytics
     */
    public function analytics(Request $request): Response
    {
        return Inertia::render('Modules/Pagi/Admin/Analytics/Index');
    }

    /**
     * Reports
     */
    /**
     * Reports
     */
    public function reports(Request $request): Response
    {
        if (PagiWork::count() === 0) {
            $this->seedPagiDemoData();
        }

        $reports = PagiReport::with(['work.user', 'reporter'])
            ->latest()
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'workId' => $r->work->id ?? null,
                    'workTitle' => $r->work->title ?? 'Karya Dihapus',
                    'userId' => $r->work->user_id ?? null,
                    'author' => $r->work->user->name ?? 'Student',
                    'authorHandle' => '@' . strstr($r->work->user->email ?? self::DEFAULT_STUDENT_EMAIL, '@', true),
                    'reporter' => $r->reporter->name ?? 'Reporter',
                    'reporterHandle' => '@' . strstr($r->reporter->email ?? self::DEFAULT_REPORTER_EMAIL, '@', true),
                    'reason' => $this->getReportReasonLabel($r->reason),
                    'description' => $r->description ?? 'Tidak ada deskripsi.',
                    'status' => $r->status, // pending, reviewed, dismissed, actioned
                    'time' => $r->created_at->diffForHumans(),
                    'thumbnail' => $r->work ? $this->getStorageUrl($r->work->cover_image) : null,
                ];
            });

        return Inertia::render('Modules/Pagi/Admin/Reports/Index', [
            'reports' => $reports
        ]);
    }

    /**
     * Settings
     */
    public function settings(Request $request): Response
    {
        return Inertia::render('Modules/Pagi/Admin/Settings/Index');
    }

    /**
     * Activity Logs
     */
    public function logs(Request $request): Response
    {
        return Inertia::render('Modules/Pagi/Admin/Logs/Index');
    }

    /**
     * Warnings
     */
    public function warnings(Request $request): Response
    {
        if (PagiWork::count() === 0) {
            $this->seedPagiDemoData();
        }

        $warnings = PagiWarning::with(['user', 'issuer', 'work'])
            ->latest()
            ->get()
            ->map(function ($w) {
                return [
                    'id' => $w->id,
                    'user' => $w->user->name ?? 'User',
                    'userHandle' => '@' . strstr($w->user->email ?? 'student@fmikom.ac.id', '@', true),
                    'userId' => $w->user_id,
                    'workId' => $w->work_id,
                    'workTitle' => $w->work->title ?? null,
                    'reason' => $w->reason,
                    'severity' => $w->severity, // low, medium, high
                    'type' => $w->type,
                    'issuedBy' => $w->issuer->name ?? 'Admin',
                    'time' => $w->created_at->diffForHumans(),
                    'expiresAt' => $w->expires_at ? $w->expires_at->format('d M Y') : 'Seterusnya',
                    'isActive' => $w->is_active,
                ];
            });

        return Inertia::render('Modules/Pagi/Admin/Warnings/Index', [
            'warnings' => $warnings
        ]);
    }

    /**
     * Takedowns
     */
    public function takedowns(Request $request): Response
    {
        if (PagiWork::count() === 0) {
            $this->seedPagiDemoData();
        }

        $takedowns = PagiWork::with('user')
            ->whereIn('status', ['hidden', 'removed'])
            ->latest()
            ->get()
            ->map(function ($w) {
                // Find why it was hidden from warnings or reports
                $reason = PagiWarning::where('work_id', $w->id)->latest()->value('reason') 
                    ?? PagiReport::where('work_id', $w->id)->whereIn('status', ['actioned', 'reviewed'])->latest()->value('admin_note')
                    ?? 'Diturunkan oleh admin karena pelanggaran pedoman.';

                return [
                    'id' => $w->id,
                    'title' => $w->title,
                    'author' => $w->user->name ?? 'Student',
                    'authorHandle' => '@' . strstr($w->user->email ?? self::DEFAULT_STUDENT_EMAIL, '@', true),
                    'category' => $w->category ?? 'Design & UI/UX',
                    'status' => $w->status, // hidden, removed
                    'reason' => $reason,
                    'time' => $w->updated_at->diffForHumans(),
                    'thumbnail' => $this->getStorageUrl($w->cover_image),
                ];
            });

        return Inertia::render('Modules/Pagi/Admin/Takedowns/Index', [
            'takedowns' => $takedowns
        ]);
    }

    /**
     * Works
     */
    public function works(Request $request): Response
    {
        if (PagiWork::count() === 0) {
            $this->seedPagiDemoData();
        }

        $works = PagiWork::with('user')
            ->latest()
            ->get()
            ->map(function ($w) {
                return [
                    'id' => $w->id,
                    'title' => $w->title,
                    'author' => $w->user->name ?? 'Student',
                    'authorHandle' => '@' . strstr($w->user->email ?? self::DEFAULT_STUDENT_EMAIL, '@', true),
                    'views' => $w->views_count,
                    'category' => $w->category ?? 'Design & UI/UX',
                    'status' => $w->status, // active, warning, hidden, removed, review
                    'isPublished' => $w->is_published,
                    'time' => $w->created_at->diffForHumans(),
                    'thumbnail' => $this->getStorageUrl($w->cover_image),
                ];
            });

        return Inertia::render('Modules/Pagi/Admin/Works/Index', [
            'works' => $works
        ]);
    }

    /**
     * Gallery
     */
    public function gallery(Request $request): Response
    {
        return Inertia::render('Modules/Pagi/Admin/Gallery/Index');
    }

    /**
     * Tags
     */
    public function tags(Request $request): Response
    {
        return Inertia::render('Modules/Pagi/Admin/Tags/Index');
    }

    /**
     * Roles
     */
    public function roles(Request $request): Response
    {
        return Inertia::render('Modules/Pagi/Admin/Roles/Index');
    }

    public function resetModeration(Request $request)
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

        // 1. Hapus semua warning
        PagiWarning::truncate();

        // 2. Hapus semua reports
        PagiReport::truncate();

        // 3. Hapus semua work tags
        DB::table('pagi_work_tags')->truncate();

        // 4. Hapus semua works untuk menghindari duplikasi
        PagiWork::truncate();

        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // 5. Seeding ulang data awal demo yang bersih & rapi
        $this->seedPagiDemoData();

        return back()->with('success', 'Antrean moderasi dan warnings berhasil di-reset.');
    }

    /**
     * Revoke user warning
     */
    public function revokeWarning(Request $request, int $warningId)
    {
        $warning = PagiWarning::findOrFail($warningId);
        $warning->update(['is_active' => false]);

        return back()->with('success', 'Peringatan berhasil dicabut.');
    }

    /**
     * Restore takedown work
     */
    public function restoreContent(Request $request, int $workId)
    {
        $work = PagiWork::findOrFail($workId);
        $work->update(['status' => 'active']);

        // Resolve any related actioned reports on this work back to dismissed or active if needed
        PagiReport::where('work_id', $workId)->update(['status' => 'dismissed']);

        return back()->with('success', 'Karya berhasil dipulihkan kembali ke publik.');
    }

    private const DEFAULT_STUDENT_EMAIL = 'student@fmikom.ac.id';
    private const DEFAULT_REPORTER_EMAIL = 'reporter@fmikom.ac.id';

    private function getStorageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        return str_starts_with($path, 'http') ? $path : asset('storage/' . $path);
    }

    private function getReportReasonLabel(?string $reason): string
    {
        return match ($reason) {
            'copyright_violation' => 'Pelanggaran Hak Cipta',
            'inappropriate_content' => 'Konten Tidak Pantas',
            'spam' => 'Spam',
            'harassment' => 'Pelecehan',
            'misinformation' => 'Misinformasi',
            default => 'Lainnya',
        };
    }

    private function formatChartLabel(\Carbon\Carbon $date, int $offsetFromEnd, int $totalDays): string
    {
        if ($totalDays <= 30) {
            return $date->translatedFormat('d M');
        }

        // For 90-day range: only label every 10th day and the first/last
        $isLabelDay = ($offsetFromEnd % 10 === 0)
            || $offsetFromEnd === $totalDays - 1
            || $offsetFromEnd === 0;

        return $isLabelDay ? $date->translatedFormat('d M') : '';
    }
}
