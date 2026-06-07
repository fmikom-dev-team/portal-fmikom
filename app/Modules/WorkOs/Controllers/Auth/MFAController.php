<?php

namespace App\Modules\WorkOs\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Modules\WorkOs\Services\AuthPlatform\MFAEngine;

class MFAController extends Controller
{
    public function __construct(
        protected MFAEngine $mfaEngine,
    ) {}

    public function status(Request $request)
    {
        $mfa = \App\Models\Auth\AuthMfa::where('user_id', $request->user()->id)->first();
        return response()->json([
            'enabled' => $mfa?->is_active ?? false,
            'type' => $mfa?->type ?? null,
            'verified_at' => $mfa?->verified_at,
        ]);
    }

    public function setup(Request $request)
    {
        try {
            return response()->json($this->mfaEngine->setupTotp($request->user()));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string|min:6|max:6']);
        try {
            return response()->json($this->mfaEngine->verifyAndActivate($request->user(), $request->code));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function challenge(Request $request)
    {
        $request->validate(['code' => 'required|string', 'user_id' => 'required']);
        $user = \App\Models\User::find($request->user_id);
        if (!$user) return response()->json(['error' => 'User not found.'], 404);
        try {
            return response()->json(['valid' => $this->mfaEngine->verifyLogin($user, $request->code)]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function verifyBackupCode(Request $request)
    {
        return $this->challenge($request);
    }

    public function disable(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        try {
            if (!$this->mfaEngine->verifyLogin($request->user(), $request->code)) {
                return response()->json(['error' => 'Invalid confirmation code.'], 400);
            }
            $this->mfaEngine->disable($request->user());
            return response()->json(['message' => 'MFA disabled successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function regenerateBackupCodes(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        try {
            if (!$this->mfaEngine->verifyLogin($request->user(), $request->code)) {
                return response()->json(['error' => 'Invalid TOTP code.'], 400);
            }
            return response()->json(['backup_codes' => $this->mfaEngine->generateBackupCodes($request->user())]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
