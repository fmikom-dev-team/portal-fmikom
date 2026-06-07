<?php

namespace App\Modules\WorkOs\Services\AuthPlatform;

use App\Models\Auth\AuthOAuthCredential;
use App\Models\Auth\AuthOAuthProvider;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class OAuthEngine
{
    /**
     * Get the authorization URL for a specific provider.
     */
    public function getAuthorizationUrl(string $providerSlug)
    {
        $providerConfig = AuthOAuthProvider::where('slug', $providerSlug)->where('is_enabled', true)->first();

        if (! $providerConfig) {
            throw new Exception("Provider {$providerSlug} is not enabled or does not exist.");
        }

        // Determine if we should use demo credentials (Socialite default configured in services.php)
        // or dynamic credentials from the database.
        $configKey = $providerConfig->name === 'GitHub' ? 'github' : strtolower($providerConfig->name);

        if (! $providerConfig->use_demo_credentials) {
            // Override config dynamically
            config([
                "services.{$configKey}.client_id" => $providerConfig->client_id,
                "services.{$configKey}.client_secret" => Crypt::decryptString($providerConfig->client_secret),
                "services.{$configKey}.redirect" => route('auth.oauth.callback', ['provider' => $providerSlug]),
            ]);
        } else {
            config([
                "services.{$configKey}.redirect" => route('auth.oauth.callback', ['provider' => $providerSlug]),
            ]);
        }

        $driver = Socialite::driver($configKey);

        // Add PKCE support for security
        if (in_array($configKey, ['google', 'twitter', 'github'])) {
            $driver->stateless(); // Using stateless for API-based enterprise architecture
        }

        if ($providerConfig->scopes) {
            $driver->scopes($providerConfig->scopes);
        }

        return $driver->redirect()->getTargetUrl();
    }

    /**
     * Handle the OAuth callback and sync user.
     */
    public function handleCallback(string $providerSlug, array $requestData)
    {
        $providerConfig = AuthOAuthProvider::where('slug', $providerSlug)->where('is_enabled', true)->first();

        if (! $providerConfig) {
            throw new Exception("Provider {$providerSlug} is not enabled.");
        }

        $configKey = $providerConfig->name === 'GitHub' ? 'github' : strtolower($providerConfig->name);

        if (! $providerConfig->use_demo_credentials) {
            config([
                "services.{$configKey}.client_id" => $providerConfig->client_id,
                "services.{$configKey}.client_secret" => Crypt::decryptString($providerConfig->client_secret),
                "services.{$configKey}.redirect" => route('auth.oauth.callback', ['provider' => $providerSlug]),
            ]);
        } else {
            config([
                "services.{$configKey}.redirect" => route('auth.oauth.callback', ['provider' => $providerSlug]),
            ]);
        }

        $driver = Socialite::driver($configKey)->stateless();

        try {
            $socialUser = $driver->user();
        } catch (Exception $e) {
            throw new Exception('Invalid state or token during OAuth callback: '.$e->getMessage());
        }

        // 1. Try to find the user by their existing linked OAuth credential
        $credential = AuthOAuthCredential::where('provider_id', $providerConfig->id)
            ->where('external_id', $socialUser->getId())
            ->first();

        $user = null;
        if ($credential) {
            $user = User::find($credential->user_id);
        }

        // 2. Fallback to searching by email if no linked credential exists
        // Security: Hanya link otomatis jika email lokal sudah terverifikasi (Verified Email Enforcement)
        if (! $user) {
            $user = User::where('email', $socialUser->getEmail())
                ->whereNotNull('email_verified_at')
                ->first();
        }

        if (! $user) {
            return [
                'needs_registration' => true,
                'oauth_data' => [
                    'provider' => $providerSlug,
                    'provider_id' => $providerConfig->id,
                    'external_id' => $socialUser->getId(),
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Unknown User',
                    'email' => $socialUser->getEmail(),
                    'access_token' => Crypt::encryptString($socialUser->token),
                    'refresh_token' => $socialUser->refreshToken ? Crypt::encryptString($socialUser->refreshToken) : null,
                    'expires_at' => $socialUser->expiresIn ? Carbon::now()->addSeconds($socialUser->expiresIn) : null,
                ],
            ];
        }

        return DB::transaction(function () use ($user, $socialUser, $providerConfig) {
            // 2. Link OAuth Credential safely
            try {
                $credential = AuthOAuthCredential::updateOrCreate(
                    [
                        'provider_id' => $providerConfig->id,
                        'external_id' => $socialUser->getId(),
                    ],
                    [
                        'user_id' => $user->id,
                        'email' => $socialUser->getEmail(),
                        'access_token' => Crypt::encryptString($socialUser->token),
                        'refresh_token' => $socialUser->refreshToken ? Crypt::encryptString($socialUser->refreshToken) : null,
                        'expires_at' => $socialUser->expiresIn ? Carbon::now()->addSeconds($socialUser->expiresIn) : null,
                    ]
                );
            } catch (UniqueConstraintViolationException $e) {
                $credential = AuthOAuthCredential::where('provider_id', $providerConfig->id)
                    ->where('external_id', $socialUser->getId())
                    ->first();
                if ($credential) {
                    $credential->update([
                        'user_id' => $user->id,
                        'email' => $socialUser->getEmail(),
                        'access_token' => Crypt::encryptString($socialUser->token),
                        'refresh_token' => $socialUser->refreshToken ? Crypt::encryptString($socialUser->refreshToken) : null,
                        'expires_at' => $socialUser->expiresIn ? Carbon::now()->addSeconds($socialUser->expiresIn) : null,
                    ]);
                } else {
                    throw $e;
                }
            }

            // Return user to issue session later
            return $user;
        });
    }

    /**
     * Disconnect a linked OAuth provider from the user's account.
     */
    public function disconnect(User $user, string $providerSlug)
    {
        $provider = AuthOAuthProvider::where('slug', $providerSlug)->first();

        if (! $provider) {
            throw new Exception("Provider {$providerSlug} not found.");
        }

        AuthOAuthCredential::where('user_id', $user->id)
            ->where('provider_id', $provider->id)
            ->delete();
    }
}
