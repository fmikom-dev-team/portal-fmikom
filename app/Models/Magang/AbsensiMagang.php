<?php

namespace App\Models\Magang;

use App\Support\PublicStorageUrl;
use App\Support\WimsStorage;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class AbsensiMagang extends Model
{
    protected $fillable = [
        'pendaftaran_id', 'tanggal', 'waktu_masuk', 'waktu_keluar',
        'latitude_masuk', 'longitude_masuk', 'latitude_keluar',
        'longitude_keluar', 'lokasi_valid', 'foto_bukti_path',
        'status', 'keterangan', 'timestamp_masuk', 'timestamp_keluar',
        'distance_masuk', 'distance_keluar', 'foto_bukti_checkout_path',
        'ip_address', 'user_agent',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'timestamp_masuk' => 'datetime',
        'timestamp_keluar' => 'datetime',
        'lokasi_valid' => 'boolean',
        'distance_masuk' => 'float',
        'distance_keluar' => 'float',
    ];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(PendaftaranMagang::class, 'pendaftaran_id');
    }

    public function resolvedCheckInAt(): ?CarbonInterface
    {
        if ($this->timestamp_masuk) {
            return $this->timestamp_masuk;
        }

        if (! $this->tanggal || ! $this->waktu_masuk) {
            return null;
        }

        return Carbon::parse($this->tanggal->toDateString().' '.$this->waktu_masuk);
    }

    public function resolvedCheckOutAt(): ?CarbonInterface
    {
        if ($this->timestamp_keluar) {
            return $this->timestamp_keluar;
        }

        if (! $this->tanggal || ! $this->waktu_keluar) {
            return null;
        }

        return Carbon::parse($this->tanggal->toDateString().' '.$this->waktu_keluar);
    }

    public function checkInPhotoUrl(): ?string
    {
        return PublicStorageUrl::signed(
            WimsStorage::exists($this->foto_bukti_path)
                ? $this->foto_bukti_path
                : null,
        );
    }

    public function validateGpsLocation(): bool
    {
        if (! $this->latitude_masuk || ! $this->longitude_masuk) {
            return false;
        }

        return $this->pendaftaran->perusahaan->isWithinRadius($this->latitude_masuk, $this->longitude_masuk);
    }

    public function getDistanceFromOffice(): float
    {
        if ($this->distance_masuk !== null) {
            return $this->distance_masuk;
        }

        if (! $this->latitude_masuk || ! $this->longitude_masuk) {
            return 0;
        }

        return $this->pendaftaran->perusahaan->distanceTo($this->latitude_masuk, $this->longitude_masuk);
    }

    public function getDurasiKerja(): int
    {
        if (! $this->waktu_masuk || ! $this->waktu_keluar) {
            return 0;
        }
        $in = Carbon::parse($this->waktu_masuk);
        $out = Carbon::parse($this->waktu_keluar);

        return $in->diffInMinutes($out);
    }

    public function checkOutPhotoUrl(): ?string
    {
        return PublicStorageUrl::signed(
            WimsStorage::exists($this->foto_bukti_checkout_path)
                ? $this->foto_bukti_checkout_path
                : null,
        );
    }
}
