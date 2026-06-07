<?php

namespace App\Modules\WorkOs\Services\Pipes;

use App\Models\Pipes\PipeProvider;
use App\Models\Pipes\PipeConnection;
use App\Models\Pipes\PipeConnectionToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OAuthEngine
{
    /**
     * Generate Authorization URL
     */
    public function getAuthorizationUrl(PipeProvider $provider, string $redirectUri, array $scopes = [], $state = null)
    {
        $state = $state ?? Str::random(40);

        // Store state in cache to verify later
        Cache::put("oauth_state_{$state}", true, now()->addMinutes(15));

        $scopeStr = implode(' ', $scopes);
        
        $query = http_build_query([
            'client_id' => $provider->client_id,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $scopeStr,
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'consent',
        ]);

        return $provider->auth_url . '?' . $query;
    }

    /**
     * Handle OAuth Callback and exchange code for tokens
     */
    public function handleCallback(PipeProvider $provider, string $code, string $redirectUri, string $state)
    {
        if (!Cache::pull("oauth_state_{$state}")) {
            throw new \Exception("Invalid or expired OAuth state.");
        }

        $response = Http::asForm()->post($provider->token_url, [
            'grant_type' => 'authorization_code',
            'client_id' => $provider->client_id,
            'client_secret' => $provider->client_secret,
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        if ($response->failed()) {
            throw new \Exception("OAuth token exchange failed: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Refresh Access Token
     */
    public function refreshToken(PipeConnection $connection)
    {
        $provider = $connection->provider;
        $activeToken = $connection->getActiveToken();

        if (!$activeToken || !$activeToken->refresh_token) {
            throw new \Exception("No refresh token available.");
        }

        $response = Http::asForm()->post($provider->token_url, [
            'grant_type' => 'refresh_token',
            'client_id' => $provider->client_id,
            'client_secret' => $provider->client_secret,
            'refresh_token' => $activeToken->refresh_token,
        ]);

        if ($response->failed()) {
            $connection->update(['status' => 'expired', 'health_status' => 'failing']);
            throw new \Exception("OAuth token refresh failed: " . $response->body());
        }

        $data = $response->json();

        PipeConnectionToken::create([
            'connection_id' => $connection->id,
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $activeToken->refresh_token, // Sometimes providers don't return a new refresh token
            'expires_at' => isset($data['expires_in']) ? now()->addSeconds($data['expires_in']) : null,
            'token_type' => $data['token_type'] ?? 'Bearer',
        ]);

        $connection->update(['status' => 'connected', 'health_status' => 'healthy']);

        return $data;
    }
}
