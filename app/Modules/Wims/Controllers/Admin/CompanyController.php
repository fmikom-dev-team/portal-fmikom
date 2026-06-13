<?php

namespace App\Modules\Wims\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Magang\PerusahaanMitra;
use App\Modules\Wims\Services\Admin\AdminCompanyActionService;
use App\Modules\Wims\Services\Admin\AdminCompanyPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function __construct(
        private readonly AdminCompanyPageService $adminCompanyPageService,
        private readonly AdminCompanyActionService $adminCompanyActionService,
    ) {
    }

    public function index(Request $request): Response
    {
        return Inertia::render(
            'Wims/Admin/Perusahaan/Index',
            $this->adminCompanyPageService->build(trim((string) $request->string('search', ''))),
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->adminCompanyActionService->validateCompany($request);

        $this->adminCompanyActionService->createCompany($validated);

        return back()->with('success', 'Perusahaan mitra berhasil ditambahkan.');
    }

    public function update(Request $request, PerusahaanMitra $company): RedirectResponse
    {
        $validated = $this->adminCompanyActionService->validateCompany($request);

        $this->adminCompanyActionService->updateCompany($company, $validated);

        return back()->with('success', 'Perusahaan mitra berhasil diperbarui.');
    }

    public function destroy(PerusahaanMitra $company): RedirectResponse
    {
        $this->adminCompanyActionService->deleteCompany($company);

        return back()->with('success', 'Perusahaan mitra berhasil dihapus.');
    }

    public function storeAccount(Request $request, PerusahaanMitra $company): RedirectResponse
    {
        if ($company->user_id) {
            return back()->with('error', 'Perusahaan ini sudah memiliki satu akun mitra aktif.');
        }

        $validated = $this->adminCompanyActionService->validateAccount($request);

        if (! $this->adminCompanyActionService->createCompanyAccount($company, $validated)) {
            return back()->with('error', 'Role mitra belum tersedia. Jalankan migrasi terbaru terlebih dahulu.');
        }

        return back()->with('success', 'Akun mitra perusahaan berhasil dibuat.');
    }
}
