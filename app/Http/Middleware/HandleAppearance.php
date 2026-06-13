<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class HandleAppearance
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $cookieKey = $user ? "appearance_student_{$user->id}" : 'appearance';

        View::share('appearance', $request->cookie($cookieKey) ?? 'system');
        View::share('appearanceCookieKey', $cookieKey);

        return $next($request);
    }
}
