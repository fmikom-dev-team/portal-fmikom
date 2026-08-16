<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Modules\WorkOs\Services\AuditLogger;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'unique:roles,slug', 'regex:/^[a-z0-9-]+$/'],
            'deskripsi' => ['nullable', 'string', 'max:255'],
        ]);

        $role = Role::create($request->only('nama', 'slug', 'deskripsi'));

        AuditLogger::log('role.created', 'info', ['name' => $role->nama], $role);

        return back()->with('success', "Role '{$request->nama}' berhasil dibuat.");
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'unique:roles,slug,'.$role->id, 'regex:/^[a-z0-9-]+$/'],
            'deskripsi' => ['nullable', 'string', 'max:255'],
        ]);

        $role->fill($request->only('nama', 'slug', 'deskripsi'));
        $role->save();

        AuditLogger::log('role.updated', 'info', ['name' => $role->nama], $role);

        return back()->with('success', "Role '{$role->nama}' berhasil diperbarui.");
    }

    public function destroy(Role $role)
    {
        $protectedSlugs = ['super-admin', 'admin', 'mahasiswa', 'dosen', 'alumni', 'mitra', 'staff', 'prodi'];
        if (in_array(strtolower($role->slug), $protectedSlugs, true)) {
            return back()->withErrors(['error' => "Role '{$role->nama}' adalah role sistem inti dan dilindungi dari penghapusan."]);
        }

        $assignedCount = $role->userModuleRoles()->count();
        if ($assignedCount > 0) {
            return back()->withErrors(['error' => "Role '{$role->nama}' sedang digunakan oleh {$assignedCount} pengguna. Harap pindahkan pengguna ke role lain terlebih dahulu."]);
        }

        $roleName = $role->nama;
        $role->permissions()->detach();
        $role->delete();

        AuditLogger::log('role.deleted', 'warning', ['name' => $roleName], $role);

        return back()->with('success', 'Role berhasil dihapus.');
    }

    public function syncPermissions(Request $request, Role $role)
    {
        $request->validate([
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['exists:permissions,id'],
        ]);

        $role->permissions()->sync($request->permission_ids ?? []);

        return back()->with('success', "Permissions untuk role '{$role->nama}' berhasil disimpan.");
    }

    public function updatePriorities(Request $request)
    {
        $request->validate([
            'roles' => ['required', 'array'],
            'roles.*.id' => ['required', 'exists:roles,id'],
            'roles.*.priority' => ['required', 'integer'],
        ]);

        foreach ($request->roles as $item) {
            Role::where('id', $item['id'])->update(['priority' => $item['priority']]);
        }

        return back()->with('success', 'Prioritas role berhasil diperbarui.');
    }
}
