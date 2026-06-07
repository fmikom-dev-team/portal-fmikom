<?php

namespace App\Modules\WorkOs\Services\OAuth;

use App\Models\Auth\AuthOAuthCredential;
use App\Models\Auth\AuthOAuthProvider;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

/**
 * TokenRefreshService — Background OAuth Token Refresh
 *
 * Responsible for:
 *   1. Detecting credentials that are expired or near expiry
 *   2. Calling each provider's token refresh endpoint
 *   3. Updating the encrypted token in `auth_oauth_credentials`
 *   4. Revoking credentials that can no longer be refreshed
 *
 * Invoked by:
 *   - Scheduled Horizon job: App\Jobs\RefreshExpiredOAuthTokens (every 30 min)
 *   - On-demand when a credential is about to be used
 */
class TokenRefreshService
{
    /** Refresh credentials expiring within the next N minutes */
    protected int $refreshWindowMinutes = 10;

    // ─────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Refresh a single credential by its model.
     * Returns the updated credential or throws on failure.
     */
    public function refresh(AuthOAuthCredential $credential): AuthOAuthCredential
    {
        $provider = $credential->provider;

        if (! $provider) {
            throw new Exception("Provider config not found for credential {$credential->id}.");
        }

        $refreshToken = $this->decryptRefreshToken($credential);

        if (! $refreshToken) {
            throw new Exception("No refresh token stored for credential {$credential->id}.");
        }

        $newTokens = $this->callRefreshEndpoint($provider, $refreshToken);

        return $this->persistNewTokens($credential, $newTokens);
    }

    /**
     * Find and refresh all credentials expiring soon.
     * Called by the scheduled job.
     *
     * @return array{refreshed: int, failed: int, skipped: int}
     */
    public function refreshExpiring(): array
    {
        $stats = ['refreshed' => 0, 'failed' => 0, 'skipped' => 0];

        $expiring = AuthOAuthCredential::with('provider')
            ->whereNotNull('refresh_token')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '<=', Carbon::now()->addMinutes($this->refreshWindowMinutes));
            })
            ->get();

        foreach ($expiring as $credential) {
            try {
                $this->refresh($credential);
                $stats['refreshed']++;
            } catch (Exception $e) {
                logger()->warning("OAuth token refresh failed for credential {$credential->id}: ".$e->getMessage());
                $stats['failed']++;
            }
        }

        return $stats;
    }

    /**
     * Get a valid (non-expired) access token for a credential.
     * Automatically refreshes if near expiry.
     */
    public function getValidAccessToken(AuthOAuthCredential $credential): string
    {
        $isExpired = $credential->expires_at && Carbon::now()->addMinutes(2)->isAfter($credential->expires_at);

        if ($isExpired && $credential->refresh_token) {
            $credential = $this->refresh($credential);
        }

        return Crypt::decryptString($credential->access_token);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Provider-specific Refresh Calls
    // ─────────────────────────────────────────────────────────────────────────

    protected function callRefreshEndpoint(AuthOAuthProvider $provider, string $refreshToken): array
    {
        $endpoint = $this->resolveTokenEndpoint($provider->name);

        $clientId = $provider->client_id ?? config("services.{$provider->slug}.client_id");
        $clientSecret = $provider->client_secret
            ? Crypt::decryptString($provider->client_secret)
            : config("services.{$provider->slug}.client_secret");

        $response = Http::asForm()->post($endpoint, [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if (! $response->successful()) {
            throw new Exception("Token refresh HTTP error {$response->status()} for provider {$provider->name}: ".$response->body());
        }

        $data = $response->json();

        if (! isset($data['access_token'])) {
            throw new Exception("Token endpoint did not return access_token for {$provider->name}.");
        }

        return $data;
    }

    protected function resolveTokenEndpoint(string $providerName): string
    {
        return match (strtolower($providerName)) {
            'google' => 'https://oauth2.googleapis.com/token',
            'github' => 'https://github.com/login/oauth/access_token',
            'microsoft' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'apple' => 'https://appleid.apple.com/auth/token',
            'linkedin' => 'https://www.linkedin.com/oauth/v2/accessToken',
            'slack' => 'https://slack.com/api/oauth.v2.access',
            default => throw new Exception("Unknown OAuth provider: {$providerName}. Add its token endpoint to TokenRefreshService."),
        };
    }

    protected function persistNewTokens(AuthOAuthCredential $credential, array $tokens): AuthOAuthCredential
    {
        $data = [
            'access_token' => Crypt::encryptString($tokens['access_token']),
        ];

        // Not all providers return a new refresh token (keep old if missing)
        if (! empty($tokens['refresh_token'])) {
            $data['refresh_token'] = Crypt::encryptString($tokens['refresh_token']);
        }

        if (! empty($tokens['expires_in'])) {
            $data['expires_at'] = Carbon::now()->addSeconds((int) $tokens['expires_in']);
        }

        $credential->update($data);

        return $credential->fresh();
    }

    protected function decryptRefreshToken(AuthOAuthCredential $credential): ?string
    {
        if (! $credential->refresh_token) {
            return null;
        }

        try {
            return Crypt::decryptString($credential->refresh_token);
        } catch (Exception) {
            return null;
        }
    }
}
