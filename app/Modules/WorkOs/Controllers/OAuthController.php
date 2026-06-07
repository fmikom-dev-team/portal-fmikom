<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthPlatform\OAuthEngine;
use Exception;
use Illuminate\Support\Facades\Auth;

class OAuthController extends Controller
{
    protected OAuthEngine $oauthEngine;
    protected \App\Services\AuthPlatform\SessionEngine $sessionEngine;

    public function __construct(OAuthEngine $oauthEngine, \App\Services\AuthPlatform\SessionEngine $sessionEngine)
    {
        $this->oauthEngine = $oauthEngine;
        $this->sessionEngine = $sessionEngine;
    }

    /**
     * Redirect the user to the provider's OAuth page.
     */
    public function redirect(string $provider)
    {
        try {
            $url = $this->oauthEngine->getAuthorizationUrl($provider);
            return response()->json(['authorization_url' => $url]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Handle the callback from the provider.
     */
    public function callback(string $provider, Request $request)
    {
        try {
            $user = $this->oauthEngine->handleCallback($provider, $request->all());
            
            // Standard login
            Auth::login($user);

            // Enterprise Session Management: create an auth_sessions record with risk score
            $session = $this->sessionEngine->createSession($user, $request);

            // Redirect back to the frontend application (or dashboard)
            return redirect('/workos/dashboard?login=success&session_token=' . $session->session_token);

        } catch (Exception $e) {
            // Redirect to frontend with error
            return redirect('/workos/dashboard?error=oauth_failed&message=' . urlencode($e->getMessage()));
        }
    }
}
