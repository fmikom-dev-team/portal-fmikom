<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWimsAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(Response::HTTP_FORBIDDEN, 'Anda harus login untuk mengakses WIMS.');
        }

        if (isset($user->is_active) && ! $user->is_active) {
            abort(Response::HTTP_FORBIDDEN, 'Akun Anda sedang tidak aktif.');
        }

        if (
            isset($user->status_approval)
            && $user->status_approval !== 'approved'
        ) {
            abort(
                Response::HTTP_FORBIDDEN,
                'Akun Anda belum disetujui untuk mengakses WIMS.',
            );
        }

        return $next($request);
    }
}
