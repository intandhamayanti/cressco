<?php

namespace App\Policies;

use App\Models\HonorAssignment;
use App\Models\User;

class HonorAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isOwner() || $user->isAdmin();
    }

    public function view(User $user, HonorAssignment $assignment): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $assignment->tenant_id) {
            return false;
        }

        if ($user->isOwner() || $user->isAdmin()) {
            return true;
        }

        if ($user->isTutor()) {
            return $user->id === $assignment->tutor_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isOwner();
    }

    public function update(User $user, HonorAssignment $assignment): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $assignment->tenant_id) {
            return false;
        }

        return $user->isOwner();
    }

    public function delete(User $user, HonorAssignment $assignment): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $assignment->tenant_id) {
            return false;
        }

        return $user->isOwner();
    }
}
