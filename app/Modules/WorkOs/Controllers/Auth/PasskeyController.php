<?php

namespace App\Modules\WorkOs\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth\AuthPasskey;
use App\Modules\WorkOs\Services\AuthPlatform\PasskeyEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PasskeyController extends Controller
{
    public function __construct(
        protected PasskeyEngine $passkeyEngine,
    ) {}

    public function index(Request $request)
    {
        $passkeys = AuthPasskey::where('user_id', '=', $request->user()->id, 'and')
            ->select(['id', 'name', 'last_used_at', 'created_at'])
            ->get();

        return response()->json(['passkeys' => $passkeys]);
    }

    public function registrationOptions(Request $request)
    {
        // Guard: Passkey registration is ONLY available to fully activated accounts
        if (! $request->user()->isAccountActive()) {
            return response()->json([
                'error' => 'Passkey hanya dapat didaftarkan setelah akun Anda aktif.',
            ], 403);
        }

        try {
            $options = $this->passkeyEngine->getRegistrationOptions($request->user());

            return response()->json($options);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function register(Request $request)
    {
        // Guard: same as registrationOptions
        if (! $request->user()->isAccountActive()) {
            return response()->json([
                'error' => 'Passkey hanya dapat didaftarkan setelah akun Anda aktif.',
            ], 403);
        }

        try {
            $passkey = $this->passkeyEngine->verifyRegistration($request->user(), $request->all());

            return response()->json(['message' => 'Passkey registered.', 'passkey' => [
                'id' => $passkey->id,
                'name' => $passkey->name,
                'created_at' => $passkey->created_at,
            ]]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function authenticationOptions()
    {
        try {
            $options = $this->passkeyEngine->getAuthenticationOptions();

            return response()->json($options);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function authenticate(Request $request)
    {
        try {
            $user = $this->passkeyEngine->verifyAuthentication($request->all());

            if (! $user->isAccountActive()) {
                $msg = $user->getLoginBlockMessage() ?? 'Akun Anda tidak dapat diakses saat ini.';

                return response()->json(['error' => $msg], 403);
            }

            Auth::login($user);
            $request->session()->regenerate(); // Prevent session fixation

            return response()->json(['message' => 'Authenticated via passkey.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function destroy(Request $request, AuthPasskey $passkey)
    {
        if ($passkey->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }
        DB::table('auth_passkeys')
            ->where('id', '=', $passkey->id, 'and')
            ->delete();

        return response()->json(['message' => 'Passkey removed.']);
    }

    public function update(Request $request, AuthPasskey $passkey)
    {
        if ($passkey->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $passkey->fill([
            'name' => $request->name,
        ])->save();

        return response()->json([
            'message' => 'Passkey renamed successfully.',
            'passkey' => [
                'id' => $passkey->id,
                'name' => $passkey->name,
            ],
        ]);
    }
}
