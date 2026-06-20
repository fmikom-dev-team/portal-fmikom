<?php

namespace App\Modules\Wims\Services\Mahasiswa\Registration;

use App\Models\Magang\PendaftaranMagang;
use App\Models\User;
use Carbon\Carbon;

class StudentRegistrationPageService
{
    public function build(User $user): array
    {
        $latestRegistration = $this->latestRegistration($user->id);
        $formSource = $latestRegistration?->status === 'revisi' ? $latestRegistration : null;
        $hasCompletedHistory = $this->hasCompletedInternshipHistory($user->id);

        return [
            'registration' => $latestRegistration ? $this->transformRegistration($latestRegistration) : null,
            'pageState' => [
                'can_submit' => $this->canSubmitRegistration($latestRegistration, $hasCompletedHistory),
                'is_revision' => $latestRegistration?->status === 'revisi',
                'is_locked' => in_array($latestRegistration?->status, ['pending', 'approved', 'aktif'], true),
                'completed_once' => $hasCompletedHistory,
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
            ->forMahasiswa($userId)
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
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
