<?php

namespace App\Modules\WorkOs\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth\AuthAuditLog;
use App\Models\Auth\AuthSession;
use App\Modules\WorkOs\Services\AuthPlatform\SessionEngine;
use Illuminate\Http\Request;
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
            ->map(fn ($s) => array_merge($s->toArray(), [
                'is_current' => $s->id === $currentToken || $s->session_token === $currentToken,
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

        if (! $currentToken) {
            return response()->json(['error' => 'Current session not identifiable.'], 400);
        }

        $activeSession = AuthSession::where(function ($query) use ($currentToken) {
            $query->where('id', $currentToken)
                ->orWhere('session_token', $currentToken);
        })
            ->where('user_id', $request->user()->id)
            ->first();

        if ($activeSession) {
            $this->sessionEngine->revokeOtherSessions($request->user(), $activeSession->id);
        }

        return response()->json(['message' => 'All other sessions revoked.']);
    }

    /**
     * Revoke ALL sessions — full logout from everywhere.
     *
     * [FIX H-7] Sebelumnya: urutan operasi kurang lengkap. Session invalidate
     * dilakukan setelah Auth::logout(), padahal seharusnya:
     * 1. Catat user ID sebelum logout
     * 2. Hapus auth_session_token dari session
     * 3. Revoke semua session di DB
     * 4. Auth::logout() — hapus state auth dari session saat ini
     * 5. invalidate() + regenerateToken() — hancurkan session & buat CSRF baru
     */
    public function revokeAll(Request $request)
    {
        $userId = $request->user()?->id;

        // [FIX H-7] Hapus token session auth sebelum logout
        $request->session()->forget('auth_session_token');

        // Revoke semua session di DB
        AuthSession::where('user_id', $userId)
            ->update(['is_revoked' => true]);

        // Audit log sebelum session dihancurkan
        if ($userId) {
            AuthAuditLog::log('auth.sessions.revoke_all', $userId, [
                'ip' => $request->ip(),
            ]);
        }

        // Logout dari Laravel auth (hapus guard state)
        Auth::logout();

        // Hancurkan session dan buat CSRF token baru
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'All sessions revoked. Logged out.']);
    }
}
