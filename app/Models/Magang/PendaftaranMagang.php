<?php

namespace App\Models\Magang;

use App\Models\Surat\Surat;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class PendaftaranMagang extends Model
{
    protected $fillable = [
        'mahasiswa_id', 'perusahaan_id', 'dosen_pembimbing_id',
        'pembimbing_lapangan_id', 'surat_tugas_id', 'tanggal_mulai',
        'tanggal_selesai', 'status', 'perusahaan_diminati_nama',
        'perusahaan_diminati_alamat', 'catatan_pengajuan',
        'catatan_revisi_admin', 'laporan_akhir_path',
        'laporan_akhir_original_name', 'laporan_akhir_uploaded_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'laporan_akhir_uploaded_at' => 'datetime',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(PerusahaanMitra::class, 'perusahaan_id');
    }

    public function finalMentor(): ?User
    {
        return $this->perusahaan?->user;
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

    public function assessmentSubmissions(): HasMany
    {
        return $this->hasMany(AssessmentSubmission::class, 'pendaftaran_magang_id');
    }

    public function suratPenetapan(): HasOne
    {
        return $this->hasOne(SuratPenetapan::class, 'pendaftaran_id');
    }

    public function scopeForMahasiswa(Builder $query, int $mahasiswaId): Builder
    {
        return $query->where('mahasiswa_id', $mahasiswaId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    public function scopeReadyForAssessment(Builder $query, ?CarbonInterface $date = null): Builder
    {
        $referenceDate = ($date ?? now())->copy()->startOfDay()->toDateString();

        return $query->where(function (Builder $builder) use ($referenceDate): void {
            $builder
                ->where('status', 'selesai')
                ->orWhere(function (Builder $periodQuery) use ($referenceDate): void {
                    $periodQuery
                        ->where('status', 'aktif')
                        ->whereNotNull('tanggal_selesai')
                        ->whereDate('tanggal_selesai', '<', $referenceDate);
                });
        });
    }

    public function isWithinActivePeriod(?CarbonInterface $date = null): bool
    {
        if (! $this->tanggal_mulai || ! $this->tanggal_selesai) {
            return false;
        }

        $currentDate = ($date ?? now())->copy()->startOfDay();
        $tanggalMulai = $this->tanggal_mulai->copy()->startOfDay();
        $tanggalSelesai = $this->tanggal_selesai->copy()->startOfDay();

        return $currentDate->greaterThanOrEqualTo($tanggalMulai)
            && $currentDate->lessThanOrEqualTo($tanggalSelesai);
    }

    public function isActivatedByAdmin(): bool
    {
        return $this->status === 'aktif';
    }

    public function allowsDailyActivity(?CarbonInterface $date = null): bool
    {
        return $this->isActivatedByAdmin() && $this->isWithinActivePeriod($date);
    }

    public function hasInternshipPeriodEnded(?CarbonInterface $date = null): bool
    {
        if (! $this->tanggal_selesai) {
            return false;
        }

        $currentDate = ($date ?? now())->copy()->startOfDay();
        $tanggalSelesai = $this->tanggal_selesai->copy()->startOfDay();

        return $currentDate->greaterThan($tanggalSelesai);
    }

    public function isPostInternshipPhase(?CarbonInterface $date = null): bool
    {
        return $this->status === 'selesai'
            || ($this->status === 'aktif' && $this->hasInternshipPeriodEnded($date));
    }

    public function canBeMarkedComplete(?CarbonInterface $date = null): bool
    {
        return $this->status === 'aktif' && $this->hasInternshipPeriodEnded($date);
    }

    public function isReadyForAssessment(?CarbonInterface $date = null): bool
    {
        return $this->isPostInternshipPhase($date);
    }

    public function finalReportDownloadName(): string
    {
        $studentName = Str::slug((string) ($this->mahasiswa?->name ?? 'mahasiswa'));
        $studentId = $this->mahasiswa?->nim_nip ?: $this->mahasiswa?->nomor_induk ?: 'tanpa-identitas';
        $periodStart = $this->tanggal_mulai?->format('Ymd') ?? 'mulai';
        $periodEnd = $this->tanggal_selesai?->format('Ymd') ?? 'selesai';
        $extension = pathinfo((string) ($this->laporan_akhir_original_name ?: $this->laporan_akhir_path), PATHINFO_EXTENSION) ?: 'pdf';

        return sprintf(
            'laporan-akhir-pkl-%s-%s-%s-%s.%s',
            $studentName ?: 'mahasiswa',
            Str::slug((string) $studentId) ?: 'tanpa-identitas',
            $periodStart,
            $periodEnd,
            strtolower((string) $extension),
        );
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
