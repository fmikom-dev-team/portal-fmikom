<?php

namespace App\Modules\Fast\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Modules\Fast\Services\Shared\NotificationFeedService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    protected const NOTIFICATION_TYPE_PREFIX = 'fast.notification';

    public function __construct(
        protected NotificationFeedService $notificationFeedService,
    ) {}

    public function read(Request $request, string $notificationId): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);
        $notifiableType = $user->getMorphClass();

        if ($this->notificationsTableReady()) {
            $updated = DB::table('notifications')
                ->where('id', $notificationId)
                ->where('notifiable_type', $notifiableType)
                ->where('notifiable_id', $user->id)
                ->where('type', 'like', self::NOTIFICATION_TYPE_PREFIX.'%')
                ->whereNull('read_at')
                ->update(['read_at' => now(), 'updated_at' => now()]);

            if ($updated > 0) {
                $this->forgetNotificationCaches($user->id);
            }
        }

        $redirectTo = $request->string('redirect_to')->trim()->toString();
        if ($redirectTo !== '' && Str::startsWith($redirectTo, '/')) {
            return redirect()->to($redirectTo);
        }

        return back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);
        $notifiableType = $user->getMorphClass();
        $activeRole = $this->resolveFastRole($request, $user);

        if ($this->notificationsTableReady()) {
            $notificationTypes = $this->notificationFeedService->notificationTypesForRole($user, $activeRole);

            $query = DB::table('notifications')
                ->where('notifiable_type', $notifiableType)
                ->where('notifiable_id', $user->id)
                ->where('type', 'like', self::NOTIFICATION_TYPE_PREFIX.'%')
                ->whereNull('read_at');

            if ($notificationTypes !== []) {
                $query->whereIn('type', $notificationTypes);
            }

            $query->update(['read_at' => Carbon::now(), 'updated_at' => now()]);
            $this->forgetNotificationCaches($user->id);
        }

        $redirectTo = $request->string('redirect_to')->trim()->toString();
        if ($redirectTo !== '' && Str::startsWith($redirectTo, '/')) {
            return redirect()->to($redirectTo);
        }

        return back();
    }

    protected function forgetNotificationCaches(int $userId): void
    {
        foreach (['admin', 'kaprodi', 'dekan', 'mahasiswa', 'dosen'] as $role) {
            Cache::forget("fast_notifications_{$userId}_{$role}");
            Cache::forget("unread_notif_count_{$userId}_{$role}");
            Cache::forget("recent_notifs_{$userId}_{$role}");
        }
    }

    protected function notificationsTableReady(): bool
    {
        return DB::getSchemaBuilder()->hasTable('notifications');
    }

    protected function resolveFastRole(Request $request, $user): string
    {
        $routeRole = strtolower((string) ($request->segment(1) ?? ''));

        return strtolower((string) $request->attributes->get(
            'resolved_role',
            session('active_role', $routeRole ?: ($user->userTypeSlug() ?? '')),
        ));
    }
}
