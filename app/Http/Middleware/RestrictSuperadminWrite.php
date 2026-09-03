<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictSuperadminWrite
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->isSuperAdmin()) {
            abort(403, 'Superadmin has view-only rights for this section. Only Firm Management allows create, update, or delete operations.');
        }

        return $next($request);
    }
}
