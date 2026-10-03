<?php

namespace App\Policies;

use App\Models\TutorReplacement;
use App\Models\User;

class TutorReplacementPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isOwner() || $user->isAdmin() || $user->isTutor();
    }

    public function view(User $user, TutorReplacement $replacement): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $replacement->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($replacement->branch_id);
        }

        if ($user->isTutor()) {
            return $user->id === $replacement->scheduled_tutor_id
                || $user->id === $replacement->replacement_tutor_id
                || $user->id === $replacement->previous_actual_tutor_id;
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

        // Tutor cannot create tutor replacement
        return false;
    }
}
