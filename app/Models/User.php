<?php

namespace App\Models;

use App\Models\Auth\AuthOAuthCredential;
use App\Models\Magang\LowonganInfo;
use App\Models\Magang\PembimbingLapangan;
use App\Models\Magang\PendaftaranMagang;
use App\Models\Magang\PenilaianMagang;
use App\Models\Pagi\PagiCv;
use App\Models\Pagi\PagiWork;
use App\Models\Surat\Surat;
use App\Models\Tracer\ProfilAlumni;
use App\Models\Tracer\Kuesioner;
use App\Models\Tracer\MitraProfile;
use App\Models\Surat\SuratApprovalFlow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use \App\Concerns\UserHelpers, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'program_studi_id',
        'no_telepon', 'foto_path', 'banner_path', 'is_active',
        'nomor_induk', 'status_approval', 'user_type',
        'tahun_lulus', 'otp_code', 'otp_expires_at', 'password_changed_at',
        'role_title', 'bio', 'location', 'website', 'twitter', 'linkedin', 'github', 'instagram',
        'metadata', 'tanggal_lahir', 'last_seen_at', 'pagi_username',
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
        'otp_code',
        'otp_expires_at',
        'password_changed_at',
        'nomor_induk',
        'clearHistory',
        'encryptHistory',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'tahun_lulus' => 'integer',
            // user_type = identity layer (mahasiswa, alumni, mitra, dosen, staff)
            // digunakan sebagai fallback role di module jika tidak ada assignment
            'user_type' => 'string',
            'metadata' => 'array',
            'tanggal_lahir' => 'date:Y-m-d',
            'last_seen_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function ($user) {
            Cache::forget("pagi_public_profile_{$user->id}");

            if ($user->wasChanged('password') || ($user->wasRecentlyCreated && $user->password)) {
                DB::table('auth_password_histories')->insert([
                    'user_id' => $user->id,
                    'password_hash' => $user->password,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        static::deleting(function ($user) {
            if ($user->user_type === 'super_admin') {
                throw new \RuntimeException('Akun dengan tipe Super Admin dilindungi oleh sistem dan tidak dapat dihapus.');
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

    public function alumniProfile()
    {
        return $this->hasOne(ProfilAlumni::class, 'user_id');
    }
      public function mitraProfile()
    {
        return $this->hasOne(MitraProfile::class, 'user_id');
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
}
