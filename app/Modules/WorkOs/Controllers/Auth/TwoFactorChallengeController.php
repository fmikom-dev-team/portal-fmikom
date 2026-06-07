<?php

namespace App\Modules\WorkOs\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Modules\WorkOs\Services\AuthPlatform\MFAEngine;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Laravel\Fortify\Http\Responses\TwoFactorLoginResponse;
use Illuminate\Validation\ValidationException;

class TwoFactorChallengeController extends Controller
{
    protected MFAEngine $mfaEngine;

    public function __construct(MFAEngine $mfaEngine)
    {
        $this->mfaEngine = $mfaEngine;
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
                // Remove the session variable
                $request->session()->forget('login.id');

                // Log the user in
                Auth::login($user, $request->session()->get('login.remember', false));

                // Log the success audit event
                \App\Models\Auth\AuthAuditLog::log('auth.login.success', $user->id, ['mfa_used' => true]);

                $request->session()->regenerate();

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
