<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\WorkOs\Services\AuthPlatform\OAuthEngine;
use App\Modules\WorkOs\Services\AuthPlatform\SessionEngine;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OAuthController extends Controller
{
    protected OAuthEngine $oauthEngine;

    protected SessionEngine $sessionEngine;

    public function __construct(OAuthEngine $oauthEngine, SessionEngine $sessionEngine)
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

            // Store opaque token (model UUID) in session for SecureSession middleware
            $request->session()->put('auth_session_token', $session->id);

            // Redirect back to the frontend application (or dashboard)
            return redirect('/workos/dashboard?login=success&session_token='.$session->session_token);
        } catch (Exception $e) {
            // Redirect to frontend with error
            return redirect('/workos/dashboard?error=oauth_failed&message='.urlencode($e->getMessage()));
        }
    }
}
