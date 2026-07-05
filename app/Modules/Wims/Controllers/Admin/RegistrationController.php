<?php

namespace App\Modules\Wims\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Magang\PendaftaranMagang;
use App\Modules\Wims\Services\Admin\AdminRegistrationActionService;
use App\Modules\Wims\Services\Admin\AdminRegistrationPageService;
use App\Support\WimsStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly AdminRegistrationPageService $adminRegistrationPageService,
        private readonly AdminRegistrationActionService $adminRegistrationActionService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Modules/Wims/Admin/Pendaftaran/Index', $this->adminRegistrationPageService->build($request));
    }

    public function updateStatus(Request $request, PendaftaranMagang $pendaftaran): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected', 'revisi'])],
            'catatan_revisi_admin' => ['nullable', 'string', 'max:1500', Rule::requiredIf($request->input('status') === 'revisi')],
        ]);

        if (in_array($pendaftaran->status, ['aktif', 'selesai'], true)) {
            return back()->with('error', 'Pendaftaran yang sudah masuk fase aktif atau selesai tidak dapat diubah dari modul approval.');
        }

        $this->adminRegistrationActionService->updateStatus($pendaftaran, $validated);

        return back()->with('success', 'Status pendaftaran berhasil diperbarui.');
    }

    public function bulkApprove(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:pendaftaran_magangs,id'],
        ]);

        $registrations = PendaftaranMagang::query()
            ->whereIn('id', $validated['ids'])
            ->whereNotIn('status', ['aktif', 'selesai'])
            ->get();

        if ($registrations->isEmpty()) {
            return back()->with('error', 'Tidak ada pendaftaran yang bisa ditandai setuju.');
        }

        DB::transaction(function () use ($registrations): void {
            foreach ($registrations as $pendaftaran) {
                $this->adminRegistrationActionService->updateStatus($pendaftaran, [
                    'status' => 'approved',
                ]);
            }
        });

        return back()->with('success', sprintf('%d pendaftaran berhasil disetujui.', $registrations->count()));
    }

    public function downloadProposal(PendaftaranMagang $pendaftaran): BinaryFileResponse
    {
        $location = WimsStorage::locate($pendaftaran->proposal_pkl_path);
        $absolutePath = $location['absolute_path'] ?? null;

        abort_unless(filled($pendaftaran->proposal_pkl_path) && $absolutePath && is_file($absolutePath), 404, 'File proposal PKL tidak ditemukan.');

        return response()->download($absolutePath, $pendaftaran->proposalAttachmentDownloadName());
    }
}
