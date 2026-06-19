<?php

namespace App\Modules\Wims\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class WimsDashboardController extends Controller
{
    private const ADMIN_ROLES = ['super-admin', 'admin', 'admin-universitas', 'admin-akademik', 'prodi'];

    public function index(Request $request): RedirectResponse
    {
        $role = $request->attributes->get('resolved_role');

        abort_unless(is_string($role) && $role !== '', 403, 'Role aktif tidak ditemukan untuk modul WIMS.');

        if (in_array($role, self::ADMIN_ROLES, true)) {
            return redirect()->route('wims.admin.dashboard');
        }

        return match ($role) {
            'mahasiswa' => redirect()->route('wims.dashboard'),
            'dosen' => redirect()->route('wims.dosen.dashboard'),
            'mitra' => redirect()->route('wims.mitra.dashboard'),
            default => abort(403, "Role aktif '{$role}' tidak dikenali untuk modul WIMS."),
        };
    }
}
