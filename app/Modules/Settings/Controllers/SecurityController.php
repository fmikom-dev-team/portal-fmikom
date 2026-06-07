<?php

namespace App\Modules\Settings\Controllers;

use App\Http\Controllers\Controller;

use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;
use App\Models\AuthSetting;
use App\Models\AuthPasskey;

class SecurityController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return Features::canManageTwoFactorAuthentication()
            && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
                ? [new Middleware('password.confirm', only: ['edit'])]
                : [];
    }

    /**
     * Show the user's security settings page.
     */
    public function edit(TwoFactorAuthenticationRequest $request): Response
    {
        $props = [
            'canManageTwoFactor' => Features::canManageTwoFactorAuthentication(),
            'passkeysEnabled' => (bool) AuthSetting::get('passkeys.enabled', true),
        ];

        if ($props['passkeysEnabled']) {
            $props['passkeys'] = AuthPasskey::where('user_id', $request->user()->id)
                ->select(['id', 'name', 'last_used_at', 'created_at'])
                ->get();
        }

        if (Features::canManageTwoFactorAuthentication()) {
            $request->ensureStateIsValid();

            $props['twoFactorEnabled'] = $request->user()->hasEnabledTwoFactorAuthentication();
            $props['requiresConfirmation'] = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        }

        return Inertia::render('settings/Security', $props);
    }

    /**
     * Update the user's password.
     *
     * Setelah ganti password:
     * 1. Update password baru
     * 2. Cycle remember_token → invalidasi SEMUA remember cookie di semua device
     * 3. Hapus remember cookie di browser saat ini
     *
     * Ini mencegah cookie "Remember Me" yang sudah dicuri tetap valid
     * setelah user mengganti password.
     */
    public function update(PasswordUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'password'       => $request->password,
            'remember_token' => Str::random(60), // cycle token → invalidasi semua device
        ]);

        // Hapus remember cookie di browser saat ini
        Cookie::queue(
            Cookie::forget(
                Auth::getRecallerName()
            )
        );

        return back()->with('status', 'password-updated');
    }

    /**
     * Update the user's email address with high security verification.
     */
    public function updateEmail(\Illuminate\Http\Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'email'            => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'required|string',
        ]);

        if (! \Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'The provided password does not match your current password.',
            ]);
        }

        $user->email = $request->email;
        $user->email_verified_at = null;
        $user->save();

        return back()->with('status', 'email-updated');
    }
}
