<?php

namespace App\Policies;

use App\Models\TeachingSession;
use App\Models\User;

class TeachingSessionPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isOwner() || $user->isAdmin() || $user->isTutor();
    }

    public function view(User $user, TeachingSession $session): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->canAccessTeachingSession($session);
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

    public function update(User $user, TeachingSession $session): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $session->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($session->branch_id);
        }

        if ($user->isTutor()) {
            // Tutor can update material/notes for their own session
            return $session->scheduled_tutor_id === $user->id || $session->actual_tutor_id === $user->id;
        }

        return false;
    }

    public function delete(User $user, TeachingSession $session): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $session->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($session->branch_id);
        }

        return false;
    }

    public function recordAttendance(User $user, TeachingSession $session): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $session->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($session->branch_id);
        }

        if ($user->isTutor()) {
            return $session->scheduled_tutor_id === $user->id
                || $session->actual_tutor_id === $user->id;
        }

        return false;
    }

    public function replaceTutor(User $user, TeachingSession $session): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $session->tenant_id) {
            return false;
        }

        // Only Owner and branch Admin can change actual tutor / replace tutor
        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($session->branch_id);
        }

        return false;
    }
}
