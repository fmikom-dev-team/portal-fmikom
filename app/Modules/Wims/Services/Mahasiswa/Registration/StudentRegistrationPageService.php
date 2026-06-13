<?php

namespace App\Modules\Wims\Services\Mahasiswa\Registration;

use App\Models\PendaftaranMagang;
use App\Models\User;
use Carbon\Carbon;

class StudentRegistrationPageService
{
    public function build(User $user): array
    {
        $latestRegistration = $this->latestRegistration($user->id);
        $formSource = $latestRegistration?->status === 'revisi' ? $latestRegistration : null;
        $hasCompletedPkl = (bool) $user->has_completed_pkl;

        return [
            'registration' => $latestRegistration ? $this->transformRegistration($latestRegistration) : null,
            'pageState' => [
                'can_submit' => $this->canSubmitRegistration($latestRegistration, $hasCompletedPkl),
                'is_revision' => $latestRegistration?->status === 'revisi',
                'is_locked' => in_array($latestRegistration?->status, ['pending', 'approved', 'aktif'], true),
                'completed_once' => $hasCompletedPkl,
            ],
            'formDefaults' => [
                'tanggal_mulai' => $formSource?->tanggal_mulai?->toDateString(),
                'tanggal_selesai' => $formSource?->tanggal_selesai?->toDateString(),
                'perusahaan_diminati_nama' => $formSource?->perusahaan_diminati_nama,
                'perusahaan_diminati_alamat' => $formSource?->perusahaan_diminati_alamat,
                'catatan_pengajuan' => $formSource?->catatan_pengajuan,
            ],
        ];
    }

    public function latestRegistration(int $userId): ?PendaftaranMagang
    {
        return PendaftaranMagang::with('perusahaan')
            ->forMahasiswa($userId)
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->first();
    }

    public function canSubmitRegistration(?PendaftaranMagang $registration, bool $hasCompletedPkl = false): bool
    {
        if ($hasCompletedPkl) {
            return false;
        }

        if (! $registration) {
            return true;
        }

        return in_array($registration->status, ['revisi', 'rejected'], true);
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
            'proposal_company_name' => $registration->perusahaan_diminati_nama,
            'proposal_company_address' => $registration->perusahaan_diminati_alamat,
            'application_note' => $registration->catatan_pengajuan,
            'revision_note' => $registration->catatan_revisi_admin,
            'final_company_name' => $registration->perusahaan?->nama,
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
