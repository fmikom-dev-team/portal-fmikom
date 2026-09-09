<?php

namespace App\Modules\Wims\Services\Mahasiswa\Registration;

use App\Models\Magang\PendaftaranMagang;
use App\Models\User;
use App\Modules\Wims\Services\Mahasiswa\Period\StudentPeriodResolverService;
use App\Modules\Wims\Services\Mahasiswa\Report\StudentFinalReportTemplateService;
use Carbon\Carbon;

class StudentRegistrationPageService
{
    public function __construct(
        private readonly StudentFinalReportTemplateService $studentFinalReportTemplateService,
        private readonly StudentPeriodResolverService $studentPeriodResolverService,
    ) {}

    public function build(User $user): array
    {
        $registrations = $this->studentPeriodResolverService->resolveRegistrations($user->id);
        $selectedRegistration = $this->studentPeriodResolverService->resolveSelectedRegistrationFromCollection($registrations);
        $formSource = $selectedRegistration
            && ! in_array($selectedRegistration->status, ['rejected', 'selesai'], true)
            ? $selectedRegistration
            : null;
        $periods = $this->studentPeriodResolverService->buildPeriodOptions($registrations, $selectedRegistration?->id);

        return [
            'registration' => $selectedRegistration ? $this->transformRegistration($selectedRegistration) : null,
            'selected_period_id' => $selectedRegistration?->id,
            'periods' => $periods,
            'pageState' => [
                'can_submit' => $this->canSubmitRegistration($selectedRegistration),
                'is_revision' => $selectedRegistration?->status === 'revisi',
                'is_new_submission' => ! $selectedRegistration || in_array($selectedRegistration->status, ['rejected', 'selesai'], true),
                'is_locked' => in_array($selectedRegistration?->status, ['pending', 'approved', 'aktif'], true),
            ],
            'proposal_template' => $this->studentFinalReportTemplateService->buildTemplateCard('proposal', 'wims.registration.proposal-template.download'),
            'formDefaults' => [
                'tanggal_mulai' => $formSource?->tanggal_mulai?->toDateString(),
                'tanggal_selesai' => $formSource?->tanggal_selesai?->toDateString(),
                'perusahaan_diminati_nama' => $formSource?->perusahaan_diminati_nama,
                'perusahaan_diminati_alamat' => $formSource?->perusahaan_diminati_alamat,
                'catatan_pengajuan' => $formSource?->catatan_pengajuan,
                'status_kip' => $formSource?->status_kip,
                'sks_ditempuh' => $formSource?->sks_ditempuh,
                'bidang_minat' => $formSource?->bidang_minat,
                'bidang_minat_lainnya' => $formSource?->bidang_minat_lainnya,
                'ukuran_seragam' => $formSource?->ukuran_seragam,
                'ukuran_seragam_custom' => $formSource?->ukuran_seragam_custom,
            ],
        ];
    }

    public function hasCompletedInternshipHistory(int $userId): bool
    {
        return PendaftaranMagang::query()
            ->forMahasiswa($userId)
            ->where('status', 'selesai')
            ->exists();
    }

    public function latestRegistration(int $userId): ?PendaftaranMagang
    {
        return PendaftaranMagang::with('perusahaan')
            ->latestForMahasiswa($userId)
            ->first();
    }

    public function registrationForStudent(int $userId, int $registrationId): ?PendaftaranMagang
    {
        return PendaftaranMagang::with('perusahaan')
            ->where('mahasiswa_id', $userId)
            ->whereKey($registrationId)
            ->first();
    }

    public function canSubmitRegistration(?PendaftaranMagang $registration, bool $hasCompletedHistory = false): bool
    {
        if (! $registration) {
            return true;
        }

        return in_array($registration->status, ['revisi', 'rejected', 'selesai'], true);
    }

    public function transformRegistration(PendaftaranMagang $registration): array
    {
        return [
            'id' => $registration->id,
            'status' => $registration->status,
            'tanggal_mulai' => $registration->tanggal_mulai?->toDateString(),
            'tanggal_selesai' => $registration->tanggal_selesai?->toDateString(),
            'tanggal_mulai_label' => $this->formatDate($registration->tanggal_mulai),
            'tanggal_selesai_label' => $this->formatDate($registration->tanggal_selesai),
            'company' => [
                'proposal' => [
                    'name' => $registration->perusahaan_diminati_nama,
                    'address' => $registration->perusahaan_diminati_alamat,
                ],
                'final' => [
                    'id' => $registration->perusahaan?->id,
                    'name' => $registration->perusahaan?->nama,
                ],
            ],
            'application_note' => $registration->catatan_pengajuan,
            'revision_note' => $registration->catatan_revisi_admin,
            'proposal_attachment' => filled($registration->proposal_pkl_path) ? [
                'exists' => true,
                'name' => $registration->proposal_pkl_original_name,
                'uploaded_at' => $registration->proposal_pkl_uploaded_at?->translatedFormat('d M Y H:i'),
            ] : null,
            'transcript_attachment' => filled($registration->transkrip_nilai_path) ? [
                'exists' => true,
                'name' => $registration->transkrip_nilai_original_name,
                'uploaded_at' => $registration->transkrip_nilai_uploaded_at?->translatedFormat('d M Y H:i'),
            ] : null,
            'recommendation_attachment' => filled($registration->surat_rekomendasi_kaprodi_path) ? [
                'exists' => true,
                'name' => $registration->surat_rekomendasi_kaprodi_original_name,
                'uploaded_at' => $registration->surat_rekomendasi_kaprodi_uploaded_at?->translatedFormat('d M Y H:i'),
            ] : null,
            'status_kip' => $registration->status_kip,
            'sks_ditempuh' => $registration->sks_ditempuh,
            'bidang_minat' => $registration->bidang_minat,
            'bidang_minat_lainnya' => $registration->bidang_minat_lainnya,
            'ukuran_seragam' => $registration->ukuran_seragam,
            'ukuran_seragam_custom' => $registration->ukuran_seragam_custom,
            'submitted_at' => $registration->created_at?->translatedFormat('d M Y H:i'),
            'updated_at' => $registration->updated_at?->translatedFormat('d M Y H:i'),
        ];
    }

    private function formatDate(mixed $date): ?string
    {
        if (blank($date)) {
            return null;
        }

        if ($date instanceof Carbon) {
            return $date->translatedFormat('d M Y');
        }

        return Carbon::parse($date)->translatedFormat('d M Y');
    }
}
