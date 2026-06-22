<?php

namespace App\Modules\Pagi\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pagi\PagiReport;
use App\Models\Pagi\PagiWarning;
use App\Models\Pagi\PagiWork;
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
    public function mahasiswa(Request $request): Response
    {
        if (PagiWork::query()->count('*') === 0) {
            $this->seedPagiDemoData();
        }

        $users = User::query()->where('user_type', '=', 'mahasiswa', 'and')
            ->withCount('pagiWorks')
            ->get();

        // Pre-load warned user IDs in one query to avoid N+1 inside map()
        $warnedUserIds = PagiWarning::query()->where('is_active', '=', true, 'and')
            ->whereIn('user_id', $users->pluck('id'), 'and', false)
            ->pluck('user_id')
            ->flip();

        $users = $users->map(function ($u) use ($warnedUserIds) {
            $status = 'suspended';
            if ($warnedUserIds->has($u->id)) {
                $status = 'warning';
            } elseif ($u->is_active) {
                $status = 'active';
            }

            return [
                'id' => $u->id,
                'name' => $u->name,
                'handle' => '@'.strstr($u->email, '@', true),
                'email' => $u->email,
                'nim' => $u->nomor_induk ?: '-',
                'prodi' => match ((int) $u->program_studi_id) {
                    1 => 'Informatika',
                    2 => 'Sistem Informasi',
                    default => 'Matematika',
                },
                'status' => $status,
                'karyaCount' => $u->pagi_works_count,
                'joinDate' => $u->created_at->format('d M Y'),
            ];
        });

        return Inertia::render('Modules/Pagi/Admin/Users/Mahasiswa', [
            'users' => $users,
        ]);
    }

    /**
     * Mitra Management
     */
    public function mitra(Request $request): Response
    {
        if (PagiWork::query()->count('*') === 0) {
            $this->seedPagiDemoData();
        }

        $mitras = User::query()->where('user_type', '=', 'mitra', 'and')
            ->withCount('pagiWorks')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'pic' => $u->metadata['pic'] ?? 'PIC Perusahaan',
                    'status' => $u->is_active ? 'active' : 'suspended',
                    'karyaCount' => $u->pagi_works_count,
                    'joinDate' => $u->created_at->format('d M Y'),
                ];
            });

        // Fallback demo partners if database contains no partners yet
        if ($mitras->isEmpty()) {
            $mitras = collect([
                [
                    'id' => 101,
                    'name' => 'PT Telkom Indonesia',
                    'email' => 'hr@telkom.co.id',
                    'pic' => 'Budi Santoso',
                    'status' => 'active',
                    'karyaCount' => 5,
                    'joinDate' => '12 Sep 2021',
                ],
                [
                    'id' => 102,
                    'name' => 'Gojek Tokopedia (GoTo)',
                    'email' => 'partners@goto.com',
                    'pic' => 'Andi Wijaya',
                    'status' => 'active',
                    'karyaCount' => 10,
                    'joinDate' => '15 Oct 2022',
                ],
                [
                    'id' => 103,
                    'name' => 'Shopee Indonesia',
                    'email' => 'career@shopee.co.id',
                    'pic' => 'Sinta',
                    'status' => 'warning',
                    'karyaCount' => 2,
                    'joinDate' => '20 Nov 2022',
                ],
                [
                    'id' => 104,
                    'name' => 'Ruangguru',
                    'email' => 'info@ruangguru.com',
                    'pic' => 'Deni',
                    'status' => 'active',
                    'karyaCount' => 8,
                    'joinDate' => '10 Jan 2023',
                ],
            ]);
        }

        return Inertia::render('Modules/Pagi/Admin/Users/Mitra', [
            'mitras' => $mitras,
        ]);
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
}
