<?php

namespace App\Policies;

use App\Models\SuratCategory;
use App\Models\User;
use App\Modules\Fast\Support\FastPermissionCatalog;

class FastSuratCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return FastPermissionCatalog::hasAny($user, [
            'fast.admin.category.view',
            'fast.admin.category.manage',
        ]);
    }

    public function create(User $user): bool
    {
        return FastPermissionCatalog::has($user, 'fast.admin.category.manage');
    }

    public function update(User $user, SuratCategory $category): bool
    {
        return FastPermissionCatalog::has($user, 'fast.admin.category.manage');
    }

    public function delete(User $user, SuratCategory $category): bool
    {
        return FastPermissionCatalog::has($user, 'fast.admin.category.manage');
    }
}
