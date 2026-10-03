<?php

namespace App\Policies;

use App\Models\TutorAttendance;
use App\Models\User;

class TutorAttendancePolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isOwner() || $user->isAdmin() || $user->isTutor();
    }

    public function view(User $user, TutorAttendance $attendance): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $attendance->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($attendance->branch_id);
        }

        if ($user->isTutor()) {
            return $user->id === $attendance->tutor_id;
        }

        return false;
    }

    public function update(User $user, TutorAttendance $attendance): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $attendance->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($attendance->branch_id);
        }

        return false;
    }
}
