<?php

namespace App\Modules\Pagi\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pagi\PagiReport;
use App\Models\Pagi\PagiWarning;
use App\Models\Pagi\PagiWork;
use App\Models\User;
use App\Modules\Pagi\Controllers\Concerns\HasAdminDashboardHelpers;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

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
    use HasAdminDashboardHelpers;

    private const DEFAULT_STUDENT_EMAIL = 'student@fmikom.ac.id';

    private const DEFAULT_REPORTER_EMAIL = 'reporter@fmikom.ac.id';

    /**
     * Dashboard Overview
     * Menampilkan statistik ringkasan untuk admin
     */
    public function index(Request $request): Response
    {
        // Auto-seed real database data if pagi_works is empty to guarantee populated metrics
        if (PagiWork::query()->count('*') === 0) {
            $this->seedPagiDemoData();
        }

        // 1. Real Stats from Database (with % change calculation)
        $stats = $this->buildStats();

        // 2. Real Moderation Summary from Database
        $moderationSummary = $this->buildModerationSummary();

        // 3. Real Recent Activities from Database
        $latestWorks = PagiWork::query()->with('user')->latest('created_at')->take(3)->get();
        $latestReports = PagiReport::query()->with(['work', 'reporter'])->latest('created_at')->take(2)->get();
        $latestWarnings = PagiWarning::query()->with('user')->latest('created_at')->take(2)->get();
        $recentActivities = $this->buildRecentActivities($latestWorks, $latestReports, $latestWarnings);

        // 4. Real Moderation Items from Database
        $reports = PagiReport::query()->with(['work.user', 'reporter'])->latest('created_at')->take(10)->get();
        $moderationItems = $this->buildModerationItems($reports);

        // If no reports exist, load review-status works as new items
        if (empty($moderationItems)) {
            $reviewWorks = PagiWork::query()->with('user')->where('status', '=', 'review', 'and')->latest('created_at')->take(10)->get();
            $moderationItems = $this->buildFallbackModerationItems($reviewWorks);
        }

        // 5. Real Popular Works ordered by views count
        $popularWorksRaw = PagiWork::query()->with('user')
            ->where('is_published', '=', true, 'and')
            ->orderBy('views_count', 'desc')
            ->take(5)
            ->get();
        $popularWorks = $this->formatPopularWorks($popularWorksRaw);

        return Inertia::render('Modules/Pagi/Admin/Dashboard', [
            'stats' => $stats,
            'moderationSummary' => $moderationSummary,
            'recentActivities' => $recentActivities,
            'moderationItems' => $moderationItems,
            'popularWorks' => $popularWorks,
            'chartData' => $this->buildChartData('7d'),
        ]);
    }

    /**
     * Analytics
     */
    public function analytics(Request $request): Response
    {
        return Inertia::render('Modules/Pagi/Admin/Analytics/Index');
    }

    /**
     * Settings
     */
    public function settings(Request $request): Response
    {
        return Inertia::render('Modules/Pagi/Admin/Settings/Index');
    }

    /**
     * Tags
     */
    public function tags(Request $request): Response
    {
        return Inertia::render('Modules/Pagi/Admin/Tags/Index');
    }

    /**
     * JSON API: Realtime stats polling
     */
    public function apiStats(Request $request): JsonResponse
    {
        if (PagiWork::query()->count('*') === 0) {
            $this->seedPagiDemoData();
        }

        return response()->json([
            'stats' => $this->buildStats(),
            'moderationSummary' => $this->buildModerationSummary(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * JSON API: Realtime chart data
     */
    public function apiChart(Request $request): JsonResponse
    {
        $range = $request->input('range', '7d');
        if (! in_array($range, ['7d', '30d', '90d'])) {
            $range = '7d';
        }

        return response()->json([
            'chartData' => $this->buildChartData($range),
            'range' => $range,
        ]);
    }

    /**
     * JSON API: Fetch real admin notifications from DB
     */
    public function apiAdminNotifications(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $notifications = $user->notifications()
            ->where('type', '=', 'App\\Notifications\\PagiNotification', 'and')
            ->latest('created_at')
            ->take(20)
            ->get()
            ->map(function ($notif) {
                $data = $notif->data;
                // Sometimes notification data is auto-cast to array, sometimes it needs decoding
                if (is_string($data)) {
                    $data = json_decode($data, true) ?: [];
                }

                return [
                    'id' => $notif->id,
                    'type' => $data['type'] ?? 'system',
                    'title' => $data['title'] ?? 'PAGI Admin System',
                    'message' => $data['message'] ?? '',
                    'avatar' => $data['avatar'] ?? null,
                    'href' => $data['href'] ?? '/pagi/admin',
                    'unread' => is_null($notif->read_at),
                    'time' => $notif->created_at->diffForHumans(),
                    'created_at' => $notif->created_at->toISOString(),
                    'extra' => $data,
                ];
            });

        $unreadCount = $user->unreadNotifications()
            ->where('type', '=', 'App\\Notifications\\PagiNotification', 'and')
            ->count('*');

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Build real stats with % change (current month vs previous month).
     * Cached for 60 seconds to avoid 12+ COUNT queries on every request.
     */
    private function buildStats(): array
    {
        return Cache::remember('pagi_admin_stats', 60, function () {
            $now = Carbon::now();
            $startThis = $now->copy()->startOfMonth();
            $startPrev = $now->copy()->subMonth()->startOfMonth();
            $endPrev = $now->copy()->subMonth()->endOfMonth();

            $mahasiswaAktif = User::query()->where('user_type', '=', 'mahasiswa', 'and')->where('is_active', '=', true, 'and')->count('*');
            $karyaPublish = PagiWork::query()->where('is_published', '=', true, 'and')->count('*');
            $laporanMasuk = PagiReport::query()->where('status', '=', 'pending', 'and')->count('*');
            $warningAktif = PagiWarning::query()->where('is_active', '=', true, 'and')->count('*');
            $karyaDitinjau = PagiWork::query()->where('status', '=', 'review', 'and')->count('*');

            // This month counts
            $mahasiswaThisMonth = User::query()->where('user_type', '=', 'mahasiswa', 'and')->where('created_at', '>=', $startThis)->count('*');
            $karyaThisMonth = PagiWork::query()->where('is_published', '=', true, 'and')->where('created_at', '>=', $startThis)->count('*');
            $laporanThisMonth = PagiReport::query()->where('created_at', '>=', $startThis)->count('*');
            $warningThisMonth = PagiWarning::query()->where('created_at', '>=', $startThis)->count('*');

            // Previous month counts
            $mahasiswaPrevMonth = User::query()->where('user_type', '=', 'mahasiswa', 'and')->whereBetween('created_at', [$startPrev, $endPrev], 'and')->count('*');
            $karyaPrevMonth = PagiWork::query()->where('is_published', '=', true, 'and')->whereBetween('created_at', [$startPrev, $endPrev], 'and')->count('*');
            $laporanPrevMonth = PagiReport::query()->whereBetween('created_at', [$startPrev, $endPrev], 'and')->count('*');
            $warningPrevMonth = PagiWarning::query()->whereBetween('created_at', [$startPrev, $endPrev], 'and')->count('*');

            $calcChange = function (int $cur, int $prev): array {
                if ($prev === 0) {
                    return ['value' => $cur > 0 ? '+'.$cur : '0', 'trend' => $cur > 0 ? 'up' : 'neutral'];
                }
                $pct = round((($cur - $prev) / $prev) * 100, 1);
                $trend = 'neutral';
                if ($pct > 0) {
                    $trend = 'up';
                } elseif ($pct < 0) {
                    $trend = 'down';
                }

                return [
                    'value' => ($pct >= 0 ? '+' : '').$pct.'%',
                    'trend' => $trend,
                ];
            };

            return [
                'mahasiswaAktif' => $mahasiswaAktif,
                'karyaPublish' => $karyaPublish,
                'laporanMasuk' => $laporanMasuk,
                'warningAktif' => $warningAktif,
                'karyaDitinjau' => $karyaDitinjau,
                'changes' => [
                    'mahasiswaAktif' => $calcChange($mahasiswaThisMonth, $mahasiswaPrevMonth),
                    'karyaPublish' => $calcChange($karyaThisMonth, $karyaPrevMonth),
                    'laporanMasuk' => $calcChange($laporanThisMonth, $laporanPrevMonth),
                    'warningAktif' => $calcChange($warningThisMonth, $warningPrevMonth),
                    'karyaDitinjau' => ['value' => $karyaDitinjau.' menunggu', 'trend' => 'neutral'],
                ],
            ];
        });
    }

    /**
     * Build real moderation summary.
     * Cached for 60 seconds to avoid 6 COUNT queries on every request.
     */
    private function buildModerationSummary(): array
    {
        return Cache::remember('pagi_admin_moderation', 60, function () {
            return [
                'total' => PagiReport::query()->count('*'),
                'pending' => PagiReport::query()->where('status', '=', 'pending', 'and')->count('*'),
                'warning' => PagiWarning::query()->where('is_active', '=', true, 'and')->count('*'),
                'takedown' => PagiWork::query()->where('status', '=', 'hidden', 'and')->count('*'),
                'rejected' => PagiReport::query()->where('status', '=', 'dismissed', 'and')->count('*'),
                'safe' => PagiReport::query()->whereIn('status', ['reviewed', 'actioned'], 'and', false)->count('*'),
            ];
        });
    }

    /**
     * Build real chart data grouped by day for given range.
     * Cached for 5 minutes to avoid repeated date-grouped aggregation queries.
     */
    private function buildChartData(string $range = '7d'): array
    {
        return Cache::remember("pagi_admin_chart_{$range}", 300, function () use ($range) {
            $days = match ($range) {
                '30d' => 30,
                '90d' => 90,
                default => 7,
            };

            $startDate = Carbon::now()->subDays($days - 1)->startOfDay();

            $karyaCounts = PagiWork::query()->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, count(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();

            $laporanCounts = PagiReport::query()->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, count(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();

            $warningCounts = PagiWarning::query()->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, count(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();

            $labels = [];
            $karya = [];
            $laporan = [];
            $warnings = [];

            for ($i = $days - 1; $i >= 0; $i--) {
                $carbonDate = Carbon::now()->subDays($i);
                $dateStr = $carbonDate->toDateString();

                $labels[] = $this->formatChartLabel($carbonDate, $i, $days);
                $karya[] = $karyaCounts[$dateStr] ?? 0;
                $laporan[] = $laporanCounts[$dateStr] ?? 0;
                $warnings[] = $warningCounts[$dateStr] ?? 0;
            }

            return [
                'categories' => $labels,
                'karya' => $karya,
                'laporan' => $laporan,
                'warnings' => $warnings,
            ];
        }); // end Cache::remember
    }

    private function buildRecentActivities($latestWorks, $latestReports, $latestWarnings): array
    {
        $activities = [];

        // Work publications
        foreach ($latestWorks as $p) {
            if ($p->user) {
                $activities[] = [
                    'id' => 'p_'.$p->id,
                    'type' => 'publish',
                    'title' => 'Karya baru dipublikasikan',
                    'description' => '"'.$p->title.'" oleh @'.strstr($p->user->email, '@', true),
                    'actor' => strstr($p->user->email, '@', true),
                    'avatar' => $this->getStorageUrl($p->user->foto_path),
                    'time' => $p->created_at->diffForHumans(),
                    'timestamp' => $p->created_at->timestamp,
                ];
            }
        }

        // Reports submitted
        foreach ($latestReports as $r) {
            if ($r->reporter && $r->work) {
                $activities[] = [
                    'id' => 'r_'.$r->id,
                    'type' => 'report',
                    'title' => 'Laporan baru dari @'.strstr($r->reporter->email, '@', true),
                    'description' => 'Melaporkan karya "'.$r->work->title.'"',
                    'actor' => strstr($r->reporter->email, '@', true),
                    'avatar' => $this->getStorageUrl($r->reporter->foto_path),
                    'time' => $r->created_at->diffForHumans(),
                    'timestamp' => $r->created_at->timestamp,
                ];
            }
        }

        // Warnings issued
        foreach ($latestWarnings as $w) {
            if ($w->user) {
                $activities[] = [
                    'id' => 'w_'.$w->id,
                    'type' => 'warning',
                    'title' => 'Peringatan dikirim ke @'.strstr($w->user->email, '@', true),
                    'description' => 'Alasan: '.$w->reason,
                    'actor' => strstr($w->user->email, '@', true),
                    'avatar' => $this->getStorageUrl($w->user->foto_path),
                    'time' => $w->created_at->diffForHumans(),
                    'timestamp' => $w->created_at->timestamp,
                ];
            }
        }

        // Sort by timestamp descending
        usort($activities, function ($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        // Limit to 5
        return array_slice($activities, 0, 5);
    }

    private function buildModerationItems($reports): array
    {
        $items = [];
        foreach ($reports as $r) {
            if ($r->work) {
                $status = 'active';
                if ($r->status === 'pending') {
                    $status = 'pending';
                } elseif ($r->work->status === 'hidden') {
                    $status = 'hidden';
                }

                $items[] = [
                    'id' => $r->work->id,
                    'title' => $r->work->title,
                    'author' => $r->work->user->name ?? 'Student',
                    'authorHandle' => '@'.strstr($r->work->user->email ?? self::DEFAULT_STUDENT_EMAIL, '@', true),
                    'type' => 'Laporan',
                    'reportedBy' => '@'.strstr($r->reporter->email ?? self::DEFAULT_REPORTER_EMAIL, '@', true),
                    'time' => $r->created_at->diffForHumans(),
                    'status' => $status,
                    'thumbnail' => $this->getStorageUrl($r->work->cover_image),
                    'userId' => $r->work->user_id,
                    'description' => $r->work->description ?? 'Tidak ada deskripsi.',
                    'category' => $r->work->category ?? 'Design & UI/UX',
                    'reportReason' => $this->getReportReasonLabel($r->reason),
                    'reportDescription' => $r->description ?? 'Tidak ada penjelasan tambahan.',
                    'reporterHandle' => '@'.strstr($r->reporter->email ?? self::DEFAULT_REPORTER_EMAIL, '@', true),
                ];
            }
        }

        return $items;
    }

    private function buildFallbackModerationItems($reviewWorks): array
    {
        $items = [];
        foreach ($reviewWorks as $p) {
            $items[] = [
                'id' => $p->id,
                'title' => $p->title,
                'author' => $p->user->name ?? 'Student',
                'authorHandle' => '@'.strstr($p->user->email ?? self::DEFAULT_STUDENT_EMAIL, '@', true),
                'type' => 'Karya Baru',
                'reportedBy' => '@'.strstr($p->user->email ?? self::DEFAULT_STUDENT_EMAIL, '@', true),
                'time' => $p->created_at->diffForHumans(),
                'status' => 'pending',
                'thumbnail' => $this->getStorageUrl($p->cover_image),
                'userId' => $p->user_id,
                'description' => $p->description ?? 'Tidak ada deskripsi.',
                'category' => $p->category ?? 'Lainnya',
                'reportReason' => 'Peninjauan Karya Baru',
                'reportDescription' => 'Karya baru dipublikasikan dan memerlukan persetujuan admin.',
                'reporterHandle' => '@'.strstr($p->user->email ?? self::DEFAULT_STUDENT_EMAIL, '@', true),
            ];
        }

        return $items;
    }

    private function formatPopularWorks($popularWorksRaw): array
    {
        $popularWorks = [];
        $rank = 1;
        foreach ($popularWorksRaw as $work) {
            if ($work->user) {
                $popularWorks[] = [
                    'id' => $work->id,
                    'rank' => $rank++,
                    'title' => $work->title,
                    'author' => '@'.strstr($work->user->email, '@', true),
                    'views' => $work->views_count,
                    'thumbnail' => $this->getStorageUrl($work->cover_image),
                ];
            }
        }

        return $popularWorks;
    }
}
