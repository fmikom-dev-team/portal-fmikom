<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Auth\AuthEmailLog;
use App\Models\Auth\AuthLoginAttempt;
use App\Models\Auth\AuthOAuthCredential;
use App\Modules\WorkOs\Services\AuditLogger;
use App\Notifications\UserApprovedNotification;
use Illuminate\Support\Facades\Auth;
/**
 * Dedicated UsersController — extracted from DashboardController
 */
use App\Models\Auth\AuthSession;
use App\Models\Module;
use App\Models\ProgramStudi;
use App\Models\User;
use App\Models\UserModuleRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Dedicated UsersController — extracted from DashboardController
 */
class UsersController extends Controller
{
    use \App\Modules\WorkOs\Controllers\Concerns\HasUserDiagnostics;
    use \App\Modules\WorkOs\Controllers\Concerns\HasUserImport;

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'user_type' => ['required', 'in:mahasiswa,alumni,mitra,dosen,staff,super_admin'],
            'nomor_induk' => ['nullable', 'string', 'max:50', 'unique:users,nomor_induk'],
        ]);

        $user = User::create([
            'name' => trim($request->first_name.' '.$request->last_name) ?: explode('@', $request->email)[0],
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'nomor_induk' => $request->nomor_induk,
            'status_approval' => 'approved',
            'is_active' => true,
            'email_verified_at' => now(),
            'password_changed_at' => null,
        ]);

        // Auto-assign default module access based on role/user_type
        $user->assignDefaultModuleRoles();

        AuditLogger::log('user.created', 'info', ['email' => $user->email], $user);

        return back()->with('success', 'User berhasil dibuat.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'user_type' => ['sometimes', 'in:mahasiswa,alumni,mitra,dosen,staff,super_admin'],
            'is_active' => ['sometimes', 'boolean'],
            'nomor_induk' => ['nullable', 'string', 'max:50', 'unique:users,nomor_induk,'.$user->id],
            'location' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
            'tanggal_lahir' => ['nullable', 'date'],
        ]);

        if ($user->user_type === 'super_admin' && (($request->has('user_type') && $request->user_type !== 'super_admin') || ($request->has('is_active') && ! $request->is_active))) {
            if ($user->id === Auth::id()) {
                return back()->withErrors(['user_type' => 'Anda tidak dapat mendemot atau menonaktifkan akun Super Admin Anda sendiri.']);
            }

            $activeSuperAdminsCount = User::query()->where('user_type', '=', 'super_admin', 'and')->where('is_active', '=', true, 'and')->count('*');
            if ($activeSuperAdminsCount <= 1) {
                return back()->withErrors(['user_type' => 'Tidak dapat mendemot atau menonaktifkan satu-satunya Super Admin aktif di sistem.']);
            }
        }

        $userData = $request->only('name', 'email', 'user_type', 'is_active', 'nomor_induk', 'location', 'metadata', 'tanggal_lahir');
        if ($request->has('nomor_induk') && str_contains((string) $request->nomor_induk, '*')) {
            unset($userData['nomor_induk']);
        }
        $user->fill($userData);
        $user->save();

        AuditLogger::log('user.updated', 'info', ['email' => $user->email], $user);

        return back()->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        abort_if($user->id === Auth::id(), 403, 'Tidak dapat menghapus akun sendiri.');
        abort_if($user->user_type === 'super_admin', 403, 'Akun Super Admin dilindungi. Silakan ubah tipe/role user ini terlebih dahulu jika ingin menghapusnya.');

        $user->{'delete'}();

        AuditLogger::log('user.deleted', 'warning', ['email' => $user->email], $user);

        return back()->with('success', 'User berhasil dihapus.');
    }

    public function approveDeletion(User $user)
    {
        abort_if($user->id === Auth::id(), 403, 'Tidak dapat menyetujui penghapusan akun sendiri.');
        abort_if($user->user_type === 'super_admin', 403, 'Akun Super Admin dilindungi.');

        $user->{'delete'}();

        return back()->with('success', 'Pengajuan penghapusan disetujui. Akun user berhasil dihapus secara permanen.');
    }

    public function rejectDeletion(User $user)
    {
        abort_if($user->id === Auth::id(), 403, 'Tidak dapat memproses akun sendiri.');

        $user->fill([
            'deletion_requested_at' => null,
        ]);
        $user->save();

        return back()->with('success', 'Pengajuan penghapusan ditolak. Akun user telah dikembalikan ke status normal.');
    }

    public function approve(User $user)
    {
        $user->fill(['status_approval' => 'approved', 'is_active' => true]);
        $user->save();
        try {
            $user->notify(new UserApprovedNotification);
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'User berhasil disetujui.');
    }

    public function reject(User $user)
    {
        $user->fill(['status_approval' => 'rejected', 'is_active' => false]);
        $user->save();

        return back()->with('success', 'User telah ditolak.');
    }

    public function assignRole(Request $request, User $user)
    {
        $request->validate(['user_type' => ['required', 'in:mahasiswa,alumni,mitra,dosen,staff,super_admin']]);
        $user->fill(['user_type' => $request->user_type]);
        $user->save();

        return back()->with('success', 'User type diperbarui.');
    }

    public function addModuleRole(Request $request, User $user)
    {
        $request->validate([
            'module_id' => ['required', 'exists:modules,id'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $roleIsMapped = DB::table('module_roles')
            ->where('module_id', $request->module_id)
            ->where('role_id', $request->role_id)
            ->exists();

        if (! $roleIsMapped) {
            return back()->withErrors(['role_id' => 'Role ini belum diaktifkan/tersedia untuk organisasi tersebut.']);
        }

        $exists = $user->moduleRoles()
            ->where('module_id', $request->module_id)
            ->where('role_id', $request->role_id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['module_id' => 'User sudah memiliki assignment ini di modul tersebut.']);
        }

        $user->moduleRoles()->create([
            'module_id' => $request->module_id,
            'role_id' => $request->role_id,
            'is_active' => true,
        ]);

        return back()->with('success', 'User berhasil ditambahkan ke modul.');
    }

    public function updateModuleRole(Request $request, UserModuleRole $moduleRole)
    {
        $request->validate([
            'role_id' => ['sometimes', 'exists:roles,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $moduleRole->fill([
            'role_id' => $request->has('role_id') ? $request->role_id : $moduleRole->role_id,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : $moduleRole->is_active,
        ]);
        $moduleRole->save();

        return back()->with('success', 'Assignment berhasil diperbarui.');
    }

    public function removeModuleRole(UserModuleRole $moduleRole)
    {
        $moduleRole->{'delete'}();

        return back()->with('success', 'Assignment berhasil dihapus.');
    }

    public function disconnectOAuth(User $user, AuthOAuthCredential $credential)
    {
        if ($credential->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $credential->{'delete'}();

        return back()->with('success', 'Connected account disconnected successfully.');
    }

    // Diagnostics methods are migrated to HasUserDiagnostics concern trait
}
