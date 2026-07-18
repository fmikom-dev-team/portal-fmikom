<?php

namespace App\Models\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * IdentityVerification — Short-lived session for Case A (admin-driven) identity claims.
 *
 * When a mahasiswa/dosen/staff tries to activate their pre-existing account,
 * they verify identity via NIM/NIDN + tanggal lahir. This model stores that
 * verification session temporarily while the OTP flow completes.
 *
 * Security design:
 *  - session_token: random 64-char key, used as lookup key (NOT stored in cookie)
 *  - expires_at: short TTL (30 minutes)
 *  - attempt_count: max 5 attempts before lockout
 *  - resolved_user_id: set only after successful identity match
 *
 * @property string $id
 * @property string $session_token
 * @property string $user_type
 * @property string $identifier (NIM or NIDN)
 * @property Carbon $tanggal_lahir
 * @property int|null $resolved_user_id
 * @property string $status (pending|verified|failed|expired)
 * @property int $attempt_count
 * @property int $max_attempts
 * @property Carbon|null $verified_at
 * @property Carbon $expires_at
 */
class IdentityVerification extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'identity_verifications';

    protected $fillable = [
        'session_token',
        'user_type',
        'identifier',
        'tanggal_lahir',
        'resolved_user_id',
        'status',
        'attempt_count',
        'max_attempts',
        'verified_at',
        'expires_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
        'attempt_count' => 'integer',
        'max_attempts' => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function resolvedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_user_id');
    }

    // ─── Factory Methods ──────────────────────────────────────────────────────

    /**
     * Create a new identity verification session.
     * Returns the newly created model (session_token is generated here).
     */
    public static function start(
        string $userType,
        string $identifier,
        string $tanggalLahir,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): static {
        return static::create([
            'session_token' => Str::random(64),
            'user_type' => $userType,
            'identifier' => $identifier,
            'tanggal_lahir' => $tanggalLahir,
            'status' => 'pending',
            'attempt_count' => 0,
            'max_attempts' => 5,
            'expires_at' => now()->addMinutes(30),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Find an active (non-expired, pending) verification session by token.
     */
    public static function findActiveByToken(string $token): ?static
    {
        return static::where('session_token', '=', $token, 'and')
            ->where('status', '=', 'pending', 'and')
            ->where('expires_at', '>', now(), 'and')
            ->first();
    }

    /**
     * Find a verified session by token (for the OTP step).
     */
    public static function findVerifiedByToken(string $token): ?static
    {
        return static::where('session_token', '=', $token, 'and')
            ->where('status', '=', 'verified', 'and')
            ->where('expires_at', '>', now(), 'and')
            ->first();
    }

    // ─── State Transitions ────────────────────────────────────────────────────

    public function markVerified(User $user): void
    {
        $this->fill([
            'status' => 'verified',
            'resolved_user_id' => $user->id,
            'verified_at' => now(),
        ])->save();
    }

    public function markFailed(): void
    {
        $this->increment('attempt_count', 1);

        if ($this->attempt_count + 1 >= $this->max_attempts) {
            $this->fill(['status' => 'failed'])->save();
        }
    }

    // ─── State Checks ─────────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isLocked(): bool
    {
        return $this->attempt_count >= $this->max_attempts || $this->status === 'failed';
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function remainingAttempts(): int
    {
        return max(0, $this->max_attempts - $this->attempt_count);
    }
}
