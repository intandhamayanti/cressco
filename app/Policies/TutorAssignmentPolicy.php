<?php

namespace App\Policies;

use App\Models\TutorAssignment;
use App\Models\User;

class TutorAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isOwner() || $user->isAdmin() || $user->isTutor();
    }

    public function view(User $user, TutorAssignment $assignment): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $assignment->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($assignment->branch_id);
        }

        if ($user->isTutor()) {
            return $user->id === $assignment->tutor_id;
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

    public function update(User $user, TutorAssignment $assignment): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $assignment->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($assignment->branch_id);
        }

        return false;
    }

    public function delete(User $user, TutorAssignment $assignment): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $assignment->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($assignment->branch_id);
        }

        return false;
    }
}
