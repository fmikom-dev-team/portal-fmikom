<?php

namespace App\Models\Magang;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KetidakhadiranMagang extends Model
{
    protected $fillable = [
        'pendaftaran_id',
        'mahasiswa_id',
        'perusahaan_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis',
        'alasan',
        'bukti_path',
        'status',
        'reviewed_by_mitra_user_id',
        'submitted_at',
        'reviewed_by_mitra_at',
        'cancelled_at',
        'catatan_mitra',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_by_mitra_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(PendaftaranMagang::class, 'pendaftaran_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(PerusahaanMitra::class, 'perusahaan_id');
    }

    public function reviewedByMitra(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_mitra_user_id');
    }
}
