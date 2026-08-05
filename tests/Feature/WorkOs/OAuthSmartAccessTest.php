<?php

namespace Tests\Feature\WorkOs;

use App\Enums\RegistrationStatus;
use App\Enums\UserAccountStatus;
use App\Mail\OAuthActivationEmail;
use App\Models\Auth\RegistrationRequest;
use App\Models\User;
use App\Services\Auth\ActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OAuthSmartAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_oauth_registration_request_queues_oauth_activation_email(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            'user_type' => 'super_admin',
            'is_active' => true,
            'status_approval' => UserAccountStatus::Activated->value,
        ]);

        $regRequest = RegistrationRequest::create([
            'full_name' => 'OAuth User Test',
            'email' => 'oauthuser@example.com',
            'role' => 'alumni',
            'status' => RegistrationStatus::Pending->value,
            'oauth_data' => [
                'provider' => 'google',
                'provider_id' => 1,
                'external_id' => '123456789',
                'name' => 'OAuth User Test',
                'email' => 'oauthuser@example.com',
            ],
            'ip_address' => '127.0.0.1',
        ]);

        $activationService = app(ActivationService::class);
        $user = $activationService->approveRegistrationRequest($regRequest, $admin->id);

        $this->assertFalse($user->is_active);
        $this->assertEquals('oauthuser@example.com', $user->email);

        Mail::assertQueued(OAuthActivationEmail::class, function ($mail) {
            return $mail->userEmail === 'oauthuser@example.com' && str_contains($mail->activationUrl, 'auth/oauth/verify-access');
        });
    }

    public function test_smart_access_verification_page_renders_for_valid_signed_request(): void
    {
        $user = User::factory()->create([
            'name' => 'Google User',
            'email' => 'googleuser@example.com',
            'is_active' => false,
            'status_approval' => UserAccountStatus::Approved->value,
        ]);

        $regRequest = RegistrationRequest::create([
            'full_name' => 'Google User',
            'email' => 'googleuser@example.com',
            'role' => 'alumni',
            'status' => RegistrationStatus::Approved->value,
            'created_user_id' => $user->id,
            'oauth_data' => [
                'provider' => 'google',
                'provider_id' => 1,
                'external_id' => 'google_123',
                'name' => 'Google User',
                'email' => 'googleuser@example.com',
            ],
            'ip_address' => '127.0.0.1',
        ]);

        $token = $regRequest->generateActivationToken();

        $signedUrl = URL::temporarySignedRoute(
            'auth.oauth.verify_access',
            now()->addHours(24),
            [
                'token' => $token,
                'request_id' => $regRequest->id,
            ]
        );

        $response = $this->get($signedUrl);

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('auth/SmartAccessVerification')
            ->where('isValid', true)
            ->where('userData.name', 'Google User')
            ->where('userData.provider', 'google')
        );
    }

    public function test_smart_access_verification_submit_activates_user_and_creates_session(): void
    {
        $user = User::factory()->create([
            'name' => 'Microsoft User',
            'email' => 'msuser@example.com',
            'is_active' => false,
            'status_approval' => UserAccountStatus::Approved->value,
        ]);

        $regRequest = RegistrationRequest::create([
            'full_name' => 'Microsoft User',
            'email' => 'msuser@example.com',
            'role' => 'alumni',
            'status' => RegistrationStatus::Approved->value,
            'created_user_id' => $user->id,
            'oauth_data' => [
                'provider' => 'microsoft',
                'provider_id' => 2,
                'external_id' => 'ms_123',
                'name' => 'Microsoft User',
                'email' => 'msuser@example.com',
            ],
            'ip_address' => '127.0.0.1',
        ]);

        $token = $regRequest->generateActivationToken();

        $signedUrl = URL::temporarySignedRoute(
            'auth.oauth.verify_access.submit',
            now()->addHours(24),
            [
                'token' => $token,
                'request_id' => $regRequest->id,
            ]
        );

        $response = $this->postJson($signedUrl, [
            'request_id' => $regRequest->id,
            'token' => $token,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'redirect_url' => '/dashboard',
        ]);

        $user->refresh();
        $this->assertTrue($user->is_active);
        $this->assertEquals(UserAccountStatus::Activated, $user->status_approval);
        $this->assertAuthenticatedAs($user);
    }

    public function test_smart_access_verification_works_with_signed_url_even_if_token_query_is_omitted(): void
    {
        $user = User::factory()->create([
            'name' => 'GitHub User',
            'email' => 'githubuser@example.com',
            'is_active' => false,
            'status_approval' => UserAccountStatus::Approved->value,
        ]);

        $regRequest = RegistrationRequest::create([
            'full_name' => 'GitHub User',
            'email' => 'githubuser@example.com',
            'role' => 'alumni',
            'status' => RegistrationStatus::Approved->value,
            'created_user_id' => $user->id,
            'oauth_data' => [
                'provider' => 'github',
                'provider_id' => 3,
                'external_id' => 'gh_123',
                'name' => 'GitHub User',
                'email' => 'githubuser@example.com',
            ],
            'ip_address' => '127.0.0.1',
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'auth.oauth.verify_access',
            now()->addHours(24),
            [
                'request_id' => $regRequest->id,
            ]
        );

        $response = $this->get($signedUrl);

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('auth/SmartAccessVerification')
            ->where('isValid', true)
            ->where('userData.name', 'GitHub User')
            ->where('userData.provider', 'github')
        );
    }
}
