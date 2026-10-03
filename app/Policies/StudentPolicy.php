<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isOwner() || $user->isAdmin() || $user->isTutor();
    }

    public function view(User $user, Student $student): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $student->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($student->branch_id);
        }

        if ($user->isTutor()) {
            // Tutor can only view students enrolled in classes taught by this tutor
            return $student->enrollments()
                ->where('status', 'active')
                ->whereIn('class_id', $user->tutorAssignments()->where('status', 'active')->pluck('class_id'))
                ->exists();
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

    public function update(User $user, Student $student): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $student->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($student->branch_id);
        }

        return false;
    }

    public function delete(User $user, Student $student): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $student->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($student->branch_id);
        }

        return false;
    }
}
