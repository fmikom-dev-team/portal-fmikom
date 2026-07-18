<?php

namespace App\Modules\WorkOs\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth\AuthMfa;
use App\Models\User;
use App\Modules\WorkOs\Services\AuthPlatform\MFAEngine;
use Illuminate\Http\Request;

class MFAController extends Controller
{
    public function __construct(
        protected MFAEngine $mfaEngine,
    ) {}

    public function status(Request $request)
    {
        $mfa = AuthMfa::where('user_id', '=', $request->user()->id, 'and')->first();

        return response()->json([
            'enabled' => $mfa?->is_active ?? false,
            'type' => $mfa?->type ?? null,
            'verified_at' => $mfa?->verified_at,
        ]);
    }

    public function setup(Request $request)
    {
        if (! $request->user()->isAccountActive()) {
            return response()->json(['error' => 'Akun Anda tidak aktif.'], 403);
        }
        try {
            return response()->json($this->mfaEngine->setupTotp($request->user()));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string|min:6|max:6']);
        if (! $request->user()->isAccountActive()) {
            return response()->json(['error' => 'Akun Anda tidak aktif.'], 403);
        }
        try {
            return response()->json($this->mfaEngine->verifyAndActivate($request->user(), $request->code));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function challenge(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $userId = $request->session()->get('login.id');

        $status = 200;
        $data = [];

        if (! $userId) {
            $data = ['error' => 'Sesi login tidak ditemukan atau kedaluwarsa.'];
            $status = 403;
        } else {
            /** @var User|null $user */
            $user = User::find($userId, ['*']);
            if (! $user) {
                $data = ['error' => 'User tidak ditemukan.'];
                $status = 404;
            } else {
                try {
                    $data = ['valid' => $this->mfaEngine->verifyLogin($user, $request->code)];
                } catch (\Exception $e) {
                    $data = ['error' => $e->getMessage()];
                    $status = 400;
                }
            }
        }

        return response()->json($data, $status);
    }

    public function verifyBackupCode(Request $request)
    {
        return $this->challenge($request);
    }

    public function disable(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        try {
            if (! $this->mfaEngine->verifyLogin($request->user(), $request->code)) {
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
            if (! $this->mfaEngine->verifyLogin($request->user(), $request->code)) {
                return response()->json(['error' => 'Invalid TOTP code.'], 400);
            }

            return response()->json(['backup_codes' => $this->mfaEngine->generateBackupCodes($request->user())]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
