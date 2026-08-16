<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Fortify;

/**
 * CustomLogoutResponse
 *
 * Memastikan SEMUA cookie autentikasi terhapus dari browser saat logout,
 * termasuk remember_me cookie — tanpa peduli apakah user login
 * dengan centang "Remember Me" atau tidak.
 */
class CustomLogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        // Force-delete remember cookie tanpa syarat
        $recallerName = Auth::getRecallerName();

        Cookie::queue(Cookie::forget($recallerName));

        // Hapus juga XSRF-TOKEN agar browser benar-benar clean
        Cookie::queue(Cookie::forget('XSRF-TOKEN'));

        // Jika request berasal dari Inertia.js, gunakan Inertia::location untuk window location redirect bersih ke Welcome page
        if ($request->header('X-Inertia')) {
            $targetUrl = Fortify::redirects('logout', url('/'));

            return Inertia::location($targetUrl);
        }

        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : redirect(Fortify::redirects('logout', '/'));
    }
}
