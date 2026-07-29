<?php

namespace App\Models\Auth;

use App\Enums\OtpPurpose;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * AuthOtpToken — Isolated OTP token management.
 *
 * Replaces otp_code/otp_expires_at columns in the users table.
 * Supports multiple concurrent OTPs for different purposes.
 * Tracks attempts to prevent brute force.
 *
 * @property string $id
 * @property int|null $user_id
 * @property string $email
 * @property string $purpose
 * @property string $token_hash
 * @property int $attempt_count
 * @property int $max_attempts
 * @property bool $is_used
 * @property Carbon|null $used_at
 * @property Carbon $expires_at
 */
class AuthOtpToken extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'auth_otp_tokens';

    protected $fillable = [
        'user_id',
        'email',
        'purpose',
        'token_hash',
        'attempt_count',
        'max_attempts',
        'is_used',
        'used_at',
        'expires_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
        'attempt_count' => 'integer',
        'max_attempts' => 'integer',
        'purpose' => OtpPurpose::class,
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Factory Methods ──────────────────────────────────────────────────────

    /**
     * Generate a new 6-digit OTP, hash it, and store.
     * Returns the plaintext OTP (to be sent via email).
     *
     * @return array{model: static, plaintext: string}
     */
    public static function generate(
        ?int $userId,
        string $email,
        OtpPurpose $purpose,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array {
        // [FIX FIND-006] Bungkus invalidasi + pembuatan dalam satu transaction + lockForUpdate()
        // untuk mencegah race condition di mana dua request bersamaan menghasilkan dua OTP aktif.
        return DB::transaction(function () use ($userId, $email, $purpose, $ipAddress, $userAgent) {
            // Invalidate existing active OTPs (with lock to prevent concurrent reads)
            static::where('email', '=', $email, 'and')
                ->where('purpose', '=', $purpose->value, 'and')
                ->where('is_used', '=', false, 'and')
                ->where('expires_at', '>', now(), 'and')
                ->lockForUpdate()
                ->update(['is_used' => true, 'used_at' => now()]);

            $plaintext = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $model = static::create([
                'user_id' => $userId,
                'email' => $email,
                'purpose' => $purpose->value,
                'token_hash' => Hash::make($plaintext),
                'attempt_count' => 0,
                'max_attempts' => $purpose->maxAttempts(),
                'is_used' => false,
                'expires_at' => now()->addMinutes($purpose->ttlMinutes()),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            return ['model' => $model, 'plaintext' => $plaintext];
        });
    }

    /**
     * Find the latest valid (non-expired, non-used) OTP for a given email+purpose.
     */
    public static function findActive(string $email, OtpPurpose $purpose): ?static
    {
        return static::where('email', '=', $email, 'and')
            ->where('purpose', '=', $purpose->value, 'and')
            ->where('is_used', '=', false, 'and')
            ->where('expires_at', '>', now(), 'and')
            ->latest()
            ->first();
    }

    // ─── Verification ─────────────────────────────────────────────────────────

    /**
     * Attempt to verify a plaintext OTP code.
     * Increments attempt_count on failure.
     * Marks as used on success.
     *
     * [FIX H-9, M-7] Dibungkus dalam DB::transaction() + lockForUpdate() untuk mencegah
     * race condition di mana dua request concurrent (mis. double-click atau multi-tab)
     * keduanya membaca status is_used=false, keduanya lolos Hash::check(), dan keduanya
     * mendapat return 'ok' sehingga OTP terpakai dua kali.
     *
     * Dengan lock, hanya satu request yang bisa memproses token pada satu waktu.
     * Request kedua akan membaca is_used=true dan mendapat return 'used'.
     *
     * @return 'ok'|'invalid'|'expired'|'used'|'locked'
     */
    public function verify(string $plaintext): string
    {
        return DB::transaction(function () use ($plaintext) {
            // Re-fetch dengan lock di dalam transaction untuk prevent race condition
            /** @var static|null $fresh */
            $fresh = static::where('id', '=', $this->id, 'and')
                ->lockForUpdate()
                ->first();

            // Jika token tidak ditemukan (sangat jarang, tapi defensif)
            if (! $fresh) {
                return 'used';
            }

            if ($fresh->is_used) {
                return 'used';
            }

            if ($fresh->isExpired()) {
                return 'expired';
            }

            if ($fresh->isLocked()) {
                return 'locked';
            }

            if (! Hash::check($plaintext, $fresh->token_hash)) {
                $fresh->increment('attempt_count', 1);

                // Sync model state
                $this->attempt_count = $fresh->attempt_count;

                return 'invalid';
            }

            // Success — mark as used
            $fresh->fill([
                'is_used' => true,
                'used_at' => now(),
            ])->save();

            // Sync model state agar caller punya data terkini
            $this->is_used = true;
            $this->used_at = $fresh->used_at;

            return 'ok';
        });
    }

    // ─── State Checks ─────────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isLocked(): bool
    {
        return $this->attempt_count >= $this->max_attempts;
    }

    public function isValid(): bool
    {
        return ! $this->is_used && ! $this->isExpired() && ! $this->isLocked();
    }

    public function remainingAttempts(): int
    {
        return max(0, $this->max_attempts - $this->attempt_count);
    }
}
