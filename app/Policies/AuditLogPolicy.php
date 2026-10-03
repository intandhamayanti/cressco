<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isOwner();
    }

    public function view(User $user, AuditLog $auditLog): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $auditLog->tenant_id) {
            return false;
        }

        return $user->isOwner();
    }
}
