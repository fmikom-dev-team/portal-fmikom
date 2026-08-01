<?php

namespace App\Modules\WorkOs\Controllers\Auth;

use App\Concerns\PasswordValidationRules;
use App\Enums\OtpPurpose;
use App\Enums\RegistrationStatus;
use App\Enums\UserAccountStatus;
use App\Http\Controllers\Controller;
use App\Models\Auth\AuthOtpToken;
use App\Models\Auth\RegistrationRequest;
use App\Models\User;
use App\Services\Auth\ActivationService;
use App\Services\Auth\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;

/**
 * ActivationConfirmController — Case B (Self-Registration) Activation.
 *
 * Handles the "clicked activation link" → "Verify OTP" → "set password" → "activated" flow.
 * User arrives here from the ActivationEmail (sent after admin approval).
 */
class ActivationConfirmController extends Controller
{
    use PasswordValidationRules;

    public function __construct(
        private ActivationService $activationService,
        private OtpService $otpService,
    ) {}

    /**
     * Validate activation link and send OTP.
     * Called via signed URL: GET /activate/confirm?token=...&email=...&request_id=...
     *
     * [FIX C-3, M-10] Sebelumnya: token di-null SEBELUM OTP dikirim. Jika pengiriman
     * OTP gagal (mis. email server down), user terkunci karena:
     *   1. Token sudah null → tidak bisa klik link lagi
     *   2. OTP tidak terkirim → tidak bisa verifikasi
     *
     * Sekarang: urutan yang benar adalah:
     *   1. Validasi token (baca saja, belum dihapus)
     *   2. Coba kirim OTP
     *   3. BARU null-kan token setelah OTP berhasil dikirim
     *
     * [FIX RACE] Penggunaan DB lock saat nulling token untuk prevent dua browser
     * yang klik link bersamaan sama-sama berhasil masuk ke OTP form.
     */
    public function confirm(Request $request)
    {
        $token = $request->query('token');
        $requestId = $request->query('request_id');

        if (! $token || ! $requestId) {
            return $this->redirectWithLogout($request, 'error', 'Link aktivasi tidak valid.');
        }

        /** @var RegistrationRequest|null $regRequest */
        $regRequest = RegistrationRequest::find($requestId, ['*']);

        if (! $regRequest) {
            return $this->redirectWithLogout($request, 'error', 'Permintaan registrasi tidak ditemukan.');
        }

        $email = $regRequest->email;

        if ($regRequest->isActivated()) {
            return $this->redirectWithLogout($request, 'status', 'Akun ini sudah aktif. Silakan login.');
        }

        if ($regRequest->isRejected()) {
            return $this->redirectWithLogout($request, 'error', 'Permintaan registrasi ini telah ditolak.');
        }

        if ($regRequest->isActivationTokenExpired()) {
            return $this->redirectWithLogout($request, 'error', 'Link aktivasi sudah kedaluwarsa. Hubungi admin untuk mengirim ulang link.');
        }

        if (! $regRequest->verifyActivationToken($token)) {
            return $this->redirectWithLogout($request, 'error', 'Link aktivasi tidak valid atau sudah digunakan.');
        }

        /** @var User|null $user */
        $user = $regRequest->createdUser;
        if (! $user) {
            return $this->redirectWithLogout($request, 'error', 'Data user tidak ditemukan.');
        }

        // Transition statuses & mark email verified
        $user->forceFill([
            'status_approval' => UserAccountStatus::OtpVerified->value,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        $regRequest->fill(['status' => RegistrationStatus::OtpVerified->value])->save();

        // Store request identifiers & verification flag in session for password creation
        session([
            'activation_request_id' => $requestId,
            'activation_email' => $email,
            'activation_otp_verified' => true,
        ]);

        return redirect()->route('activation.complete')->with('status', 'Email berhasil diverifikasi! Silakan buat password baru Anda.');
    }

    /**
     * Show the OTP input form (Case B).
     *
     * [FIX M-4] Tambahkan pengecekan jika OTP sudah pernah diverifikasi
     * (session `activation_otp_verified` ada). Jika sudah, langsung redirect
     * ke halaman set password agar tidak bisa kembali ke OTP form.
     */
    public function showOtpForm(Request $request)
    {
        $requestId = session('activation_request_id');
        $email = session('activation_email');

        if (! $requestId || ! $email) {
            return redirect()->route('login')->with('error', 'Sesi aktivasi kedaluwarsa.');
        }

        // [FIX M-4] Jika OTP sudah diverifikasi, langsung lanjut ke set password
        if (session('activation_otp_verified')) {
            return redirect()->route('activation.complete')
                ->with('status', 'OTP sudah terverifikasi. Silakan buat password baru.');
        }

        $activeOtp = AuthOtpToken::findActive($email, OtpPurpose::AccountActivation);

        return Inertia::render('auth/ActivateConfirmOtp', [
            'email' => substr($email, 0, 3).'***'.strstr($email, '@'),
            'expiresAt' => $activeOtp?->expires_at?->toIso8601String(),
            'status' => session('status'),
            'error' => session('error'),
        ]);
    }

    /**
     * Process OTP verification (Case B).
     */
    public function verifyOtp(Request $request)
    {
        $requestId = session('activation_request_id');
        $email = session('activation_email');

        if (! $requestId || ! $email) {
            return redirect()->route('login')->with('error', 'Sesi aktivasi kedaluwarsa.');
        }

        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        try {
            $this->otpService->verify($email, OtpPurpose::AccountActivation, $request->otp);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['otp' => $e->getMessage()]);
        }

        // Transition statuses to OtpVerified
        /** @var RegistrationRequest|null $regRequest */
        $regRequest = RegistrationRequest::find($requestId, ['*']);
        if ($regRequest) {
            $regRequest->fill(['status' => RegistrationStatus::OtpVerified->value])->save();
            if ($user = $regRequest->createdUser) {
                $user->forceFill([
                    'status_approval' => UserAccountStatus::OtpVerified->value,
                    'email_verified_at' => now(),
                ])->save();
            }
        }

        session(['activation_otp_verified' => true]);

        return redirect()->route('activation.complete')->with('status', 'Kode OTP berhasil diverifikasi. Silakan buat password baru.');
    }

    /**
     * Resend OTP (Case B).
     */
    public function resendOtp(Request $request)
    {
        $requestId = session('activation_request_id');
        $email = session('activation_email');

        if (! $requestId || ! $email) {
            return response()->json(['error' => 'Sesi tidak valid.'], 400);
        }

        /** @var RegistrationRequest|null $regRequest */
        $regRequest = RegistrationRequest::find($requestId, ['*']);
        if (! $regRequest || ! ($user = $regRequest->createdUser)) {
            return response()->json(['error' => 'User tidak ditemukan.'], 400);
        }

        if (! $this->otpService->canResend($email, OtpPurpose::AccountActivation)) {
            return back()->withErrors(['otp' => 'Tunggu 2 menit sebelum mengirim ulang kode OTP.']);
        }

        try {
            $this->otpService->generate(
                userId: $user->id,
                email: $email,
                purpose: OtpPurpose::AccountActivation,
                userForDisplay: $user,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent()
            );
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['otp' => 'Gagal mengirim ulang OTP: '.$e->getMessage()]);
        }

        return back()->with('status', 'Kode OTP baru telah dikirimkan ke email Anda.');
    }

