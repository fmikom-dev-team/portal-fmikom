<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // auth_settings — stores all toggleable auth platform configuration
        if (! Schema::hasTable('auth_settings')) {
            Schema::create('auth_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('type')->default('boolean'); // boolean, string, integer, json
                $table->string('group')->default('general'); // login_methods, providers, mfa, session
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // auth_magic_links — one-time sign-in tokens
        if (! Schema::hasTable('auth_magic_links')) {
            Schema::create('auth_magic_links', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->unsignedBigInteger('user_id')->nullable()->index(); // null if email not yet registered
                $table->string('email')->index();
                $table->string('token', 64)->unique(); // SHA-256 random token
                $table->string('token_hash', 128)->index(); // Hashed version for DB lookup
                $table->boolean('is_used')->default(false);
                $table->timestamp('used_at')->nullable();
                $table->timestamp('expires_at');
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
                $table->index(['email', 'is_used', 'expires_at']); // Composite for lookup
            });
        } else {
            Schema::table('auth_magic_links', function (Blueprint $table) {
                if (! Schema::hasColumn('auth_magic_links', 'used_at')) {
                    $table->timestamp('used_at')->nullable()->after('is_used');
                }
                if (! Schema::hasColumn('auth_magic_links', 'token')) {
                    $table->string('token', 64)->nullable()->after('email');
                }
                if (! Schema::hasColumn('auth_magic_links', 'user_agent')) {
                    $table->text('user_agent')->nullable()->after('ip_address');
                }
            });
        }

        // auth_password_histories — prevent password reuse
        if (! Schema::hasTable('auth_password_histories')) {
            Schema::create('auth_password_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('password_hash'); // bcrypt
                $table->timestamps();
                $table->index(['user_id', 'created_at']); // Quick lookup of N recent passwords
            });
        }

        // auth_audit_logs — immutable audit trail
        if (! Schema::hasTable('auth_audit_logs')) {
            Schema::create('auth_audit_logs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('event'); // login.success, mfa.enabled, session.revoked, etc.
                $table->string('actor_type')->default('user'); // user, system, admin
                $table->string('ip_address', 45)->nullable()->index();
                $table->text('user_agent')->nullable();
                $table->json('metadata')->nullable(); // Contextual data per event type
                $table->string('severity')->default('info'); // info, warning, critical
                $table->timestamp('occurred_at')->useCurrent();
                // Audit logs are NEVER updated or deleted — no timestamps(), no softDeletes()
                $table->index(['event', 'occurred_at']);
                $table->index(['user_id', 'occurred_at']);
                $table->index(['severity', 'occurred_at']);
            });
        }

        // Seed default auth settings
        $now = now();
        if (DB::table('auth_settings')->count() === 0) {
            DB::table('auth_settings')->insert([
                // Login Methods
                ['key' => 'email_password.enabled',          'value' => '1',  'type' => 'boolean', 'group' => 'login_methods', 'description' => 'Allow email + password login',        'created_at' => $now, 'updated_at' => $now],
                ['key' => 'email_password.min_length',        'value' => '10', 'type' => 'integer', 'group' => 'login_methods', 'description' => 'Minimum password length',             'created_at' => $now, 'updated_at' => $now],
                ['key' => 'email_password.complexity',        'value' => '3',  'type' => 'integer', 'group' => 'login_methods', 'description' => 'Password complexity level (1-5)',      'created_at' => $now, 'updated_at' => $now],
                ['key' => 'email_password.require_uppercase', 'value' => '0',  'type' => 'boolean', 'group' => 'login_methods', 'description' => 'Require at least one uppercase letter', 'created_at' => $now, 'updated_at' => $now],
                ['key' => 'email_password.require_lowercase', 'value' => '0',  'type' => 'boolean', 'group' => 'login_methods', 'description' => 'Require at least one lowercase letter', 'created_at' => $now, 'updated_at' => $now],
                ['key' => 'email_password.require_number',    'value' => '0',  'type' => 'boolean', 'group' => 'login_methods', 'description' => 'Require at least one number',            'created_at' => $now, 'updated_at' => $now],
                ['key' => 'email_password.require_special',   'value' => '0',  'type' => 'boolean', 'group' => 'login_methods', 'description' => 'Require at least one special character',   'created_at' => $now, 'updated_at' => $now],
                ['key' => 'passkeys.enabled',                 'value' => '1',  'type' => 'boolean', 'group' => 'login_methods', 'description' => 'Allow passkey (WebAuthn) login',      'created_at' => $now, 'updated_at' => $now],
                ['key' => 'magic_links.enabled',              'value' => '1',  'type' => 'boolean', 'group' => 'login_methods', 'description' => 'Allow magic link email login',        'created_at' => $now, 'updated_at' => $now],
                ['key' => 'sso.enabled',                      'value' => '1',  'type' => 'boolean', 'group' => 'login_methods', 'description' => 'Allow SSO (SAML/OIDC) login',        'created_at' => $now, 'updated_at' => $now],
                ['key' => 'mfa.required',                     'value' => '0',  'type' => 'boolean', 'group' => 'mfa',           'description' => 'Require MFA for all users',           'created_at' => $now, 'updated_at' => $now],
                ['key' => 'mfa.totp.enabled',                 'value' => '1',  'type' => 'boolean', 'group' => 'mfa',           'description' => 'Allow TOTP authenticator app',        'created_at' => $now, 'updated_at' => $now],
                ['key' => 'mfa.sms.enabled',                  'value' => '0',  'type' => 'boolean', 'group' => 'mfa',           'description' => 'Allow SMS-based OTP',                 'created_at' => $now, 'updated_at' => $now],
                // Session
                ['key' => 'session.ttl_days',                 'value' => '7',  'type' => 'integer', 'group' => 'session',       'description' => 'Session expiration in days',          'created_at' => $now, 'updated_at' => $now],
                ['key' => 'session.inactivity_minutes',       'value' => '60', 'type' => 'integer', 'group' => 'session',       'description' => 'Inactivity timeout in minutes',       'created_at' => $now, 'updated_at' => $now],
                ['key' => 'session.concurrent_limit',         'value' => '5',  'type' => 'integer', 'group' => 'session',       'description' => 'Max concurrent sessions per user',    'created_at' => $now, 'updated_at' => $now],
                // Password Policies
                ['key' => 'password.reject_breached',         'value' => '1',  'type' => 'boolean', 'group' => 'password',      'description' => 'Reject breached passwords via HIBP',  'created_at' => $now, 'updated_at' => $now],
                ['key' => 'password.history_count',           'value' => '5',  'type' => 'integer', 'group' => 'password',      'description' => 'Prevent reuse of last N passwords',   'created_at' => $now, 'updated_at' => $now],
                ['key' => 'password.expiration_days',         'value' => '90', 'type' => 'integer', 'group' => 'password',      'description' => 'Password expiration in days (0=never)', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        // Seed default OAuth providers (all from WorkOS reference)
        if (DB::table('auth_oauth_providers')->count() === 0) {
            DB::table('auth_oauth_providers')->insert([
                ['id' => Str::uuid(), 'name' => 'Google',     'slug' => 'google',     'is_enabled' => false, 'use_demo_credentials' => true,  'created_at' => $now, 'updated_at' => $now],
                ['id' => Str::uuid(), 'name' => 'Microsoft',  'slug' => 'microsoft',  'is_enabled' => false, 'use_demo_credentials' => true,  'created_at' => $now, 'updated_at' => $now],
                ['id' => Str::uuid(), 'name' => 'GitHub',     'slug' => 'github',     'is_enabled' => false, 'use_demo_credentials' => true,  'created_at' => $now, 'updated_at' => $now],
                ['id' => Str::uuid(), 'name' => 'Apple',      'slug' => 'apple',      'is_enabled' => false, 'use_demo_credentials' => true,  'created_at' => $now, 'updated_at' => $now],
                ['id' => Str::uuid(), 'name' => 'GitLab',     'slug' => 'gitlab',     'is_enabled' => false, 'use_demo_credentials' => false, 'created_at' => $now, 'updated_at' => $now],
                ['id' => Str::uuid(), 'name' => 'LinkedIn',   'slug' => 'linkedin',   'is_enabled' => false, 'use_demo_credentials' => false, 'created_at' => $now, 'updated_at' => $now],
                ['id' => Str::uuid(), 'name' => 'Salesforce', 'slug' => 'salesforce', 'is_enabled' => false, 'use_demo_credentials' => false, 'created_at' => $now, 'updated_at' => $now],
                ['id' => Str::uuid(), 'name' => 'Slack',      'slug' => 'slack',      'is_enabled' => false, 'use_demo_credentials' => false, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_audit_logs');
        Schema::dropIfExists('auth_password_histories');
        Schema::dropIfExists('auth_magic_links');
        Schema::dropIfExists('auth_settings');
    }
};
