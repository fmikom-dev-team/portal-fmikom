<?php

namespace App\Providers;

use App\Models\Auth\AuthSetting;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Models\Tracer\ActivityLog;

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
            URL::forceScheme('https');
        }

        // Authorization Gate for Laravel Pulse
        Gate::define('viewPulse', function ($user) {
            return method_exists($user, 'isSuperAdmin') && ($user->isSuperAdmin() || $user->isAdmin());
        });

        // Register Tracer Policies explicitly due to sub-namespace auto-discovery limitation
        Gate::policy(\App\Models\Tracer\CareerHistory::class, \App\Policies\CareerHistoryPolicy::class);

        // ── Pagi Chat Rate Limiting (Flood Prevention) ─────────────────────────
        RateLimiter::for('pagi-chat-send', function ($request) {
            return Limit::perMinute(30)->by($request->user()->id);
        });

        // Load migrations from subdirectories
        $mainPath = database_path('migrations');
        if (is_dir($mainPath)) {
            $directories = glob($mainPath.'/*', GLOB_ONLYDIR);
            $paths = array_merge([$mainPath], $directories);
            $this->loadMigrationsFrom($paths);
        }

        // ── Activity Log: Auth Events ─────────────────────────────────────────
        Event::listen(Login::class, function (Login $event) {
            ActivityLog::create([
                'user_id' => $event->user->id,
                'action' => 'auth.login',
                'description' => 'Login ke sistem',
                'ip_address' => request()->ip(),
            ]);
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                ActivityLog::create([
                    'user_id' => $event->user->id,
                    'action' => 'auth.logout',
                    'description' => 'Logout dari sistem',
                    'ip_address' => request()->ip(),
                ]);
            }
        });
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

            if ((bool) AuthSetting::get('password.reject_breached', false)) {
                $rule->uncompromised();
            }

            return $rule;
        });
    }
}
