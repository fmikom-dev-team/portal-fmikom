<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PendaftaranMagang extends Model
{
    use HasFactory;

    protected $table = 'pendaftaran_magangs';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'laporan_akhir_uploaded_at' => 'datetime',
        ];
    }

    public function scopeForMahasiswa(Builder $query, int $mahasiswaId): Builder
    {
        return $query->where('mahasiswa_id', $mahasiswaId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
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

    public function hasActiveInternshipPeriod(?CarbonInterface $date = null): bool
    {
        return $this->status === 'aktif' && $this->isWithinActivePeriod($date);
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

    public function isReadyForAssessment(?CarbonInterface $date = null): bool
    {
        return $this->isPostInternshipPhase($date);
    }

    public function canBeMarkedComplete(?CarbonInterface $date = null): bool
    {
        return $this->status === 'aktif' && $this->hasInternshipPeriodEnded($date);
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

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function perusahaan()
    {
        return $this->belongsTo(PerusahaanMitra::class, 'perusahaan_id');
    }

    public function dosenPembimbing()
    {
        return $this->belongsTo(User::class, 'dosen_pembimbing_id');
    }

    public function absensis()
    {
        return $this->hasMany(AbsensiMagang::class, 'pendaftaran_id');
    }

    public function logbooks()
    {
        return $this->hasMany(LogbookMagang::class, 'pendaftaran_id');
    }

    public function assessmentSubmissions()
    {
        return $this->hasMany(AssessmentSubmission::class, 'pendaftaran_magang_id');
    }

    public function ketidakhadiranMagangs()
    {
        return $this->hasMany(KetidakhadiranMagang::class, 'pendaftaran_id');
    }

    public function suratPenetapan()
    {
        return $this->hasOne(SuratPenetapan::class, 'pendaftaran_id');
    }
}
