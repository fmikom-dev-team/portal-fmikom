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

/**
 * @property-read User|null $mahasiswa
 * @property-read User|null $dosenPembimbing
 * @property-read PerusahaanMitra|null $perusahaan
 * @method static Builder forMahasiswa(int $mahasiswaId)
 */
class PendaftaranMagang extends Model
{
    protected $fillable = [
        'mahasiswa_id', 'perusahaan_id', 'dosen_pembimbing_id',
        'pembimbing_lapangan_id', 'surat_tugas_id', 'tanggal_mulai',
        'tanggal_selesai', 'status', 'perusahaan_diminati_nama',
        'perusahaan_diminati_alamat', 'catatan_pengajuan',
        'catatan_revisi_admin', 'proposal_pkl_path',
        'proposal_pkl_original_name', 'proposal_pkl_uploaded_at',
        'laporan_akhir_path', 'laporan_akhir_original_name', 'laporan_akhir_uploaded_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'proposal_pkl_uploaded_at' => 'datetime',
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

    public function scopeLatestForMahasiswa(Builder $query, int $mahasiswaId): Builder
    {
        return $query
            ->where('mahasiswa_id', $mahasiswaId)
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    public function scopeReadyForAssessment(Builder $query, ?CarbonInterface $date = null): Builder
    {
        return $query->whereNotNull('laporan_akhir_path');
    }

    public function isWithinActivePeriod(?CarbonInterface $date = null): bool
    {
        $tanggalMulai = data_get($this, 'tanggal_mulai');
        $tanggalSelesai = data_get($this, 'tanggal_selesai');

        if (! $tanggalMulai instanceof CarbonInterface || ! $tanggalSelesai instanceof CarbonInterface) {
            return false;
        }

        $currentDate = ($date ?? now())->copy()->startOfDay();
        $tanggalMulai = $tanggalMulai->copy()->startOfDay();
        $tanggalSelesai = $tanggalSelesai->copy()->startOfDay();

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
        $tanggalSelesai = data_get($this, 'tanggal_selesai');

        if (! $tanggalSelesai instanceof CarbonInterface) {
            return false;
        }

        $currentDate = ($date ?? now())->copy()->startOfDay();
        $tanggalSelesai = $tanggalSelesai->copy()->startOfDay();

        return $currentDate->greaterThan($tanggalSelesai);
    }

    public function isPostInternshipPhase(?CarbonInterface $date = null): bool
    {
        if ($this->status === 'selesai') {
            return true;
        }

        return $this->status === 'aktif' && $this->hasInternshipPeriodEnded($date);
    }

    public function canBeMarkedComplete(?CarbonInterface $date = null): bool
    {
        if ($this->status !== 'aktif') {
            return false;
        }

        return $this->hasInternshipPeriodEnded($date);
    }

    public function isReadyForAssessment(?CarbonInterface $date = null): bool
    {
        return filled($this->laporan_akhir_path);
    }

    public function proposalAttachmentDownloadName(): string
    {
        $mahasiswa = $this->mahasiswa;
        $studentName = Str::slug((string) data_get($mahasiswa, 'name', 'mahasiswa'));
        $studentId = data_get($mahasiswa, 'nim_nip') ?: data_get($mahasiswa, 'nomor_induk') ?: 'tanpa-identitas';

        $tanggalMulai = data_get($this, 'tanggal_mulai');
        $periodStart = $tanggalMulai instanceof CarbonInterface ? $tanggalMulai->format('Ymd') : 'mulai';

        $tanggalSelesai = data_get($this, 'tanggal_selesai');
        $periodEnd = $tanggalSelesai instanceof CarbonInterface ? $tanggalSelesai->format('Ymd') : 'selesai';

        $extension = pathinfo((string) ($this->proposal_pkl_original_name ?: $this->proposal_pkl_path), PATHINFO_EXTENSION) ?: 'pdf';

        return sprintf(
            'proposal-pkl-%s-%s-%s-%s.%s',
            $studentName ?: 'mahasiswa',
            Str::slug((string) $studentId) ?: 'tanpa-identitas',
            $periodStart,
            $periodEnd,
            strtolower((string) $extension),
        );
    }

    public function finalReportDownloadName(): string
    {
        $mahasiswa = $this->mahasiswa;
        $studentName = Str::slug((string) data_get($mahasiswa, 'name', 'mahasiswa'));
        $studentId = data_get($mahasiswa, 'nim_nip') ?: data_get($mahasiswa, 'nomor_induk') ?: 'tanpa-identitas';

        $tanggalMulai = data_get($this, 'tanggal_mulai');
        $periodStart = $tanggalMulai instanceof CarbonInterface ? $tanggalMulai->format('Ymd') : 'mulai';

        $tanggalSelesai = data_get($this, 'tanggal_selesai');
        $periodEnd = $tanggalSelesai instanceof CarbonInterface ? $tanggalSelesai->format('Ymd') : 'selesai';

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

    public function periodLabel(): ?string
    {
        $tanggalMulai = data_get($this, 'tanggal_mulai');
        $tanggalSelesai = data_get($this, 'tanggal_selesai');

        if (! $tanggalMulai instanceof CarbonInterface || ! $tanggalSelesai instanceof CarbonInterface) {
            return null;
        }

        return sprintf(
            '%s - %s',
            $tanggalMulai->translatedFormat('d M Y'),
            $tanggalSelesai->translatedFormat('d M Y'),
        );
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Review',
            'approved' => 'Disetujui',
            'revisi' => 'Revisi',
            'rejected' => 'Ditolak',
            'aktif' => 'Aktif',
            'selesai' => 'Selesai',
            default => ucfirst((string) $this->status ?: '-'),
        };
    }

    public function dashboardPhase(): string
    {
        if ($this->isReadyForAssessment(now())) {
            return 'completed';
        }

        if ($this->status === 'aktif') {
            return 'active';
        }

        if ($this->status) {
            return 'assigned';
        }

        return 'unregistered';
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
