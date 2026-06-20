<?php

namespace App\Modules\Pagi\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pagi\PagiReport;
use App\Models\Pagi\PagiWarning;
use App\Models\Pagi\PagiWork;
use App\Modules\Pagi\Controllers\Concerns\HasAdminDashboardHelpers;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminWorkController extends Controller
{
    use HasAdminDashboardHelpers;

    /**
     * Reports
     */
    public function reports(Request $request): Response
    {
        if (PagiWork::query()->count('*') === 0) {
            $this->seedPagiDemoData();
        }

        $reports = PagiReport::query()->with(['work.user', 'reporter'])
            ->latest('created_at')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'workId' => $r->work->id ?? null,
                    'workTitle' => $r->work->title ?? 'Karya Dihapus',
                    'userId' => $r->work->user_id ?? null,
                    'author' => $r->work->user->name ?? 'Student',
                    'authorHandle' => '@'.strstr($r->work->user->email ?? self::$DEFAULT_STUDENT_EMAIL, '@', true),
                    'reporter' => $r->reporter->name ?? 'Reporter',
                    'reporterHandle' => '@'.strstr($r->reporter->email ?? self::$DEFAULT_REPORTER_EMAIL, '@', true),
                    'reason' => $this->getReportReasonLabel($r->reason),
                    'description' => $r->description ?? 'Tidak ada deskripsi.',
                    'status' => $r->status, // pending, reviewed, dismissed, actioned
                    'time' => $r->created_at->diffForHumans(),
                    'thumbnail' => $r->work ? $this->getStorageUrl($r->work->cover_image) : null,
                ];
            });

        return Inertia::render('Modules/Pagi/Admin/Reports/Index', [
            'reports' => $reports,
        ]);
    }

    /**
     * Warnings
     */
    public function warnings(Request $request): Response
    {
        if (PagiWork::query()->count('*') === 0) {
            $this->seedPagiDemoData();
        }

        $warnings = PagiWarning::query()->with(['user', 'issuer', 'work'])
            ->latest('created_at')
            ->get()
            ->map(function ($w) {
                return [
                    'id' => $w->id,
                    'user' => $w->user->name ?? 'User',
                    'userHandle' => '@'.strstr($w->user->email ?? 'student@fmikom.ac.id', '@', true),
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
            'warnings' => $warnings,
        ]);
    }

    /**
     * Takedowns
     */
    public function takedowns(Request $request): Response
    {
        if (PagiWork::query()->count('*') === 0) {
            $this->seedPagiDemoData();
        }

        $takedowns = PagiWork::query()->with('user')
            ->whereIn('status', ['hidden', 'removed'], 'and', false)
            ->latest('created_at')
            ->get()
            ->map(function ($w) {
                // Find why it was hidden from warnings or reports
                $reason = PagiWarning::query()->where('work_id', '=', $w->id, 'and')->latest('created_at')->value('reason')
                    ?? PagiReport::query()->where('work_id', '=', $w->id, 'and')->whereIn('status', ['actioned', 'reviewed'], 'and', false)->latest('created_at')->value('admin_note')
                    ?? 'Diturunkan oleh admin karena pelanggaran pedoman.';

                return [
                    'id' => $w->id,
                    'title' => $w->title,
                    'author' => $w->user->name ?? 'Student',
                    'authorHandle' => '@'.strstr($w->user->email ?? self::$DEFAULT_STUDENT_EMAIL, '@', true),
                    'category' => $w->category ?? 'Design & UI/UX',
                    'status' => $w->status, // hidden, removed
                    'reason' => $reason,
                    'time' => $w->updated_at->diffForHumans(),
                    'thumbnail' => $this->getStorageUrl($w->cover_image),
                ];
            });

        return Inertia::render('Modules/Pagi/Admin/Takedowns/Index', [
            'takedowns' => $takedowns,
        ]);
    }

    /**
     * Works
     */
    public function works(Request $request): Response
    {
        if (PagiWork::query()->count('*') === 0) {
            $this->seedPagiDemoData();
        }

        $works = PagiWork::query()->with('user')
            ->latest('created_at')
            ->get()
            ->map(function ($w) {
                return [
                    'id' => $w->id,
                    'title' => $w->title,
                    'author' => $w->user->name ?? 'Student',
                    'authorHandle' => '@'.strstr($w->user->email ?? self::$DEFAULT_STUDENT_EMAIL, '@', true),
                    'views' => $w->views_count,
                    'category' => $w->category ?? 'Design & UI/UX',
                    'status' => $w->status, // active, warning, hidden, removed, review
                    'isPublished' => $w->is_published,
                    'time' => $w->created_at->diffForHumans(),
                    'thumbnail' => $this->getStorageUrl($w->cover_image),
                ];
            });

        return Inertia::render('Modules/Pagi/Admin/Works/Index', [
            'works' => $works,
        ]);
    }
}
