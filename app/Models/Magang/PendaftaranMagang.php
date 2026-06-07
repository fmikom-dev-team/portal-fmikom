<?php

namespace App\Models\Magang;

use App\Models\Surat\Surat;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PendaftaranMagang extends Model
{
    protected $fillable = [
        'mahasiswa_id', 'perusahaan_id', 'dosen_pembimbing_id',
        'pembimbing_lapangan_id', 'surat_tugas_id', 'tanggal_mulai',
        'tanggal_selesai', 'status',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(PerusahaanMitra::class, 'perusahaan_id');
    }

    public function dosenPembimbing(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_pembimbing_id');
    }

    public function pembimbingLapangan(): BelongsTo
    {
        return $this->belongsTo(PembimbingLapangan::class, 'pembimbing_lapangan_id');
    }

    public function suratTugas(): BelongsTo
    {
        return $this->belongsTo(Surat::class, 'surat_tugas_id');
    }

    public function absensis(): HasMany
    {
        return $this->hasMany(AbsensiMagang::class, 'pendaftaran_id');
    }

    public function logbooks(): HasMany
    {
        return $this->hasMany(LogbookMagang::class, 'pendaftaran_id');
    }

    public function penilaian(): HasOne
    {
        return $this->hasOne(PenilaianMagang::class, 'pendaftaran_id');
    }

    public function getTotalHadir(): int
    {
        return $this->absensis()->where('status', 'hadir')->count();
    }

    public function isActive(): bool
    {
        $now = now();

        return $this->status === 'approved' && $now->between($this->tanggal_mulai, $this->tanggal_selesai);
    }
}
