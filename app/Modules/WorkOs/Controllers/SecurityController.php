<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\WorkOs\Services\AuthPlatform\MFAEngine;
use App\Modules\WorkOs\Services\AuthPlatform\PasskeyEngine;
use Illuminate\Support\Facades\Auth;

class SecurityController extends Controller
{
    protected MFAEngine $mfaEngine;
    protected PasskeyEngine $passkeyEngine;

    public function __construct(MFAEngine $mfaEngine, PasskeyEngine $passkeyEngine)
    {
        $this->mfaEngine = $mfaEngine;
        $this->passkeyEngine = $passkeyEngine;
    }

    // --- MFA (TOTP) Endpoints ---
    
    public function userMfaStatus(Request $request)
    {
        $user = Auth::user() ?? \App\Models\User::first();
        $mfa = \App\Models\Auth\AuthMfa::where('user_id', $user->id)->where('is_active', true)->first();
        
        return response()->json([
            'is_active' => $mfa !== null,
        ]);
    }

    public function setupMfa(Request $request)
    {
        // Mocking user since there's no real session for the admin testing this right now
        $user = Auth::user() ?? \App\Models\User::first();

        try {
            $data = $this->mfaEngine->setupTotp($user);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function verifyMfa(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $user = Auth::user() ?? \App\Models\User::first();

        try {
            $data = $this->mfaEngine->verifyAndActivate($user, $request->code);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // --- PASSKEYS (WebAuthn) Endpoints ---

    public function registerPasskeyOptions()
    {
        $user = Auth::user() ?? \App\Models\User::first();

        try {
            $options = $this->passkeyEngine->getRegistrationOptions($user);
            return response()->json($options);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function verifyPasskeyRegistration(Request $request)
    {
        $user = Auth::user() ?? \App\Models\User::first();

        try {
            $passkey = $this->passkeyEngine->verifyRegistration($user, $request->all());
            return response()->json(['message' => 'Passkey registered successfully', 'passkey' => $passkey]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
