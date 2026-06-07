<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Password Policies
        Schema::create('auth_password_policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('organization_id')->nullable()->index(); // Null = global policy
            $table->integer('min_length')->default(10);
            $table->boolean('require_uppercase')->default(true);
            $table->boolean('require_lowercase')->default(true);
            $table->boolean('require_numbers')->default(true);
            $table->boolean('require_symbols')->default(true);
            $table->boolean('reject_breached')->default(true); // check HaveIBeenPwned
            $table->integer('password_history_count')->default(5); // prevent reuse
            $table->integer('expiration_days')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Auth Devices (Fingerprinting)
        Schema::create('auth_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('device_fingerprint')->index();
            $table->string('os')->nullable();
            $table->string('browser')->nullable();
            $table->boolean('is_trusted')->default(false);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
        });

        // 3. Sessions Management
        Schema::create('auth_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id')->index();
            $table->uuid('device_id')->nullable()->index();
            $table->string('session_token')->unique();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('geolocation')->nullable(); // lat, lng, city, country
            $table->boolean('is_revoked')->default(false);
            $table->integer('risk_score')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('last_activity_at');
            $table->timestamps();
        });

        // 4. MFA Configurations
        Schema::create('auth_mfa', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id')->index();
            $table->enum('type', ['totp', 'sms', 'email']);
            $table->text('secret'); // Encrypted
            $table->boolean('is_active')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('auth_backup_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('code_hash'); // Hashed like password
            $table->boolean('is_used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        // 5. Passkeys / WebAuthn
        Schema::create('auth_passkeys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('credential_id')->unique();
            $table->text('public_key');
            $table->string('name')->nullable(); // "iPhone", "YubiKey"
            $table->bigInteger('sign_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. OAuth Providers Configuration (Platform level)
        Schema::create('auth_oauth_providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // Google, GitHub
            $table->string('slug')->unique();
            $table->boolean('is_enabled')->default(true);
            $table->string('client_id')->nullable();
            $table->text('client_secret')->nullable(); // Encrypted
            $table->json('scopes')->nullable();
            $table->boolean('use_demo_credentials')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // 7. OAuth Credentials (User level bindings)
        Schema::create('auth_oauth_credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id')->index();
            $table->uuid('provider_id')->index();
            $table->string('external_id')->index();
            $table->string('email')->nullable();
            $table->text('access_token')->nullable(); // Encrypted
            $table->text('refresh_token')->nullable(); // Encrypted
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->unique(['provider_id', 'external_id']);
        });

        // 8. Magic Links
        Schema::create('auth_magic_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('email')->index();
            $table->string('token_hash')->unique();
            $table->boolean('is_used')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        // 9. SSO Connections (SAML / OIDC per Organization)
        Schema::create('auth_sso_connections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('organization_id')->index();
            $table->string('name');
            $table->enum('type', ['saml', 'oidc']);
            $table->boolean('is_active')->default(false);
            
            // SAML fields
            $table->string('idp_entity_id')->nullable();
            $table->string('sso_url')->nullable();
            $table->text('x509_cert')->nullable();
            
            // OIDC fields
            $table->string('issuer_url')->nullable();
            $table->string('client_id')->nullable();
            $table->text('client_secret')->nullable(); // Encrypted
            
            $table->json('domain_mappings')->nullable(); // e.g. ["example.com"]
            $table->timestamps();
            $table->softDeletes();
        });

        // 10. Login Attempts (Brute Force Protection)
        Schema::create('auth_login_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->index();
            $table->string('ip_address', 45)->index();
            $table->boolean('is_successful');
            $table->string('failure_reason')->nullable();
            $table->integer('risk_score')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_login_attempts');
        Schema::dropIfExists('auth_sso_connections');
        Schema::dropIfExists('auth_magic_links');
        Schema::dropIfExists('auth_oauth_credentials');
        Schema::dropIfExists('auth_oauth_providers');
        Schema::dropIfExists('auth_passkeys');
        Schema::dropIfExists('auth_backup_codes');
        Schema::dropIfExists('auth_mfa');
        Schema::dropIfExists('auth_sessions');
        Schema::dropIfExists('auth_devices');
        Schema::dropIfExists('auth_password_policies');
    }
};
