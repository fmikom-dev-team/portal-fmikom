<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
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
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
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
            'tanggal_lahir' => 'date',
            'tahun_lulus' => 'integer',
            'two_factor_confirmed_at' => 'datetime',
            'metadata' => 'array',
            'has_completed_pkl' => 'boolean',
            'pkl_completed_at' => 'datetime',
        ];
    }

    public function pendaftaranMagangs(): HasMany
    {
        return $this->hasMany(PendaftaranMagang::class, 'mahasiswa_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id');
    }

    public function penilaianSebagaiDosen(): HasMany
    {
        return $this->hasMany(PenilaianMagang::class, 'dosen_id');
    }

    public function assessmentSubmissions(): HasMany
    {
        return $this->hasMany(AssessmentSubmission::class, 'assessor_id');
    }

    public function suratPenetapanRequests(): HasMany
    {
        return $this->hasMany(SuratPenetapan::class, 'requested_by');
    }

    public function perusahaanMitra(): HasOne
    {
        return $this->hasOne(PerusahaanMitra::class, 'user_id');
    }

    public function ketidakhadiranMagangs(): HasMany
    {
        return $this->hasMany(KetidakhadiranMagang::class, 'mahasiswa_id');
    }

    public function hasRole(string $role): bool
    {
        $slug = $this->role?->slug;

        return $slug !== null && $slug === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        $slug = $this->role?->slug;

        return $slug !== null && in_array($slug, $roles, true);
    }

    public function photoUrl(): ?string
    {
        if (! $this->foto_path) {
            return null;
        }

        return Storage::disk('public')->exists($this->foto_path)
            ? '/storage/' . ltrim($this->foto_path, '/')
            : null;
    }
}
