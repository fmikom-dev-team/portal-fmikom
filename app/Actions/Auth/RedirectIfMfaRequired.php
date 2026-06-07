<?php

namespace App\Actions\Auth;

use Illuminate\Http\Request;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;

class RedirectIfMfaRequired
{
    /**
     * Handle the incoming request.
     *
     * @param  callable  $next
     * @return mixed
     */
    public function handle(Request $request, $next)
    {
        $user = $request->user();

        if ($user) {
            // Check if user has active MFA
            $mfaActive = $user->hasEnabledTwoFactorAuthentication();

            if ($mfaActive) {
                // Logout the user temporarily
                auth()->logout();

                // Store their ID in the session for the challenge phase
                $request->session()->put([
                    'login.id' => $user->getKey(),
                    'login.remember' => $request->boolean('remember'),
                ]);

                event(new TwoFactorAuthenticationChallenged($user));

                return $request->wantsJson()
                            ? response()->json(['two_factor' => true])
                            : redirect()->route('two-factor.login');
            }
        }

        return $next($request);
    }
}
