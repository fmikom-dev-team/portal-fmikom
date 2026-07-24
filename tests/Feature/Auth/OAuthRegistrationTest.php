<?php

use App\Enums\RegistrationStatus;
use App\Enums\UserAccountStatus;
use App\Models\Auth\AuthOAuthCredential;
use App\Models\Auth\AuthOAuthProvider;
use App\Models\Fakultas;
use App\Models\Module;
use App\Models\ProgramStudi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

const GOOGLE_USER_NAME = 'Google User';
const GOOGLE_USER_EMAIL = 'googleuser@example.com';
const GOOGLE_NEW_EMAIL = 'googlenew@example.com';

beforeEach(function () {
    // Set up dummy roles, modules, and program studies if they don't exist
    Role::firstOrCreate(['slug' => 'mahasiswa'], ['nama' => 'Mahasiswa']);
    Role::firstOrCreate(['slug' => 'alumni'], ['nama' => 'Alumni']);
    Role::firstOrCreate(['slug' => 'mitra'], ['nama' => 'Mitra Perusahaan']);

    Module::firstOrCreate(['code' => 'FAST'], ['name' => 'FAST', 'is_active' => true]);
    Module::firstOrCreate(['code' => 'PAGI'], ['name' => 'PAGI', 'is_active' => true]);
    Module::firstOrCreate(['code' => 'WIMS'], ['name' => 'WIMS', 'is_active' => true]);
    Module::firstOrCreate(['code' => 'TRACE'], ['name' => 'TRACE', 'is_active' => true]);

    $fakultas = Fakultas::firstOrCreate(['kode' => 'FMIKOM'], ['nama' => 'FMIKOM']);
    ProgramStudi::firstOrCreate(['kode' => 'IF'], ['fakultas_id' => $fakultas->id, 'nama' => 'Informatika']);
    ProgramStudi::firstOrCreate(['kode' => 'SI'], ['fakultas_id' => $fakultas->id, 'nama' => 'Sistem Informasi']);

    AuthOAuthProvider::firstOrCreate(['slug' => 'google'], [
        'name' => 'Google',
        'is_enabled' => true,
        'client_id' => 'client_id',
        'client_secret' => Crypt::encryptString('client_secret'),
    ]);
});

test('oauth register screen redirects to login if no session data exists', function () {
    $response = $this->get(route('auth.oauth.register.view'));
    $response->assertRedirect(route('login'));
});

test('oauth register screen can be rendered with valid session data', function () {
    $oauthData = [
        'provider' => 'google',
        'provider_id' => 1,
        'external_id' => 'google-id-123',
        'name' => GOOGLE_USER_NAME,
        'email' => GOOGLE_USER_EMAIL,
        'access_token' => 'mock-token',
    ];

    $response = $this->withSession(['oauth_register_data' => $oauthData])
        ->get(route('auth.oauth.register.view'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('auth/OAuthRegister')
        ->has('oauthData')
        ->where('oauthData.name', GOOGLE_USER_NAME)
        ->where('oauthData.email', GOOGLE_USER_EMAIL)
    );
});

test('oauth registration stores user and links provider successfully', function () {
    $provider = AuthOAuthProvider::where('slug', 'google')->first();
    $oauthData = [
        'provider' => 'google',
        'provider_id' => $provider->id,
        'external_id' => 'google-id-123',
        'name' => GOOGLE_USER_NAME,
        'email' => GOOGLE_USER_EMAIL,
        'access_token' => 'mock-token',
    ];

    $response = $this->withSession(['oauth_register_data' => $oauthData])
        ->post(route('auth.oauth.register.store'), [
            'role' => 'mahasiswa',
            'nomor_induk' => '12345678',
            'program_studi_id' => ProgramStudi::where('kode', 'IF')->first()->id,
        ]);

    $this->assertGuest();

    $user = User::where('email', GOOGLE_USER_EMAIL)->first();
    expect($user)->not->toBeNull();
    expect($user->user_type)->toBe('mahasiswa');
    expect($user->status_approval)->toBe(UserAccountStatus::Pending);
    expect($user->is_active)->toBeFalse();

    // Check RegistrationRequest created
    $this->assertDatabaseHas('registration_requests', [
        'email' => GOOGLE_USER_EMAIL,
        'role' => 'mahasiswa',
        'status' => RegistrationStatus::Pending->value,
        'created_user_id' => $user->id,
    ]);

    // Check session data is cleared
    expect(session()->has('oauth_register_data'))->toBeFalse();

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status', function ($val) {
        return str_contains($val, 'sedang diproses oleh admin');
    });
});

