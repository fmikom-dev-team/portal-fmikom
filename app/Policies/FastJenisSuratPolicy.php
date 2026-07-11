<?php

namespace App\Policies;

use App\Models\JenisSurat;
use App\Models\User;
use App\Modules\Fast\Support\FastPermissionCatalog;

class FastJenisSuratPolicy
{
    public function viewAny(User $user): bool
    {
        return FastPermissionCatalog::hasAny($user, [
            'fast.admin.template.view',
            'fast.admin.template.manage',
        ]);
    }

    public function view(User $user, JenisSurat $jenisSurat): bool
    {
        return FastPermissionCatalog::hasAny($user, [
            'fast.admin.template.view',
            'fast.admin.template.manage',
            'fast.submission.create',
        ]);
    }

    public function create(User $user): bool
    {
        return $this->isAdminTemplateManager($user);
    }

    public function update(User $user, JenisSurat $jenisSurat): bool
    {
        return $this->isAdminTemplateManager($user);
    }

    public function delete(User $user, JenisSurat $jenisSurat): bool
    {
        return $this->isAdminTemplateManager($user);
    }

    public function duplicate(User $user, JenisSurat $jenisSurat): bool
    {
        return $this->isAdminTemplateManager($user);
    }

    public function toggleActive(User $user, JenisSurat $jenisSurat): bool
    {
        return $this->isAdminTemplateManager($user);
    }

    public function preview(User $user, JenisSurat $jenisSurat): bool
    {
        return FastPermissionCatalog::hasAny($user, [
            'fast.admin.template.view',
            'fast.admin.template.manage',
        ]);
    }

    protected function isAdminTemplateManager(User $user): bool
    {
        return FastPermissionCatalog::has($user, 'fast.admin.template.manage');
    }
}
