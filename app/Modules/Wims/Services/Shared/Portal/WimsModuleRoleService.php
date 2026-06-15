<?php

namespace App\Modules\Wims\Services\Shared\Portal;

use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModuleRole;
use Illuminate\Database\Eloquent\Builder;

class WimsModuleRoleService
{
    public const MODULE_CODE = 'WIMS';

    public function resolveModule(): ?Module
    {
        return Module::query()
            ->where('code', self::MODULE_CODE)
            ->first();
    }

    public function resolveRole(string $roleSlug): ?Role
    {
        return Role::query()
            ->where('slug', $roleSlug)
            ->first();
    }

    public function usersForRole(string $roleSlug): Builder
    {
        return User::query()
            ->whereHas('moduleRoles', function (Builder $query) use ($roleSlug): void {
                $query->where('is_active', true)
                    ->whereHas('module', fn (Builder $moduleQuery) => $moduleQuery
                        ->where('code', self::MODULE_CODE)
                        ->where('is_active', true))
                    ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('slug', $roleSlug));
            })
            ->distinct('users.id');
    }

    public function hasActiveRole(int $userId, string $roleSlug): bool
    {
        return UserModuleRole::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->whereHas('module', fn (Builder $moduleQuery) => $moduleQuery
                ->where('code', self::MODULE_CODE)
                ->where('is_active', true))
            ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('slug', $roleSlug))
            ->exists();
    }

    public function ensureAssignment(User $user, string $roleSlug, bool $isActive = true): bool
    {
        $module = $this->resolveModule();
        $role = $this->resolveRole($roleSlug);

        if (! $module || ! $role) {
            return false;
        }

        $module->roles()->syncWithoutDetaching([$role->id => ['is_default' => false]]);

        UserModuleRole::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'module_id' => $module->id,
                'role_id' => $role->id,
            ],
            [
                'is_active' => $isActive,
            ],
        );

        return true;
    }
}
