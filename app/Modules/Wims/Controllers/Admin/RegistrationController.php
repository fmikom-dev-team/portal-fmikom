<?php

namespace App\Modules\Wims\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Magang\PendaftaranMagang;
use App\Modules\Wims\Services\Admin\AdminRegistrationActionService;
use App\Modules\Wims\Services\Admin\AdminRegistrationPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

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
}
