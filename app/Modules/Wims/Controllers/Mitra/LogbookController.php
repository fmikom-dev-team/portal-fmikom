<?php

namespace App\Modules\Wims\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\LogbookMagang;
use App\Modules\Wims\Services\Mitra\MitraAccessService;
use App\Modules\Wims\Services\Mitra\MitraLogbookReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LogbookController extends Controller
{
    public function __construct(
        private readonly MitraAccessService $mitraAccessService,
        private readonly MitraLogbookReviewService $mitraLogbookReviewService,
    ) {
    }

    public function review(Request $request, LogbookMagang $logbook): RedirectResponse
    {
        $this->mitraAccessService->authorizeLogbookReview($request->user(), $logbook);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['disetujui', 'revisi'])],
            'catatan_mitra' => ['nullable', 'string', 'max:3000'],
        ]);

        $this->mitraLogbookReviewService->review($logbook, $request->user()->id, $validated);

        return back()->with('success', 'Review logbook oleh pembimbing mitra berhasil disimpan.');
    }
}
