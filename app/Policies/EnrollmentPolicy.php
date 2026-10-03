<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;

class EnrollmentPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isOwner() || $user->isAdmin();
    }

    public function view(User $user, Enrollment $enrollment): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $enrollment->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($enrollment->branch_id);
        }

        return false;
    }

    public function create(User $user, ?string $branchId = null): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $branchId === null || $user->hasBranchAccess($branchId);
        }

        return false;
    }

    public function update(User $user, Enrollment $enrollment): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $enrollment->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($enrollment->branch_id);
        }

        return false;
    }

    public function delete(User $user, Enrollment $enrollment): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $enrollment->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($enrollment->branch_id);
        }

        return false;
    }
}
