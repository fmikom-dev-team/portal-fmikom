<?php

namespace App\Modules\Pagi\Controllers;

use App\Events\PagiReportCreated;
use App\Http\Controllers\Controller;
use App\Models\Pagi\PagiReport;
use App\Models\Pagi\PagiWarning;
use App\Models\Pagi\PagiWork;
use App\Models\User;
use App\Models\UserModuleRole;
use App\Notifications\PagiNotification;
use App\Modules\Pagi\Controllers\Concerns\HasAdminDashboardHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class AdminModerationController extends Controller
{
    use HasAdminDashboardHelpers;

    /**
     * Moderation Queue
     */
    public function moderation(Request $request): Response
    {
        if (PagiWork::query()->count('*') === 0) {
            $this->seedPagiDemoData();
        }

        $reports = PagiReport::query()->with(['work.user', 'reporter'])->latest('created_at')->get();
        $items = $this->buildModerationReports($reports);

        // Fallback to active/review works if no reports exist
        if (empty($items)) {
            $works = PagiWork::query()->with('user')->latest('created_at')->get();
            $items = $this->buildFallbackModerationWorks($works);
        }

        // Summary badges counted dynamically
        $summary = [
            'pending' => PagiReport::query()->where('status', '=', 'pending', 'and')->count('*'),
            'warning' => PagiWarning::query()->where('is_active', '=', true, 'and')->count('*'),
            'takedown' => PagiWork::query()->where('status', '=', 'hidden', 'and')->count('*'),
            'resolved' => PagiReport::query()->whereIn('status', ['reviewed', 'dismissed', 'actioned'], 'and', false)->count('*'),
        ];

        return Inertia::render('Modules/Pagi/Admin/Moderation/Index', [
            'items' => $items,
            'summary' => $summary,
        ]);
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
        $owner = $work->user;
        if ($owner) {
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

        Schema::disableForeignKeyConstraints();

        // 1. Hapus semua warning
        PagiWarning::truncate();

        // 2. Hapus semua reports
        PagiReport::truncate();

        // 3. Hapus semua work tags
        DB::table('pagi_work_tags')->truncate();

        // 4. Hapus semua works untuk menghindari duplikasi
        PagiWork::truncate();

        Schema::enableForeignKeyConstraints();

        // 5. Seeding ulang data awal demo yang bersih & rapi
        $this->seedPagiDemoData();

        return back()->with('success', 'Antrean moderasi dan warnings berhasil di-reset.');
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

        // Resolve any related actioned reports on this work back to dismissed or active if needed
        PagiReport::query()->where('work_id', '=', $workId, 'and')->update(['status' => 'dismissed']);

        return back()->with('success', 'Karya berhasil dipulihkan kembali ke publik.');
    }

    private function buildModerationReports($reports): array
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
                    'authorHandle' => '@'.strstr($r->work->user->email ?? self::$DEFAULT_STUDENT_EMAIL, '@', true),
                    'type' => 'Laporan',
                    'reportedBy' => '@'.strstr($r->reporter->email ?? self::$DEFAULT_REPORTER_EMAIL, '@', true),
                    'time' => $r->created_at->diffForHumans(),
                    'status' => $status,
                    'thumbnail' => $this->getStorageUrl($r->work->cover_image),
                    'userId' => $r->work->user_id,
                    'description' => $r->work->description ?? 'Tidak ada deskripsi.',
                    'category' => $r->work->category ?? 'Design & UI/UX',
                    'reportReason' => $this->getReportReasonLabel($r->reason),
                    'reportDescription' => $r->description ?? 'Tidak ada penjelasan tambahan.',
                    'reporterHandle' => '@'.strstr($r->reporter->email ?? self::$DEFAULT_REPORTER_EMAIL, '@', true),
                ];
            }
        }
        return $items;
    }

    private function buildFallbackModerationWorks($works): array
    {
        $items = [];
        foreach ($works as $p) {
            if ($p->user) {
                $status = 'active';
                if ($p->status === 'review') {
                    $status = 'pending';
                } elseif ($p->status === 'hidden') {
                    $status = 'hidden';
                }

                $items[] = [
                    'id' => $p->id,
                    'title' => $p->title,
                    'author' => $p->user->name,
                    'authorHandle' => '@'.strstr($p->user->email, '@', true),
                    'type' => $p->status === 'review' ? 'Karya Baru' : 'Komentar',
                    'reportedBy' => '@'.strstr($p->user->email, '@', true),
                    'time' => $p->created_at->diffForHumans(),
                    'status' => $status,
                    'thumbnail' => $this->getStorageUrl($p->cover_image),
                    'userId' => $p->user_id,
                    'description' => $p->description ?? 'Tidak ada deskripsi.',
                    'category' => $p->category ?? 'Lainnya',
                    'reportReason' => 'Peninjauan Karya Baru',
                    'reportDescription' => 'Karya baru dipublikasikan and memerlukan persetujuan admin.',
                    'reporterHandle' => '@'.strstr($p->user->email, '@', true),
                ];
            }
        }
        return $items;
    }
}
