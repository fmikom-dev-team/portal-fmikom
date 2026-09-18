<?php

namespace App\Modules\Fast\Support;

use App\Models\Role;
use App\Models\User;

class FastPermissionCatalog
{
    /**
     * Roles that use the full FAST admin permission set when no custom
     * permissions have been configured for the role yet.
     *
     * @var array<int, string>
     */
    protected const ADMIN_ROLE_SLUGS = [
        'admin',
        'super-admin',
        'admin-universitas',
        'admin-akademik',
        'prodi',
    ];

    /**
     * @var array<string, array<int, string>>
     */
    protected const DEFAULT_ROLE_PERMISSIONS = [
        'admin' => [
            'fast.admin.dashboard.view',
            'fast.admin.queue.view',
            'fast.admin.surat.view',
            'fast.admin.surat.create',
            'fast.admin.surat.update',
            'fast.admin.surat.approve',
            'fast.admin.history.view',
            'fast.admin.archive.view',
            'fast.admin.template.manage',
            'fast.admin.category.manage',
            'fast.admin.qr.manage',
            'fast.admin.settings.manage',
            'fast.document.preview',
            'fast.document.download',
        ],
        'kaprodi' => [
            'fast.admin.dashboard.view',
            'fast.admin.queue.view',
            'fast.admin.surat.view',
            'fast.admin.history.view',
            'fast.admin.archive.view',
            'fast.admin.template.view',
            'fast.admin.category.view',
            'fast.admin.qr.view',
            'fast.approval.dashboard.view',
            'fast.approval.queue.view',
            'fast.approval.archive.view',
            'fast.approval.surat.view',
            'fast.approval.surat.approve',
            'fast.approval.surat.reject',
            'fast.approval.note.write',
            'fast.document.preview',
            'fast.document.download',
        ],
        'dekan' => [
            'fast.admin.dashboard.view',
            'fast.admin.queue.view',
            'fast.admin.surat.view',
            'fast.admin.history.view',
            'fast.admin.archive.view',
            'fast.admin.template.view',
            'fast.admin.category.view',
            'fast.admin.qr.view',
            'fast.approval.dashboard.view',
            'fast.approval.queue.view',
            'fast.approval.archive.view',
            'fast.approval.surat.view',
            'fast.approval.surat.approve',
            'fast.approval.surat.reject',
            'fast.approval.note.write',
            'fast.document.preview',
            'fast.document.download',
        ],
        'mahasiswa' => [
            'fast.dashboard.view',
            'fast.submission.create',
            'fast.submission.view',
            'fast.submission.cancel',
            'fast.document.preview',
            'fast.document.download',
        ],
        'dosen' => [
            'fast.dashboard.view',
            'fast.submission.create',
            'fast.submission.view',
            'fast.submission.cancel',
            'fast.document.preview',
            'fast.document.download',
        ],
    ];

    /**
     * @return array<int, string>
     */
    public static function permissionsForUser(User $user, ?string $roleSlug = null): array
    {
        // Global super-admin tetap memiliki permission penuh, tetapi policy masih
        // membatasi cakupan data sesuai role konteks yang sedang dipilih.
        // Contoh: super-admin yang memilih konteks mahasiswa dapat membuka
        // dashboard mahasiswa tanpa otomatis membaca pengajuan mahasiswa lain.
        if ($user->isSuperAdmin()) {
            return ['*'];
        }

        $roleSlug = static::normalizeRoleSlug($roleSlug ?: $user->getResolvedRoleSlug() ?: $user->getGlobalRoleSlug());

        if ($roleSlug === '') {
            return [];
        }

        $dbPermissions = Role::query()
            ->where('slug', $roleSlug)
            ->with(['permissions:id,slug'])
            ->first()
            ?->permissions
            ?->pluck('slug')
            ?->map(fn ($permission) => (string) $permission)
            ?->filter()
            ?->unique()
            ?->values()
            ?->all() ?? [];

        if ($dbPermissions !== []) {
            // Data role lama masih memakai permission dengan format `fast:*`.
            // Selama belum ada permission FAST kanonis (`fast.*`) yang dikonfigurasi,
            // pertahankan akses baseline agar mahasiswa/dosen tidak terkunci dari
            // dashboard setelah policy FAST berbasis permission diaktifkan.
            $hasCanonicalFastPermission = collect($dbPermissions)
                ->contains(fn (string $permission): bool => str_starts_with($permission, 'fast.'));

            if (! $hasCanonicalFastPermission && isset(self::DEFAULT_ROLE_PERMISSIONS[$roleSlug])) {
                $dbPermissions = array_merge(
                    self::DEFAULT_ROLE_PERMISSIONS[$roleSlug],
                    $dbPermissions,
                );
            }

            $dbPermissions = array_values(array_unique(array_merge(
                $dbPermissions,
                self::readonlyAdminPermissionsForRole($roleSlug),
            )));

            return $dbPermissions;
        }

        if (in_array($roleSlug, self::ADMIN_ROLE_SLUGS, true)) {
            return self::DEFAULT_ROLE_PERMISSIONS['admin'];
        }

        return self::DEFAULT_ROLE_PERMISSIONS[$roleSlug] ?? [];
    }

    public static function has(User $user, string $permission, ?string $roleSlug = null): bool
    {
        $permission = static::normalizePermissionSlug($permission);

        if ($permission === '') {
            return false;
        }

        $permissions = static::permissionsForUser($user, $roleSlug);

        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }

    /**
     * @param  array<int, string>  $permissions
     */
    public static function hasAny(User $user, array $permissions, ?string $roleSlug = null): bool
    {
        foreach ($permissions as $permission) {
            if (static::has($user, (string) $permission, $roleSlug)) {
                return true;
            }
        }

        return false;
    }

    protected static function normalizeRoleSlug(?string $roleSlug): string
    {
        $roleSlug = strtolower(trim((string) $roleSlug));

        return str_replace('_', '-', $roleSlug);
    }

    protected static function normalizePermissionSlug(string $permission): string
    {
        return strtolower(trim($permission));
    }

    /**
     * @return array<int, string>
     */
    protected static function readonlyAdminPermissionsForRole(string $roleSlug): array
    {
        if (! in_array($roleSlug, ['kaprodi', 'dekan'], true)) {
            return [];
        }

        return [
            'fast.admin.dashboard.view',
            'fast.admin.queue.view',
            'fast.admin.surat.view',
            'fast.admin.history.view',
            'fast.admin.archive.view',
            'fast.admin.template.view',
            'fast.admin.category.view',
            'fast.admin.qr.view',
        ];
    }
}
