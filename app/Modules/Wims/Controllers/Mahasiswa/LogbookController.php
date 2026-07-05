<?php

namespace App\Modules\Wims\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Magang\LogbookMagang;
use App\Modules\Wims\Requests\Mahasiswa\StoreLogbookRequest;
use App\Modules\Wims\Services\Mahasiswa\Logbook\LogbookActionService;
use App\Modules\Wims\Services\Mahasiswa\Logbook\LogbookExportService;
use App\Modules\Wims\Services\Mahasiswa\Logbook\LogbookPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class LogbookController extends Controller
{
    public function __construct(
        protected LogbookPageService $logbookPageService,
        protected LogbookActionService $logbookActionService,
        protected LogbookExportService $logbookExportService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Modules/Wims/Mahasiswa/Logbook/Index', $this->logbookPageService->build(
            $request->user()->id,
            $request->integer('pendaftaran'),
        ));
    }

    public function store(StoreLogbookRequest $request): RedirectResponse
    {
        $pendaftaran = $this->logbookPageService->resolvePendaftaran($request->user()->id, $request->integer('pendaftaran_id'));

        if (! $pendaftaran) {
            return back()->withErrors([
                'logbook' => 'Data pendaftaran magang tidak ditemukan.',
            ]);
        }

        if (! $this->logbookPageService->canSubmitToday($pendaftaran)) {
            return back()->withErrors([
                'logbook' => $this->logbookPageService->blockedMessage($pendaftaran),
            ]);
        }

        if ($this->logbookActionService->alreadySubmittedToday($pendaftaran)) {
            return back()->withErrors([
                'logbook' => 'Logbook hari ini sudah disimpan.',
            ]);
        }

        try {
            $this->logbookActionService->create(
                $pendaftaran,
                [
                    'jam_mulai' => $request->safe()->string('jam_mulai')->toString(),
                    'jam_selesai' => $request->safe()->string('jam_selesai')->toString(),
                    'aktivitas_harian' => $request->safe()->string('aktivitas_harian')->trim()->toString(),
                    'kompetensi_dicapai' => $request->safe()->string('kompetensi_dicapai')->trim()->toString(),
                ],
                $request->file('photos', []),
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'logbook' => 'Terjadi kendala saat menyimpan logbook. Silakan coba lagi.',
            ]);
        }

        return back()->with([
            'success' => 'Logbook berhasil disimpan',
        ]);
    }

    public function update(StoreLogbookRequest $request, LogbookMagang $logbook): RedirectResponse
    {
        $pendaftaran = $this->logbookPageService->resolvePendaftaran($request->user()->id, $request->integer('pendaftaran_id'));

        if (! $pendaftaran || (int) $logbook->pendaftaran_id !== (int) $pendaftaran->id) {
            abort(403);
        }

        if ($logbook->status !== 'revisi') {
            return back()->withErrors([
                'logbook' => 'Hanya logbook dengan status revisi yang dapat diperbarui.',
            ]);
        }

        try {
            $this->logbookActionService->updateRevision(
                $logbook,
                [
                    'jam_mulai' => $request->safe()->string('jam_mulai')->toString(),
                    'jam_selesai' => $request->safe()->string('jam_selesai')->toString(),
                    'aktivitas_harian' => $request->safe()->string('aktivitas_harian')->trim()->toString(),
                    'kompetensi_dicapai' => $request->safe()->string('kompetensi_dicapai')->trim()->toString(),
                ],
                $request->hasFile('photos'),
                $request->file('photos', []),
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'logbook' => 'Terjadi kendala saat memperbarui logbook. Silakan coba lagi.',
            ]);
        }

        return back()->with([
            'success' => 'Perbaikan logbook berhasil dikirim ulang dan menunggu review mitra.',
        ]);
    }

    public function downloadCurrentPeriod(Request $request)
    {
        return $this->logbookExportService->downloadCurrentPeriod($request->user()->id);
    }
}

