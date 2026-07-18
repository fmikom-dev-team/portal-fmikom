<?php

namespace App\Concerns;

use App\Models\Auth\AuthMfa;
use App\Modules\Fast\Support\FastPermissionCatalog;
use App\Support\PublicStorageUrl;

trait UserHelpers
{
    public function photoUrl(): ?string
    {
        $path = $this->foto_path ?? null;

        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return PublicStorageUrl::signed($path);
    }

    public function getGlobalRoleSlug(): ?string
    {
        if (blank($this->user_type)) {
            return null;
        }

        return str_replace('_', '-', (string) $this->user_type);
    }

    /**
     * Backward-compatible alias used by older FAST code paths.
     */
    public function userTypeSlug(): ?string
    {
        return $this->getResolvedRoleSlug();
    }

    /**
     * Backward-compatible label helper used by older FAST code paths.
     */
    public function roleDisplayName(): string
    {
        return $this->getResolvedRoleLabel();
    }

    /**
     * FAST legacy compatibility helper.
     * Submission flow only allows mahasiswa and dosen.
     */
    public function hasFastUserRole(): bool
    {
        return $this->isMahasiswa() || $this->isDosen();
    }

    public function hasGlobalRole(string ...$roles): bool
    {
        $globalRole = $this->getGlobalRoleSlug();

        if (! $globalRole) {
            return false;
        }

        return in_array($globalRole, $roles, true);
    }

    public function hasRole(string ...$roles): bool
    {
        $normalizedRoles = array_values(array_filter(array_map(
            fn (string $role): string => $this->normalizeRoleSlug($role),
            $roles,
        )));

        if ($normalizedRoles === []) {
            return false;
        }

        $candidateRoles = array_values(array_filter([
            $this->getResolvedRoleSlug(),
            $this->normalizeRoleSlug($this->role?->slug ?? null),
            $this->normalizeRoleSlug(session('active_role')),
        ]));

        $activeModule = strtoupper((string) session('active_module', ''));

        foreach ($this->moduleRoles as $moduleRole) {
            if ($activeModule !== '') {
                $moduleRole->loadMissing('module');
                if (strtoupper($moduleRole->module?->code ?? '') !== $activeModule) {
                    continue;
                }
            }
            $candidateRoles[] = $this->normalizeRoleSlug($moduleRole->role?->slug ?? null);
        }

        $candidateRoles = array_values(array_unique(array_filter($candidateRoles)));

        return (bool) array_intersect($normalizedRoles, $candidateRoles);
    }

    /**
     * FAST permission helper used by policies and sidebar visibility.
     */
    public function hasFastPermission(string ...$permissions): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $permissions = array_values(array_filter(array_map(
            fn (string $permission): string => strtolower(trim($permission)),
            $permissions,
        )));

        if ($permissions === []) {
            return false;
        }

        return FastPermissionCatalog::hasAny($this, $permissions);
    }

    /**
     * @return array<int, string>
     */
    public function fastPermissionSlugs(): array
    {
        return FastPermissionCatalog::permissionsForUser($this);
    }

    public function getGlobalRoleLabel(): ?string
    {
        $roleSlug = $this->getGlobalRoleSlug();

        if (! $roleSlug) {
            return null;
        }

        return str($roleSlug)->replace('-', ' ')->headline()->toString();
    }

    public function isMahasiswa(): bool
    {
        return $this->hasGlobalRole('mahasiswa');
    }

    public function isDosen(): bool
    {
        return $this->hasGlobalRole('dosen');
    }

    public function isAlumni(): bool
    {
        return $this->hasGlobalRole('alumni');
    }

    public function isMitra(): bool
    {
        return $this->hasGlobalRole('mitra');
    }

    /** @deprecated gunakan isMitra() */
    public function isPembimbingLapangan(): bool
    {
        return $this->isMitra();
    }

    public function isAdmin(): bool
    {
        return $this->hasGlobalRole('admin');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasGlobalRole(self::USER_TYPE_SUPER_ADMIN);
    }

    public function hasEnabledTwoFactorAuthentication(): bool
    {
        if (app()->environment('testing') && ! is_null($this->two_factor_confirmed_at)) {
            return true;
        }

        return AuthMfa::where('user_id', $this->id)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get the resolved reader-friendly role title for settings display.
     * Prioritas: role_title custom user -> fallback ke tipe sistem.
     */
    public function getResolvedRoleTitle(): string
    {
        if (! empty($this->role_title)) {
            return $this->role_title;
        }

        if ($this->isSuperAdmin()) {
            return 'Super Admin';
        } elseif ($this->isAdmin()) {
            return 'Admin';
        } elseif ($this->isDosen()) {
            return 'Dosen';
        } elseif ($this->isMahasiswa()) {
            return 'Mahasiswa';
        } elseif ($this->isAlumni()) {
            return 'Alumni';
        } elseif ($this->isMitra()) {
            return 'Mitra';
        }

        return $this->getGlobalRoleLabel() ?? 'User';
    }

    public function getResolvedRoleSlug(): ?string
    {
        $activeRole = $this->normalizeRoleSlug(session('active_role'));

        if ($activeRole) {
            return $activeRole;
        }

        return $this->getGlobalRoleSlug();
    }

    public function getResolvedRoleLabel(): string
    {
        $activeRole = $this->getResolvedRoleSlug();

        if ($activeRole) {
            return str($activeRole)->replace('-', ' ')->headline()->toString();
        }

        return $this->getResolvedRoleTitle();
    }

    protected function normalizeRoleSlug(mixed $role): ?string
    {
        if (! is_string($role)) {
            return null;
        }

        $role = trim(strtolower($role));

        if ($role === '') {
            return null;
        }

        return str_replace('_', '-', $role);
    }
}
