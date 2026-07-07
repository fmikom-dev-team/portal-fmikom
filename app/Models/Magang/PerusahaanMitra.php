<?php

namespace App\Models\Magang;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property-read User|null $user
 */
class PerusahaanMitra extends Model
{
    public const WORKING_DAY_OPTIONS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
    ];

    public const DEFAULT_WORKING_DAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
    ];

    protected $fillable = [
        'nama', 'alamat', 'kota', 'latitude', 'longitude',
        'radius_valid_meter', 'bidang_industri', 'kontak_person',
        'telepon', 'email', 'user_id', 'mitra_jabatan',
        'jam_masuk', 'jam_pulang', 'toleransi_terlambat_menit',
        'hari_kerja', 'is_active',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'radius_valid_meter' => 'float',
        'toleransi_terlambat_menit' => 'integer',
        'hari_kerja' => 'array',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function workingDayOptions(): array
    {
        return self::WORKING_DAY_OPTIONS;
    }

    public function getWorkingDays(): array
    {
        $days = collect($this->hari_kerja ?: self::DEFAULT_WORKING_DAYS)
            ->map(fn ($day) => strtolower((string) $day))
            ->filter(fn ($day) => in_array($day, self::WORKING_DAY_OPTIONS, true))
            ->unique()
            ->values()
            ->all();

        return $days ?: self::DEFAULT_WORKING_DAYS;
    }

    public function worksOnDate(CarbonInterface $date, Collection|array $holidayDates = []): bool
    {
        if ($date->isSunday()) {
            return false;
        }

        $holidayLookup = $holidayDates instanceof Collection ? $holidayDates : collect($holidayDates);

        if ($holidayLookup->has($date->toDateString()) || $holidayLookup->contains($date->toDateString())) {
            return false;
        }

        return in_array(strtolower($date->englishDayOfWeek), $this->getWorkingDays(), true);
    }

    public function pembimbingLapangans(): HasMany
    {
        return $this->hasMany(PembimbingLapangan::class, 'perusahaan_id');
    }

    public function pendaftaranMagangs(): HasMany
    {
        return $this->hasMany(PendaftaranMagang::class, 'perusahaan_id');
    }

    public function isWithinRadius($lat, $lng): bool
    {
        return $this->distanceTo($lat, $lng) <= $this->radius_valid_meter;
    }

    public function distanceTo($lat, $lng): float
    {
        // Simple Haversine approximation
        $earthRadius = 6371000;
        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($lat);
        $lonTo = deg2rad($lng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}
