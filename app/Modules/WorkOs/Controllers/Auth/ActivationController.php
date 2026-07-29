<?php

namespace App\Modules\WorkOs\Controllers\Auth;

use App\Concerns\PasswordValidationRules;
use App\Enums\OtpPurpose;
use App\Enums\UserAccountStatus;
use App\Http\Controllers\Controller;
use App\Models\Auth\AuthOtpToken;
use App\Models\Auth\IdentityVerification;
use App\Models\Portal\PortalSetting;
use App\Models\User;
use App\Notifications\WorkOsAlert;
use App\Services\Auth\ActivationService;
use App\Services\Auth\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
     * Helper to mask email address for privacy (e.g. bu***o@gmail.com).
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';

        if (strlen($name) <= 2) {
            $maskedName = substr($name, 0, 1).'***';
        } else {
            $maskedName = substr($name, 0, 2).'***'.substr($name, -1);
        }

        return $maskedName.'@'.$domain;
    }

    /**
     * Process identity verification for Mahasiswa (NIM lookup without DOB requirement).
     *
     * [FIX C-1] Sebelumnya: session token langsung ditimpa jika user membuka
     * dua tab berbeda dan melakukan verifikasi NIM di keduanya secara bersamaan.
     * Sekarang: session lama dihapus terlebih dahulu untuk menghindari kebingungan
     * state, dan setiap verifikasi NIM selalu menghasilkan sesi baru yang bersih.
     */
    public function verifyIdentity(Request $request)
    {
        $request->validate([
            'identifier' => ['required', 'string', 'max:50'],
        ], [
            'identifier.required' => 'NIM wajib diisi.',
        ]);

        $rateLimitKey = 'identity_verify:'.$request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, maxAttempts: 5)) {
            return back()->withErrors([
                'identifier' => 'Terlalu banyak percobaan. Silakan tunggu 15 menit sebelum mencoba lagi.',
            ]);
        }
        RateLimiter::hit($rateLimitKey, decaySeconds: 900); // 15 menit

        // Check if identifier belongs to Dosen or Staff
        $staffUser = User::where('nomor_induk', $request->identifier)
            ->whereIn('user_type', ['dosen', 'staff'])
            ->first();

        if ($staffUser) {
            return back()->withErrors([
                'identifier' => 'Aktivasi akun Dosen dan Staff dikelola langsung oleh Administrator IT Kampus. Silakan hubungi Administrator.',
            ]);
        }

        // Look up Mahasiswa / Alumni user by NIM
        $user = User::where('nomor_induk', $request->identifier)
            ->whereIn('user_type', ['mahasiswa', 'alumni'])
            ->first();

        if (! $user) {
            return back()->withErrors([
                'identifier' => 'Data mahasiswa tidak ditemukan. Pastikan NIM yang Anda masukkan benar.',
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

        // [FIX C-1 & M-3] Hapus session token lama jika masih ada.
        // Ini mencegah kondisi di mana user membuka dua tab, melakukan verifikasi
        // NIM di keduanya, dan tab yang lebih lama punya token yang sudah tidak
        // relevan. Token lama di DB dibiarkan expire secara natural (TTL 30 menit).
        $oldToken = session('activation_session_token');
        if ($oldToken) {
            session()->forget('activation_session_token');
        }

        $maskedEmail = $this->maskEmail($user->email);

        // Create identity verification session
        $verification = IdentityVerification::start(
            userType: $user->user_type ?? 'mahasiswa',
            identifier: $request->identifier,
            tanggalLahir: $user->tanggal_lahir?->format('Y-m-d'),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $verification->markVerified($user);

        // Send OTP to user's stored email
        $this->activationService->sendActivationOtp($user, $request->ip());

        session()->put('activation_session_token', $verification->session_token);

        return redirect()->route('activation.verify-otp')
            ->with('status', 'Kode OTP telah dikirimkan ke email '.$maskedEmail.'. Silakan cek inbox Anda.');
    }

    // ─── Step 2: OTP Verification ─────────────────────────────────────────────

    /**
     * Show OTP verification form.
     */
    public function showOtpForm(Request $request)
    {
        // [FIX FIND-004] Baca session token dari server-side session, bukan URL parameter.
        $sessionToken = session('activation_session_token');

        if (! $sessionToken) {
            return redirect()->route('activation.show')->with('error', 'Sesi tidak valid. Silakan mulai ulang.');
        }

        $verification = IdentityVerification::findVerifiedByToken($sessionToken);

        if (! $verification) {
            return redirect()->route('activation.show')->with('error', 'Sesi aktivasi kedaluwarsa. Silakan mulai ulang.');
        }

        $user = $verification->resolvedUser;
        $activeOtp = $user ? AuthOtpToken::findActive($user->email, OtpPurpose::AccountActivation) : null;
        $expiresAt = $activeOtp?->expires_at ?? $verification->expires_at;

        return Inertia::render('auth/ActivateVerifyOtp', [
            'email' => $user ? $this->maskEmail($user->email) : null,
            'expiresAt' => $expiresAt ? $expiresAt->toIso8601String() : null,
            'status' => session('status'),
        ]);
    }

    /**
     * Process OTP verification.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        // [FIX FIND-004] Baca session token dari server-side session.
        $sessionToken = session('activation_session_token');

        $verification = IdentityVerification::findVerifiedByToken($sessionToken);

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

        // [FIX FIND-004] Redirect ke URL bersih tanpa token di URL.
        return redirect()->route('activation.set-password')
            ->with('status', 'Identitas berhasil diverifikasi. Silakan buat password baru.');
    }

    /**
     * Resend OTP for Case A.
     *
     * [FIX M-1] Sebelumnya: menggunakan back()->withErrors() yang tidak konsisten
     * karena route ini dipanggil dari form POST bukan API.
     * Sekarang: tetap menggunakan back() tapi dengan pesan yang konsisten.
     */
    public function resendOtp(Request $request)
    {
        $request->validate(['otp' => ['nullable']]);

        // [FIX FIND-004] Baca session token dari server-side session.
        $sessionToken = session('activation_session_token');
        $verification = IdentityVerification::findVerifiedByToken($sessionToken);

        if (! $verification) {
            // Jika dipanggil via fetch/AJAX, kembalikan JSON
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Sesi tidak valid.'], 400);
            }

            return redirect()->route('activation.show')->with('error', 'Sesi tidak valid. Silakan mulai ulang.');
        }

        $user = $verification->resolvedUser;

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'User tidak ditemukan.'], 400);
            }

            return redirect()->route('activation.show')->with('error', 'Data user tidak ditemukan. Silakan mulai ulang.');
        }

        if (! $this->otpService->canResend($user->email, OtpPurpose::AccountActivation)) {
            return back()->withErrors(['otp' => 'Tunggu 2 menit sebelum mengirim ulang kode OTP.']);
        }

        $this->activationService->sendActivationOtp($user, $request->ip());

        return back()->with('status', 'Kode OTP baru telah dikirimkan ke email Anda.');
    }

    /**
     * Submit Helpdesk / Email Change request & generate WA link.
     */
    public function submitHelpdeskRequest(Request $request)
    {
        $request->validate([
            'new_email' => ['required', 'email', 'max:255'],
        ]);

        $sessionToken = session('activation_session_token');
        $verification = IdentityVerification::findVerifiedByToken($sessionToken);

        if (! $verification) {
            return back()->withErrors(['helpdesk' => 'Sesi aktivasi tidak valid. Silakan mulai ulang.']);
        }

        $user = $verification->resolvedUser;

        if (! $user) {
            return back()->withErrors(['helpdesk' => 'Data user tidak ditemukan.']);
        }

        $maskedEmail = $this->maskEmail($user->email);
        $newEmail = strtolower(trim($request->new_email));

        // Send WorkOS Database Notification to Staff & Dosen Admins
        try {
            $admins = User::whereIn('user_type', ['staff', 'dosen'])->get();
            $alert = new WorkOsAlert(
                title: 'Permintaan Pembaruan Email Aktivasi',
                description: "Mahasiswa {$user->name} (NIM: {$user->nomor_induk}) meminta ubah email dari {$maskedEmail} ke {$newEmail}.",
                severity: 'warning',
                extra: [
                    'user_id' => $user->id,
                    'nomor_induk' => $user->nomor_induk,
                    'name' => $user->name,
                    'old_email' => $user->email,
                    'new_email' => $newEmail,
                    'requested_at' => now()->toIso8601String(),
                ]
            );

            foreach ($admins as $admin) {
                $admin->notify($alert);
            }
        } catch (\Throwable $e) {
            // Suppress notification errors to ensure WA link is still generated
        }

        $dbWa = PortalSetting::where('key', 'helpdesk_wa_number')->value('value');
        $waNumber = ! empty($dbWa) ? preg_replace('/\D/', '', $dbWa) : config('services.helpdesk.wa_number', '628123456789');

        $defaultTemplate = "Halo Admin FMIKOM, saya bermaksud mengajukan pembaruan email aktivasi akun:\n\n• Nama Mahasiswa : {nama}\n• NIM            : {nim}\n• Email Lama     : {email_lama}\n• Email Baru     : {email_baru}\n\nSaya siap melampirkan foto KTM/KTP sebagai verifikasi fisik. Mohon bantuannya.";
        $rawTemplate = PortalSetting::where('key', 'helpdesk_wa_template')->value('value') ?: $defaultTemplate;

        $text = str_replace(
            ['{nama}', '{nim}', '{email_lama}', '{email_baru}'],
            [$user->name, $user->nomor_induk, $maskedEmail, $newEmail],
            $rawTemplate
        );

        $waUrl = "https://wa.me/{$waNumber}?text=".urlencode($text);

        return back()->with([
            'status' => 'Permintaan bantuan telah dikirim ke Admin WorkOS.',
            'waUrl' => $waUrl,
        ]);
    }

    // ─── Step 3: Password Creation ───────────────────────────────────────────

    /**
     * Show the password creation form.
     *
     * [FIX M-2] Tambahkan re-validasi sesi sebelum menampilkan form password.
     * Sebelumnya: tidak ada pengecekan apakah sesi masih valid (non-expired).
     */
    public function showPasswordForm(Request $request)
    {
        // [FIX FIND-004] Baca session token dari server-side session.
        $sessionToken = session('activation_session_token');

        if (! $sessionToken) {
            return redirect()->route('activation.show');
        }

        $verification = IdentityVerification::findVerifiedByToken($sessionToken);

        if (! $verification) {
            // [FIX M-2] Bersihkan session yang sudah tidak relevan
            session()->forget('activation_session_token');

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
     *
     * [FIX C-2, H-3, M-9] Sebelumnya: tidak ada proteksi terhadap race condition.
     * Dua request bersamaan (mis. double-click) bisa melewati guard is_active=false
     * dan keduanya sama-sama mengaktifkan akun.
     *
     * Sekarang: menggunakan DB::transaction() + lockForUpdate() untuk memastikan
     * hanya SATU request yang bisa menyelesaikan aktivasi. Request kedua akan
     * mendapatkan user yang sudah active dan diredirect ke dashboard.
     */
    public function setPassword(Request $request)
    {
        $request->validate([
            'password' => $this->passwordRules(),
        ]);

        // [FIX FIND-004] Baca session token dari server-side session.
        $sessionToken = session('activation_session_token');

        if (! $sessionToken) {
            return redirect()->route('activation.show')->with('error', 'Sesi aktivasi kedaluwarsa.');
        }

        try {
            $result = DB::transaction(function () use ($sessionToken, $request) {
                // Re-fetch verification dengan lock di dalam transaction
                $verification = IdentityVerification::where('session_token', '=', $sessionToken, 'and')
                    ->where('status', '=', 'verified', 'and')
                    ->where('expires_at', '>', now(), 'and')
                    ->lockForUpdate()
                    ->first();

                if (! $verification) {
                    return ['error' => 'Sesi aktivasi kedaluwarsa.'];
                }

                // [FIX M-9] Re-fetch user dengan lock untuk prevent race condition
                $user = User::where('id', '=', $verification->resolved_user_id, 'and')
                    ->lockForUpdate()
                    ->first();

                if (! $user) {
                    return ['error' => 'Data user tidak ditemukan. Silakan mulai ulang.'];
                }

                // [FIX C-2] Guard: jika akun sudah aktif (request lain sudah selesai lebih dulu),
                // jangan lakukan apa-apa — redirect saja ke dashboard.
                if ($user->isAccountActive()) {
                    return ['already_active' => true];
                }

                if ($user->status_approval !== UserAccountStatus::OtpVerified) {
                    return ['error' => 'Proses aktivasi tidak valid. Silakan mulai ulang.'];
                }

                // Complete activation
                $this->activationService->completeActivation($user, $request->password);

                return ['user' => $user];
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('activation.show')->with('error', 'Terjadi kesalahan saat mengaktifkan akun. Silakan coba lagi.');
        }

        // Handle hasil transaction
        if (isset($result['error'])) {
            return redirect()->route('activation.show')->with('error', $result['error']);
        }

        // Ambil user dari hasil transaction
        $user = isset($result['already_active'])
            ? User::find(IdentityVerification::where('session_token', $sessionToken)->value('resolved_user_id'))
            : $result['user'];

        // Log the user in
        Auth::login($user);
        $request->session()->regenerate();

        // [FIX FIND-004] Hapus activation session token dari server-side session setelah selesai.
        session()->forget('activation_session_token');

        if (isset($result['already_active'])) {
            return redirect()->route('dashboard')->with('status', 'Akun Anda sudah aktif sebelumnya. Selamat datang kembali!');
        }

        return redirect()->route('dashboard')->with('success', 'Akun Anda berhasil diaktifkan! Selamat datang di Portal FMIKOM.');
    }
}
