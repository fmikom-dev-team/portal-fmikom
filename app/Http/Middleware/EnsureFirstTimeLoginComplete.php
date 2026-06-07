<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
        $user = $request->user()?->fresh();

        // Pass jika belum login
        if (!$user) {
            return $next($request);
        }

        // Update user's last_seen_at timestamp (throttled to once per minute)
        if (!$user->last_seen_at || $user->last_seen_at->addMinutes(1)->isPast()) {
            $user->updateQuietly(['last_seen_at' => now()]);
        }

        // Route yang dikecualikan dari pengecekan
        $isOtpRoute  = $request->routeIs('verify.otp') || $request->routeIs('resend.otp');
        $isForcePass = $request->routeIs('password.force.*');
        $isLogout    = $request->routeIs('logout');

        // Izinkan akses ke route OTP, force change password, dan logout
        if ($isOtpRoute || $isForcePass || $isLogout) {
            return $next($request);
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
