<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class PerusahaanMitra extends Model
{
    use HasFactory;

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

    protected $table = 'perusahaan_mitras';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            // Koordinat dan radius perusahaan menjadi pusat geofence untuk validasi kehadiran.
            'latitude' => 'float',
            'longitude' => 'float',
            'radius_valid_meter' => 'float',
            'toleransi_terlambat_menit' => 'integer',
            'hari_kerja' => 'array',
            'is_active' => 'boolean',
        ];
    }

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
}
