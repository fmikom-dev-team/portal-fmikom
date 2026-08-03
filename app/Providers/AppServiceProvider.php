<?php

namespace App\Providers;

use App\Models\Auth\AuthEmailLog;
use App\Models\Auth\AuthLoginAttempt;
use App\Models\Auth\AuthSession;
use App\Models\Auth\AuthSetting;
use App\Models\JenisSurat;
use App\Models\Portal\PortalSetting;
use App\Models\Surat;
use App\Models\SuratCategory;
use App\Models\TemplateGlobalSetting;
use App\Models\Tracer\ActivityLog;
use App\Models\Tracer\CareerHistory;
use App\Models\User;
use App\Modules\WorkOs\Services\AuditLogger;
use App\Modules\WorkOs\Services\AuthPlatform\SessionEngine;
use App\Policies\CareerHistoryPolicy;
use App\Policies\FastJenisSuratPolicy;
use App\Policies\FastSuratCategoryPolicy;
use App\Policies\FastSuratPolicy;
use App\Policies\FastTemplateGlobalSettingPolicy;
use App\Services\Auth\ActivationService;
use App\Services\Auth\MagicLinkService;
use App\Services\Auth\OtpService;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
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
        if (class_exists(TelescopeApplicationServiceProvider::class)) {
            $this->app->register(TelescopeServiceProvider::class);
        }

        $this->app->singleton(OtpService::class);
        $this->app->singleton(ActivationService::class);
        $this->app->singleton(MagicLinkService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureDynamicMailSettings();
        $this->registerLoginEvents();
        $this->registerLogoutEvents();
        $this->registerSecurityEvents();
        $this->registerEmailEvents();
        $this->registerSecurityAndGates();
    }

    protected function configureDynamicMailSettings(): void
    {
        try {
            if (! Schema::hasTable('portal_settings')) {
                return;
            }

            $settings = cache()->remember('portal_settings', 86400, function () {
                return PortalSetting::query()->pluck('value', 'key')->toArray();
            });

            if (empty($settings['smtp_host'])) {
                return;
            }

            $host = $settings['smtp_host'];
            $port = (int) ($settings['smtp_port'] ?? 587);
            $sender = $settings['smtp_sender'] ?? config('mail.from.address');
            $encryption = $settings['smtp_encryption'] ?? 'tls';
            if ($encryption === 'none') {
                $encryption = null;
            }
            $username = $settings['smtp_username'] ?? null;
            $rawPassword = $settings['smtp_password'] ?? null;
            $password = null;

            if ($rawPassword) {
                if (str_starts_with($rawPassword, 'base64:')) {
                    try {
                        $password = Crypt::decryptString(substr($rawPassword, 7));
                    } catch (\Throwable $e) {
                        $password = $rawPassword;
                    }
                } else {
                    $password = $rawPassword;
                }
            }

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => $port,
                'mail.mailers.smtp.encryption' => $encryption,
                'mail.mailers.smtp.username' => $username,
                'mail.mailers.smtp.password' => $password,
                'mail.from.address' => $sender,
                'mail.from.name' => $settings['brand_name'] ?? config('mail.from.name', 'Portal FMIKOM'),
            ]);
        } catch (\Throwable $e) {
            // Silently fall back to default mail configuration if database is unreachable
        }
    }

    protected function resolveAuthProvider(): string
    {
        if (request()->is('auth/oauth/*')) {
            $provider = request()->segment(3);

            return ($provider === 'register' || ! $provider) ? 'oauth' : (string) $provider;
        }
        if (request()->is('passkeys/*')) {
            return 'passkey';
        }
        if (request()->is('sso/*')) {
            return 'sso';
        }

        return 'password';
    }

    protected function registerLoginEvents(): void
    {
        Event::listen(Login::class, function ($event) {
            $email = $event->user->email;
            $ip = request()->ip();

            $exists = AuthLoginAttempt::query()->where('email', $email)
                ->where('ip_address', $ip)
                ->where('is_successful', true)
                ->where('created_at', '>=', now()->subSeconds(2))
                ->exists();

            if (! $exists) {
                AuthLoginAttempt::create([
                    'email' => $email,
                    'ip_address' => $ip,
                    'is_successful' => true,
                    'provider' => $this->resolveAuthProvider(),
                ]);
            }

            AuditLogger::log('user.signed_in', 'info', [
                'device' => request()->userAgent(),
            ], $event->user);

            if ($event->user && request()->hasSession() && ! session('auth_session_token')) {
                try {
                    $sessionEngine = app(SessionEngine::class);
                    $authSession = $sessionEngine->createSession($event->user, request());
                    session(['auth_session_token' => $authSession->id]);
                } catch (\Throwable $e) {
                    Log::error('[AppServiceProvider] Failed to create AuthSession on login: '.$e->getMessage());
                }
            }
        });

        Event::listen(Login::class, function (Login $event) {
            if (! Schema::hasTable('activity_logs')) {
                return;
            }

            try {
                /** @phpstan-ignore-next-line */
                $userId = $event->user ? $event->user->getAuthIdentifier() : null;
                if (! $userId) {
                    return;
                }

                ActivityLog::create([
                    'user_id' => $userId,
                    'action' => 'auth.login',
                    'description' => 'Login ke sistem',
                    'ip_address' => request()->ip(),
                ]);
            } catch (\Throwable $e) {
                Log::error('[AppServiceProvider] Failed to log activity on login: '.$e->getMessage());
            }
        });
    }

    protected function registerLogoutEvents(): void
    {
        Event::listen(Logout::class, function (Logout $event) {
            try {
                $token = session('auth_session_token');
                if ($token) {
                    AuthSession::query()->where('id', $token)->update(['is_revoked' => true]);
                }
            } catch (\Throwable $e) {
                Log::error('[AppServiceProvider] Failed to revoke session on logout: '.$e->getMessage());
            }

            if (! Schema::hasTable('activity_logs')) {
                return;
            }

            try {
                /** @phpstan-ignore-next-line */
                $userId = $event->user ? $event->user->getAuthIdentifier() : null;
                if (! $userId) {
                    return;
                }

                ActivityLog::create([
                    'user_id' => $userId,
                    'action' => 'auth.logout',
                    'description' => 'Logout dari sistem',
                    'ip_address' => request()->ip(),
                ]);
            } catch (\Throwable $e) {
                Log::error('[AppServiceProvider] Failed to log activity on logout: '.$e->getMessage());
            }
        });
    }

    protected function registerSecurityEvents(): void
    {
        Event::listen(Failed::class, function ($event) {
            $email = $event->credentials['email'] ?? ($event->credentials['username'] ?? 'unknown');

            AuthLoginAttempt::create([
                'email' => $email,
                'ip_address' => request()->ip(),
                'is_successful' => false,
                'failure_reason' => 'invalid_credentials',
                'provider' => $this->resolveAuthProvider(),
            ]);

            AuditLogger::log('user.login_failed', 'warning', [
                'email' => $email,
                'device' => request()->userAgent(),
            ]);
        });

        Event::listen(PasswordReset::class, function ($event) {
            try {
                AuthSession::where('user_id', $event->user->id)
                    ->where('is_revoked', false)
                    ->update(['is_revoked' => true]);
            } catch (\Throwable $e) {
                Log::error('[PasswordReset] Gagal merevoke AuthSession: '.$e->getMessage(), [
                    'user_id' => $event->user->id,
                ]);
            }

            AuditLogger::log('user.password_reset', 'info', [
                'device' => request()->userAgent(),
            ], $event->user);
        });

        Event::listen(Registered::class, function ($event) {
            AuditLogger::log('user.registered', 'info', [
                'device' => request()->userAgent(),
            ], $event->user);
        });
    }

    protected function registerEmailEvents(): void
    {
        Event::listen(MessageSent::class, function ($event) {
            $message = $event->message;
            $toAddresses = $message->getTo();

            foreach ($toAddresses as $address) {
                $email = $address->getAddress();
                $user = User::query()->whereRaw('LOWER(email) = ?', [strtolower($email)])->first();

                $body = '';
                if (method_exists($message, 'getHtmlBody') && $message->getHtmlBody()) {
                    $body = $message->getHtmlBody();
                } elseif (method_exists($message, 'getTextBody') && $message->getTextBody()) {
                    $body = $message->getTextBody();
                } elseif (method_exists($message, 'getBody') && $message->getBody()) {
                    $body = $message->getBody()->toString();
                }

                AuthEmailLog::create([
                    'user_id' => $user ? $user->id : null,
                    'email' => $email,
                    'subject' => $message->getSubject() ?? '(No Subject)',
                    'body' => $body,
                    'status' => 'Delivered',
                ]);
            }
        });
    }

    protected function registerSecurityAndGates(): void
    {
        if (! in_array(config('app.env'), ['local', 'testing'])) {
            URL::forceScheme('https');
        }

        Gate::define('viewPulse', function ($user) {
            return method_exists($user, 'isSuperAdmin') && ($user->isSuperAdmin() || $user->isAdmin());
        });

        if (class_exists(Livewire::class) && ! app()->runningInConsole() && request()->is(config('pulse.path', 'pulse').'*')) {
            Livewire::forceAssetInjection();
        }

        Gate::policy(CareerHistory::class, CareerHistoryPolicy::class);
        Gate::policy(Surat::class, FastSuratPolicy::class);
        Gate::policy(JenisSurat::class, FastJenisSuratPolicy::class);
        Gate::policy(SuratCategory::class, FastSuratCategoryPolicy::class);
        Gate::policy(TemplateGlobalSetting::class, FastTemplateGlobalSettingPolicy::class);

        RateLimiter::for('pagi-chat-send', function ($request) {
            return Limit::perMinute(30)->by($request->user()->id);
        });

        RateLimiter::for('pagi-api', function ($request) {
            $rateLimit = (int) (PortalSetting::query()->where('key', 'pagi_rate_limit_per_minute')->value('value') ?? 60);

            return Limit::perMinute($rateLimit)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('uploads', function ($request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        $mainPath = database_path('migrations');
        if (is_dir($mainPath)) {
            $directories = glob($mainPath.'/*', GLOB_ONLYDIR);
            $paths = array_merge([$mainPath], $directories);
            $this->loadMigrationsFrom($paths);
        }

        $mailPassword = config('mail.mailers.smtp.password');
        if (is_string($mailPassword) && str_starts_with($mailPassword, 'base64:')) {
            try {
                $decrypted = Crypt::decryptString(substr($mailPassword, 7));
                config(['mail.mailers.smtp.password' => $decrypted]);
            } catch (\Throwable $e) {
                Log::error('SMTP password decryption failed: '.$e->getMessage());
            }
        }
    }

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

            if ((bool) AuthSetting::get('password.reject_breached', false)) {
                $rule->uncompromised();
            }

            return $rule;
        });
    }
}
