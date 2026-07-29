<?php

namespace App\Models\Magang;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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

    public function proofDownloadName(): string
    {
        $mahasiswa = $this->mahasiswa;
        $studentName = Str::slug((string) data_get($mahasiswa, 'name', 'mahasiswa')) ?: 'mahasiswa';
        $studentId = data_get($mahasiswa, 'nim_nip') ?: data_get($mahasiswa, 'nomor_induk') ?: 'tanpa-identitas';

        $tanggalMulai = data_get($this, 'tanggal_mulai');
        $periodStart = $tanggalMulai instanceof CarbonInterface ? $tanggalMulai->format('Ymd') : 'mulai';

        $tanggalSelesai = data_get($this, 'tanggal_selesai');
        $periodEnd = $tanggalSelesai instanceof CarbonInterface ? $tanggalSelesai->format('Ymd') : 'selesai';

        $extension = pathinfo((string) $this->bukti_path, PATHINFO_EXTENSION) ?: 'pdf';

        return sprintf(
            'bukti-ketidakhadiran-%s-%s-%s-%s.%s',
            $studentName,
            Str::slug((string) $studentId) ?: 'tanpa-identitas',
            $periodStart,
            $periodEnd,
            strtolower((string) $extension),
        );
    }
}
