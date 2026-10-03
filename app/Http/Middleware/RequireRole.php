<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(Response::HTTP_UNAUTHORIZED, 'Unauthenticated.');
        }

        if (empty($roles)) {
            return $next($request);
        }

        // Support comma-separated strings inside roles array (e.g. "owner,admin")
        $allowedRoles = [];
        foreach ($roles as $roleGroup) {
            foreach (explode(',', $roleGroup) as $role) {
                $allowedRoles[] = trim($role);
            }
        }

        if (! in_array($user->role, $allowedRoles, true)) {
            abort(Response::HTTP_FORBIDDEN, 'Unauthorized role.');
        }

        return $next($request);
    }
}
