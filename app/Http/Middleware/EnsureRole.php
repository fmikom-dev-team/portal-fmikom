<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(Response::HTTP_FORBIDDEN, 'Anda harus login untuk mengakses halaman ini.');
        }

        if ($roles === []) {
            return $next($request);
        }

        if (! $user->hasAnyRole($roles)) {
            abort(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses ke halaman ini.');
        }

        return $next($request);
    }
}
