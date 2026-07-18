<?php

use App\Models\User;
use App\Models\Auth\AuthOtpToken;
use App\Models\Auth\AuthMagicLink;
use App\Models\Magang\PendaftaranMagang;
use App\Enums\UserAccountStatus;
use App\Enums\OtpPurpose;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Features;

test('BL-01: MFA challenge fails if no login session is active in request session', function () {
    $user = User::factory()->create([
        'is_active' => true,
        'status_approval' => UserAccountStatus::Activated,
    ]);

    // Send code acting as the user (to pass auth middleware), but NO login.id in session
    $response = $this->actingAs($user)->postJson(route('auth.mfa.challenge'), [
        'code' => '123456',
    ]);

    // Should return 403 Forbidden because there is no pending login.id session
    $response->assertStatus(403);
    $response->assertJsonPath('error', 'Sesi login tidak ditemukan atau kedaluwarsa.');
});

test('BL-02: Passkey authentication enforces account active status check', function () {
    $user = User::factory()->create([
        'is_active' => true,
        'status_approval' => UserAccountStatus::Suspended,
    ]);

    // Mock passkeyEngine to return this suspended user
    $mockEngine = Mockery::mock(\App\Modules\WorkOs\Services\AuthPlatform\PasskeyEngine::class);
    $mockEngine->shouldReceive('verifyAuthentication')->andReturn($user);
    $this->app->instance(\App\Modules\WorkOs\Services\AuthPlatform\PasskeyEngine::class, $mockEngine);

    $response = $this->postJson(route('auth.passkeys.auth.verify'), [
        'id' => 'dummy',
    ]);

    $response->assertStatus(403);
    $response->assertJsonPath('error', 'Akun Anda telah ditangguhkan. Hubungi administrator.');
});

test('BL-03: Two-factor challenge re-validates account active status', function () {
    $this->skipUnlessFortifyFeature(Features::twoFactorAuthentication());

    $user = User::factory()->create([
        'is_active' => true,
        'status_approval' => UserAccountStatus::Activated,
    ]);

    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    // Set login.id session, then suspend the user
    $this->withSession(['login.id' => $user->id]);
    $user->update(['status_approval' => UserAccountStatus::Suspended]);

    $response = $this->post('/two-factor-challenge', [
        'code' => '123456',
    ]);

    $response->assertSessionHasErrors('code');
    $this->assertGuest();
});

test('BL-04: Magic link verification locks link record during verification', function () {
    $user = User::factory()->create([
        'is_active' => true,
        'status_approval' => UserAccountStatus::Activated,
    ]);

    $plainToken = 'secret-token-123';
    $link = AuthMagicLink::create([
        'user_id' => $user->id,
        'email' => $user->email,
        'token_hash' => hash('sha256', $plainToken),
        'is_used' => false,
        'expires_at' => now()->addMinutes(15),
    ]);

    $service = app(\App\Services\Auth\MagicLinkService::class);
    $resolvedUser = $service->verify($user->email, $plainToken);

    expect($resolvedUser->id)->toBe($user->id);
    expect($link->fresh()->is_used)->toBeTrue();
});

test('BL-06: resubmitRevision prevents updating if registration is not in revisi status', function () {
    $user = User::factory()->create();
    $pendaftaran = PendaftaranMagang::create([
        'mahasiswa_id' => $user->id,
        'status' => 'approved', // already approved, not revisi!
        'tanggal_mulai' => now()->toDateString(),
        'tanggal_selesai' => now()->addMonths(3)->toDateString(),
    ]);

    $service = app(\App\Modules\Wims\Services\Mahasiswa\Registration\StudentRegistrationActionService::class);

    $this->expectException(\Illuminate\Validation\ValidationException::class);
    $service->resubmitRevision($pendaftaran, [
        'catatan_pengajuan' => 'Re-submitting',
    ]);
});

test('BL-07: password reset updates password_changed_at and sets status_approval to Activated', function () {
    $user = User::factory()->create([
        'is_active' => true,
        'status_approval' => UserAccountStatus::OtpVerified,
        'password_changed_at' => null,
    ]);

    $action = app(\App\Actions\Fortify\ResetUserPassword::class);
    $action->reset($user, [
        'password' => 'BaruPass123!@#',
        'password_confirmation' => 'BaruPass123!@#',
    ]);

    $user->refresh();
    expect($user->password_changed_at)->not->toBeNull();
    expect($user->status_approval)->toBe(UserAccountStatus::Activated);
});

test('BL-08: pruneExpired keeps used OTPs for 24 hours', function () {
    $user = User::factory()->create();

    // OTP 1: Used 5 minutes ago
    $otp1 = AuthOtpToken::create([
        'user_id' => $user->id,
        'email' => $user->email,
        'token_hash' => hash('sha256', '111111'),
        'purpose' => OtpPurpose::AccountActivation->value,
        'is_used' => true,
        'used_at' => now()->subMinutes(5),
        'expires_at' => now()->addMinutes(10),
    ]);

    // OTP 2: Used 25 hours ago
    $otp2 = AuthOtpToken::create([
        'user_id' => $user->id,
        'email' => $user->email,
        'token_hash' => hash('sha256', '222222'),
        'purpose' => OtpPurpose::AccountActivation->value,
        'is_used' => true,
        'used_at' => now()->subHours(25),
        'expires_at' => now()->subHours(24)->addMinutes(10),
    ]);

    $service = app(\App\Services\Auth\OtpService::class);
    $service->pruneExpired();

    expect(AuthOtpToken::find($otp1->id))->not->toBeNull(); // retained!
    expect(AuthOtpToken::find($otp2->id))->toBeNull();     // pruned!
});
