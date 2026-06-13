<?php

namespace App\Models\Magang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class AbsensiMagang extends Model
{
    protected $fillable = [
        'pendaftaran_id', 'tanggal', 'waktu_masuk', 'waktu_keluar',
        'latitude_masuk', 'longitude_masuk', 'latitude_keluar',
        'longitude_keluar', 'lokasi_valid', 'foto_bukti_path',
        'status', 'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'lokasi_valid' => 'boolean',
    ];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(PendaftaranMagang::class, 'pendaftaran_id');
    }

    public function resolvedCheckInAt(): ?Carbon
    {
        if (! $this->tanggal || ! $this->waktu_masuk) {
            return null;
        }

        return Carbon::parse($this->tanggal->toDateString() . ' ' . $this->waktu_masuk);
    }

    public function resolvedCheckOutAt(): ?Carbon
    {
        if (! $this->tanggal || ! $this->waktu_keluar) {
            return null;
        }

        return Carbon::parse($this->tanggal->toDateString() . ' ' . $this->waktu_keluar);
    }

    public function checkInPhotoUrl(): ?string
    {
        if (! $this->foto_bukti_path) {
            return null;
        }

        return Storage::disk('public')->exists($this->foto_bukti_path)
            ? '/storage/' . ltrim($this->foto_bukti_path, '/')
            : null;
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
}
