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
        $roleName = $role->nama;
        $role->permissions()->detach();
        $role->{'delete'}();

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
}
