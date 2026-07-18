<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureFirstTimeLoginComplete
{
    /**
     * Handle an incoming request.
     *
     * Alur verifikasi setelah login:
     * 1. Jika email belum terverifikasi (OTP) → /verify-otp
     * 2. Jika password belum pernah diubah (admin-created) → /force-change-password
     * 3. Lolos semua → akses normal
     *
     * Catatan: Tidak ada lagi waiting room. Semua user yang terdaftar sendiri
     * langsung berstatus 'approved' dan bisa langsung akses dashboard setelah
     * verifikasi OTP.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // BUG-011: Removed ->fresh() which triggered a DB query on every authenticated request.
        // $request->user() returns the already-resolved, cached auth user.
        $user = $request->user();

        // Pass jika belum login
        if (! $user) {
            return $next($request);
        }

        // Route yang dikecualikan dari pengecekan
        $isOtpRoute = $request->routeIs('verify.otp') || $request->routeIs('resend.otp');
        $isForcePass = $request->routeIs('password.force.*');
        $isLogout = $request->routeIs('logout');

        // Update user's last_seen_at timestamp (throttled to once per minute)
        // Only touch the DB if not on excluded routes to minimize overhead
        if (! $isOtpRoute && ! $isForcePass && ! $isLogout) {
            if (! $user->last_seen_at || $user->last_seen_at->addMinutes(1)->isPast()) {
                $user->updateQuietly(['last_seen_at' => now()]);
            }
        }

        // Izinkan akses ke route OTP, force change password, dan logout
        if ($isOtpRoute || $isForcePass || $isLogout) {
            return $next($request);
        }

        // ── LANGKAH 0: Cek Status Akun & Aktivasi (Enforce Active State) ─────────
        if ($user->isAccountActive()) {
            if ($request->routeIs('waiting-room')) {
                return redirect()->route('dashboard');
            }
        } else {
            $isWaitingRoomRoute = $request->routeIs('waiting-room')
                || $request->routeIs('waiting-room.resign')
                || $request->routeIs('approval.status');

            if ($user->isPendingReview() || $user->isRejected()) {
                if ($isWaitingRoomRoute || $isLogout) {
                    return $next($request);
                }

                return redirect()->route('waiting-room');
            }

            $message = $user->getLoginBlockMessage() ?? 'Akun Anda tidak aktif.';

            // Log out user since session is invalid/not allowed
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['error' => $message], 403);
            }

            return redirect()->route('login')->with('error', $message);
        }

        // ── LANGKAH 1: Cek verifikasi email via OTP ─────────────────────────────
        if (is_null($user->email_verified_at)) {
            return redirect()->route('verify.otp');
        }

        // ── LANGKAH 2: Force Password Change (hanya admin-created users) ────────
        // Self-registered users sudah punya password_changed_at dari saat daftar
        if (is_null($user->password_changed_at)) {
            return redirect()->route('password.force.change');
        }

        return $next($request);
    }
}
