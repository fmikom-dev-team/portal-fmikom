<?php

namespace App\Modules\Wims\Services\Shared\Assessment;

use App\Models\AssessmentSubmission;
use App\Models\AssessmentTemplate;
use App\Models\Magang\PendaftaranMagang;

class AssessmentTemplateResolverService
{
    public function resolveForRegistration(
        PendaftaranMagang $pendaftaran,
        ?AssessmentSubmission $submission = null,
    ): ?AssessmentTemplate {
        // Jika penilaian pernah dimulai, sistem selalu memakai template yang sama
        // agar struktur komponen penilaian tidak berubah di tengah proses asesmen.
        if ($submission?->template) {
            return $submission->template;
        }

        if (! $pendaftaran->tanggal_mulai) {
            return null;
        }

        return AssessmentTemplate::query()
            // Jika belum ada submission, template aktif dipilih berdasarkan periode tahun magang mahasiswa.
            ->where('is_active', true)
            ->whereDate('periode_mulai', '<=', $pendaftaran->tanggal_mulai->toDateString())
            ->whereDate('periode_selesai', '>=', $pendaftaran->tanggal_mulai->toDateString())
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
    }
}
