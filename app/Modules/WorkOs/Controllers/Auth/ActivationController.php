<?php

namespace App\Modules\WorkOs\Controllers\Auth;

use App\Concerns\PasswordValidationRules;
use App\Enums\OtpPurpose;
use App\Enums\UserAccountStatus;
use App\Http\Controllers\Controller;
use App\Models\Auth\IdentityVerification;
use App\Models\User;
use App\Services\Auth\ActivationService;
use App\Services\Auth\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;

/**
 * ActivationController — Case A: Admin-Driven Account Activation.
 *
 * Flow:
 *  1. User visits /activate — fills NIM/NIDN + tanggal lahir
 *  2. Server verifies identity against DB (nomor_induk + tanggal_lahir)
 *  3. OTP sent to stored email
 *  4. User verifies OTP
 *  5. User creates password
 *  6. Account activated → auto login → dashboard
 *
 * Public routes (no auth middleware):
 *  - GET  /activate               — show identity form
 *  - POST /activate               — verify identity
 *  - GET  /activate/verify-otp   — show OTP form
 *  - POST /activate/verify-otp   — process OTP
 *  - GET  /activate/set-password — show password form
 *  - POST /activate/set-password — process password
 */
class ActivationController extends Controller
{
    use PasswordValidationRules;

    public function __construct(
        private OtpService $otpService,
        private ActivationService $activationService,
    ) {}

    // ─── Step 1: Identity Verification ───────────────────────────────────────

    /**
     * Show the identity verification form.
     */
    public function showIdentityForm()
    {
        return Inertia::render('auth/Activate', [
            'status' => session('status'),
            'error' => session('error'),
        ]);
    }

    /**
     * Process identity verification (NIM/NIDN + tanggal lahir).
     */
    public function verifyIdentity(Request $request)
    {
        $request->validate([
            'user_type' => ['required', 'in:mahasiswa,dosen,staff,alumni'],
            'identifier' => ['required', 'string', 'max:50'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
        ], [
            'identifier.required' => 'NIM/NIDN/NIP wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.before' => 'Tanggal lahir tidak valid.',
        ]);

        // [FIX MED-05] Rate limit diperketat: 5 percobaan / 15 menit per IP.
        // Sebelumnya: 10/5 menit = 2880 percobaan/hari.
        // Sekarang:   5/15 menit = 480 percobaan/hari.
        // Mencegah brute force tanggal lahir (hanya 365 kemungkinan/tahun) terhadap NIM yang diketahui.
        $rateLimitKey = 'identity_verify:'.$request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, maxAttempts: 5)) {
            return back()->withErrors([
                'identifier' => 'Terlalu banyak percobaan. Silakan tunggu 15 menit sebelum mencoba lagi.',
            ]);
        }
        RateLimiter::hit($rateLimitKey, decaySeconds: 900); // 15 menit

        // Look up the user by identifier + tanggal_lahir (double-factor verification)
        $user = User::where('nomor_induk', '=', $request->identifier, 'and')
            ->where('tanggal_lahir', '=', $request->tanggal_lahir, 'and')
            ->where('user_type', '=', $request->user_type, 'and')
            ->first();

        if (! $user) {
            // Generic error — don't reveal whether NIM exists or DOB is wrong
            return back()->withErrors([
                'identifier' => 'Data tidak ditemukan. Pastikan NIM/NIDN dan tanggal lahir yang Anda masukkan benar.',
            ]);
        }

        // Guard: Only non-activated accounts can use this flow
        if ($user->isAccountActive()) {
            return back()->withErrors([
                'identifier' => 'Akun ini sudah aktif. Silakan login menggunakan password Anda.',
            ]);
        }

        // Guard: Rejected accounts cannot activate
        if ($user->isRejected()) {
            return back()->withErrors([
                'identifier' => 'Akun ini tidak dapat diaktifkan. Hubungi administrator.',
            ]);
        }

        // Create identity verification session
        $verification = IdentityVerification::start(
            userType: $request->user_type,
            identifier: $request->identifier,
            tanggalLahir: $request->tanggal_lahir,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $verification->markVerified($user);

        // Send OTP to user's stored email
        $this->activationService->sendActivationOtp($user, $request->ip());

        return redirect()->route('activation.verify-otp', [
            'session' => $verification->session_token,
        ])->with('status', 'Kode OTP telah dikirimkan ke email '.substr($user->email, 0, 3).'***.');
    }

