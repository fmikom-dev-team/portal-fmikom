<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Fortify;

/**
 * CustomLogoutResponse
 *
 * Memastikan SEMUA cookie autentikasi terhapus dari browser saat logout,
 * termasuk remember_me cookie — tanpa peduli apakah user login
 * dengan centang "Remember Me" atau tidak.
 *
 * Masalah default Laravel:
 * - guard->logout() hanya menghapus recaller cookie JIKA ada di request
 * - Jika user login tanpa "Remember Me", cookie lama dari sesi sebelumnya
 *   tidak ikut dihapus → masih tampak di browser inspector
 */
class CustomLogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        // Force-delete remember cookie tanpa syarat
        // Auth::getRecallerName() mengembalikan nama cookie yang digunakan guard aktif
        $recallerName = Auth::getRecallerName();

        Cookie::queue(Cookie::forget($recallerName));

        // Hapus juga XSRF-TOKEN agar browser benar-benar clean
        Cookie::queue(Cookie::forget('XSRF-TOKEN'));

        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : redirect(Fortify::redirects('logout', '/'));
    }
}
