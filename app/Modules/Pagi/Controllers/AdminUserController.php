<?php

namespace App\Modules\Pagi\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pagi\PagiReport;
use App\Models\Pagi\PagiWarning;
use App\Models\Pagi\PagiWork;
use App\Models\Portal\PortalSetting;
use App\Models\User;
use App\Modules\Pagi\Controllers\Concerns\HasAdminDashboardHelpers;
use App\Notifications\PagiNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    use HasAdminDashboardHelpers;

    /**
     * Mahasiswa Management
     */
    /**
     * Unified Users Management
     */
    public function index(Request $request): Response
    {
        $role = $request->attributes->get('resolved_role', session('active_role'));

        $query = User::query();

        // Abaikan akun test/dummy internal
        $query->where('email', 'not like', '%@test.com')
            ->where('email', 'not like', '%@fmikom.test');

        // Filter Default: Hanya tampilkan pengguna yang sudah aktif (activated) di WorkOS
        $statusFilter = $request->input('status');
        if ($statusFilter === 'suspended') {
            $query->where('status_approval', '=', 'suspended');
        } elseif ($statusFilter === 'pending') {
            $query->where('status_approval', '!=', 'activated')
                ->where('status_approval', '!=', 'suspended');
        } elseif ($statusFilter === 'warning' || $statusFilter === 'active') {
            $query->where('status_approval', '=', 'activated')
                ->where('is_active', '=', true);
        } else {
            // Default (termasuk 'all' atau tanpa parameter status):
            // Hanya tampilkan akun yang sudah aktif & diaktivasi di WorkOS
            $query->where('status_approval', '=', 'activated')
                ->where('is_active', '=', true);
        }

        $query->where(function ($q) {
            $q->whereIn('user_type', ['mahasiswa', 'mitra', 'dosen', 'alumni'], 'and', false)
                ->orWhereHas('moduleRoles');
        });

        // Security Scope: Restrict Prodi Role to their own department's students
        if (strtolower($role) === 'prodi') {
            /** @var User $adminUser */
            $adminUser = Auth::user();
            if ($adminUser && $adminUser->program_studi_id) {
                $query->where('program_studi_id', '=', $adminUser->program_studi_id, 'and');
            }
        }

        // Server-Side Search Filter
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nomor_induk', 'like', "%{$search}%");
            });
        }

        // Server-Side Type Filter
        if ($type = $request->input('type')) {
            if (in_array($type, ['mahasiswa', 'mitra', 'dosen', 'alumni'], true)) {
                $query->where('user_type', '=', $type, 'and');
            }
        }

        $paginator = $query->withCount('pagiWorks')
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $pageItems = collect($paginator->items());

        // Pre-load warned user IDs in one query to avoid N+1 inside map()
        $warnedUserIds = PagiWarning::query()->where('is_active', '=', true, 'and')
            ->whereIn('user_id', $pageItems->pluck('id'), 'and', false)
            ->pluck('user_id')
            ->flip();

        $formattedUsers = $pageItems->map(function ($u) use ($warnedUserIds) {
            $statusApprovalStr = is_object($u->status_approval) ? $u->status_approval->value : (string) $u->status_approval;

            if ($statusApprovalStr === 'suspended') {
                $status = 'suspended';
            } elseif (! $u->is_active || $statusApprovalStr !== 'activated') {
                $status = 'pending';
            } elseif ($warnedUserIds->has($u->id)) {
                $status = 'warning';
            } else {
                $status = 'active';
            }

            return [
                'id' => $u->id,
                'name' => $u->name,
                'type' => $u->user_type, // 'mahasiswa' | 'mitra' | 'dosen' | 'alumni'
                'handle' => $u->user_type === 'mahasiswa' ? '@'.strstr($u->email, '@', true) : null,
                'email' => $u->email,
                'nim' => $u->user_type === 'mahasiswa' ? ($u->nomor_induk ?: '-') : null,
                'prodi' => $u->user_type === 'mahasiswa' ? match ((int) $u->program_studi_id) {
                    1 => 'Informatika',
                    2 => 'Sistem Informasi',
                    default => 'Matematika',
                } : null,
                'pic' => $u->user_type === 'mitra' ? ($u->metadata['pic'] ?? 'PIC Perusahaan') : null,
                'status' => $status,
                'karyaCount' => $u->pagi_works_count,
                'joinDate' => $u->created_at->format('d M Y'),
            ];
        });

        // Filter status locally on formatted collection if filterStatus is requested
        $filterStatus = $request->input('status');
        if ($filterStatus && in_array($filterStatus, ['active', 'warning', 'suspended'], true)) {
            $formattedUsers = $formattedUsers->filter(fn ($u) => $u['status'] === $filterStatus)->values();
        }

        return Inertia::render('Modules/Pagi/Admin/Users/Index', [
            'users' => [
                'data' => $formattedUsers,
                'links' => $paginator->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'from' => $paginator->firstItem(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'to' => $paginator->lastItem(),
                    'total' => $paginator->total(),
                ],
            ],
            'filters' => [
                'search' => $request->input('search', ''),
                'type' => $request->input('type', 'all'),
                'status' => $request->input('status', 'all'),
            ],
        ]);
    }

    /**
     * Admin update user status (active / warning / suspended)
     */
    public function updateUserStatus(Request $request, int $userId)
    {
        $targetUser = User::query()->findOrFail($userId);
        $role = $request->attributes->get('resolved_role', session('active_role'));

        // Otorisasi program studi untuk peran prodi
        $this->authorizeProdiRole($role, $targetUser, 'Akses Ditolak: Anda hanya dapat mengubah status mahasiswa program studi Anda sendiri.');

        $request->validate([
            'status' => 'required|in:active,warning,suspended',
            'reason' => 'nullable|string|max:500',
        ]);

        $newStatus = $request->status;
        $reason = $request->reason ?: 'Perubahan status oleh administrator.';

        if ($newStatus === 'active') {
            // Restore account active status & revoke active warnings
            $targetUser->forceFill(['is_active' => true])->save();
            PagiWarning::query()->where('user_id', '=', $userId, 'and')
                ->where('is_active', '=', true, 'and')
                ->update(['is_active' => false]);

            $targetUser->notify(new PagiNotification(
                type: 'system',
                title: 'Status Akun Aktif',
                message: 'Akun Anda telah diaktifkan kembali oleh administrator.',
                avatar: null,
                href: '/pagi'
            ));
        } elseif ($newStatus === 'suspended') {
            // Suspend account
            $targetUser->forceFill(['is_active' => false])->save();

            $targetUser->notify(new PagiNotification(
                type: 'admin_warning',
                title: 'Akun Ditangguhkan',
                message: 'Akun Anda telah ditangguhkan oleh admin. Alasan: '.$reason,
                avatar: null,
                href: '/pagi/notifications',
                extra: ['reason' => $reason]
            ));
        } elseif ($newStatus === 'warning') {
            // Ensure account is active so it displays as warning
            $targetUser->forceFill(['is_active' => true])->save();

            // Issue warning
            PagiWarning::create([
                'user_id' => $userId,
                'issued_by' => Auth::id() ?: 1,
                'severity' => 'medium',
                'type' => 'inappropriate_content',
                'reason' => $reason,
                'is_active' => true,
                'expires_at' => now()->addDays(30),
            ]);

            $targetUser->notify(new PagiNotification(
                type: 'admin_warning',
                title: 'Peringatan Akun',
                message: 'Akun Anda menerima peringatan dari admin: '.$reason,
                avatar: null,
                href: '/pagi/notifications',
                extra: ['reason' => $reason]
            ));
        }

        return back()->with('success', 'Status pengguna berhasil diperbarui.');
    }

    /**
     * Warn a user
     */
    public function warnUser(Request $request, int $userId)
    {
        $user = User::query()->findOrFail($userId);
        $role = $request->attributes->get('resolved_role', session('active_role'));

        // Otorisasi program studi untuk peran prodi
        $this->authorizeProdiRole($role, $user, 'Akses Ditolak: Anda hanya dapat memperingatkan mahasiswa dari program studi Anda sendiri.');

        $request->validate([
            'reason' => 'required|string|max:500',
            'content_id' => 'nullable|integer|exists:pagi_works,id',
        ]);

        $warning = PagiWarning::create([
            'user_id' => $userId,
            'work_id' => $request->content_id,
            'issued_by' => Auth::id() ?: 1,
            'severity' => 'medium',
            'type' => 'inappropriate_content',
            'reason' => $request->reason,
            'is_active' => true,
            'expires_at' => now()->addDays(30),
        ]);

        // Send notification to the user warned
        $work = $request->content_id ? PagiWork::query()->find($request->content_id, ['*']) : null;

        $user->notify(new PagiNotification(
            type: 'admin_warning',
            title: 'Peringatan Akun',
            message: 'Anda menerima peringatan dari admin: '.$request->reason.($work ? ' (terkait karya "'.$work->title.'")' : ''),
            avatar: null,
            href: '/pagi/notifications',
            extra: [
                'warning_id' => $warning->id,
                'work_id' => $request->content_id,
                'reason' => $request->reason,
                'edit_url' => $request->content_id ? '/pagi/editor?id='.$request->content_id : null,
                'appeal' => true,
            ]
        ));

        // If it was related to a work, we should also resolve any pending reports on that work and notify reporters
        if ($request->content_id) {
            $this->resolvePendingReportsAndNotify((int) $request->content_id, $request->reason, $work);
        }

        // Cek apakah total warning melebihi batas pagi_max_warnings_before_suspend untuk auto-suspend
        $this->checkAutoSuspendUser($user);

        return back()->with('success', 'Peringatan berhasil dikirim.');
    }

    /**
     * Revoke user warning
     */
    public function revokeWarning(Request $request, int $warningId)
    {
        $warning = PagiWarning::query()->findOrFail($warningId);
        $role = $request->attributes->get('resolved_role', session('active_role'));

        // Otorisasi program studi untuk peran prodi
        $targetUser = User::query()->find($warning->user_id, ['*']);
        if ($targetUser) {
            $this->authorizeProdiRole($role, $targetUser, 'Akses Ditolak: Anda hanya dapat mencabut peringatan mahasiswa program studi Anda sendiri.');
        }
        $warning->fill(['is_active' => false])->save();

        // Automatic account restoration check if user was suspended
        if ($targetUser && ! $targetUser->is_active) {
            $maxAllowed = (int) (PortalSetting::query()->where('key', 'pagi_max_warnings_before_suspend')->value('value') ?? 3);
            $remainingActiveCount = PagiWarning::query()
                ->where('user_id', $targetUser->id)
                ->where('is_active', true)
                ->count();

            if ($remainingActiveCount < $maxAllowed) {
                $targetUser->forceFill(['is_active' => true])->save();

                $targetUser->notify(new PagiNotification(
                    type: 'system',
                    title: 'Status Akun Dipulihkan',
                    message: 'Akun Anda telah diaktifkan kembali karena Surat Peringatan (SP) Anda telah dicabut.',
                    avatar: null,
                    href: '/pagi'
                ));
            }
        }

        return back()->with('success', 'Peringatan berhasil dicabut.');
    }

    /**
     * Admin sends a notification to the owner of a reported work
     * action: 'warn' | 'takedown' | 'message'
     */
    public function sendNotificationToUser(Request $request, int $userId)
    {
        $targetUser = User::query()->findOrFail($userId);
        $role = $request->attributes->get('resolved_role', session('active_role'));

        // Otorisasi program studi untuk peran prodi
        $this->authorizeProdiRole($role, $targetUser, 'Akses Ditolak: Anda hanya dapat mengirim notifikasi kepada mahasiswa program studi Anda sendiri.');

        $request->validate([
            'message' => 'required|string|min:5|max:1000',
            'action' => 'required|in:warn,takedown,message',
            'work_id' => 'nullable|integer|exists:pagi_works,id',
        ]);

        /** @var User $admin */
        $admin = Auth::user();

        $actionLabel = match ($request->action) {
            'warn' => 'Peringatan',
            'takedown' => 'Takedown',
            default => 'Pesan Admin',
        };

        $type = 'admin_message';
        if ($request->action === 'takedown') {
            $type = 'admin_takedown';
        } elseif ($request->action === 'warn') {
            $type = 'admin_warning';
        }

        // Store notification in the DB using Laravel's notification system
        $targetUser->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'App\\Notifications\\PagiNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $userId,
            'data' => json_encode([
                'type' => $type,
                'title' => $actionLabel.' dari Admin',
                'message' => $request->message,
                'work_id' => $request->work_id,
                'admin_name' => $admin->name ?? 'Admin',
                'action' => $request->action,
                'options' => $request->action === 'takedown' ? ['takedown', 'delete'] : [],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->applyNotificationSideEffects(
            $request->action,
            $userId,
            $request->work_id ? (int) $request->work_id : null,
            $request->message,
            $admin->id ?: 1
        );

        return back()->with('success', 'Notifikasi berhasil dikirim ke pengguna.');
    }

    /**
     * Authorize action for the 'prodi' role.
     */
    private function authorizeProdiRole(string $role, User $targetUser, string $errorMessage): void
    {
        if (strtolower($role) === 'prodi') {
            /** @var User $adminUser */
            $adminUser = Auth::user();
            $adminProdiId = $adminUser->program_studi_id;
            if ($adminProdiId && (int) $targetUser->program_studi_id !== (int) $adminProdiId) {
                abort(403, $errorMessage);
            }
        }
    }

    /**
     * Resolve pending reports on a work and notify reporters.
     */
    private function resolvePendingReportsAndNotify(int $workId, string $reason, ?PagiWork $work): void
    {
        // Get all pending reports for this work
        $reports = PagiReport::query()->where('work_id', '=', $workId, 'and')
            ->where('status', '=', 'pending', 'and')
            ->get();

        PagiReport::query()->where('work_id', '=', $workId, 'and')
            ->where('status', '=', 'pending', 'and')
            ->update([
                'status' => 'actioned',
                'reviewed_by' => Auth::id() ?: 1,
                'admin_note' => 'Peringatan dikirim ke pengguna: '.$reason,
                'reviewed_at' => now(),
            ]);

        foreach ($reports as $r) {
            $reporter = $r->reporter ?? User::query()->find($r->reporter_id, ['*']);
            if ($reporter) {
                $reporter->notify(new PagiNotification(
                    type: 'system',
                    title: 'Tindakan Laporan',
                    message: 'Terima kasih atas laporan Anda. Kami telah mengambil tindakan terhadap karya "'.($work->title ?? 'terkait').'" yang Anda laporkan.',
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

    /**
     * Apply secondary effects when sending notification (takedown or warn).
     */
    private function applyNotificationSideEffects(string $action, int $userId, ?int $workId, string $message, int $adminId): void
    {
        // If takedown action, hide the work
        if ($action === 'takedown' && $workId) {
            PagiWork::query()->where('id', '=', $workId, 'and')->update(['status' => 'hidden']);
            PagiReport::query()->where('work_id', '=', $workId, 'and')
                ->where('status', '=', 'pending', 'and')
                ->update(['status' => 'actioned', 'reviewed_by' => $adminId, 'reviewed_at' => now()]);
        }

        // If warn, also create a PagiWarning
        if ($action === 'warn' && $workId) {
            PagiWarning::create([
                'user_id' => $userId,
                'work_id' => $workId,
                'issued_by' => $adminId,
                'severity' => 'medium',
                'type' => 'inappropriate_content',
                'reason' => $message,
                'is_active' => true,
                'expires_at' => now()->addDays(30),
            ]);
        }
    }

    /**
     * PAGI Admin Instant Search Endpoint (JSON)
     */
    public function instantSearch(Request $request)
    {
        $q = trim((string) $request->input('q'));
        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // 1. Search Works
        $works = PagiWork::query()
            ->where('title', 'like', "%{$q}%")
            ->limit(5)
            ->get();

        foreach ($works as $w) {
            $results[] = [
                'title' => $w->title,
                'subtitle' => 'Karya oleh '.($w->user->name ?? 'Mahasiswa'),
                'category' => 'Karya PAGI',
                'href' => '/pagi/admin/moderation',
                'avatar' => $this->getStorageUrl($w->cover_image),
            ];
        }

        // 2. Search Users
        $users = User::query()
            ->whereIn('user_type', ['mahasiswa', 'mitra'], 'and', false)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('nomor_induk', 'like', "%{$q}%");
            })
            ->limit(5)
            ->get();

        foreach ($users as $u) {
            $results[] = [
                'title' => $u->name,
                'subtitle' => $u->user_type === 'mahasiswa' ? $u->email.' (NIM: '.($u->nomor_induk ?: '-').')' : $u->email,
                'category' => $u->user_type === 'mahasiswa' ? 'Mahasiswa' : 'Mitra Industri',
                'href' => '/pagi/admin/users?search='.urlencode($u->name),
                'avatar' => null,
            ];
        }

        return response()->json($results);
    }

    /**
     * Otomatis menangguhkan akun jika total warning aktif melampaui batas pagi_max_warnings_before_suspend
     */
    private function checkAutoSuspendUser(User $targetUser): void
    {
        $maxAllowed = (int) (PortalSetting::query()->where('key', 'pagi_max_warnings_before_suspend')->value('value') ?? 3);
        $activeWarningsCount = PagiWarning::query()
            ->where('user_id', $targetUser->id)
            ->where('is_active', true)
            ->count();

        if ($activeWarningsCount >= $maxAllowed) {
            $targetUser->forceFill(['is_active' => false])->save();

            $targetUser->notify(new PagiNotification(
                type: 'admin_warning',
                title: 'Akun Otomatis Ditangguhkan',
                message: "Akun Anda otomatis ditangguhkan karena telah mencapai batas maksimal ({$maxAllowed}) peringatan aktif.",
                avatar: null,
                href: '/pagi/notifications',
                extra: ['auto_suspended' => true, 'warning_count' => $activeWarningsCount]
            ));
        }
    }
}
