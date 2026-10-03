<?php

namespace App\Policies;

use App\Models\TenantSetting;
use App\Models\User;

class TenantSettingPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isOwner();
    }

    public function view(User $user, TenantSetting $setting): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $setting->tenant_id) {
            return false;
        }

        return $user->isOwner();
    }

    public function update(User $user, TenantSetting $setting): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $setting->tenant_id) {
            return false;
        }

        return $user->isOwner();
    }
}
