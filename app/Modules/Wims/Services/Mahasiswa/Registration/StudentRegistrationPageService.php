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

    public function build(User $user, ?int $selectedRegistrationId = null): array
    {
        $registrations = $this->studentPeriodResolverService->resolveRegistrations($user->id);
        $selectedRegistration = $this->studentPeriodResolverService->resolveSelectedRegistrationFromCollection($registrations, $selectedRegistrationId);
        $latestRegistration = $this->latestRegistration($user->id);
        $hasCompletedHistory = $this->hasCompletedInternshipHistory($user->id);
        $formSource = $selectedRegistration?->status === 'revisi' ? $selectedRegistration : null;
        $periods = $this->studentPeriodResolverService->buildPeriodOptions($registrations, $selectedRegistration?->id);

        return [
            'registration' => $selectedRegistration ? $this->transformRegistration($selectedRegistration) : null,
            'selected_period_id' => $selectedRegistration?->id,
            'periods' => $periods,
            'pageState' => [
                'completed_once' => $hasCompletedHistory,
                'can_submit' => $this->canSubmitRegistration($selectedRegistration, $hasCompletedHistory),
                'is_revision' => $selectedRegistration?->status === 'revisi',
                'is_locked' => in_array($selectedRegistration?->status, ['pending', 'approved', 'aktif'], true),
            ],
            'proposal_template' => $this->studentFinalReportTemplateService->buildTemplateCard('proposal', 'wims.registration.proposal-template.download'),
            'formDefaults' => [
                'tanggal_mulai' => $formSource?->tanggal_mulai?->toDateString(),
                'tanggal_selesai' => $formSource?->tanggal_selesai?->toDateString(),
                'perusahaan_diminati_nama' => $formSource?->perusahaan_diminati_nama,
                'perusahaan_diminati_alamat' => $formSource?->perusahaan_diminati_alamat,
                'catatan_pengajuan' => $formSource?->catatan_pengajuan,
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

    public function canSubmitRegistration(?PendaftaranMagang $registration, bool $hasCompletedHistory = false): bool
    {
        if ($hasCompletedHistory) {
            return false;
        }

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
