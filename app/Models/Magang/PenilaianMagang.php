<?php

namespace App\Models\Magang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenilaianMagang extends Model
{
    protected $fillable = [
        'pendaftaran_id', 'dosen_id', 'pembimbing_lapangan_id',
        'nilai_logbook', 'nilai_presentasi', 'nilai_dari_pembimbing',
        'nilai_akhir', 'catatan', 'tanggal_nilai'
    ];

    protected $casts = [
        'tanggal_nilai' => 'datetime',
    ];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(PendaftaranMagang::class, 'pendaftaran_id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function pembimbingLapangan(): BelongsTo
    {
        return $this->belongsTo(PembimbingLapangan::class, 'pembimbing_lapangan_id');
    }

    public function hitungNilaiAkhir(): float
    {
        // Example logic:
        $logbook = $this->nilai_logbook ?? 0;
        $presentasi = $this->nilai_presentasi ?? 0;
        $pembimbing = $this->nilai_dari_pembimbing ?? 0;

        // Custom weightage: 30% logbook, 40% presentasi, 30% pembimbing
        $nilai = ($logbook * 0.3) + ($presentasi * 0.4) + ($pembimbing * 0.3);
        $this->nilai_akhir = $nilai;
        return $nilai;
    }

    public function getGrade(): string
    {
        $na = $this->nilai_akhir ?? 0;
        if ($na >= 85) return 'A';
        if ($na >= 70) return 'B';
        if ($na >= 55) return 'C';
        if ($na >= 40) return 'D';
        return 'E';
    }
}