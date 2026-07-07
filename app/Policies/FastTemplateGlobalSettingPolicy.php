<?php

namespace App\Policies;

use App\Models\TemplateGlobalSetting;
use App\Models\User;
use App\Modules\Fast\Support\FastPermissionCatalog;

class FastTemplateGlobalSettingPolicy
{
    public function update(User $user, ?TemplateGlobalSetting $setting = null): bool
    {
        return FastPermissionCatalog::has($user, 'fast.admin.settings.manage');
    }

    public function previewLogo(User $user, ?TemplateGlobalSetting $setting = null): bool
    {
        return FastPermissionCatalog::has($user, 'fast.admin.settings.manage');
    }
}
