<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // Prioritas: resolved_role (dari CheckActiveContext) → session active_role → DB role → user_type
        $userRoleSlug = $request->attributes->get('resolved_role')
            ?? session('active_role')
            ?? ($user->role ? $user->role->slug : null)
            ?? $user->user_type;

        if ($userRoleSlug) {
            $userRoleSlug = str_replace('_', '-', $userRoleSlug);
        }

        // Jika user tidak punya role_slug sama sekali, lempar kembali ke dashboard dengan hening
        if (! $userRoleSlug) {
            return redirect()->route('dashboard');
        }

        // 1. Keamanan Tingkat Tinggi (God Mode Bypass)
        // Super Admin selalu diizinkan mengakses apapun tanpa terkecuali.
        if ($userRoleSlug === 'super-admin') {
            return $next($request);
        }

        // 2. Proteksi Akses Silang (Cross-Role Protection)
        // Periksa apakah user memiliki salah satu dari role yang diwajibkan oleh Route.
        if (! empty($roles)) {
            if (! in_array($userRoleSlug, $roles)) {
                // Kejahatan Ditebas: Mengembalikan user ke dashboard jika tidak memiliki akses
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
