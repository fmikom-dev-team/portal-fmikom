<?php

namespace App\Models;

use App\Concerns\UserHelpers;
use App\Enums\UserAccountStatus;
use App\Exceptions\SuperAdminProtectionException;
use App\Jobs\DispatchWorkOsWebhookJob;
use App\Models\Auth\AuthOAuthCredential;
use App\Models\Auth\AuthOtpToken;
use App\Models\Magang\LowonganInfo;
use App\Models\Magang\PembimbingLapangan;
use App\Models\Magang\PendaftaranMagang;
use App\Models\Magang\PenilaianMagang;
use App\Models\Pagi\PagiCv;
use App\Models\Pagi\PagiWork;
use App\Models\Tracer\Bookmark;
use App\Models\Tracer\EventRegistration;
use App\Models\Tracer\Kuesioner;
use App\Models\Tracer\MitraProfile;
use App\Models\Tracer\ProfilAlumni;
use App\Models\Tracer\Response;
use App\Models\Traits\HasPagiRelations;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $user_type
 * @property string|null $nomor_induk
 * @property UserAccountStatus $status_approval — Full account lifecycle state
 * @property bool $is_active
 * @property string|null $foto_path
 * @property string|null $location
 * @property array|null $metadata
 * @property Carbon|null $tanggal_lahir
 * @property Carbon|null $password_changed_at
 * @property Role|null $role
 * @property Collection|UserModuleRole[] $moduleRoles
 * @property Collection|AuthOAuthCredential[] $oauthCredentials
 * @property int|null $sign_in_count
 * @property string|null $last_sign_in_at
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasPagiRelations, Notifiable, Searchable, UserHelpers;

    protected $fillable = [
        'name', 'email', 'password', 'program_studi_id',
        'no_telepon', 'foto_path', 'banner_path',
        'nomor_induk',
        // otp_code / otp_expires_at removed — now in auth_otp_tokens table
        'tahun_lulus', 'password_changed_at',
        'role_title', 'bio', 'location', 'website', 'twitter', 'linkedin', 'github', 'instagram',
        'metadata', 'tanggal_lahir', 'last_seen_at', 'pagi_username', 'deletion_requested_at',
    ];

    /**
     * ⚠️ KEAMANAN — Semua field sensitif WAJIB tersembunyi dari serialisasi JSON/array.
     * Ini adalah lapis kedua keamanan selain filter di HandleInertiaRequests.php
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        // otp_code / otp_expires_at removed (now in auth_otp_tokens)
        'password_changed_at',
        'nomor_induk',
        'clearHistory',
        'encryptHistory',
    ];

    /**
     * Scout: Only index public, safe fields.
     * NEVER index: password, metadata, nomor_induk, tokens.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'pagi_username' => $this->pagi_username ?? '',
            'role_title' => $this->role_title ?? '',
            'user_type' => $this->user_type ?? '',
            'location' => $this->location ?? '',
            'bio' => $this->bio ?? '',
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_active
            && $this->status_approval === UserAccountStatus::Activated;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // otp_expires_at removed — OTP now in auth_otp_tokens table
            'password' => 'hashed',
            'is_active' => 'boolean',
            'tahun_lulus' => 'integer',
            // user_type = identity layer (mahasiswa, alumni, mitra, dosen, staff)
            // digunakan sebagai fallback role di module jika tidak ada assignment
            'user_type' => 'string',
            // status_approval = full account lifecycle state machine
            'status_approval' => UserAccountStatus::class,
            'metadata' => 'array',
            'tanggal_lahir' => 'date:Y-m-d',
            'last_seen_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'deletion_requested_at' => 'datetime',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function ($user) {
            Cache::forget("pagi_public_profile_{$user->id}");

            if ($user->wasChanged('password') || ($user->wasRecentlyCreated && $user->password)) {
                DB::afterCommit(function () use ($user) {
                    DB::table('auth_password_histories')->insert([
                        'user_id' => $user->id,
                        'password_hash' => $user->password,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });
            }
        });

        static::created(function ($user) {
            try {
                dispatch(new DispatchWorkOsWebhookJob('user.created', [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'created_at' => $user->created_at?->toIso8601String() ?? now()->toIso8601String(),
                ]));
            } catch (\Throwable $e) {
                Log::warning('User created webhook failed: '.$e->getMessage());
            }
        });

        static::deleted(function ($user) {
            try {
                dispatch(new DispatchWorkOsWebhookJob('user.deleted', [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'deleted_at' => now()->toIso8601String(),
                ]));
            } catch (\Throwable $e) {
                Log::warning('User deleted webhook failed: '.$e->getMessage());
            }
        });

        static::deleting(function ($user) {
            if ($user->user_type === 'super_admin' || $user->user_type === 'super-admin') {
                throw new SuperAdminProtectionException('Akun dengan tipe Super Admin dilindungi oleh sistem dan tidak dapat dihapus.');
            }
        });
    }

    /**
     * Relasi ke SSO global role (super-admin, admin, dll).
     * Digunakan hanya untuk CheckRole middleware (portal-level).
     * Untuk otorisasi modul, gunakan UserModuleRole + active_role session.
     *
     * @deprecated Gunakan user_type untuk identity, UserModuleRole untuk otorisasi modul.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relasi ke pivot table custom untuk Multi-Module SSO
     */
    public function moduleRoles(): HasMany
    {
        return $this->hasMany(UserModuleRole::class);
    }

    /**
     * Relasi ke akun OAuth yang terhubung.
     */
    public function oauthCredentials(): HasMany
    {
        return $this->hasMany(AuthOAuthCredential::class);
    }

    /**
     * Mengambil daftar modul unik yang bisa diakses user ini
     */
    public function accessibleModules()
    {
        return Module::whereHas('userRoles', function ($query) {
            $query->where('user_id', $this->id);
        })->get();
    }

    /**
     * @return BelongsTo<ProgramStudi, $this>
     */
    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function surats(): HasMany
    {
        return $this->hasMany(Surat::class, 'pemohon_id');
    }

    public function suratApprovals(): HasMany
    {
        return $this->hasMany(SuratApprovalFlow::class, 'approver_id');
    }

    public function pendaftaranMagangs(): HasMany
    {
        return $this->hasMany(PendaftaranMagang::class, 'mahasiswa_id');
    }

    public function penilaianMagangs(): HasMany
    {
        return $this->hasMany(PenilaianMagang::class, 'dosen_id');
    }

    public function lowonganInfos(): HasMany
    {
        return $this->hasMany(LowonganInfo::class, 'pembuat_id');
    }

    public function pembimbingLapangan(): HasOne
    {
        return $this->hasOne(PembimbingLapangan::class);
    }

    public function kuesioners()
    {
        return $this->hasMany(Kuesioner::class, 'created_by');
    }

    /**
     * @return HasOne<ProfilAlumni, $this>
     */
    public function alumniProfile(): HasOne
    {
        return $this->hasOne(ProfilAlumni::class, 'user_id');
    }

    /**
     * @return HasOne<MitraProfile, $this>
     */
    public function mitraProfile(): HasOne
    {
        return $this->hasOne(MitraProfile::class, 'user_id');
    }

    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function kuesionerResponses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    // -----------------------------------------------------------------------

    // Helper Methods — Identity Check
    // Menggunakan user_type (identity layer) sebagai sumber utama.
    // Fallback ke role->slug untuk kompatibilitas mundur.
    // -----------------------------------------------------------------------

    /**
     * Format standar untuk super-admin user_type.
     * Gunakan konstanta ini untuk konsistensi dan menghindari typo.
     */
    const USER_TYPE_SUPER_ADMIN = 'super-admin';

    public function pagiCvs(): HasMany
    {
        return $this->hasMany(PagiCv::class, 'user_id');
    }

    public function pagiWorks(): HasMany
    {
        return $this->hasMany(PagiWork::class, 'user_id');
    }

    // -----------------------------------------------------------------------

    /**
     * Centralized helper to assign default module access based on role/user_type.
     */
    public function assignDefaultModuleRoles(): void
    {
        $roleObj = Role::query()->where('slug', '=', $this->user_type, 'and')->first();
        if ($roleObj) {
            $defaultModules = [];
            if ($this->user_type === 'mahasiswa' || $this->user_type === 'dosen') {
                $defaultModules = ['FAST', 'PAGI', 'WIMS'];
            } elseif ($this->user_type === 'alumni') {
                $defaultModules = ['TRACE', 'PAGI'];
            } elseif ($this->user_type === 'mitra') {
                $defaultModules = ['WIMS', 'TRACE', 'PAGI'];
            }

            if (! empty($defaultModules)) {
                $modules = Module::query()->whereIn('code', $defaultModules, 'and', false)->get();
                foreach ($modules as $mod) {
                    UserModuleRole::firstOrCreate([
                        'user_id' => $this->id,
                        'module_id' => $mod->id,
                        'role_id' => $roleObj->id,
                    ], [
                        'is_active' => true,
                    ]);
                }
            }
        }
    }

    public function getNimNipAttribute(): ?string
    {
        return $this->attributes['nim_nip'] ?? $this->attributes['nomor_induk'] ?? null;
    }

    // ─── Account Lifecycle Relations ──────────────────────────────────────────

    /**
     * OTP tokens for this user (replaces otp_code/otp_expires_at on users table).
     */
    public function otpTokens(): HasMany
    {
        return $this->hasMany(AuthOtpToken::class);
    }

    // ─── Account Lifecycle Helpers ────────────────────────────────────────────

    /**
     * True if the user can login and access the application.
     * Only 'activated' accounts are allowed through.
     */
    public function isAccountActive(): bool
    {
        return (bool) $this->is_active
            && $this->status_approval === UserAccountStatus::Activated;
    }

    /**
     * True if the registration is pending admin review.
     */
    public function isPendingReview(): bool
    {
        return $this->status_approval === UserAccountStatus::Pending;
    }

    /**
     * True if the account is in any "in progress" activation state.
     * (approved but not yet activated)
     */
    public function isInActivation(): bool
    {
        return in_array($this->status_approval, [
            UserAccountStatus::Approved,
            UserAccountStatus::OtpSent,
            UserAccountStatus::OtpVerified,
        ]);
    }

    /**
     * True if the account has been rejected.
     */
    public function isRejected(): bool
    {
        return $this->status_approval === UserAccountStatus::Rejected;
    }

    /**
     * True if the account has been suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status_approval === UserAccountStatus::Suspended;
    }

    /**
     * Get the human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status_approval instanceof UserAccountStatus
            ? $this->status_approval->label()
            : ucfirst((string) $this->status_approval);
    }

    /**
     * Get the login block message for this user's status (null if login is allowed).
     */
    public function getLoginBlockMessage(): ?string
    {
        if (! ($this->status_approval instanceof UserAccountStatus)) {
            return 'Status akun tidak valid. Hubungi administrator.';
        }

        return $this->status_approval->loginBlockMessage();
    }

    /**
     * Whether this user needs to complete the first-time setup
     * (OTP verification → password creation).
     * Used by EnsureFirstTimeLoginComplete middleware.
     */
    public function needsFirstTimeSetup(): bool
    {
        // Admin-created users: email not verified OR no password set yet
        return is_null($this->email_verified_at) || is_null($this->password_changed_at);
    }
}
