<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KetidakhadiranMagang extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'submitted_at' => 'datetime',
            'reviewed_by_mitra_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

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
