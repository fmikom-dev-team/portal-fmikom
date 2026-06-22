<?php

namespace App\Providers;

use App\Models\Auth\AuthEmailLog;
use App\Models\Auth\AuthLoginAttempt;
use App\Models\Auth\AuthSession;
use App\Models\Auth\AuthSetting;
use App\Models\User;
use App\Modules\WorkOs\Services\AuditLogger;
use App\Modules\WorkOs\Services\AuthPlatform\SessionEngine;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Telescope\TelescopeApplicationServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Telescope only in non-production or when the package is installed (dev dependency).
        // In production Docker builds, Telescope is excluded via --no-dev, so we guard with class_exists.
        if (class_exists(TelescopeApplicationServiceProvider::class)) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        // ── Auth Event Listeners for Audit Logging & Session Tracking ─────────────
        Event::listen(Login::class, function ($event) {
            $email = $event->user->email;
            $ip = request()->ip();

            $exists = AuthLoginAttempt::where('email', $email)
                ->where('ip_address', $ip)
                ->where('is_successful', true)
                ->where('created_at', '>=', now()->subSeconds(2))
                ->exists();

            if (! $exists) {
                $provider = 'password';
                if (request()->is('auth/oauth/*')) {
                    $provider = request()->segment(3);
                    if ($provider === 'register' || ! $provider) {
                        $provider = 'oauth';
                    }
                } elseif (request()->is('passkeys/*')) {
                    $provider = 'passkey';
                } elseif (request()->is('sso/*')) {
                    $provider = 'sso';
                }

                AuthLoginAttempt::create([
                    'email' => $email,
                    'ip_address' => $ip,
                    'is_successful' => true,
                    'provider' => $provider,
                ]);
            }

            // Create enterprise auth session for standard/credentials logins (other logins create this manually)
            $isSpecialLogin = request()->is('auth/oauth/*') || request()->is('passkeys/*') || request()->is('sso/*');
            if (! $isSpecialLogin) {
                if (! session()->has('auth_session_token')) {
                    $sessionEngine = app(SessionEngine::class);
                    $authSession = $sessionEngine->createSession($event->user, request());
                    session(['auth_session_token' => $authSession->id]);
                }
            }

            AuditLogger::log('user.signed_in', 'info', [
                'device' => request()->userAgent(),
            ], $event->user);
        });

        Event::listen(Logout::class, function ($event) {
            $token = session('auth_session_token');
            if ($token) {
                AuthSession::where('id', $token)->update(['is_revoked' => true]);
            }
        });

        // ── Real Email Logging ─────────────────────────────────────────────────────
        Event::listen(MessageSent::class, function ($event) {
            $message = $event->message;
            $toAddresses = $message->getTo();

            foreach ($toAddresses as $address) {
                $email = $address->getAddress();

                // Find user by email (case-insensitive) to associate log correctly
                $user = User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
                if ($user) {
                    $body = '';
                    if (method_exists($message, 'getHtmlBody') && $message->getHtmlBody()) {
                        $body = $message->getHtmlBody();
                    } elseif (method_exists($message, 'getTextBody') && $message->getTextBody()) {
                        $body = $message->getTextBody();
                    } elseif (method_exists($message, 'getBody') && $message->getBody()) {
                        $body = $message->getBody()->toString();
                    }

                    AuthEmailLog::create([
                        'user_id' => $user->id,
                        'email' => $email,
                        'subject' => $message->getSubject() ?? '(No Subject)',
                        'body' => $body,
                        'status' => 'Delivered',
                    ]);
                }
            }
        });

        Event::listen(Failed::class, function ($event) {
            $email = $event->credentials['email'] ?? ($event->credentials['username'] ?? 'unknown');
            $ip = request()->ip();

            $provider = 'password';
            if (request()->is('auth/oauth/*')) {
                $provider = request()->segment(3);
                if ($provider === 'register' || ! $provider) {
                    $provider = 'oauth';
                }
            } elseif (request()->is('passkeys/*')) {
                $provider = 'passkey';
            } elseif (request()->is('sso/*')) {
                $provider = 'sso';
            }

            AuthLoginAttempt::create([
                'email' => $email,
                'ip_address' => $ip,
                'is_successful' => false,
                'failure_reason' => 'invalid_credentials',
                'provider' => $provider,
            ]);

            AuditLogger::log('user.login_failed', 'warning', [
                'email' => $email,
                'device' => request()->userAgent(),
            ]);
        });

        Event::listen(PasswordReset::class, function ($event) {
            AuditLogger::log('user.password_reset', 'info', [
                'device' => request()->userAgent(),
            ], $event->user);
        });

        Event::listen(Registered::class, function ($event) {
            AuditLogger::log('user.registered', 'info', [
                'device' => request()->userAgent(),
            ], $event->user);
        });

        // Force HTTPS in non-local environments to avoid mixed content issues
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        // Authorization Gate for Laravel Pulse
        Gate::define('viewPulse', function ($user) {
            return method_exists($user, 'isSuperAdmin') && ($user->isSuperAdmin() || $user->isAdmin());
        });

        // Force Livewire asset injection only on Pulse routes
        if (class_exists(Livewire::class) && ! app()->runningInConsole() && request()->is(config('pulse.path', 'pulse').'*')) {
            Livewire::forceAssetInjection();
        }

        // ── Pagi Chat Rate Limiting (Flood Prevention) ─────────────────────────
        RateLimiter::for('pagi-chat-send', function ($request) {
            return Limit::perMinute(30)->by($request->user()->id);
        });

        // ── File Upload Rate Limiting (Flood & DoS Prevention) ─────────────────
        RateLimiter::for('uploads', function ($request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Load migrations from subdirectories
        $mainPath = database_path('migrations');
        if (is_dir($mainPath)) {
            $directories = glob($mainPath.'/*', GLOB_ONLYDIR);
            $paths = array_merge([$mainPath], $directories);
            $this->loadMigrationsFrom($paths);
        }

        // Decrypt SMTP password dynamically if encrypted
        $mailPassword = config('mail.mailers.smtp.password');
        if (is_string($mailPassword) && str_starts_with($mailPassword, 'base64:')) {
            try {
                $decrypted = Crypt::decryptString(substr($mailPassword, 7));
                config(['mail.mailers.smtp.password' => $decrypted]);
            } catch (\Throwable $e) {
                \Log::error('SMTP password decryption failed: '.$e->getMessage());
            }
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(function () {
            $min = (int) AuthSetting::get('email_password.min_length', 10);
            $rule = Password::min($min);

            $requireUppercase = (bool) AuthSetting::get('email_password.require_uppercase', false);
            $requireLowercase = (bool) AuthSetting::get('email_password.require_lowercase', false);
            $requireNumber = (bool) AuthSetting::get('email_password.require_number', false);
            $requireSpecial = (bool) AuthSetting::get('email_password.require_special', false);

            if ($requireUppercase || $requireLowercase) {
                $rule->mixedCase();
            }

            if ($requireNumber) {
                $rule->numbers();
            }

            if ($requireSpecial) {
                $rule->symbols();
            }

            if ((bool) AuthSetting::get('password.reject_breached', true)) {
                $rule->uncompromised();
            }

            return $rule;
        });
    }
}
