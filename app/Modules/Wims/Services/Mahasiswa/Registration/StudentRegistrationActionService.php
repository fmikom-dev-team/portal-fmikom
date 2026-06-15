<?php

namespace App\Modules\Wims\Services\Mahasiswa\Registration;

use App\Models\Magang\PendaftaranMagang;
use App\Models\User;

class StudentRegistrationActionService
{
    public function buildPayload(array $input): array
    {
        return [
            'tanggal_mulai' => $input['tanggal_mulai'] ?? null,
            'tanggal_selesai' => $input['tanggal_selesai'] ?? null,
            'perusahaan_diminati_nama' => $this->nullIfBlank($input['perusahaan_diminati_nama'] ?? null),
            'perusahaan_diminati_alamat' => $this->nullIfBlank($input['perusahaan_diminati_alamat'] ?? null),
            'catatan_pengajuan' => $this->nullIfBlank($input['catatan_pengajuan'] ?? null),
            'catatan_revisi_admin' => null,
            'perusahaan_id' => null,
            'dosen_pembimbing_id' => null,
            'status' => 'pending',
        ];
    }

    public function resubmitRevision(PendaftaranMagang $registration, array $payload): void
    {
        $registration->update($payload);
    }

    public function create(User $user, array $payload): void
    {
        PendaftaranMagang::create([
            'mahasiswa_id' => $user->id,
            ...$payload,
        ]);
    }

    private function nullIfBlank(?string $value): ?string
    {
        return blank($value) ? null : $value;
    }
}
