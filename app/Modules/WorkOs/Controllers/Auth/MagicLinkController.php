<?php

namespace App\Modules\WorkOs\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Modules\WorkOs\Services\AuthPlatform\SessionEngine;
use App\Services\Auth\MagicLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * MagicLinkController — Full implementation of magic link authentication.
 *
 * ONLY active users can request and use magic links.
 * Pending/rejected/suspended users are silently ignored.
 */
class MagicLinkController extends Controller
{
    public function __construct(
        private MagicLinkService $magicLinkService,
        private SessionEngine $sessionEngine,
    ) {}

    /**
     * Send a magic link to the provided email.
     * Silently succeeds even if email is not found (prevent enumeration).
     */
    public function send(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        // Always return same response regardless of whether email exists
        $this->magicLinkService->send(
            email: $request->email,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()->json([
            'message' => 'Jika email Anda terdaftar dan akun aktif, link login akan dikirimkan segera.',
        ]);
    }

    /**
     * Verify and consume a magic link.
     * Route must have 'signed' middleware applied.
     */
    public function verify(Request $request)
    {
        $email = $request->query('email');
        $token = $request->query('token');

        if (! $email || ! $token) {
            return redirect()->route('login')->with('error', 'Magic link tidak valid.');
        }

        try {
            $user = $this->magicLinkService->verify($email, $token);
        } catch (\RuntimeException $e) {
            return redirect()->route('login')->with('error', $e->getMessage());
        }

        // Log user in
        Auth::login($user, remember: false);
        $request->session()->regenerate(); // Prevent session fixation

        // Create enterprise session record
        $session = $this->sessionEngine->createSession($user, $request);
        $request->session()->put('auth_session_token', $session->id);

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('success', 'Berhasil login via Magic Link.');
    }
}
