<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function view(User $user, Tenant $tenant): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isOwner() && $user->tenant_id === $tenant->id;
    }

    public function update(User $user, Tenant $tenant): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isOwner() && $user->tenant_id === $tenant->id;
    }
}
