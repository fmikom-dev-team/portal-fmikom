<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        // Force HTTPS in non-local environments to avoid mixed content issues
        if (config('app.env') !== 'local') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Authorization Gate for Laravel Pulse
        \Illuminate\Support\Facades\Gate::define('viewPulse', function ($user) {
            return method_exists($user, 'isSuperAdmin') && ($user->isSuperAdmin() || $user->isAdmin());
        });

        // ── Pagi Chat Rate Limiting (Flood Prevention) ─────────────────────────
        RateLimiter::for('pagi-chat-send', function ($request) {
            return Limit::perMinute(30)->by($request->user()->id);
        });

        // Load migrations from subdirectories
        $mainPath = database_path('migrations');
        if (is_dir($mainPath)) {
            $directories = glob($mainPath . '/*', GLOB_ONLYDIR);
            $paths = array_merge([$mainPath], $directories);
            $this->loadMigrationsFrom($paths);
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
            $min = (int) \App\Models\Auth\AuthSetting::get('email_password.min_length', 10);
            $rule = Password::min($min);

            $requireUppercase = (bool) \App\Models\Auth\AuthSetting::get('email_password.require_uppercase', false);
            $requireLowercase = (bool) \App\Models\Auth\AuthSetting::get('email_password.require_lowercase', false);
            $requireNumber    = (bool) \App\Models\Auth\AuthSetting::get('email_password.require_number', false);
            $requireSpecial   = (bool) \App\Models\Auth\AuthSetting::get('email_password.require_special', false);

            if ($requireUppercase || $requireLowercase) {
                $rule->mixedCase();
            }

            if ($requireNumber) {
                $rule->numbers();
            }

            if ($requireSpecial) {
                $rule->symbols();
            }

            if ((bool) \App\Models\Auth\AuthSetting::get('password.reject_breached', true)) {
                $rule->uncompromised();
            }

            return $rule;
        });
    }
}
