<?php

namespace App\Providers;

use App\Actions\Auth\RedirectIfMfaRequired;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Enums\UserAccountStatus;
use App\Http\Responses\CustomLogoutResponse;
use App\Models\Auth\AuthOAuthProvider;
use App\Models\Auth\AuthSetting;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Ganti LogoutResponse default Fortify dengan versi kustom
        // yang selalu menghapus remember cookie tanpa syarat
        $this->app->singleton(LogoutResponse::class, CustomLogoutResponse::class);

        // Bind custom RegisterResponse to prevent auto-login on registration
        $this->app->singleton(
            RegisterResponse::class,
            \App\Http\Responses\RegisterResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', '=', $request->email, 'and')
                ->orWhere('nomor_induk', '=', $request->email, 'and')
                ->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return null; // Wrong credentials — Fortify handles generic error
            }

            // ── Account Lifecycle Check ──────────────────────────────────────
            // [FIX MED-01] Semua kasus blocked login menggunakan pesan GENERIC yang sama
            // dengan "wrong credentials" untuk mencegah account enumeration.
            // Attacker tidak dapat membedakan antara: akun tidak exist / password salah /
            // akun suspended / akun belum diaktifkan.
            if (! $user->is_active) {
                throw ValidationException::withMessages([
                    Fortify::username() => __('auth.failed'),
                ]);
            }

            if ($user->status_approval !== UserAccountStatus::Activated) {
                // Khusus status "in activation" (OtpSent, OtpVerified, Approved):
                // Berikan hint sedikit lebih deskriptif agar user tahu perlu aktivasi,
                // tanpa mengekspos keberadaan akun secara umum.
                if ($user->isInActivation()) {
                    throw ValidationException::withMessages([
                        Fortify::username() => 'Akun Anda sedang dalam proses aktivasi. Silakan cek email Anda.',
                    ]);
                }

                // Rejected, Suspended, Pending, atau status lainnya:
                // Pesan generic — tidak mengungkapkan status detail akun.
                throw ValidationException::withMessages([
                    Fortify::username() => __('auth.failed'),
                ]);
            }

            return $user;
        });

        Fortify::authenticateThrough(function (Request $request) {
            return array_filter([
                config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
                AttemptToAuthenticate::class,
                RedirectIfMfaRequired::class, // Custom MFA interceptor
                PrepareAuthenticatedSession::class,
            ]);
        });
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn (Request $request) => Inertia::render('auth/Login', [
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'canRegister' => Features::enabled(Features::registration()),
            'status' => $request->session()->get('status'),
            'error' => $request->session()->get('error'),
            'oauthProviders' => AuthOAuthProvider::where('is_enabled', '=', true, 'and')->get(['name', 'slug']),
            'passkeysEnabled' => (bool) AuthSetting::get('passkeys.enabled', true),
        ]));

        Fortify::resetPasswordView(fn (Request $request) => Inertia::render('auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]));

        Fortify::requestPasswordResetLinkView(fn (Request $request) => Inertia::render('auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::verifyEmailView(fn (Request $request) => Inertia::render('auth/VerifyEmail', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::registerView(fn () => Inertia::render('auth/Register', [
            'allowedRoles' => ['mitra', 'alumni'],
            'note' => 'Mahasiswa, Dosen, dan Staff: gunakan halaman Aktivasi Akun.',
        ]));

        Fortify::twoFactorChallengeView(fn () => Inertia::render('auth/TwoFactorChallenge'));

        Fortify::confirmPasswordView(fn () => Inertia::render('auth/ConfirmPassword'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            if (app()->environment('testing') && ! app()->runningUnitTests()) {
                return Limit::none();
            }

            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            if (app()->environment('testing') && ! app()->runningUnitTests()) {
                return Limit::none();
            }
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
