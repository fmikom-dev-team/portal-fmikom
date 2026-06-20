<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffAccess
{
    /**
     * Allows admin, kaprodi, dekan, and dosen to access
     * shared staff pages (dashboard overview, archive).
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_if($user === null, 403);
        abort_unless($user->hasStaffAccess(), 403);

        return $next($request);
    }
}
