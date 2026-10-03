<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TenantContext
{
    /**
     * Get current authenticated user.
     */
    public static function getUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    /**
     * Get current user's tenant.
     */
    public static function getTenant(): ?Tenant
    {
        $user = static::getUser();

        if (! $user || ! $user->isTenantUser()) {
            return null;
        }

        return $user->tenant;
    }

    /**
     * Get current user's tenant ID.
     */
    public static function getTenantId(): ?string
    {
        $user = static::getUser();

        return $user?->tenant_id;
    }

    /**
     * Check if current user is a valid tenant user.
     */
    public static function isTenantUser(): bool
    {
        return static::getUser()?->isTenantUser() ?? false;
    }

    /**
     * Check if user belongs to the given tenant.
     */
    public static function belongsToTenant(string|Tenant $tenant): bool
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;
        $currentTenantId = static::getTenantId();

        return $currentTenantId !== null && $currentTenantId === $tenantId;
    }
}
