<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class RoleBasedLoginResponse implements LoginResponseContract
{
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $target = match (true) {
            $user?->hasRole('super-admin') && Route::has('wims.admin.dashboard') => route('wims.admin.dashboard'),
            $user?->hasRole('dosen') && Route::has('wims.dosen.dashboard') => route('wims.dosen.dashboard'),
            $user?->hasRole('mitra') && Route::has('wims.mitra.dashboard') => route('wims.mitra.dashboard'),
            $user?->hasAnyRole(['user', 'super-admin']) && Route::has('wims.dashboard') => route('wims.dashboard'),
            default => config('fortify.home'),
        };

        return $request->wantsJson()
            ? new JsonResponse(['two_factor' => false, 'redirect' => $target])
            : redirect()->intended($target);
    }
}
