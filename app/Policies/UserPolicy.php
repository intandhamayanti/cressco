<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isOwner() || $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->id === $model->id) {
            return true;
        }

        if ($user->tenant_id !== $model->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            // Admin can view tutors in tenant or own profile
            return $model->isTutor() || $model->id === $user->id;
        }

        return false;
    }

    public function create(User $user, ?string $targetRole = null): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isOwner()) {
            // Owner can create admin or tutor
            return true;
        }

        if ($user->isAdmin()) {
            // Admin can only create tutors, never other admins or owners
            return $targetRole === 'tutor';
        }

        return false;
    }

    public function update(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Own profile update
        if ($user->id === $model->id) {
            return true;
        }

        if ($user->tenant_id !== $model->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            // Admin can only update tutors, cannot update other admins or owner
            return $model->isTutor();
        }

        return false;
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Cannot delete oneself
        if ($user->id === $model->id) {
            return false;
        }

        if ($user->tenant_id !== $model->tenant_id) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            // Admin can only deactivate/delete tutor
            return $model->isTutor();
        }

        return false;
    }

    public function updateRole(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin/Tutor cannot change roles; only Owner can change roles within tenant
        if (! $user->isOwner() || $user->tenant_id !== $model->tenant_id) {
            return false;
        }

        return true;
    }

    public function updateBranchAccess(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Only Owner can assign/change branch access for users in their tenant
        if (! $user->isOwner() || $user->tenant_id !== $model->tenant_id) {
            return false;
        }

        return true;
    }
}