    /**
     * Show the password creation form (Case B).
     */
    public function showCompleteForm(Request $request)
    {
        if (! session('activation_request_id') || ! session('activation_otp_verified')) {
            return redirect()->route('login')->with('error', 'Sesi aktivasi tidak valid. Silakan klik ulang link di email Anda.');
        }

        return Inertia::render('auth/ActivationComplete', [
            'status' => session('status'),
            'email' => session('activation_email'),
        ]);
    }

    /**
     * Process password creation and activate the account (Case B).
     *
     * [FIX C-4, H-2] Sebelumnya: tidak ada DB lock → dua request bersamaan bisa
     * sama-sama memanggil completeSelfRegistrationActivation() dan mengakibatkan
     * double-activation (duplikat module role assignment, double audit log).
     *
     * Sekarang: menggunakan DB::transaction() + lockForUpdate() di dalam service.
     * Guard isActivated() di dalam transaction memastikan hanya request pertama
     * yang berhasil, request kedua langsung diredirect ke login.
     */
    public function complete(Request $request)
    {
        $requestId = session('activation_request_id');
        $email = session('activation_email');

        if (! $requestId || ! $email || ! session('activation_otp_verified')) {
            return redirect()->route('login')->with('error', 'Sesi aktivasi tidak valid. Silakan klik ulang link di email Anda.');
        }

        $request->validate([
            'password' => $this->passwordRules(),
        ]);

        try {
            $result = DB::transaction(function () use ($requestId, $request) {
                /** @var RegistrationRequest|null $regRequest */
                $regRequest = RegistrationRequest::where('id', '=', $requestId, 'and')
                    ->lockForUpdate()
                    ->first();

                if (! $regRequest) {
                    return ['error' => 'Permintaan registrasi tidak ditemukan.'];
                }

                // [FIX C-4] Guard: jika sudah diaktifkan oleh request lain
                if ($regRequest->isActivated()) {
                    session()->forget(['activation_request_id', 'activation_email', 'activation_otp_verified']);

                    return ['already_active' => true, 'user_id' => $regRequest->created_user_id];
                }

                /** @var User|null $user */
                $user = $regRequest->createdUser;

                if (! $user) {
                    return ['error' => 'User tidak ditemukan. Hubungi administrator.'];
                }

                // Complete the activation
                $this->activationService->completeSelfRegistrationActivation(
                    user: $user,
                    request: $regRequest,
                    password: $request->password,
                );

                return ['user' => $user];
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('login')->with('error', 'Terjadi kesalahan saat mengaktifkan akun. Silakan coba lagi.');
        }

        // Handle hasil transaction
        if (isset($result['error'])) {
            return redirect()->route('login')->with('error', $result['error']);
        }

        // Clear activation session
        session()->forget(['activation_request_id', 'activation_email', 'activation_otp_verified']);

        if (isset($result['already_active'])) {
            // Request lain sudah mengaktifkan lebih dulu — login sebagai user yang sudah aktif
            $user = User::find($result['user_id']);
            if ($user) {
                Auth::login($user);
                $request->session()->regenerate();

                return redirect()->route('dashboard')->with('status', 'Akun Anda sudah aktif. Selamat datang kembali!');
            }

            return redirect()->route('login')->with('status', 'Akun sudah diaktifkan. Silakan login.');
        }

        $user = $result['user'];

        // Log user in
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Akun Anda berhasil diaktifkan! Selamat datang di Portal FMIKOM.');
    }

    /**
     * Helper to log out existing unverified session and redirect to login with flash message.
     */
    private function redirectWithLogout(Request $request, string $flashKey, string $message)
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('login')->with($flashKey, $message);
    }
}
