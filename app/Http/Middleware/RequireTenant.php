<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(Response::HTTP_UNAUTHORIZED, 'Unauthenticated.');
        }

        if (! $user->isTenantUser()) {
            abort(Response::HTTP_FORBIDDEN, 'User is not assigned to a tenant.');
        }

        if ($user->status !== 'active') {
            abort(Response::HTTP_FORBIDDEN, 'User account is inactive.');
        }

        $tenant = $user->tenant;

        if (! $tenant || $tenant->status !== 'active') {
            abort(Response::HTTP_FORBIDDEN, 'Tenant is inactive or suspended.');
        }

        return $next($request);
    }
}
