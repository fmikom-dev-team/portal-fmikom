<?php

namespace App\Models\Auth;

use App\Enums\RegistrationStatus;
use App\Models\ProgramStudi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * RegistrationRequest — Pending registration lifecycle.
 *
 * Represents a registration attempt that has NOT yet been converted into
 * an active User record. This separates "calon user" from "user aktif".
 *
 * Applicable to:
 *   - Case B: Self-registration (mitra, alumni baru, partner)
 *   - OAuth-based registration (Google → needs approval)
 *
 * Only after admin approval → activation flow → is a User record created.
 *
 * @property string $id
 * @property string $full_name
 * @property string $email
 * @property string|null $phone
 * @property string $role
 * @property string|null $student_number
 * @property string|null $employee_number
 * @property int|null $program_studi_id
 * @property int|null $tahun_lulus
 * @property array|null $extra_data
 * @property RegistrationStatus $status
 * @property string|null $approval_notes
 * @property int|null $approved_by
 * @property Carbon|null $approved_at
 * @property int|null $rejected_by
 * @property Carbon|null $rejected_at
 * @property string|null $rejection_reason
 * @property string|null $activation_token_hash
 * @property Carbon|null $activation_token_expires_at
 * @property array|null $oauth_data
 * @property int|null $created_user_id
 */
class RegistrationRequest extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'registration_requests';

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'role',
        'student_number',
        'employee_number',
        'program_studi_id',
        'tahun_lulus',
        'extra_data',
        'status',
        'approval_notes',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'activation_token_hash',
        'activation_token_expires_at',
        'oauth_data',
        'ip_address',
        'user_agent',
        'created_user_id',
    ];

    protected $casts = [
        'status' => RegistrationStatus::class,
        'extra_data' => 'array',
        'oauth_data' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'activation_token_expires_at' => 'datetime',
        'tahun_lulus' => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function createdUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    // ─── Activation Token ─────────────────────────────────────────────────────

    /**
     * Generate and store a hashed activation token.
     * Returns the plaintext token (for inclusion in activation link).
     */
    public function generateActivationToken(): string
    {
        $token = Str::random(64);

        $this->update([
            'activation_token_hash' => Hash::make($token),
            'activation_token_expires_at' => now()->addHours(24),
        ]);

        return $token;
    }

    /**
     * Verify a plaintext activation token against the stored hash.
     */
    public function verifyActivationToken(string $token): bool
    {
        if (! $this->activation_token_hash) {
            return false;
        }

        if ($this->activation_token_expires_at && $this->activation_token_expires_at->isPast()) {
            return false;
        }

        return Hash::check($token, $this->activation_token_hash);
    }

    // ─── State Checks ─────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === RegistrationStatus::Pending;
    }

    public function isApproved(): bool
    {
        return in_array($this->status, [
            RegistrationStatus::Approved,
            RegistrationStatus::OtpSent,
            RegistrationStatus::OtpVerified,
        ]);
    }

    public function isActivated(): bool
    {
        return $this->status === RegistrationStatus::Activated;
    }

    public function isRejected(): bool
    {
        return $this->status === RegistrationStatus::Rejected;
    }

    public function hasOAuthData(): bool
    {
        return ! empty($this->oauth_data);
    }

    public function isActivationTokenExpired(): bool
    {
        return $this->activation_token_expires_at
            && $this->activation_token_expires_at->isPast();
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', RegistrationStatus::Pending->value);
    }

    public function scopeApproved($query)
    {
        return $query->whereIn('status', [
            RegistrationStatus::Approved->value,
            RegistrationStatus::OtpSent->value,
            RegistrationStatus::OtpVerified->value,
        ]);
    }

    public function scopeActivated($query)
    {
        return $query->where('status', RegistrationStatus::Activated->value);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', RegistrationStatus::Rejected->value);
    }
}
