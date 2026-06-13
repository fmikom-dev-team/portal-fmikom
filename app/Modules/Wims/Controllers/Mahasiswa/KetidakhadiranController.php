<?php

namespace App\Modules\Wims\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\KetidakhadiranMagang;
use App\Modules\Wims\Services\Mahasiswa\Absence\StudentAbsenceActionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KetidakhadiranController extends Controller
{
    public function __construct(
        private readonly StudentAbsenceActionService $studentAbsenceActionService,
    ) {
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pendaftaran_id' => [
                'required',
                Rule::exists('pendaftaran_magangs', 'id')->where(
                    fn ($query) => $query->where('mahasiswa_id', $request->user()->id)->where('status', 'aktif'),
                ),
            ],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date'],
            'jenis' => ['required', Rule::in(['izin', 'sakit'])],
            'alasan' => ['required', 'string', 'max:2000'],
            'bukti' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [
            'tanggal_mulai.required' => 'Tanggal mulai ketidakhadiran wajib diisi.',
        ]);

        $pendaftaran = $this->studentAbsenceActionService->resolveActiveRegistration((int) $validated['pendaftaran_id']);

        $this->studentAbsenceActionService->submit(
            $pendaftaran,
            $request->user()->id,
            $validated,
            $request->file('bukti'),
        );

        return back()->with('success', 'Pengajuan tidak berangkat PKL berhasil dikirim ke pembimbing mitra.');
    }

    public function destroy(Request $request, KetidakhadiranMagang $ketidakhadiran): RedirectResponse
    {
        abort_unless(
            (int) $ketidakhadiran->mahasiswa_id === (int) $request->user()->id
            && $ketidakhadiran->status === 'pending',
            403,
        );

        $this->studentAbsenceActionService->cancel($ketidakhadiran);

        return back()->with('success', 'Pengajuan ketidakhadiran berhasil dibatalkan.');
    }
}
