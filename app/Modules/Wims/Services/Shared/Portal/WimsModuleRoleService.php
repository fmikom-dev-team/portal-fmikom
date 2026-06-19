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

    private const ROLE_LABELS = [
        'super-admin' => 'Super Admin',
        'admin' => 'Admin',
        'admin-universitas' => 'Admin Universitas',
        'admin-akademik' => 'Admin Akademik',
        'prodi' => 'Prodi',
        'mahasiswa' => 'Mahasiswa',
        'dosen' => 'Dosen',
        'mitra' => 'Mitra',
    ];

    /**
     * @var array<int, array<int, string>>
     */
    private array $activeRoleSlugsByUserId = [];

    /**
     * @var array<int, string|null>
     */
    private array $primaryRoleSlugByUserId = [];

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
        $this->preloadContextRoles([$userId]);

        return in_array($roleSlug, $this->activeRoleSlugsByUserId[$userId] ?? [], true);
    }

    public function labelForRole(string $roleSlug): string
    {
        return self::ROLE_LABELS[$roleSlug] ?? str($roleSlug)->replace('-', ' ')->headline()->toString();
    }

    public function resolveContextRoleLabel(User $user, ?string $preferredRole = null): ?string
    {
        $roleData = $this->resolveContextRoleData($user, $preferredRole);

        return $roleData['label'] ?? null;
    }

    public function resolveContextRoleData(User $user, ?string $preferredRole = null): ?array
    {
        if (! $user->id) {
            return null;
        }

        $this->preloadContextRoles([$user->id]);

        if ($preferredRole && $this->hasActiveRole($user->id, $preferredRole)) {
            return [
                'slug' => $preferredRole,
                'label' => $this->labelForRole($preferredRole),
            ];
        }

        $roleSlug = $this->primaryRoleSlugByUserId[$user->id] ?? null;

        if (! $roleSlug) {
            return null;
        }

        return [
            'slug' => $roleSlug,
            'label' => $this->labelForRole($roleSlug),
        ];
    }

    /**
     * @param  iterable<int, int|User|null>  $users
     */
    public function preloadContextRoles(iterable $users): void
    {
        $userIds = collect($users)
            ->map(fn (int|User|null $user) => $user instanceof User ? $user->id : $user)
            ->filter(fn ($id) => is_int($id) && $id > 0)
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return;
        }

        $missingIds = $userIds
            ->reject(fn (int $id) => array_key_exists($id, $this->activeRoleSlugsByUserId))
            ->values();

        if ($missingIds->isEmpty()) {
            return;
        }

        $assignments = UserModuleRole::query()
            ->whereIn('user_id', $missingIds->all())
            ->where('is_active', true)
            ->whereHas('module', fn (Builder $moduleQuery) => $moduleQuery
                ->where('code', self::MODULE_CODE)
                ->where('is_active', true))
            ->with('role:id,slug')
            ->orderByDesc('id')
            ->get();

        foreach ($missingIds as $userId) {
            $this->activeRoleSlugsByUserId[$userId] = [];
            $this->primaryRoleSlugByUserId[$userId] = null;
        }

        foreach ($assignments as $assignment) {
            $userId = (int) $assignment->user_id;
            $roleSlug = $assignment->role?->slug;

            if (! $roleSlug) {
                continue;
            }

            $this->activeRoleSlugsByUserId[$userId][] = $roleSlug;

            if ($this->primaryRoleSlugByUserId[$userId] === null) {
                $this->primaryRoleSlugByUserId[$userId] = $roleSlug;
            }
        }
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
