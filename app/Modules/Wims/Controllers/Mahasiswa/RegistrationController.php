<?php

namespace App\Modules\Wims\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Modules\Wims\Requests\Mahasiswa\StoreRegistrationRequest;
use App\Modules\Wims\Services\Mahasiswa\Registration\StudentRegistrationActionService;
use App\Modules\Wims\Services\Mahasiswa\Registration\StudentRegistrationPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly StudentRegistrationPageService $studentRegistrationPageService,
        private readonly StudentRegistrationActionService $studentRegistrationActionService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Modules/Wims/Mahasiswa/Pendaftaran/Index', $this->studentRegistrationPageService->build($request->user()));
    }

    public function store(StoreRegistrationRequest $request): RedirectResponse
    {
        $user = $request->user();
        $latestRegistration = $this->studentRegistrationPageService->latestRegistration($user->id);
        $hasCompletedHistory = $this->studentRegistrationPageService->hasCompletedInternshipHistory($user->id);

        if (! $this->studentRegistrationPageService->canSubmitRegistration($latestRegistration, $hasCompletedHistory)) {
            return back()->withErrors([
                'registration' => $hasCompletedHistory
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