    // ─── Step 2: OTP Verification ─────────────────────────────────────────────

    /**
     * Show OTP verification form.
     */
    public function showOtpForm(Request $request)
    {
        $sessionToken = $request->query('session');

        if (! $sessionToken) {
            return redirect()->route('activation.show')->with('error', 'Sesi tidak valid. Silakan mulai ulang.');
        }

        $verification = IdentityVerification::findVerifiedByToken($sessionToken);

        if (! $verification) {
            return redirect()->route('activation.show')->with('error', 'Sesi aktivasi kedaluwarsa. Silakan mulai ulang.');
        }

        $user = $verification->resolvedUser;

        return Inertia::render('auth/ActivateVerifyOtp', [
            'session' => $sessionToken,
            'email' => $user ? substr($user->email, 0, 3).'***' : null,
            'expiresAt' => $verification->expires_at,
            'status' => session('status'),
        ]);
    }

    /**
     * Process OTP verification.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'session' => ['required', 'string'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $verification = IdentityVerification::findVerifiedByToken($request->input('session'));

        if (! $verification) {
            return redirect()->route('activation.show')->with('error', 'Sesi aktivasi kedaluwarsa. Silakan mulai ulang.');
        }

        $user = $verification->resolvedUser;

        if (! $user) {
            return redirect()->route('activation.show')->with('error', 'Data user tidak ditemukan. Silakan mulai ulang.');
        }

        try {
            $this->otpService->verify($user->email, OtpPurpose::AccountActivation, $request->otp);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['otp' => $e->getMessage()]);
        }

        // Mark OTP as verified
        $this->activationService->markOtpVerified($user);

        return redirect()->route('activation.set-password', [
            'session' => $request->input('session'),
        ])->with('status', 'Identitas berhasil diverifikasi. Silakan buat password baru.');
    }

    /**
     * Resend OTP for Case A.
     */
    public function resendOtp(Request $request)
    {
        $request->validate(['session' => ['required', 'string']]);

        $verification = IdentityVerification::findVerifiedByToken($request->input('session'));

        if (! $verification) {
            return response()->json(['error' => 'Sesi tidak valid.'], 400);
        }

        $user = $verification->resolvedUser;

        if (! $user) {
            return response()->json(['error' => 'User tidak ditemukan.'], 400);
        }

        if (! $this->otpService->canResend($user->email, OtpPurpose::AccountActivation)) {
            return back()->withErrors(['otp' => 'Tunggu 2 menit sebelum mengirim ulang kode OTP.']);
        }

        $this->activationService->sendActivationOtp($user, $request->ip());

        return back()->with('status', 'Kode OTP baru telah dikirimkan ke email Anda.');
    }

    // ─── Step 3: Password Creation ───────────────────────────────────────────

    /**
     * Show the password creation form.
     */
    public function showPasswordForm(Request $request)
    {
        $sessionToken = $request->query('session');

        if (! $sessionToken) {
            return redirect()->route('activation.show');
        }

        $verification = IdentityVerification::findVerifiedByToken($sessionToken);

        if (! $verification) {
            return redirect()->route('activation.show')->with('error', 'Sesi aktivasi kedaluwarsa. Silakan mulai ulang.');
        }

        // Ensure OTP has been verified before showing password form
        $user = $verification->resolvedUser;
        if (! $user || $user->status_approval !== UserAccountStatus::OtpVerified) {
            return redirect()->route('activation.verify-otp', ['session' => $sessionToken]);
        }

        return Inertia::render('auth/ActivateSetPassword', [
            'session' => $sessionToken,
        ]);
    }

    /**
     * Process password creation and complete activation.
     */
    public function setPassword(Request $request)
    {
        $request->validate([
            'session' => ['required', 'string'],
            'password' => $this->passwordRules(),
        ]);

        $verification = IdentityVerification::findVerifiedByToken($request->input('session'));

        if (! $verification) {
            return redirect()->route('activation.show')->with('error', 'Sesi aktivasi kedaluwarsa.');
        }

        $user = $verification->resolvedUser;

        if (! $user || $user->status_approval !== UserAccountStatus::OtpVerified) {
            return redirect()->route('activation.show')->with('error', 'Proses aktivasi tidak valid. Silakan mulai ulang.');
        }

        // Complete activation
        $this->activationService->completeActivation($user, $request->password);

        // Log the user in
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Akun Anda berhasil diaktifkan! Selamat datang di Portal FMIKOM.');
    }
}
