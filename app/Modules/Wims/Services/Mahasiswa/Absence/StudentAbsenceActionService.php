<?php

namespace App\Modules\Wims\Services\Mahasiswa\Absence;

use App\Models\Magang\KetidakhadiranMagang;
use App\Models\Magang\PendaftaranMagang;
use Illuminate\Http\UploadedFile;
use App\Modules\Wims\Services\Shared\Absence\KetidakhadiranService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentAbsenceActionService
{
    public function __construct(
        private readonly KetidakhadiranService $ketidakhadiranService,
    ) {
    }

    public function resolveActiveRegistration(int $registrationId): PendaftaranMagang
    {
        return PendaftaranMagang::query()
            ->with(['perusahaan', 'mahasiswa'])
            ->findOrFail($registrationId);
    }

    public function submit(PendaftaranMagang $pendaftaran, int $mahasiswaId, array $validated, ?UploadedFile $bukti): void
    {
        $resolved = $this->ketidakhadiranService->validateSubmission($pendaftaran, $validated);
        $buktiPath = $this->storeProof($bukti);

        KetidakhadiranMagang::query()->create([
            'pendaftaran_id' => $pendaftaran->id,
            'mahasiswa_id' => $mahasiswaId,
            'perusahaan_id' => $pendaftaran->perusahaan_id,
            'tanggal_mulai' => $resolved['start_date']->toDateString(),
            'tanggal_selesai' => $resolved['end_date']->toDateString(),
            'jenis' => $validated['jenis'],
            'alasan' => $validated['alasan'],
            'bukti_path' => $buktiPath,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);
    }

    public function cancel(KetidakhadiranMagang $ketidakhadiran): void
    {
        $this->ketidakhadiranService->cancel($ketidakhadiran);

        if ($ketidakhadiran->bukti_path && Storage::disk('public')->exists($ketidakhadiran->bukti_path)) {
            Storage::disk('public')->delete($ketidakhadiran->bukti_path);
        }
    }

    private function storeProof(?UploadedFile $file): ?string
    {
        if (! $file) {
            return null;
        }

        $directory = 'ketidakhadiran';
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $filename = Str::uuid() . '.' . $extension;

        Storage::disk('public')->putFileAs($directory, $file, $filename);

        return $directory . '/' . $filename;
    }
}
