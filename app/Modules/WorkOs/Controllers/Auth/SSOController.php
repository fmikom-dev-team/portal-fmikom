<?php

namespace App\Modules\WorkOs\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Stub — implement with your SSO service (e.g., OneLogin\Saml2)
 */
class SSOController extends Controller
{
    public function samlMetadata()
    {
        return response('<!-- SP Metadata -->', 200)->header('Content-Type', 'application/xml');
    }

    public function samlAcs(Request $request)
    { /* Process SAML assertion */
    }

    public function samlSls(Request $request)
    { /* Single Logout */
    }

    public function oidcCallback(Request $request)
    { /* OIDC token exchange */
    }

    public function initiate(string $connection)
    { /* Redirect to IdP */
    }

    public function index()
    {
        return response()->json(['connections' => []]);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Created']);
    }

    public function update(Request $request, $connection)
    {
        return response()->json(['message' => 'Updated']);
    }

    public function destroy($connection)
    {
        return response()->json(['message' => 'Deleted']);
    }
}
