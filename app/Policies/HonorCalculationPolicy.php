<?php

namespace App\Policies;

use App\Models\HonorCalculation;
use App\Models\User;

class HonorCalculationPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isOwner() || $user->isAdmin() || $user->isTutor();
    }

    public function view(User $user, HonorCalculation $calculation): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $calculation->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $calculation->branch_id === null || $user->hasBranchAccess($calculation->branch_id);
        }

        if ($user->isTutor()) {
            // Tutor can only view their own honor calculation
            return $calculation->tutor_id === $user->id;
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

    public function update(User $user, HonorCalculation $calculation): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $calculation->tenant_id) {
            return false;
        }

        if ($calculation->status === 'paid') {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $calculation->branch_id === null || $user->hasBranchAccess($calculation->branch_id);
        }

        return false;
    }

    public function finalize(User $user, HonorCalculation $calculation): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $calculation->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $calculation->branch_id === null || $user->hasBranchAccess($calculation->branch_id);
        }

        return false;
    }

    public function delete(User $user, HonorCalculation $calculation): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $calculation->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $calculation->status === 'draft' && ($calculation->branch_id === null || $user->hasBranchAccess($calculation->branch_id));
        }

        return false;
    }
}
