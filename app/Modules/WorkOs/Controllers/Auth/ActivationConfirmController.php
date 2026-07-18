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
     */
    public function confirm(Request $request)
    {
        $email = $request->query('email');
        $token = $request->query('token');
        $requestId = $request->query('request_id');

        if (! $email || ! $token || ! $requestId) {
            return redirect()->route('login')->with('error', 'Link aktivasi tidak valid.');
        }

        /** @var RegistrationRequest|null $regRequest */
        $regRequest = RegistrationRequest::find($requestId, ['*']);

        if (! $regRequest) {
            return redirect()->route('login')->with('error', 'Permintaan registrasi tidak ditemukan.');
        }

        if ($regRequest->isActivated()) {
            return redirect()->route('login')->with('status', 'Akun ini sudah aktif. Silakan login.');
        }

        if ($regRequest->isRejected()) {
            return redirect()->route('login')->with('error', 'Permintaan registrasi ini telah ditolak.');
        }

        if ($regRequest->isActivationTokenExpired()) {
            return redirect()->route('login')->with('error', 'Link aktivasi sudah kedaluwarsa. Hubungi admin untuk mengirim ulang link.');
        }

        if (! $regRequest->verifyActivationToken($token)) {
            return redirect()->route('login')->with('error', 'Link aktivasi tidak valid atau sudah digunakan.');
        }

        /** @var User|null $user */
        $user = $regRequest->createdUser;
        if (! $user) {
            return redirect()->route('login')->with('error', 'Data user tidak ditemukan.');
        }

        // Generate and send OTP for Case B activation
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

            return redirect()->route('login')->with('error', 'Gagal mengirim OTP aktivasi: '.$e->getMessage());
        }

        // Transition statuses
        $user->forceFill(['status_approval' => UserAccountStatus::OtpSent->value])->save();
        $regRequest->fill(['status' => RegistrationStatus::OtpSent->value])->save();

        // Store request identifiers in session
        session([
            'activation_request_id' => $requestId,
            'activation_email' => $email,
        ]);

        return redirect()->route('activation.confirm.otp')->with('status', 'Kode OTP verifikasi telah dikirimkan ke email Anda.');
    }

    /**
     * Show the OTP input form (Case B).
     */
    public function showOtpForm(Request $request)
    {
        $requestId = session('activation_request_id');
        $email = session('activation_email');

        if (! $requestId || ! $email) {
            return redirect()->route('login')->with('error', 'Sesi aktivasi kedaluwarsa.');
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

        /** @var RegistrationRequest|null $regRequest */
        $regRequest = RegistrationRequest::find($requestId, ['*']);

        if (! $regRequest || $regRequest->isActivated()) {
            session()->forget(['activation_request_id', 'activation_email', 'activation_otp_verified']);

            return redirect()->route('login')->with('status', 'Akun sudah diaktifkan. Silakan login.');
        }

        /** @var User|null $user */
        $user = $regRequest->createdUser;

        if (! $user) {
            return redirect()->route('login')->with('error', 'User tidak ditemukan. Hubungi administrator.');
        }

        // Complete the activation
        $this->activationService->completeSelfRegistrationActivation(
            user: $user,
            request: $regRequest,
            password: $request->password,
        );

        // Clear activation session
        session()->forget(['activation_request_id', 'activation_email', 'activation_otp_verified']);

        // Log user in
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Akun Anda berhasil diaktifkan! Selamat datang di Portal FMIKOM.');
    }
}
