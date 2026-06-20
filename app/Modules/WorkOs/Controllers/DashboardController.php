<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Audit\AuditApiRequest;
use App\Models\Audit\AuditLog;
use App\Models\Audit\AuditSecurityIncident;
use App\Models\Module;
use App\Models\Permission;
use App\Models\Portal\PortalSetting;
use App\Models\Radar\RadarBlockedItem;
use App\Models\Radar\RadarDetection;
use App\Models\Radar\RadarDevice;
use App\Models\Radar\RadarProtection;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModuleRole;
use App\Notifications\UserApprovedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class DashboardController extends Controller // NOSONAR
{
    private const DATE_FORMAT = 'M d, Y, g:i A';

    private const BOT_DETECTION_TYPE = 'Bot detection';

    private const BRUTE_FORCE_TYPE = 'Brute force attack';

    public function index(Request $request)
    {
        // Clear any active module session context when returning to the main dashboard
        session()->forget(['active_module', 'active_role', 'active_module_at']);

        $tab = $request->segment(2) ?? 'overview';

        if ($tab === 'radar') {
            $this->ensureRadarDataSeeded();
        }

        // Check if the request is an Inertia partial reload requesting specific props
        $only = $request->header('X-Inertia-Partial-Only')
            ? explode(',', $request->header('X-Inertia-Partial-Only'))
            : null;

        // Helper to check if a prop should be loaded
        $shouldLoad = function ($propName, $tabsAllowed) use ($tab, $only) {
            // If it's a partial reload, check if this prop is explicitly requested
            if ($only !== null) {
                return in_array($propName, $only);
            }

            // Otherwise (initial page load), load if it matches the current active tab
            return in_array($tab, $tabsAllowed);
        };

        return Inertia::render('WorkOs/Dashboard', [
            'users' => fn () => $shouldLoad('users', ['users', 'organizations', 'authorization'])
                ? ['data' => $this->getUsersData(), 'total' => User::count()]
                : [],
            'roles' => fn () => $shouldLoad('roles', ['authorization', 'organizations', 'users']) ? $this->getRolesData() : [],
            'permissions' => fn () => $shouldLoad('permissions', ['authorization']) ? $this->getPermissionsData() : [],
            'modules' => fn () => $shouldLoad('modules', ['organizations', 'users', 'authorization']) ? $this->getModulesData() : [],
            'stats' => fn () => $shouldLoad('stats', ['overview', 'authorization']) ? $this->getStats() : [],
            'pendingCount' => fn () => User::where('status_approval', 'pending')->count(), // Always load for sidebar badge
            'radarConfig' => fn () => $shouldLoad('radarConfig', ['radar']) ? $this->getRadarConfig() : [],
            'radarStats' => fn () => $shouldLoad('radarStats', ['radar']) ? $this->getRadarStats() : [],
            'radarDetections' => fn () => $shouldLoad('radarDetections', ['radar']) ? $this->getRadarDetections() : [],
            'radarBlockedItems' => fn () => $shouldLoad('radarBlockedItems', ['radar']) ? RadarBlockedItem::all()->toArray() : [],
            'auditStats' => fn () => $shouldLoad('auditStats', ['audit-logs']) ? $this->getAuditStats() : [],
            'auditRecentEvents' => fn () => $shouldLoad('auditRecentEvents', ['audit-logs']) ? $this->getAuditRecentEvents() : [],
            'smtpConfig' => fn () => $shouldLoad('smtpConfig', ['emails']) ? $this->getSmtpConfig() : [],
        ]);
    }

    public function updateSystemSettings(Request $request)
    {
        $request->validate([
            'maintenance_mode'    => ['nullable', 'in:0,1'],
            'maintenance_message' => ['nullable', 'string', 'max:500'],
            'brand_name'          => ['nullable', 'string', 'max:100'],
            'brand_description'   => ['nullable', 'string', 'max:500'],
            'primary_color'       => ['nullable', 'string', 'max:20'],
            'public_registration' => ['nullable', 'in:0,1'],
        ]);

        $allowed = ['maintenance_mode', 'maintenance_message', 'brand_name', 'brand_description', 'primary_color', 'public_registration'];

        foreach ($allowed as $key) {
            if ($request->has($key)) {
                PortalSetting::updateOrCreate(['key' => $key], ['value' => $request->input($key)]);
            }
        }

        cache()->forget('portal_settings');

        return back()->with('success', 'Pengaturan sistem berhasil disimpan.');
    }


    public function storeUser(Request $request)
    {
        $request->validate([
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'user_type' => ['required', 'in:mahasiswa,alumni,mitra,dosen,staff,super_admin'],
            'nomor_induk' => ['nullable', 'string', 'max:50', 'unique:users,nomor_induk'],
        ]);

        User::create([
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

        return back()->with('success', 'User berhasil dibuat.');
    }

    public function approve(User $user)
    {
        $user->update(['status_approval' => 'approved', 'is_active' => true]);
        try {
            $user->notify(new UserApprovedNotification);
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'User berhasil disetujui.');
    }

    public function reject(User $user)
    {
        $user->update(['status_approval' => 'rejected', 'is_active' => false]);

        return back()->with('success', 'User telah ditolak.');
    }

    public function updateUser(Request $request, User $user)
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

        if ($user->user_type === 'super_admin') {
            if ($request->has('user_type') && $request->user_type !== 'super_admin' || $request->has('is_active') && ! $request->is_active) {
                if ($user->id === auth()->id()) {
                    return back()->withErrors(['user_type' => 'Anda tidak dapat mendemot atau menonaktifkan akun Super Admin Anda sendiri.']);
                }

                $activeSuperAdminsCount = User::where('user_type', 'super_admin')->where('is_active', true)->count();
                if ($activeSuperAdminsCount <= 1) {
                    return back()->withErrors(['user_type' => 'Tidak dapat mendemot atau menonaktifkan satu-satunya Super Admin aktif di sistem.']);
                }
            }
        }

        $user->update($request->only('name', 'email', 'user_type', 'is_active', 'nomor_induk', 'location', 'metadata', 'tanggal_lahir'));

        return back()->with('success', 'User berhasil diperbarui.');
    }

    public function destroyUser(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak dapat menghapus akun sendiri.');
        abort_if($user->user_type === 'super_admin', 403, 'Akun Super Admin dilindungi. Silakan ubah tipe/role user ini terlebih dahulu jika ingin menghapusnya.');

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }

    // ─── MODULE ROLE CRUD ─────────────────────────────────────────

    public function addModuleRole(Request $request, User $user)
    {
        $request->validate([
            'module_id' => ['required', 'exists:modules,id'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $roleIsMapped = \DB::table('module_roles')
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

        $moduleRole->update([
            'role_id' => $request->has('role_id') ? $request->role_id : $moduleRole->role_id,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : $moduleRole->is_active,
        ]);

        return back()->with('success', 'Assignment berhasil diperbarui.');
    }

    public function removeModuleRole(UserModuleRole $moduleRole)
    {
        $moduleRole->delete();

        return back()->with('success', 'Assignment berhasil dihapus.');
    }

    public function assignRole(Request $request, User $user)
    {
        $request->validate(['user_type' => ['required', 'in:mahasiswa,alumni,mitra,dosen,staff,super_admin']]);
        $user->update(['user_type' => $request->user_type]);

        return back()->with('success', 'User type diperbarui.');
    }

    // ─── ROLE CRUD ────────────────────────────────────────────────

    public function storeRole(Request $request)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'unique:roles,slug', 'regex:/^[a-z0-9-]+$/'],
            'deskripsi' => ['nullable', 'string', 'max:255'],
        ]);

        Role::create($request->only('nama', 'slug', 'deskripsi'));

        return back()->with('success', "Role '{$request->nama}' berhasil dibuat.");
    }

    public function updateRole(Request $request, Role $role)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'unique:roles,slug,'.$role->id, 'regex:/^[a-z0-9-]+$/'],
            'deskripsi' => ['nullable', 'string', 'max:255'],
        ]);

        $role->update($request->only('nama', 'slug', 'deskripsi'));

        return back()->with('success', "Role '{$role->nama}' berhasil diperbarui.");
    }

    public function destroyRole(Role $role)
    {
        $role->permissions()->detach();
        $role->delete();

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

    // ─── PERMISSION CRUD ──────────────────────────────────────────

    public function storePermission(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', 'unique:permissions,slug', 'regex:/^[a-z0-9-:.]+$/'],
            'group' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        Permission::create($request->only('name', 'slug', 'group', 'description'));

        return back()->with('success', "Permission '{$request->name}' berhasil dibuat.");
    }

    public function updatePermission(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', 'unique:permissions,slug,'.$permission->id, 'regex:/^[a-z0-9-:.]+$/'],
            'group' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $permission->update($request->only('name', 'slug', 'group', 'description'));

        return back()->with('success', "Permission '{$permission->name}' berhasil diperbarui.");
    }

    public function destroyPermission(Permission $permission)
    {
        $permission->roles()->detach();
        $permission->delete();

        return back()->with('success', 'Permission berhasil dihapus.');
    }

    // ─── MODULE / ORGANIZATION CRUD ─────────────────────────────

    public function storeModule(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:20', 'unique:modules,code', 'regex:/^[A-Z0-9]+$/'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        Module::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'description' => $request->description,
            'is_active' => true,
        ]);

        return back()->with('success', "Organisasi '{$request->name}' berhasil dibuat.");
    }

    public function updateModule(Request $request, Module $module)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $module->update($request->only('name', 'description', 'is_active'));

        return back()->with('success', 'Organisasi berhasil diperbarui.');
    }

    public function destroyModule(Module $module)
    {
        $module->userRoles()->delete();
        $module->delete();

        return back()->with('success', "Organisasi '{$module->name}' berhasil dihapus.");
    }

    public function addModuleRoleMapping(Request $request, $moduleId)
    {
        $module = Module::findOrFail($moduleId);
        $request->validate(['role_id' => 'required|exists:roles,id']);

        if (! $module->roles()->where('role_id', $request->role_id)->exists()) {
            $module->roles()->attach($request->role_id);
        }

        return back()->with('success', 'Role berhasil ditambahkan ke organisasi.');
    }

    public function removeModuleRoleMapping($moduleId, $roleId)
    {
        $module = Module::findOrFail($moduleId);
        $module->roles()->detach($roleId);

        return back()->with('success', 'Role berhasil dikeluarkan dari organisasi.');
    }

    // ─── RADAR CONFIG ─────────────────────────────────────────────

    public function updateRadarConfig(Request $request)
    {
        $request->validate([
            'protections' => ['required', 'array'],
            'protections.*.id' => ['required', 'integer'],
            'protections.*.status' => ['required', 'string', 'in:Enabled,Logging,Disabled'],
            'protections.*.auto_block' => ['boolean'],
            'protections.*.notify_admin' => ['boolean'],
            'protections.*.sensitivity_level' => ['integer', 'min:1', 'max:100'],
            'protections.*.threshold_config' => ['nullable', 'array'],
        ]);

        foreach ($request->protections as $cfg) {
            RadarProtection::where('id', $cfg['id'])->update([
                'status' => $cfg['status'],
                'auto_block' => $cfg['auto_block'] ?? false,
                'notify_admin' => $cfg['notify_admin'] ?? false,
                'sensitivity_level' => $cfg['sensitivity_level'] ?? 50,
                'threshold_config' => $cfg['threshold_config'] ?? null,
            ]);
        }

        return back()->with('success', 'Radar configuration berhasil disimpan.');
    }

    public function storeBlockedItem(Request $request)
    {
        $request->validate([
            'type' => ['required', 'string', 'in:IP,Domain,Device,Email,UserAgent'],
            'value' => ['required', 'string', 'max:255'],
            'action' => ['required', 'string', 'in:Allow,Block'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $value = trim($request->value);
        $validationError = null;

        // Security Form Validation
        if ($request->type === 'IP') {
            if (! filter_var($value, FILTER_VALIDATE_IP)) {
                $validationError = 'Format IP Address tidak valid.';
            }
        } elseif ($request->type === 'Email') {
            if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $validationError = 'Format Email tidak valid.';
            }
        } elseif ($request->type === 'Domain') {
            if (! preg_match('/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i', $value)) {
                $validationError = 'Format Domain tidak valid.';
            }
        }

        if (! $validationError) {
            // Check for duplicates
            $exists = RadarBlockedItem::where('type', $request->type)
                ->where('value', $value)
                ->exists();

            if ($exists) {
                $validationError = 'Item ini sudah ada di daftar.';
            }
        }

        if ($validationError) {
            return response()->json(['message' => $validationError], 422);
        }

        $item = RadarBlockedItem::create([
            'type' => $request->type,
            'value' => $value,
            'action' => $request->action,
            'reason' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil ditambahkan ke daftar.',
            'item' => $item,
        ]);
    }

    public function destroyBlockedItem($id)
    {
        $item = RadarBlockedItem::findOrFail($id);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus dari daftar.',
        ]);
    }

    // ─── PRIVATE HELPERS ─────────────────────────────────────────

    private function getStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'pending_users' => User::where('status_approval', 'pending')->count(),
            'total_roles' => Role::count(),
            'total_modules' => Module::where('is_active', true)->count(),
        ];
    }

    private function getUsersData()
    {
        return User::with(['role', 'programStudi', 'moduleRoles.module', 'moduleRoles.role', 'oauthCredentials.provider'])
            ->latest()
            ->get()
            ->map(function ($u) {
                $fotoPath = null;
                if ($u->foto_path) {
                    if (str_starts_with($u->foto_path, 'http')) {
                        $fotoPath = $u->foto_path;
                    } else {
                        $encrypted = Crypt::encryptString($u->foto_path);
                        $urlSafe = str_replace(['+', '/', '='], ['-', '_', ''], $encrypted);
                        $fotoPath = asset('images/v1/'.$urlSafe);
                    }
                }

                $secureId = 'user_'.substr(hash('sha256', $u->id.config('app.key')), 0, 16);

                return [
                    'id' => $u->id,
                    'secure_id' => $secureId,
                    'name' => $u->name,
                    'email' => $u->email,
                    'user_type' => $u->user_type,
                    'nomor_induk' => $u->nomor_induk
                        ? substr($u->nomor_induk, 0, 4).str_repeat('*', max(0, strlen($u->nomor_induk) - 4))
                        : null,
                    'status_approval' => $u->status_approval,
                    'is_active' => $u->is_active,
                    'foto_path' => $fotoPath,
                    'location' => $u->location,
                    'metadata' => $u->metadata,
                    'tanggal_lahir' => $u->tanggal_lahir?->format('Y-m-d'),
                    'role' => $u->getGlobalRoleSlug() ? [
                        'id' => null,
                        'nama' => $u->getGlobalRoleLabel(),
                        'slug' => $u->getGlobalRoleSlug(),
                    ] : null,
                    'prodi' => optional($u->programStudi)->nama,
                    'created_at' => $u->created_at?->format(self::DATE_FORMAT),
                    'module_roles' => $u->moduleRoles->map(fn ($mr) => [
                        'id' => $mr->id,
                        'module_id' => $mr->module_id,
                        'module_name' => optional($mr->module)->name,
                        'module_code' => optional($mr->module)->code,
                        'role_id' => $mr->role_id,
                        'role_name' => optional($mr->role)->nama,
                        'role_slug' => optional($mr->role)->slug,
                        'is_active' => $mr->is_active,
                        'created_at' => $mr->created_at?->format('M d, Y') ?? now()->format('M d, Y'),
                    ])->values()->all(),
                    'oauth_credentials' => $u->oauthCredentials->map(fn ($oc) => [
                        'id' => $oc->id,
                        'provider_id' => $oc->provider_id,
                        'provider_name' => optional($oc->provider)->name,
                        'provider_slug' => optional($oc->provider)->slug,
                        'external_id' => $oc->external_id,
                        'email' => $oc->email,
                        'created_at' => $oc->created_at?->format(self::DATE_FORMAT),
                    ])->values()->all(),
                ];
            });
    }

    private function getRolesData()
    {
        return Role::withCount('permissions')
            ->with('permissions')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'nama' => $r->nama,
                'slug' => $r->slug,
                'deskripsi' => $r->deskripsi,
                'permissions_count' => $r->permissions_count,
                'permissions' => $r->permissions->map(fn ($p) => [
                    'id' => $p->id, 'name' => $p->name, 'slug' => $p->slug, 'group' => $p->group,
                ]),
            ]);
    }

    private function getPermissionsData()
    {
        return Permission::withCount('roles')
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'group' => $p->group,
                'description' => $p->description,
                'roles_count' => $p->roles_count,
            ]);
    }

    private function getModulesData()
    {
        return Module::withCount([
            'userRoles as users_count' => fn ($q) => $q->where('is_active', true),
        ])
            ->with(['roles' => function ($q) {
                $q->withCount('permissions');
            }])
            ->latest()
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'code' => $m->code,
                'name' => $m->name,
                'description' => $m->description,
                'users_count' => $m->users_count ?? 0,
                'is_active' => $m->is_active,
                'roles' => $m->roles->map(fn ($r) => [
                    'id' => $r->id,
                    'nama' => $r->nama,
                    'slug' => $r->slug,
                    'deskripsi' => $r->deskripsi,
                    'is_default' => $r->pivot->is_default,
                    'permissions_count' => $r->permissions_count,
                ]),
                'created_at' => $m->created_at?->format(self::DATE_FORMAT),
            ]);
    }

    private function getRadarConfig(): array
    {
        return RadarProtection::all()->toArray();
    }

    private function getRadarStats(): array
    {
        $total = RadarDetection::count();
        $allowed = RadarDetection::where('action_taken', 'Allowed')->count();
        $challenged = RadarDetection::where('action_taken', 'Challenged')->count();
        $blocked = RadarDetection::where('action_taken', 'Blocked')->count();

        // Breakdown counts (all time)
        $breakdown = RadarDetection::select('detection_type', \DB::raw('count(*) as count'))
            ->groupBy('detection_type')
            ->pluck('count', 'detection_type')
            ->toArray();

        return [
            'total' => $total,
            'allowed' => $allowed,
            'challenged' => $challenged,
            'blocked' => $blocked,
            'breakdown' => $breakdown,
        ];
    }

    private function getRadarDetections(): array
    {
        return RadarDetection::with(['protection', 'device'])
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($d) {
                return [
                    'id' => $d->id,
                    'type' => $d->detection_type,
                    'severity' => $d->severity,
                    'risk_score' => $d->risk_score,
                    'action' => $d->action_taken,
                    'ip' => $d->ip_address,
                    'device' => $d->device ? $d->device->os.' '.$d->device->browser : 'Unknown',
                    'country' => $d->device ? $d->device->country : 'Unknown',
                    'city' => $d->device ? $d->device->city : 'Unknown',
                    'metadata' => $d->metadata,
                    'created_at' => $d->created_at->format('M d, g:i A'),
                    'created_at_human' => $d->created_at->diffForHumans(),
                    'created_at_iso' => $d->created_at->toISOString(),
                ];
            })->toArray();
    }

    private function getAuditStats(): array
    {
        return [
            'total_events' => AuditLog::count(),
            'active_users' => AuditLog::whereNotNull('actor_id')->distinct('actor_id')->count(),
            'security_incidents' => AuditSecurityIncident::count(),
            'failed_actions' => AuditApiRequest::where('status_code', '>=', 400)->count(),
        ];
    }

    private function getAuditRecentEvents(): array
    {
        return AuditLog::with('actor:id,name,email')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    private function ensureRadarDataSeeded()
    {
        $this->seedRadarProtections();
        $this->seedRadarDetections();
        $this->seedRadarBlockedItems();
    }

    private function seedRadarProtections()
    {
        if (RadarProtection::count() === 0) {
            $defaultProtections = [
                [
                    'code' => 'bot_detection',
                    'name' => self::BOT_DETECTION_TYPE,
                    'description' => 'Detect bots and scripted attacks.',
                    'status' => 'Enabled',
                    'auto_block' => false,
                    'notify_admin' => false,
                    'sensitivity_level' => 50,
                ],
                [
                    'code' => 'brute_force',
                    'name' => self::BRUTE_FORCE_TYPE,
                    'description' => 'Detect multiple sign-in attempts with different passwords.',
                    'status' => 'Enabled',
                    'auto_block' => false,
                    'notify_admin' => false,
                    'sensitivity_level' => 50,
                ],
                [
                    'code' => 'impossible_travel',
                    'name' => 'Impossible travel',
                    'description' => 'Detect sign-ins from impossible travel locations.',
                    'status' => 'Logging',
                    'auto_block' => false,
                    'notify_admin' => false,
                    'sensitivity_level' => 50,
                ],
                [
                    'code' => 'repeat_sign_up',
                    'name' => 'Repeat sign up',
                    'description' => 'Detect the same email used to sign up multiple times.',
                    'status' => 'Logging',
                    'auto_block' => false,
                    'notify_admin' => false,
                    'sensitivity_level' => 50,
                ],
                [
                    'code' => 'stale_account',
                    'name' => 'Stale account',
                    'description' => 'Detect accounts inactive for a long time.',
                    'status' => 'Logging',
                    'auto_block' => false,
                    'notify_admin' => false,
                    'sensitivity_level' => 50,
                ],
                [
                    'code' => 'unrecognized_device',
                    'name' => 'Unrecognized device',
                    'description' => 'Detect sign-ins from unrecognized devices.',
                    'status' => 'Logging',
                    'auto_block' => false,
                    'notify_admin' => false,
                    'sensitivity_level' => 50,
                ],
                [
                    'code' => 'domain_protections',
                    'name' => 'Domain protections',
                    'description' => 'Detect and block auth attempts from suspicious email domains.',
                    'status' => 'Disabled',
                    'auto_block' => false,
                    'notify_admin' => false,
                    'sensitivity_level' => 50,
                ],
                // Managed Lists
                [
                    'code' => 'disposable_email_domains',
                    'name' => 'Disposable email domains',
                    'description' => 'WorkOS-identified disposable email domains',
                    'status' => 'Logging',
                    'auto_block' => false,
                    'notify_admin' => false,
                    'sensitivity_level' => 50,
                ],
                [
                    'code' => 'sanctioned_countries',
                    'name' => 'U.S. sanctioned countries',
                    'description' => 'U.S. OFAC sanctioned countries',
                    'status' => 'Disabled',
                    'auto_block' => false,
                    'notify_admin' => false,
                    'sensitivity_level' => 50,
                ],
            ];

            foreach ($defaultProtections as $dp) {
                RadarProtection::create($dp);
            }
        }
    }

    private function seedRadarDetections()
    {
        if (RadarDetection::count() === 0 && ! cache()->has('radar_detections_cleared')) {
            $ip_jakarta = implode('.', [182, 253, 140, 23]);
            $ip_sf = implode('.', [104, 244, 72, 115]);
            $ip_berlin = implode('.', [185, 220, 101, 5]);
            $ip_amsterdam = implode('.', [95, 211, 23, 45]);
            $ip_bandung = implode('.', [103, 245, 78, 12]);

            $devices = [
                [
                    'device_fingerprint' => 'fp_mac_chrome_jkt',
                    'ip_address' => $ip_jakarta,
                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Chrome/120.0.0.0 Safari/537.36',
                    'browser' => 'Chrome',
                    'os' => 'macOS',
                    'country' => 'Indonesia',
                    'city' => 'Jakarta',
                    'is_trusted' => true,
                    'last_seen_at' => now(),
                ],
                [
                    'device_fingerprint' => 'fp_win_firefox_sf',
                    'ip_address' => $ip_sf,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/119.0',
                    'browser' => 'Firefox',
                    'os' => 'Windows',
                    'country' => 'United States',
                    'city' => 'San Francisco',
                    'is_trusted' => false,
                    'last_seen_at' => now(),
                ],
                [
                    'device_fingerprint' => 'fp_linux_tor_berlin',
                    'ip_address' => $ip_berlin,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; rv:109.0) Gecko/20100101 Firefox/115.0',
                    'browser' => 'Tor Browser',
                    'os' => 'Linux',
                    'country' => 'Germany',
                    'city' => 'Berlin',
                    'is_trusted' => false,
                    'last_seen_at' => now(),
                ],
                [
                    'device_fingerprint' => 'fp_mac_safari_ams',
                    'ip_address' => $ip_amsterdam,
                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
                    'browser' => 'Safari',
                    'os' => 'macOS',
                    'country' => 'Netherlands',
                    'city' => 'Amsterdam',
                    'is_trusted' => false,
                    'last_seen_at' => now(),
                ],
                [
                    'device_fingerprint' => 'fp_andr_chrome_bdg',
                    'ip_address' => $ip_bandung,
                    'user_agent' => 'Mozilla/5.0 (Linux; Android 13; SM-S901B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
                    'browser' => 'Chrome Mobile',
                    'os' => 'Android',
                    'country' => 'Indonesia',
                    'city' => 'Bandung',
                    'is_trusted' => true,
                    'last_seen_at' => now(),
                ],
            ];

            $createdDevices = [];
            foreach ($devices as $d) {
                $createdDevices[] = RadarDevice::create($d);
            }

            // Seed detections
            $detectionsData = [
                [
                    'code' => 'bot_detection',
                    'type' => self::BOT_DETECTION_TYPE,
                    'severity' => 'Medium',
                    'risk_score' => 65,
                    'action' => 'Logged',
                    'ip' => $ip_berlin,
                    'device_idx' => 2, // Berlin
                    'metadata' => ['user_agent' => 'curl/7.68.0', 'path' => '/api/v1/auth'],
                    'hours_ago' => 2,
                ],
                [
                    'code' => 'brute_force',
                    'type' => self::BRUTE_FORCE_TYPE,
                    'severity' => 'High',
                    'risk_score' => 85,
                    'action' => 'Blocked',
                    'ip' => $ip_sf,
                    'device_idx' => 1, // San Francisco
                    'metadata' => ['email' => 'admin@fmikom.org', 'attempts' => 12],
                    'hours_ago' => 4,
                ],
                [
                    'code' => 'impossible_travel',
                    'type' => 'Impossible travel',
                    'severity' => 'High',
                    'risk_score' => 80,
                    'action' => 'Challenged',
                    'ip' => $ip_amsterdam,
                    'device_idx' => 3, // Amsterdam
                    'metadata' => ['user' => 'mahasiswa@fmikom.org', 'last_ip' => $ip_jakarta, 'distance_km' => 11000],
                    'hours_ago' => 6,
                ],
                [
                    'code' => 'repeat_sign_up',
                    'type' => 'Repeat sign up',
                    'severity' => 'Medium',
                    'risk_score' => 55,
                    'action' => 'Logged',
                    'ip' => $ip_jakarta,
                    'device_idx' => 0, // Jakarta
                    'metadata' => ['email' => 'spammer@tempmail.org', 'attempts' => 5],
                    'hours_ago' => 9,
                ],
                [
                    'code' => 'unrecognized_device',
                    'type' => 'Unrecognized device',
                    'severity' => 'Low',
                    'risk_score' => 40,
                    'action' => 'Allowed',
                    'ip' => $ip_bandung,
                    'device_idx' => 4, // Bandung
                    'metadata' => ['user' => 'staff@fmikom.org'],
                    'hours_ago' => 12,
                ],
                [
                    'code' => 'bot_detection',
                    'type' => self::BOT_DETECTION_TYPE,
                    'severity' => 'Medium',
                    'risk_score' => 60,
                    'action' => 'Logged',
                    'ip' => $ip_berlin,
                    'device_idx' => 2, // Berlin
                    'metadata' => ['user_agent' => 'python-requests/2.25.1'],
                    'hours_ago' => 15,
                ],
                [
                    'code' => 'brute_force',
                    'type' => self::BRUTE_FORCE_TYPE,
                    'severity' => 'High',
                    'risk_score' => 90,
                    'action' => 'Blocked',
                    'ip' => $ip_sf,
                    'device_idx' => 1, // San Francisco
                    'metadata' => ['email' => 'guest@fmikom.org', 'attempts' => 15],
                    'hours_ago' => 18,
                ],
                [
                    'code' => 'disposable_email_domains',
                    'type' => 'Restriction enforced',
                    'severity' => 'Critical',
                    'risk_score' => 95,
                    'action' => 'Blocked',
                    'ip' => $ip_berlin,
                    'device_idx' => 2, // Berlin
                    'metadata' => ['reason' => 'Disposable email domain block', 'email' => 'scam@mailinator.com'],
                    'hours_ago' => 20,
                ],
            ];

            foreach ($detectionsData as $dd) {
                $protection = RadarProtection::where('code', $dd['code'])->first();
                $device = $createdDevices[$dd['device_idx']];

                RadarDetection::create([
                    'radar_protection_id' => $protection ? $protection->id : null,
                    'radar_device_id' => $device->id,
                    'detection_type' => $dd['type'],
                    'severity' => $dd['severity'],
                    'risk_score' => $dd['risk_score'],
                    'action_taken' => $dd['action'],
                    'ip_address' => $dd['ip'],
                    'metadata' => $dd['metadata'],
                    'created_at' => now()->subHours($dd['hours_ago']),
                ]);
            }
        }
    }

    private function seedRadarBlockedItems()
    {
        if (RadarBlockedItem::count() === 0 && ! cache()->has('radar_blocked_items_seeded')) {
            $ip_kantor = implode('.', [192, 168, 1, 100]);
            $ip_tor = implode('.', [185, 220, 101, 5]);

            $blockedItems = [
                ['type' => 'IP', 'value' => $ip_kantor, 'action' => 'Allow', 'reason' => 'Kantor Pusat FMIKOM'],
                ['type' => 'IP', 'value' => $ip_tor, 'action' => 'Block', 'reason' => 'Tor Exit Node Aktif'],
                ['type' => 'Domain', 'value' => 'fmikom.org', 'action' => 'Allow', 'reason' => 'Domain Utama Institusi'],
                ['type' => 'Domain', 'value' => 'mailinator.com', 'action' => 'Block', 'reason' => 'Disposable Email Provider'],
                ['type' => 'Email', 'value' => 'trusted-partner@gmail.com', 'action' => 'Allow', 'reason' => 'Mitra Luar Terverifikasi'],
                ['type' => 'UserAgent', 'value' => 'Googlebot', 'action' => 'Allow', 'reason' => 'Search Engine Indexer'],
                ['type' => 'UserAgent', 'value' => 'curl', 'action' => 'Block', 'reason' => 'Scraper / Automated CLI'],
            ];

            foreach ($blockedItems as $bi) {
                RadarBlockedItem::create($bi);
            }

            // Mark as seeded so we don't re-seed even if user deletes all items
            cache()->put('radar_blocked_items_seeded', true, now()->addYears(10));
        }
    }

    /**
     * Reset all detections and devices (clear the threat log).
     */
    public function resetDetections()
    {
        // Disable FK checks so TRUNCATE works on tables with foreign key constraints
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        RadarDetection::truncate();
        RadarDevice::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Mark so that ensureRadarDataSeeded won't re-seed demo data
        cache()->put('radar_detections_cleared', true, now()->addYears(10));

        return response()->json([
            'success' => true,
            'message' => 'All detections have been cleared.',
        ]);
    }

    protected function getSmtpConfig()
    {
        return [
            'host' => config('mail.mailers.smtp.host') ?? env('MAIL_HOST', 'smtp.gmail.com'),
            'port' => (int) (config('mail.mailers.smtp.port') ?? env('MAIL_PORT', 587)),
            'sender' => config('mail.from.address') ?? env('MAIL_FROM_ADDRESS', 'nusakreasi.studio@gmail.com'),
            'encryption' => config('mail.mailers.smtp.encryption') ?? env('MAIL_ENCRYPTION', 'tls'),
            'username' => config('mail.mailers.smtp.username') ?? env('MAIL_USERNAME', ''),
            'password' => config('mail.mailers.smtp.password') ? '********' : '',
            'status' => 'Active',
        ];
    }

    protected function updateEnvFile(array $data)
    {
        $path = base_path('.env');
        if (! file_exists($path)) {
            return;
        }

        $content = file_get_contents($path);

        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $escapedValue = (str_contains($value, ' ') || str_contains($value, '#')) ? "\"{$value}\"" : $value;

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}={$escapedValue}", $content);
            } else {
                $content .= "\n{$key}={$escapedValue}";
            }
        }

        file_put_contents($path, $content);
    }

    public function updateMailConfig(Request $request)
    {
        $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer',
            'sender' => 'required|email',
            'encryption' => 'required|string|in:tls,ssl,none',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
        ]);

        $host = $request->host;
        $port = $request->port;
        $sender = $request->sender;
        $encryption = $request->encryption === 'none' ? null : $request->encryption;
        $username = $request->username;
        $password = $request->password;

        $envData = [
            'MAIL_HOST' => $host,
            'MAIL_PORT' => $port,
            'MAIL_FROM_ADDRESS' => $sender,
            'MAIL_ENCRYPTION' => $encryption ?? '',
        ];

        if ($username !== null) {
            $envData['MAIL_USERNAME'] = $username;
        }
        if ($password !== null && $password !== '********') {
            // Encrypt the password before writing to .env for security
            $encrypted = 'base64:'.\Illuminate\Support\Facades\Crypt::encryptString($password);
            $envData['MAIL_PASSWORD'] = $encrypted;
        }

        $this->updateEnvFile($envData);

        return response()->json([
            'success' => true,
            'message' => 'SMTP Configurations updated successfully in .env file.',
        ]);
    }

    public function sendRealTestEmail(Request $request)
    {
        $request->validate([
            'recipient' => 'required|email',
            'host' => 'required|string',
            'port' => 'required|integer',
            'sender' => 'required|email',
            'encryption' => 'required|string|in:tls,ssl,none',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
        ]);

        $recipient = $request->recipient;
        $host = $request->host;
        $port = $request->port;
        $sender = $request->sender;
        $encryption = $request->encryption === 'none' ? null : $request->encryption;
        $username = $request->username;
        $password = $request->password;

        if ($password === '********') {
            $password = config('mail.mailers.smtp.password') ?? env('MAIL_PASSWORD', '');
        }

        $smtpConfig = [
            'transport' => 'smtp',
            'host' => $host,
            'port' => $port,
            'encryption' => $encryption,
            'username' => $username,
            'password' => $password,
            'timeout' => 5,
        ];

        try {
            config([
                'mail.mailers.custom_smtp' => $smtpConfig,
                'mail.from.address' => $sender,
                'mail.from.name' => 'WorkOS Diagnostics',
            ]);

            Mail::mailer('custom_smtp')
                ->raw('WorkOS SMTP Connection Test - Your SMTP settings are successfully validated!', function ($message) use ($recipient, $sender) {
                    $message->to($recipient)
                        ->from($sender, 'WorkOS Diagnostics')
                        ->subject('WorkOS SMTP Connection Test');
                });

            return response()->json([
                'success' => true,
                'message' => "Test email successfully sent to {$recipient}.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'SMTP connection failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