test('oauth registration stores alumni and links provider successfully', function () {
    $provider = AuthOAuthProvider::where('slug', 'google')->first();
    $oauthData = [
        'provider' => 'google',
        'provider_id' => $provider->id,
        'external_id' => 'google-id-456',
        'name' => 'Google Alumni',
        'email' => 'googlealumni@example.com',
        'access_token' => 'mock-token',
    ];

    $response = $this->withSession(['oauth_register_data' => $oauthData])
        ->post(route('auth.oauth.register.store'), [
            'role' => 'alumni',
            'nomor_induk' => '87654321',
            'program_studi_id' => ProgramStudi::where('kode', 'IF')->first()->id,
            'tahun_lulus' => '2024',
        ]);

    $this->assertGuest();

    $user = User::where('email', 'googlealumni@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->user_type)->toBe('alumni');
    expect($user->status_approval)->toBe(UserAccountStatus::Pending);
    expect($user->is_active)->toBeFalse();

    // Check RegistrationRequest created
    $this->assertDatabaseHas('registration_requests', [
        'email' => 'googlealumni@example.com',
        'role' => 'alumni',
        'status' => RegistrationStatus::Pending->value,
        'created_user_id' => $user->id,
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status', function ($val) {
        return str_contains($val, 'sedang diproses oleh admin');
    });
});

test('oauth registration stores mitra and links provider successfully', function () {
    $provider = AuthOAuthProvider::where('slug', 'google')->first();
    $oauthData = [
        'provider' => 'google',
        'provider_id' => $provider->id,
        'external_id' => 'google-id-789',
        'name' => 'Google Partner',
        'email' => 'googlepartner@example.com',
        'access_token' => 'mock-token',
    ];

    $response = $this->withSession(['oauth_register_data' => $oauthData])
        ->post(route('auth.oauth.register.store'), [
            'role' => 'mitra',
            'nomor_induk' => 'NIB-999',
            'nama_perusahaan' => 'PT. Tech Jaya',
            'no_telepon' => '08987654321',
        ]);

    $this->assertGuest();

    $user = User::where('email', 'googlepartner@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->user_type)->toBe('mitra');
    expect($user->status_approval)->toBe(UserAccountStatus::Pending);
    expect($user->is_active)->toBeFalse();

    // Check RegistrationRequest created
    $this->assertDatabaseHas('registration_requests', [
        'email' => 'googlepartner@example.com',
        'role' => 'mitra',
        'status' => RegistrationStatus::Pending->value,
        'created_user_id' => $user->id,
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status', function ($val) {
        return str_contains($val, 'sedang diproses oleh admin');
    });
});

test('oauth registration handles existing/orphaned oauth credentials gracefully without unique violation', function () {
    $provider = AuthOAuthProvider::where('slug', 'google')->first();
    $oauthData = [
        'provider' => 'google',
        'provider_id' => $provider->id,
        'external_id' => 'google-id-duplicate-123',
        'name' => 'Google New User',
        'email' => GOOGLE_NEW_EMAIL,
        'access_token' => 'mock-token',
    ];

    // Create an orphaned credential in the database (linked to a non-existent or deleted user ID)
    AuthOAuthCredential::create([
        'user_id' => 9999, // Non-existent user
        'provider_id' => $provider->id,
        'external_id' => 'google-id-duplicate-123',
        'email' => 'googleold@example.com',
        'access_token' => 'old-token',
    ]);

    $response = $this->withSession(['oauth_register_data' => $oauthData])
        ->post(route('auth.oauth.register.store'), [
            'role' => 'mahasiswa',
            'nomor_induk' => '99998888',
            'program_studi_id' => ProgramStudi::where('kode', 'IF')->first()->id,
        ]);

    $this->assertGuest();

    $user = User::where('email', GOOGLE_NEW_EMAIL)->first();
    expect($user)->not->toBeNull();
    expect($user->status_approval)->toBe(UserAccountStatus::Pending);

    // Check RegistrationRequest created
    $this->assertDatabaseHas('registration_requests', [
        'email' => GOOGLE_NEW_EMAIL,
        'role' => 'mahasiswa',
        'status' => RegistrationStatus::Pending->value,
        'created_user_id' => $user->id,
    ]);

    $response->assertRedirect(route('login'));
});

test('oauth registration handles verified existing users with same email gracefully by linking and logging in', function () {
    $provider = AuthOAuthProvider::where('slug', 'google')->first();

    // Create an existing user with the same email
    $existingUser = new User([
        'name' => 'Existing User',
        'email' => GOOGLE_NEW_EMAIL,
        'password' => Hash::make('password'),
        'nomor_induk' => '88887777',
    ]);
    $existingUser->user_type = 'mahasiswa';
    $existingUser->status_approval = UserAccountStatus::Activated;
    $existingUser->is_active = true;
    $existingUser->email_verified_at = now();
    $existingUser->save();

    $oauthData = [
        'provider' => 'google',
        'provider_id' => $provider->id,
        'external_id' => 'google-id-duplicate-email-123',
        'name' => 'Google New User Name',
        'email' => GOOGLE_NEW_EMAIL,
        'access_token' => 'mock-token',
    ];

    $response = $this->withSession(['oauth_register_data' => $oauthData])
        ->post(route('auth.oauth.register.store'), [
            'role' => 'mahasiswa',
            'nomor_induk' => '11112222', // Even if different NIM is submitted
            'program_studi_id' => ProgramStudi::where('kode', 'IF')->first()->id,
        ]);

    $this->assertAuthenticatedAs($existingUser);

    // Check that the oauth credential was created/linked to the existing user
    $this->assertDatabaseHas('auth_oauth_credentials', [
        'user_id' => $existingUser->id,
        'provider_id' => $provider->id,
        'external_id' => 'google-id-duplicate-email-123',
        'email' => GOOGLE_NEW_EMAIL,
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));
});

test('oauth registration rejects linking to unverified existing users with same email', function () {
    $provider = AuthOAuthProvider::where('slug', 'google')->first();

    // Create an existing user with the same email (unverified!)
    User::create([
        'name' => 'Existing User',
        'email' => GOOGLE_NEW_EMAIL,
        'password' => Hash::make('password'),
        'user_type' => 'mahasiswa',
        'nomor_induk' => '88887777',
        'status_approval' => 'activated',
        'email_verified_at' => null, // unverified email!
    ]);

    $oauthData = [
        'provider' => 'google',
        'provider_id' => $provider->id,
        'external_id' => 'google-id-duplicate-email-123',
        'name' => 'Google New User Name',
        'email' => GOOGLE_NEW_EMAIL,
        'access_token' => 'mock-token',
    ];

    $response = $this->withSession(['oauth_register_data' => $oauthData])
        ->post(route('auth.oauth.register.store'), [
            'role' => 'mahasiswa',
            'nomor_induk' => '11112222',
            'program_studi_id' => ProgramStudi::where('kode', 'IF')->first()->id,
        ]);

    // Validation should fail due to unverified email
    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
});

test('oauth registration rejects linking to inactive existing users', function () {
    $provider = AuthOAuthProvider::where('slug', 'google')->first();

    // Create an existing inactive user
    User::create([
        'name' => 'Inactive User',
        'email' => GOOGLE_NEW_EMAIL,
        'password' => Hash::make('password'),
        'user_type' => 'mahasiswa',
        'nomor_induk' => '88887777',
        'status_approval' => 'activated',
        'email_verified_at' => now(),
        'is_active' => false,
    ]);

    $oauthData = [
        'provider' => 'google',
        'provider_id' => $provider->id,
        'external_id' => 'google-id-duplicate-email-123',
        'name' => 'Google New User Name',
        'email' => GOOGLE_NEW_EMAIL,
        'access_token' => 'mock-token',
    ];

    $response = $this->withSession(['oauth_register_data' => $oauthData])
        ->post(route('auth.oauth.register.store'), [
            'role' => 'mahasiswa',
            'nomor_induk' => '11112222',
            'program_studi_id' => ProgramStudi::where('kode', 'IF')->first()->id,
        ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
});

test('oauth registration rejects linking to unapproved existing users', function () {
    $provider = AuthOAuthProvider::where('slug', 'google')->first();

    // Create an existing unapproved user
    User::create([
        'name' => 'Unapproved User',
        'email' => GOOGLE_NEW_EMAIL,
        'password' => Hash::make('password'),
        'user_type' => 'mahasiswa',
        'nomor_induk' => '88887777',
        'status_approval' => 'pending',
        'email_verified_at' => now(),
    ]);

    $oauthData = [
        'provider' => 'google',
        'provider_id' => $provider->id,
        'external_id' => 'google-id-duplicate-email-123',
        'name' => 'Google New User Name',
        'email' => GOOGLE_NEW_EMAIL,
        'access_token' => 'mock-token',
    ];

    $response = $this->withSession(['oauth_register_data' => $oauthData])
        ->post(route('auth.oauth.register.store'), [
            'role' => 'mahasiswa',
            'nomor_induk' => '11112222',
            'program_studi_id' => ProgramStudi::where('kode', 'IF')->first()->id,
        ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
});
