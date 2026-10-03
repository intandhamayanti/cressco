<?php

namespace App\Policies;

use App\Models\HonorScheme;
use App\Models\User;

class HonorSchemePolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Only Owner has policy access to configure honor schemes
        return $user->isOwner();
    }

    public function view(User $user, HonorScheme $scheme): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $scheme->tenant_id) {
            return false;
        }

        return $user->isOwner();
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isOwner();
    }

    public function update(User $user, HonorScheme $scheme): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $scheme->tenant_id) {
            return false;
        }

        return $user->isOwner();
    }

    public function delete(User $user, HonorScheme $scheme): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $scheme->tenant_id) {
            return false;
        }

        return $user->isOwner();
    }
}
