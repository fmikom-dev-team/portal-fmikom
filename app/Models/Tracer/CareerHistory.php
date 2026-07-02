<?php

namespace App\Models\Tracer;

use App\Enums\CareerStatus;
use App\Enums\CareerType;
use App\Modules\Trace\Services\TraceCacheService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CareerHistory extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(function ($career) {
            TraceCacheService::forgetDashboardCaches(userId: $career->alumniProfile?->user_id);
        });

        static::deleted(function ($career) {
            TraceCacheService::forgetDashboardCaches(userId: $career->alumniProfile?->user_id);
        });
    }

    protected $table = 'career_history';

    protected $fillable = [
        'profil_alumni_id',
        'type',
        'status',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_current',
        'provinsi_id',
        'kota_id',
        'latitude',
        'longitude',
        'tahun_mulai',
        'tahun_selesai',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'provinsi_id' => 'integer',
        'kota_id' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'status' => CareerStatus::class,
        'type' => CareerType::class,
    ];

    /*
    |-------------------------
    | SCOPES
    |-------------------------
    */

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeWorking($query)
    {
        return $query->current()->whereIn('status', ['bekerja', 'wirausaha']);
    }

    /**
     * @return BelongsTo<ProfilAlumni, $this>
     */
    public function alumniProfile(): BelongsTo
    {
        return $this->belongsTo(ProfilAlumni::class, 'profil_alumni_id');
    }

    /**
     * @return BelongsTo<Provinsi, $this>
     */
    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class);
    }

    /**
     * @return BelongsTo<Kota, $this>
     */
    public function kota(): BelongsTo
    {
        return $this->belongsTo(Kota::class);
    }

    /**
     * @return HasOne<Employment, $this>
     */
    public function employment(): HasOne
    {
        return $this->hasOne(Employment::class, 'career_history_id');
    }

    /**
     * @return HasOne<Education, $this>
     */
    public function education(): HasOne
    {
        return $this->hasOne(Education::class, 'career_history_id');
    }
}
