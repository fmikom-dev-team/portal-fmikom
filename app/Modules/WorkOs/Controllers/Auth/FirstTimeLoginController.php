<?php

namespace App\Modules\WorkOs\Controllers\Auth;

use App\Concerns\PasswordValidationRules;
use App\Enums\OtpPurpose;
use App\Enums\UserAccountStatus;
use App\Http\Controllers\Controller;
use App\Models\Auth\AuthOtpToken;
use App\Models\Auth\AuthSession;
use App\Models\Auth\RegistrationRequest;
use App\Models\ProgramStudi;
use App\Models\UserModuleRole;
use App\Services\Auth\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

/**
 * FirstTimeLoginController — Handles admin-created user first login.
 *
 * REFACTORED:
 *  - OTP is now read from auth_otp_tokens (not users.otp_code)
 *  - Attempt counting is handled by AuthOtpToken model
 *  - User must be 'activated' status to access dashboard
 *
 * This controller handles the post-login state for admin-created users
 * who have NOT yet verified their email or set a password.
 *
 * Flow:
 *  1. Admin creates user → OTP sent to email → status = otp_sent
 *  2. User logs in (with temporary password) → middleware redirects here
 *  3. User verifies OTP → email_verified_at = now()
 *  4. User creates password → password_changed_at = now(), status = activated
 */
class FirstTimeLoginController extends Controller
{
    use PasswordValidationRules;

    public function __construct(
        private OtpService $otpService,
    ) {}

    /**
     * Tampilkan halaman Verifikasi OTP
     */
    public function showOtpForm(Request $request)
    {
        $user = $request->user();

        // Jika tidak ada OTP aktif, generate baru otomatis
        $existingOtp = AuthOtpToken::findActive($user->email, OtpPurpose::AccountActivation);

        if (! $existingOtp) {
            $this->otpService->generate(
                userId: $user->id,
                email: $user->email,
                purpose: OtpPurpose::AccountActivation,
                userForDisplay: $user,
                ipAddress: $request->ip(),
            );
        }

        $otp = AuthOtpToken::findActive($user->email, OtpPurpose::AccountActivation);

        return Inertia::render('auth/VerifyOtp', [
            'email' => $user->email,
            'expiresAt' => $otp?->expires_at,
            'status' => session('status'),
        ]);
    }

    /**
     * Verifikasi kode OTP (now using AuthOtpToken)
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        try {
            $this->otpService->verify($user->email, OtpPurpose::AccountActivation, $request->otp);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['otp' => $e->getMessage()]);
        }

        // OTP verified → update email_verified_at
        $user->forceFill([
            'email_verified_at' => now(),
            'status_approval' => UserAccountStatus::OtpVerified->value,
        ])->save();

        // [FIX FLAW-004] Dead code dihapus: kondisi status Pending tidak pernah tercapai karena
        // forceFill() di atas sudah mengubah status menjadi OtpVerified.
        // Admin-created users selalu dalam status OtpSent/Approved ketika masuk alur ini.

        // Lanjut ke step berikutnya (force change password)
        return redirect()->route('password.force.change');
    }

    /**
     * Resend OTP (now using OtpService)
     */
    public function resendOtp(Request $request)
    {
        $user = $request->user();

        if (! $this->otpService->canResend($user->email, OtpPurpose::AccountActivation)) {
            return back()->withErrors(['otp' => 'Tunggu 2 menit sebelum mengirim ulang kode OTP.']);
        }

        $this->otpService->generate(
            userId: $user->id,
            email: $user->email,
            purpose: OtpPurpose::AccountActivation,
            userForDisplay: $user,
            ipAddress: $request->ip(),
        );

        return back()->with('status', 'Kode OTP baru telah dikirimkan ke email Anda.');
    }

    /**
     * Tampilkan form Buat Password Baru
     */
    public function showPasswordForm(Request $request)
    {
        $user = $request->user();

        // Pastikan email sudah terverifikasi via OTP
        if (is_null($user->email_verified_at)) {
            return redirect()->route('verify.otp');
        }

        // Jika sudah ada password_changed_at (sudah set password), skip
        if (! is_null($user->password_changed_at)) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('auth/ForceChangePassword');
    }

    /**
     * Proses penggantian password pertama kali
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'password' => $this->passwordRules(),
        ]);

        $user = $request->user();

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'password_changed_at' => now(),
            'status_approval' => UserAccountStatus::Activated->value,
            'is_active' => true,
        ])->save();

        // Assign default module roles if none set yet
        if (! UserModuleRole::where('user_id', '=', $user->id, 'and')->exists()) {
            $user->assignDefaultModuleRoles();
        }

        // Selesai! Redirect ke dashboard
        return redirect()->route('dashboard')->with('success', 'Password berhasil dibuat. Selamat datang!');
    }

    /**
     * Show the waiting room view (Inertia page).
     */
    public function showWaitingRoom(Request $request)
    {
        $user = $request->user();

        // Get details of the registration request if exists
        $regRequest = RegistrationRequest::where('created_user_id', '=', $user->id, 'and')->first();

        // Optional extra details
        $extra = [];
        if ($regRequest) {
            if ($regRequest->role === 'alumni') {
                $prodi = ProgramStudi::find($regRequest->program_studi_id);
                $extra = [
                    'Program Studi' => $prodi ? $prodi->nama : '-',
                    'Tahun Lulus' => $regRequest->tahun_lulus ?? '-',
                ];
            } elseif ($regRequest->role === 'mitra') {
                $extra = [
                    'Nama Perusahaan' => $regRequest->nama_perusahaan ?? '-',
                    'Nomor Telepon' => $regRequest->phone ?? '-',
                ];
            }
        }

        return Inertia::render('auth/WaitingRoom', [
            'status' => $user->status_approval,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->user_type,
            'identifier' => $user->nomor_induk ?? ($regRequest?->student_number ?? $regRequest?->employee_number ?? '-'),
            'extra' => $extra,
            'flash' => session('status'),
        ]);
    }

    /**
     * API endpoint to poll user's status_approval.
     */
    public function checkApprovalStatus(Request $request)
    {
        return response()->json([
            'status_approval' => $request->user()->status_approval,
        ]);
    }

    /**
     * Let rejected users resign (delete their temporary account & request so they can re-register).
     *
     * [FIX M-8] Sebelumnya: hanya logout dari sesi saat ini. Jika user memiliki
     * tab/device lain yang masih login, sesi tersebut tetap aktif padahal akun
     * sudah dihapus dari database. Ini bisa menyebabkan error atau akses ghost.
     *
     * Sekarang: revoke semua AuthSession di DB sebelum menghapus akun,
     * sehingga semua device otomatis ter-kick saat request berikutnya.
     */
    public function resign(Request $request)
    {
        $user = $request->user();

        if ($user->status_approval !== UserAccountStatus::Rejected) {
            return back()->withErrors(['error' => 'Hanya pendaftaran yang ditolak yang dapat dibatalkan.']);
        }

        // [FIX M-8] Revoke semua AuthSession milik user ini sebelum hapus akun
        // Ini memastikan semua device lain ter-kick saat request berikutnya
        AuthSession::where('user_id', $user->id)->update(['is_revoked' => true]);

        // Delete RegistrationRequest linked to this user
        RegistrationRequest::where('created_user_id', '=', $user->id, 'and')->delete();

        // Delete user
        $user->delete();

        // Log out dari sesi saat ini
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('register')->with('status', 'Pendaftaran sebelumnya telah dibatalkan. Silakan lakukan pendaftaran ulang.');
    }
}
