<?php

namespace App\Modules\Wims\Services\Mahasiswa\Registration;

use App\Models\Magang\AssessmentSubmission;
use App\Models\Magang\PendaftaranMagang;

class StudentNextRegistrationEligibilityService
{
    /**
     * @return array{
     *     is_complete: bool,
     *     dosen: array{is_submitted: bool},
     *     mitra: array{is_submitted: bool},
     *     blocking_reasons: list<string>
     * }
     */
    public function evaluate(PendaftaranMagang $registration): array
    {
        $registration->loadMissing('perusahaan:id,user_id');

        $dosenId = $registration->dosen_pembimbing_id;
        $mitraId = $registration->perusahaan?->user_id;

        $dosenSubmitted = $dosenId !== null && $this->hasSubmittedAssessment(
            $registration->id,
            'dosen',
            (int) $dosenId,
        );
        $mitraSubmitted = $mitraId !== null && $this->hasSubmittedAssessment(
            $registration->id,
            'mitra',
            (int) $mitraId,
        );

        $blockingReasons = [];

        if ($dosenId === null) {
            $blockingReasons[] = 'Dosen pembimbing belum ditetapkan.';
        } elseif (! $dosenSubmitted) {
            $blockingReasons[] = 'Menunggu penilaian final dari Dosen Pembimbing.';
        }

        if ($mitraId === null) {
            $blockingReasons[] = 'Akun Mitra untuk perusahaan belum ditetapkan.';
        } elseif (! $mitraSubmitted) {
            $blockingReasons[] = 'Menunggu penilaian final dari Mitra.';
        }

        return [
            'is_complete' => $blockingReasons === [],
            'dosen' => ['is_submitted' => $dosenSubmitted],
            'mitra' => ['is_submitted' => $mitraSubmitted],
            'blocking_reasons' => $blockingReasons,
        ];
    }

    private function hasSubmittedAssessment(int $registrationId, string $role, int $assessorId): bool
    {
        return AssessmentSubmission::query()
            ->where('pendaftaran_magang_id', $registrationId)
            ->where('assessor_role', $role)
            ->where('assessor_id', $assessorId)
            ->where('status', 'submitted')
            ->exists();
    }
}
