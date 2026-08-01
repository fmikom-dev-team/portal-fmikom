<?php

namespace App\Modules\WorkOs\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth\AuthAuditLog;
use App\Models\User;
use App\Modules\WorkOs\Services\AuthPlatform\MFAEngine;
use App\Modules\WorkOs\Services\AuthPlatform\SessionEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Http\Responses\TwoFactorLoginResponse;

class TwoFactorChallengeController extends Controller
{
    protected MFAEngine $mfaEngine;

    protected SessionEngine $sessionEngine;

    public function __construct(MFAEngine $mfaEngine, SessionEngine $sessionEngine)
    {
        $this->mfaEngine = $mfaEngine;
        $this->sessionEngine = $sessionEngine;
    }

    /**
     * Attempt to authenticate a new session using the two-factor authentication code.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'nullable|string',
            'recovery_code' => 'nullable|string',
        ]);

        $userId = $request->session()->get('login.id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        if (! $user->isAccountActive()) {
            $request->session()->forget('login.id');
            $msg = $user->getLoginBlockMessage() ?? 'Akun Anda tidak dapat diakses saat ini.';
            throw ValidationException::withMessages([
                'code' => $msg,
            ]);
        }

        // Try to verify using the submitted code or recovery code
        $code = $request->code ?? $request->recovery_code;

        if (! $code) {
            throw ValidationException::withMessages([
                'code' => __('The provided two factor authentication code was invalid.'),
            ]);
        }

        try {
            $valid = $this->mfaEngine->verifyLogin($user, $code);

            if ($valid) {
                // Remove the pending login session variables
                $remember = $request->session()->get('login.remember', false);
                $request->session()->forget(['login.id', 'login.remember']);

                // Prevent session fixation BEFORE Auth::login() fires the Login event
                $request->session()->regenerate();

                // Authenticate the user (fires Login event synchronously)
                Auth::login($user, $remember);

                // Create enterprise session record
                $session = $this->sessionEngine->createSession($user, $request);
                $request->session()->put('auth_session_token', $session->id);

                // Log the success audit event
                AuthAuditLog::log('auth.login.success', $user->id, ['mfa_used' => true]);

                return app(TwoFactorLoginResponse::class);
            }
        } catch (\Exception $e) {
            // Invalid code or error
        }

        throw ValidationException::withMessages([
            'code' => __('The provided two factor authentication code was invalid.'),
        ]);
    }
}
