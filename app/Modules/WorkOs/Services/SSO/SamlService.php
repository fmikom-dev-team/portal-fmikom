<?php

namespace App\Modules\WorkOs\Services\SSO;

use App\Models\User;
use App\Models\Auth\AuthSsoConnection;
use App\Modules\WorkOs\Services\Auth\LoginService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Exception;

/**
 * SamlService — Enterprise SAML 2.0 Integration
 *
 * Handles:
 *   1. SP Metadata generation
 *   2. AuthRequest construction (IdP redirect)
 *   3. Assertion Consumer Service (ACS) — parses and validates IdP response
 *   4. Attribute mapping (email, name, groups from SAML attributes)
 *   5. Just-in-time (JIT) user provisioning
 *   6. Single Logout (SLO)
 *
 * Architecture Note:
 *   This service wraps the raw SAML XML parsing. In production, delegate
 *   the cryptographic heavy lifting to `onelogin/php-saml` or
 *   `lightsaml/lightsaml`. The structure here ensures you can swap the
 *   underlying library without touching controllers or the login flow.
 *
 * Install: composer require onelogin/php-saml
 */
class SamlService
{
    public function __construct(
        protected LoginService $loginService,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // SP Metadata
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Generate XML SP metadata for IdP registration.
     * IdP admins download this to configure their side.
     */
    public function generateMetadata(): string
    {
        $acsUrl      = route('auth.sso.saml.acs');
        $slsUrl      = route('auth.sso.saml.sls');
        $entityId    = config('app.url');
        $spName      = config('app.name');

        // In production: use OneLogin\Saml2\Settings::getSPMetadata()
        return <<<XML
<?xml version="1.0"?>
<md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata"
    entityID="{$entityId}">
  <md:SPSSODescriptor
      AuthnRequestsSigned="false"
      WantAssertionsSigned="true"
      protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
    <md:NameIDFormat>urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress</md:NameIDFormat>
    <md:AssertionConsumerService
        Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
        Location="{$acsUrl}"
        index="1" />
    <md:SingleLogoutService
        Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
        Location="{$slsUrl}" />
  </md:SPSSODescriptor>
</md:EntityDescriptor>
XML;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // IdP Redirect
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Build the SSO redirect URL to the IdP's authentication endpoint.
     * Called when user clicks "Sign in with SSO" for a given connection.
     */
    public function buildAuthRequest(AuthSsoConnection $connection): string
    {
        $relayState = Str::random(32);

        // Cache relay state for CSRF/replay protection (5 minutes)
        Cache::put("sso_relay_{$relayState}", $connection->id, now()->addMinutes(5));

        $requestId  = '_' . Str::uuid()->toString();
        $issueInstant = now()->toAtomString();
        $acsUrl     = route('auth.sso.saml.acs');
        $entityId   = config('app.url');

        $ssoUrl = $connection->metadata['sso_url'] ?? null;

        if (!$ssoUrl) {
            throw new Exception("SSO URL not configured for connection {$connection->id}.");
        }

        // In production: use OneLogin\Saml2\AuthnRequest to properly sign this
        $authRequest = base64_encode(gzdeflate(<<<XML
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="{$requestId}"
    Version="2.0"
    IssueInstant="{$issueInstant}"
    AssertionConsumerServiceURL="{$acsUrl}"
    DestinationURL="{$ssoUrl}">
  <saml:Issuer>{$entityId}</saml:Issuer>
</samlp:AuthnRequest>
XML));

        return $ssoUrl . '?' . http_build_query([
            'SAMLRequest' => $authRequest,
            'RelayState'  => $relayState,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACS — Assertion Consumer Service
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Process the SAML Response POSTed by the IdP.
     * Returns a User (JIT provisioned if needed).
     *
     * IMPORTANT: This method must validate:
     *   - Signature on the SAMLResponse
     *   - Assertion validity window (NotBefore / NotOnOrAfter)
     *   - Audience restriction (EntityID must match ours)
     *   - InResponseTo (ties assertion to our AuthnRequest)
     */
    public function processAcsResponse(Request $request): User
    {
        $samlResponse = $request->input('SAMLResponse');
        $relayState   = $request->input('RelayState');

        if (!$samlResponse) {
            throw new Exception('No SAMLResponse in request.');
        }

        // Validate relay state (replay attack prevention)
        if ($relayState) {
            $connectionId = Cache::pull("sso_relay_{$relayState}");
            if (!$connectionId) {
                throw new Exception('Invalid or expired SSO relay state.');
            }
        }

        // Decode and parse — in production: OneLogin\Saml2\Response
        $decoded = base64_decode($samlResponse);

        // STUB: Extract attributes from XML
        // In production, the library handles signature verification + attribute extraction.
        $attributes = $this->extractAttributesStub($decoded);

        $email = $attributes['email'] ?? null;

        if (!$email) {
            throw new Exception('SAML assertion did not contain an email attribute.');
        }

        // JIT Provisioning
        return $this->jitProvision($email, $attributes);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Single Logout
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Handle SLO (Single Logout) request from IdP.
     */
    public function processSlo(Request $request): void
    {
        // Decode SAMLRequest (IdP-initiated SLO)
        // Revoke all sessions for the affected user
        // In production: OneLogin\Saml2\LogoutRequest
    }

    // ─────────────────────────────────────────────────────────────────────────
    // JIT User Provisioning
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Find or create a user from SAML attributes.
     * This is the enterprise "Just-in-Time" provisioning pattern.
     */
    protected function jitProvision(string $email, array $attributes): User
    {
        return DB::transaction(function () use ($email, $attributes) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'             => $attributes['name'] ?? $attributes['first_name'] . ' ' . ($attributes['last_name'] ?? ''),
                    'password'         => Hash::make(Str::random(32)), // Passwordless SSO account
                    'email_verified_at' => now(),
                ]
            );

            return $user;
        });
    }

    /**
     * Stub attribute extraction (replace with library parsing).
     */
    protected function extractAttributesStub(string $xml): array
    {
        // In production: parse NameID + AttributeStatement from verified XML
        return [
            'email'      => null,
            'name'       => null,
            'first_name' => null,
            'last_name'  => null,
            'groups'     => [],
        ];
    }
}
