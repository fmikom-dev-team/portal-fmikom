<?php

namespace App\Modules\WorkOs\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Auth\AuthSession;
use App\Modules\WorkOs\Services\AuthPlatform\SessionEngine;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function __construct(
        protected SessionEngine $sessionEngine,
    ) {}

    /**
     * List all active sessions for the authenticated user.
     */
    public function index(Request $request)
    {
        $currentToken = $request->session()->get('auth_session_token');

        $sessions = AuthSession::with('device')
            ->where('user_id', $request->user()->id)
            ->where('is_revoked', false)
            ->orderByDesc('last_activity_at')
            ->get()
            ->map(fn($s) => array_merge($s->toArray(), [
                'is_current' => $s->session_token === $currentToken,
            ]));

        return response()->json(['sessions' => $sessions]);
    }

    /**
     * Revoke a specific session.
     */
    public function revoke(Request $request, AuthSession $session)
    {
        if ($session->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $this->sessionEngine->revokeSession($session);

        return response()->json(['message' => 'Session revoked.']);
    }

    /**
     * Revoke all sessions EXCEPT the current one.
     */
    public function revokeOthers(Request $request)
    {
        $currentToken = $request->session()->get('auth_session_token');

        if (!$currentToken) {
            return response()->json(['error' => 'Current session not identifiable.'], 400);
        }

        $this->sessionEngine->revokeOtherSessions($request->user(), $currentToken);

        return response()->json(['message' => 'All other sessions revoked.']);
    }

    /**
     * Revoke ALL sessions — full logout from everywhere.
     */
    public function revokeAll(Request $request)
    {
        AuthSession::where('user_id', $request->user()->id)
            ->update(['is_revoked' => true]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'All sessions revoked. Logged out.']);
    }
}
