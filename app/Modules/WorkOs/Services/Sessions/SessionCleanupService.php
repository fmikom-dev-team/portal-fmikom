<?php

namespace App\Modules\WorkOs\Services\Sessions;

use App\Models\Auth\AuthSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * SessionCleanupService — Expired Session Garbage Collector
 *
 * Handles the lifecycle of sessions that have outlived their TTL:
 *   1. Marks expired sessions as revoked (soft state)
 *   2. Purges very old revoked sessions from the database
 *   3. Reports cleanup statistics for monitoring
 *
 * Invoked by:
 *   - Scheduled Horizon job: App\Jobs\CleanExpiredSessions (every hour)
 *   - On-demand via `php artisan auth:cleanup-sessions`
 */
class SessionCleanupService
{
    /** Purge sessions revoked/expired older than this many days */
    protected int $purgeAfterDays = 30;

    /** Maximum sessions to delete per run (prevents long DB locks) */
    protected int $batchSize = 500;

    // ─────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Run the full cleanup cycle.
     * Returns stats for logging / monitoring.
     *
     * @return array{revoked: int, purged: int, orphaned: int}
     */
    public function run(): array
    {
        $revoked  = $this->revokeExpired();
        $purged   = $this->purgeOld();
        $orphaned = $this->removeOrphaned();

        logger()->info('Session cleanup completed', compact('revoked', 'purged', 'orphaned'));

        return compact('revoked', 'purged', 'orphaned');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Cleanup Steps
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Step 1 — Mark all sessions past their `expires_at` as revoked.
     * This is a soft operation — data is preserved for audit purposes.
     */
    public function revokeExpired(): int
    {
        return AuthSession::where('is_revoked', false)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', Carbon::now())
            ->update(['is_revoked' => true]);
    }

    /**
     * Step 2 — Hard delete sessions that have been revoked for more than N days.
     * Runs in batches to avoid long table locks.
     */
    public function purgeOld(): int
    {
        $threshold = Carbon::now()->subDays($this->purgeAfterDays);
        $total = 0;

        do {
            $deleted = AuthSession::where('is_revoked', true)
                ->where('updated_at', '<', $threshold)
                ->limit($this->batchSize)
                ->delete();

            $total += $deleted;
        } while ($deleted === $this->batchSize); // Continue if batch was full

        return $total;
    }

    /**
     * Step 3 — Remove sessions whose user no longer exists (orphaned records).
     * Happens when users are hard-deleted without cascading session cleanup.
     */
    public function removeOrphaned(): int
    {
        return AuthSession::whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('users')
                ->whereColumn('users.id', 'auth_sessions.user_id');
        })->delete();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Reporting
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Get statistics about the current session state.
     * Used by the WorkOS analytics dashboard.
     */
    public function stats(): array
    {
        $now = Carbon::now();

        return [
            'total_active'   => AuthSession::where('is_revoked', false)->where('expires_at', '>', $now)->count(),
            'total_revoked'  => AuthSession::where('is_revoked', true)->count(),
            'total_expired'  => AuthSession::where('is_revoked', false)->where('expires_at', '<', $now)->count(),
            'high_risk'      => AuthSession::where('is_revoked', false)->where('risk_score', '>', 60)->count(),
            'expiring_soon'  => AuthSession::where('is_revoked', false)
                ->whereBetween('expires_at', [$now, $now->copy()->addHours(24)])
                ->count(),
        ];
    }
}
