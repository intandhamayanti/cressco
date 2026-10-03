<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isOwner() || $user->isAdmin();
    }

    public function view(User $user, Branch $branch): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $branch->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($branch);
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Only Owner can create branches inside their tenant. Admin/Tutor cannot.
        return $user->isOwner();
    }

    public function update(User $user, Branch $branch): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $branch->tenant_id) {
            return false;
        }

        // Only Owner can manage branch details. Admin cannot.
        return $user->isOwner();
    }

    public function delete(User $user, Branch $branch): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $branch->tenant_id) {
            return false;
        }

        return $user->isOwner();
    }
}
