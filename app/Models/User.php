<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'role_title',
        'user_type',
        'email',
        'password',
        'program_studi_id',
        'nim_nip',
        'nomor_induk',
        'tanggal_lahir',
        'tahun_lulus',
        'otp_code',
        'otp_expires_at',
        'password_changed_at',
        'no_telepon',
        'foto_path',
        'banner_path',
        'bio',
        'location',
        'metadata',
        'website',
        'twitter',
        'linkedin',
        'github',
        'is_active',
        'status_approval',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'otp_code',
        'otp_expires_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'tanggal_lahir' => 'date',
            'tahun_lulus' => 'integer',
            'otp_expires_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<ProgramStudi, $this>
     */
    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function hasFastUserRole(): bool
    {
        return $this->isMahasiswa()
            || $this->isDosen();
    }

    public function userTypeSlug(): string
    {
        $userType = $this->normalizedUserType((string) ($this->user_type ?? ''));

        if ($userType !== '') {
            return $userType;
        }

        return 'mahasiswa';
    }

    public function roleDisplayName(): string
    {
        $roleTitle = trim((string) ($this->role_title ?? ''));

        if ($roleTitle !== '') {
            return $roleTitle;
        }

        return $this->displayRoleTitle($this->userTypeSlug());
    }

    public function roleSlug(): string
    {
        return $this->userTypeSlug();
    }

    public function hasRole(string ...$roles): bool
    {
        $roleSlug = $this->roleSlug();

        return in_array($roleSlug, array_map(
            static fn (string $role): string => str($role)->slug()->toString(),
            $roles,
        ), true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isApprover(): bool
    {
        return $this->isKaprodi() || $this->isDekan();
    }

    public function isKaprodi(): bool
    {
        return str($this->roleSlug())->contains('kaprodi');
    }

    public function isDekan(): bool
    {
        return str($this->roleSlug())->contains('dekan');
    }

    public function isMahasiswa(): bool
    {
        $roleSlug = $this->roleSlug();

        return str($roleSlug)->contains('mahasiswa');
    }

    public function isDosen(): bool
    {
        $roleSlug = $this->roleSlug();

        return $roleSlug === 'lecturer'
            || str($roleSlug)->contains('dosen');
    }

    public function isLab(): bool
    {
        $roleSlug = $this->roleSlug();

        return str($roleSlug)->contains('lab');
    }

    public function isSekfak(): bool
    {
        $roleSlug = $this->roleSlug();

        return str($roleSlug)->contains('sekfak')
            || str($roleSlug)->contains('sekretaris');
    }

    public function hasStaffAccess(): bool
    {
        return $this->isAdmin() || $this->isApprover();
    }

    public function dashboardRouteName(): string
    {
        return match (true) {
            $this->isAdmin() => 'admin.dashboard',
            $this->isKaprodi() => 'kaprodi.dashboard',
            $this->isDekan() => 'dekan.dashboard',
            $this->isDosen() => 'dosen.dashboard',
            $this->isMahasiswa() => 'mahasiswa.dashboard',
            default => abort(403),
        };
    }

    private function normalizedUserType(string $value): string
    {
        $slug = str($value)->trim()->slug()->toString();

        return match ($slug) {
            'super_admin', 'super-admin' => 'admin',
            default => $slug,
        };
    }

    private function displayRoleTitle(string $userType): string
    {
        return match ($userType) {
            'admin' => 'Admin',
            'super_admin' => 'Super Admin',
            default => ucwords(str_replace(['-', '_'], ' ', $userType)),
        };
    }
}
