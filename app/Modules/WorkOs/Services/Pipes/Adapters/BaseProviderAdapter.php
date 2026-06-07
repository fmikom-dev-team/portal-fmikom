<?php

namespace App\Modules\WorkOs\Services\Pipes\Adapters;

use App\Models\Pipes\PipeConnection;
use Illuminate\Support\Facades\Http;
use App\Modules\WorkOs\Services\Pipes\OAuthEngine;

abstract class BaseProviderAdapter implements ProviderAdapterInterface
{
    protected $oauthEngine;

    public function __construct(OAuthEngine $oauthEngine)
    {
        $this->oauthEngine = $oauthEngine;
    }

    /**
     * Create an HTTP client configured with the connection's access token.
     * Automatically handles token refresh if necessary.
     */
    protected function getHttpClient(PipeConnection $connection)
    {
        $token = $connection->getActiveToken();
        
        // If token is expired or close to expiring (within 5 minutes), refresh it
        if (!$token || ($token->expires_at && $token->expires_at->subMinutes(5)->isPast())) {
            $tokenData = $this->oauthEngine->refreshToken($connection);
            $accessToken = $tokenData['access_token'];
        } else {
            $accessToken = $token->access_token;
        }

        return Http::withToken($accessToken)
                   ->baseUrl($connection->provider->api_base_url)
                   ->timeout(30)
                   ->retry(3, 100); // Built-in simple retry for transient errors
    }

    /**
     * Process raw items into a standard internal format
     */
    abstract protected function transformItem(array $rawItem): array;
}
