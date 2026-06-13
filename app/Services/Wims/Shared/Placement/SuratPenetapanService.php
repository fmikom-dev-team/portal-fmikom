<?php

namespace App\Services\Wims\Shared\Placement;

use App\Models\PendaftaranMagang;
use App\Models\SuratPenetapan;

class SuratPenetapanService
{
    public function requestGeneration(PendaftaranMagang $pendaftaran, ?int $requestedBy): void
    {
        // Snapshot disimpan agar data surat tetap merekam kondisi penempatan saat permintaan dibuat,
        // meskipun data mahasiswa atau perusahaan berubah setelahnya.
        $payloadSnapshot = [
            'pendaftaran_id' => $pendaftaran->id,
            'mahasiswa' => [
                'nama' => $pendaftaran->mahasiswa?->name,
                'email' => $pendaftaran->mahasiswa?->email,
                'nomor_induk' => $pendaftaran->mahasiswa?->nim_nip ?: $pendaftaran->mahasiswa?->nomor_induk,
            ],
            'perusahaan' => [
                'id' => $pendaftaran->perusahaan?->id,
                'nama' => $pendaftaran->perusahaan?->nama,
            ],
            'dosen_pembimbing_id' => $pendaftaran->dosen_pembimbing_id,
            'periode' => [
                'mulai' => $pendaftaran->tanggal_mulai,
                'selesai' => $pendaftaran->tanggal_selesai,
            ],
        ];

        SuratPenetapan::updateOrCreate(
            // Satu pendaftaran hanya memiliki satu dokumen surat penetapan yang terus diperbarui statusnya.
            ['pendaftaran_id' => $pendaftaran->id],
            [
                'requested_by' => $requestedBy,
                'status' => 'requested',
                'provider' => 'fast',
                'requested_at' => now(),
                'generated_at' => null,
                'nomor_surat' => null,
                'file_url' => null,
                'error_message' => 'Menunggu integrasi modul FASt.',
                'payload_snapshot' => $payloadSnapshot,
            ],
        );
    }
}
