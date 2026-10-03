<?php

namespace App\Policies;

use App\Models\AssessmentResult;
use App\Models\User;

class AssessmentResultPolicy
{
    public function view(User $user, AssessmentResult $result): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $result->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($result->branch_id);
        }

        if ($user->isTutor()) {
            return $user->canTeachClass($result->assessment->class_id);
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

    public function update(User $user, AssessmentResult $result): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $result->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($result->branch_id);
        }

        if ($user->isTutor()) {
            return $user->canTeachClass($result->assessment->class_id);
        }

        return false;
    }
}
