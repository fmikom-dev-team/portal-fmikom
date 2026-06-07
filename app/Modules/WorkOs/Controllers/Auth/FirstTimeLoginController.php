<?php

namespace App\Modules\WorkOs\Controllers\Auth;

use App\Concerns\PasswordValidationRules;
use App\Http\Controllers\Controller;
use App\Mail\SendOtpEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class FirstTimeLoginController extends Controller
{
    use PasswordValidationRules;

    /**
     * Tampilkan halaman Verifikasi OTP
     */
    public function showOtpForm(Request $request)
    {
        $user = $request->user();

        // Jika tidak ada OTP atau sudah kadaluarsa, generate baru otomatis
        if (! $user->otp_code || now()->isAfter($user->otp_expires_at)) {
            $this->generateAndSendOtp($user);
        }

        return Inertia::render('auth/VerifyOtp', [
            'email' => $user->email,
            'expiresAt' => $user->otp_expires_at, // Untuk timer JS
            'status' => session('status'),
        ]);
    }

    /**
     * Verifikasi kode OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        // Bandingkan OTP menggunakan Hash::check() karena OTP disimpan ter-hash
        if (! Hash::check($request->otp, $user->otp_code)) {
            return back()->withErrors(['otp' => 'Kode OTP salah atau tidak valid.']);
        }

        if (now()->isAfter($user->otp_expires_at)) {
            return back()->withErrors(['otp' => 'Kode OTP sudah kadaluarsa. Silakan minta ulang.']);
        }

        // Lulus! Update waktu email_verified_at dan aktifkan akun
        $user->forceFill([
            'email_verified_at' => now(),
            'is_active' => true,
            'otp_code' => null,
            'otp_expires_at' => null,
        ])->save();

        // Jika status masih pending → ke waiting room
        // Jika sudah approved (mis. admin-created) → lanjut ke force change password
        if ($user->status_approval === 'pending') {
            return redirect()->route('waiting.room')->with('status', 'Email berhasil diverifikasi! Pendaftaran Anda sedang diproses oleh admin.');
        }

        // Lanjut ke step berikutnya (force change password jika admin-created)
        return redirect()->route('password.force.change');
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        $user = $request->user();

        // Cegah spam resend
        if ($user->otp_expires_at && now()->diffInMinutes($user->otp_expires_at) > 13) {
            return back()->withErrors(['otp' => 'Tunggu beberapa saat sebelum mengirim ulang.']);
        }

        $this->generateAndSendOtp($user);

        return back()->with('status', 'Kode OTP baru telah dikirimkan ke email Anda.');
    }

    /**
     * Tampilkan form Buat Password Baru
     */
    public function showPasswordForm(Request $request)
    {
        // Pastikan email sudah terverifikasi via OTP
        if (is_null($request->user()->email_verified_at)) {
            return redirect()->route('verify.otp');
        }

        // Jika self-registered user (sudah punya password_changed_at), skip force change
        if (! is_null($request->user()->password_changed_at)) {
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
        ])->save();

        // Selesai! Redirect ke dashboard
        return redirect()->route('dashboard');
    }

    /**
     * Helper logic untuk generate 6-digit OTP dan mengirimnya
     */
    private function generateAndSendOtp($user)
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->forceFill([
            // ⚠️ OTP di-hash sebelum disimpan ke DB — tidak boleh plaintext
            'otp_code' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(15),
        ])->save();

        try {
            Mail::to($user->email)->queue(new SendOtpEmail($user, $otp));
        } catch (\Exception $e) {
            Log::error('Gagal mengirim email OTP ke '.$user->email.': '.$e->getMessage());
        }

        // ⚠️ KEAMANAN: OTP tidak pernah di-log sebagai plaintext di production
        if (app()->isLocal()) {
            Log::info("[DEV ONLY] OTP untuk {$user->email}: {$otp}");
        }
    }
}
