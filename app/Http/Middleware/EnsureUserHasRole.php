<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Pakai: ->middleware('role:admin') atau ->middleware('role:admin,dosen').
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless(
            $user !== null && $user->is_active && in_array($user->role, $roles, true),
            403,
            'Anda tidak memiliki akses ke halaman ini.',
        );

        return $next($request);
    }
}
