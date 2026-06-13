<?php

namespace App\Http\Controllers\Wims\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wims\StoreRegistrationRequest;
use App\Services\Wims\Mahasiswa\Registration\StudentRegistrationActionService;
use App\Services\Wims\Mahasiswa\Registration\StudentRegistrationPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly StudentRegistrationPageService $studentRegistrationPageService,
        private readonly StudentRegistrationActionService $studentRegistrationActionService,
    ) {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Wims/Mahasiswa/Pendaftaran', $this->studentRegistrationPageService->build($request->user()));
    }

    public function store(StoreRegistrationRequest $request): RedirectResponse
    {
        $user = $request->user();
        $latestRegistration = $this->studentRegistrationPageService->latestRegistration($user->id);
        $hasCompletedPkl = (bool) $user->has_completed_pkl;

        if (! $this->studentRegistrationPageService->canSubmitRegistration($latestRegistration, $hasCompletedPkl)) {
            return back()->withErrors([
                'registration' => $hasCompletedPkl
                    ? 'Akun ini sudah pernah menyelesaikan PKL dan tidak dapat mendaftar kembali.'
                    : 'Pendaftaran sedang menunggu keputusan kampus atau periode magang yang berjalan belum selesai.',
            ]);
        }

        $payload = $this->studentRegistrationActionService->buildPayload([
            'tanggal_mulai' => $request->date('tanggal_mulai')?->toDateString(),
            'tanggal_selesai' => $request->date('tanggal_selesai')?->toDateString(),
            'perusahaan_diminati_nama' => $request->safe()->string('perusahaan_diminati_nama')->trim()->toString(),
            'perusahaan_diminati_alamat' => $request->safe()->string('perusahaan_diminati_alamat')->trim()->toString(),
            'catatan_pengajuan' => $request->safe()->string('catatan_pengajuan')->trim()->toString(),
        ]);

        if ($latestRegistration?->status === 'revisi') {
            $this->studentRegistrationActionService->resubmitRevision($latestRegistration, $payload);

            return back()->with('success', 'Perbaikan pendaftaran berhasil dikirim ulang dan menunggu review kampus.');
        }

        $this->studentRegistrationActionService->create($user, $payload);

        return back()->with('success', 'Pendaftaran PKL/magang berhasil dikirim dan menunggu review kampus.');
    }
}
