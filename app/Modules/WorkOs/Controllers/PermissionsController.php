<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Modules\WorkOs\Services\AuditLogger;
use Illuminate\Http\Request;

class PermissionsController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', 'unique:permissions,slug', 'regex:/^[a-z0-9-:.]+$/'],
            'group' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $permission = Permission::create($request->only('name', 'slug', 'group', 'description'));

        AuditLogger::log('permission.created', 'info', ['name' => $permission->name], $permission);

        return back()->with('success', "Permission '{$request->name}' berhasil dibuat.");
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', 'unique:permissions,slug,'.$permission->id, 'regex:/^[a-z0-9-:.]+$/'],
            'group' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $permission->fill($request->only('name', 'slug', 'group', 'description'));
        $permission->save();

        AuditLogger::log('permission.updated', 'info', ['name' => $permission->name], $permission);

        return back()->with('success', "Permission '{$permission->name}' berhasil diperbarui.");
    }

    public function destroy(Permission $permission)
    {
        $permissionName = $permission->name;
        $permission->roles()->detach();
        $permission->{'delete'}();

        AuditLogger::log('permission.deleted', 'warning', ['name' => $permissionName], $permission);

        return back()->with('success', 'Permission berhasil dihapus.');
    }
}
