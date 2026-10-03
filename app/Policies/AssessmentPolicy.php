<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\User;

class AssessmentPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isOwner() || $user->isAdmin() || $user->isTutor();
    }

    public function view(User $user, Assessment $assessment): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $assessment->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($assessment->branch_id);
        }

        if ($user->isTutor()) {
            return $user->canTeachClass($assessment->class_id);
        }

        return false;
    }

    public function create(User $user, ?string $branchId = null, ?string $classId = null): bool
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

        if ($user->isTutor()) {
            return $classId !== null && $user->canTeachClass($classId);
        }

        return false;
    }

    public function update(User $user, Assessment $assessment): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $assessment->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($assessment->branch_id);
        }

        if ($user->isTutor()) {
            return $user->canTeachClass($assessment->class_id);
        }

        return false;
    }

    public function delete(User $user, Assessment $assessment): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $assessment->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($assessment->branch_id);
        }

        return false;
    }
}
