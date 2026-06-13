<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class AbsensiMagang extends Model
{
    use HasFactory;

    protected $table = 'absensi_magangs';

    protected $guarded = [];

    protected $casts = [
        'tanggal' => 'date',
        'timestamp_masuk' => 'datetime',
        'timestamp_keluar' => 'datetime',
        'lokasi_valid' => 'boolean',
        'distance_masuk' => 'float',
        'distance_keluar' => 'float',
    ];

    // 🔗 KE PENDAFTARAN
    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranMagang::class);
    }

    // 🔥 AKSES LANGSUNG KE PERUSAHAAN
    public function perusahaan()
    {
        return $this->pendaftaran->perusahaan;
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

    public function checkOutPhotoUrl(): ?string
    {
        if (! $this->foto_bukti_checkout_path) {
            return null;
        }

        return Storage::disk('public')->exists($this->foto_bukti_checkout_path)
            ? '/storage/' . ltrim($this->foto_bukti_checkout_path, '/')
            : null;
    }
}
