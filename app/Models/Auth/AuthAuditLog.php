<?php

namespace App\Models\Auth;

use App\Models\User;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * AuthAuditLog — Immutable append-only audit trail.
 * Never update or delete records. Only insert.
 *
 * Standard event names:
 *   auth.login.success, auth.login.failed, auth.logout
 *   auth.mfa.enabled, auth.mfa.disabled, auth.mfa.failed
 *   auth.passkey.registered, auth.passkey.used
 *   auth.session.created, auth.session.revoked
 *   auth.oauth.connected, auth.oauth.disconnected
 *   auth.magic_link.sent, auth.magic_link.used
 *   auth.setting.changed, auth.provider.toggled
 */
class AuthAuditLog extends Model
{
    use HasUuids;

    protected $table = 'auth_audit_logs';

    // No timestamps() — uses occurred_at only
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'event', 'actor_type',
        'ip_address', 'user_agent',
        'metadata', 'severity', 'occurred_at',
    ];

    protected $casts = [
        'metadata'    => 'array',
        'occurred_at' => 'datetime',
    ];

    // ─── Factory Method ───────────────────────────────────────────────────

    public static function log(
        string $event,
        ?int $userId = null,
        array $metadata = [],
        string $severity = 'info',
    ): static {
        $request = request();

        return static::create([
            'event'       => $event,
            'user_id'     => $userId,
            'actor_type'  => 'user',
            'ip_address'  => $request?->ip(),
            'user_agent'  => $request?->userAgent(),
            'metadata'    => $metadata,
            'severity'    => $severity,
            'occurred_at' => now(),
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
