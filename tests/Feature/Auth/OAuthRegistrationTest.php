<?php

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

    $fakultas = Fakultas::firstOrCreate(['id' => 1], ['nama' => 'FMIKOM', 'kode' => 'FMIKOM']);
    ProgramStudi::firstOrCreate(['id' => 1], ['fakultas_id' => $fakultas->id, 'nama' => 'Informatika', 'kode' => 'IF']);
    ProgramStudi::firstOrCreate(['id' => 2], ['fakultas_id' => $fakultas->id, 'nama' => 'Sistem Informasi', 'kode' => 'SI']);

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
        'access_token' => Crypt::encryptString('mock-token'),
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
        'access_token' => Crypt::encryptString('mock-token'),
    ];

    $response = $this->withSession(['oauth_register_data' => $oauthData])
        ->post(route('auth.oauth.register.store'), [
            'role' => 'mahasiswa',
            'nomor_induk' => '12345678',
            'program_studi_id' => 1,
        ]);

    $this->assertAuthenticated();

    $user = User::where('email', GOOGLE_USER_EMAIL)->first();
    expect($user)->not->toBeNull();
    expect($user->user_type)->toBe('mahasiswa');
    expect($user->nomor_induk)->toBe('12345678');
    expect($user->email_verified_at)->not->toBeNull(); // Auto-verified!

    // Check linked credential
    $this->assertDatabaseHas('auth_oauth_credentials', [
        'user_id' => $user->id,
        'provider_id' => $provider->id,
        'external_id' => 'google-id-123',
    ]);

    // Check assigned modules for mahasiswa
    $this->assertDatabaseHas('user_module_roles', [
        'user_id' => $user->id,
        'module_id' => Module::where('code', 'FAST')->first()->id,
    ]);

    // Check session data is cleared
    expect(session()->has('oauth_register_data'))->toBeFalse();

    $response->assertRedirect(route('dashboard', absolute: false));
});

test('oauth registration stores alumni and links provider successfully', function () {
    $provider = AuthOAuthProvider::where('slug', 'google')->first();
    $oauthData = [
        'provider' => 'google',
        'provider_id' => $provider->id,
        'external_id' => 'google-id-456',
        'name' => 'Google Alumni',
        'email' => 'googlealumni@example.com',
        'access_token' => Crypt::encryptString('mock-token'),
    ];

    $response = $this->withSession(['oauth_register_data' => $oauthData])
        ->post(route('auth.oauth.register.store'), [
            'role' => 'alumni',
            'nomor_induk' => '87654321',
            'program_studi_id' => 1,
            'tahun_lulus' => '2024',
        ]);

    $this->assertAuthenticated();

    $user = User::where('email', 'googlealumni@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->user_type)->toBe('alumni');
    expect($user->nomor_induk)->toBe('87654321');
    expect($user->tahun_lulus)->toBe(2024);
    expect($user->email_verified_at)->not->toBeNull();

    // Check assigned modules for alumni
    $this->assertDatabaseHas('user_module_roles', [
        'user_id' => $user->id,
        'module_id' => Module::where('code', 'TRACE')->first()->id,
    ]);
    $this->assertDatabaseHas('user_module_roles', [
        'user_id' => $user->id,
        'module_id' => Module::where('code', 'PAGI')->first()->id,
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));
});

test('oauth registration stores mitra and links provider successfully', function () {
    $provider = AuthOAuthProvider::where('slug', 'google')->first();
    $oauthData = [
        'provider' => 'google',
        'provider_id' => $provider->id,
        'external_id' => 'google-id-789',
        'name' => 'Google Partner',
        'email' => 'googlepartner@example.com',
        'access_token' => Crypt::encryptString('mock-token'),
    ];

    $response = $this->withSession(['oauth_register_data' => $oauthData])
        ->post(route('auth.oauth.register.store'), [
            'role' => 'mitra',
            'nomor_induk' => 'NIB-999',
            'nama_perusahaan' => 'PT. Tech Jaya',
            'no_telepon' => '08987654321',
        ]);

    $this->assertAuthenticated();

    $user = User::where('email', 'googlepartner@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->user_type)->toBe('mitra');
    expect($user->nomor_induk)->toBe('NIB-999');
    expect($user->no_telepon)->toBe('08987654321');
    expect($user->email_verified_at)->not->toBeNull();

    // Check assigned modules for mitra
    $this->assertDatabaseHas('user_module_roles', [
        'user_id' => $user->id,
        'module_id' => Module::where('code', 'WIMS')->first()->id,
    ]);
    $this->assertDatabaseHas('user_module_roles', [
        'user_id' => $user->id,
        'module_id' => Module::where('code', 'TRACE')->first()->id,
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));
});

test('oauth registration handles existing/orphaned oauth credentials gracefully without unique violation', function () {
    $provider = AuthOAuthProvider::where('slug', 'google')->first();
    $oauthData = [
        'provider' => 'google',
        'provider_id' => $provider->id,
        'external_id' => 'google-id-duplicate-123',
        'name' => 'Google New User',
        'email' => GOOGLE_NEW_EMAIL,
        'access_token' => Crypt::encryptString('mock-token'),
    ];

    // Create an orphaned credential in the database (linked to a non-existent or deleted user ID)
    AuthOAuthCredential::create([
        'user_id' => 9999, // Non-existent user
        'provider_id' => $provider->id,
        'external_id' => 'google-id-duplicate-123',
        'email' => 'googleold@example.com',
        'access_token' => Crypt::encryptString('old-token'),
    ]);

    $response = $this->withSession(['oauth_register_data' => $oauthData])
        ->post(route('auth.oauth.register.store'), [
            'role' => 'mahasiswa',
            'nomor_induk' => '99998888',
            'program_studi_id' => 1,
        ]);

    $this->assertAuthenticated();

    $user = User::where('email', GOOGLE_NEW_EMAIL)->first();
    expect($user)->not->toBeNull();

    // Check that the existing oauth credential was updated to the new user ID instead of throwing a unique constraint violation
    $this->assertDatabaseHas('auth_oauth_credentials', [
        'user_id' => $user->id,
        'provider_id' => $provider->id,
        'external_id' => 'google-id-duplicate-123',
        'email' => GOOGLE_NEW_EMAIL,
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));
});

test('oauth registration handles verified existing users with same email gracefully by linking and logging in', function () {
    $provider = AuthOAuthProvider::where('slug', 'google')->first();

    // Create an existing user with the same email
    $existingUser = User::create([
        'name' => 'Existing User',
        'email' => GOOGLE_NEW_EMAIL,
        'password' => Hash::make('password'),
        'user_type' => 'mahasiswa',
        'nomor_induk' => '88887777',
        'status_approval' => 'approved',
    ]);
    $existingUser->email_verified_at = now(); // Set manually because it is not fillable
    $existingUser->save();

    $oauthData = [
        'provider' => 'google',
        'provider_id' => $provider->id,
        'external_id' => 'google-id-duplicate-email-123',
        'name' => 'Google New User Name',
        'email' => GOOGLE_NEW_EMAIL,
        'access_token' => Crypt::encryptString('mock-token'),
    ];

    $response = $this->withSession(['oauth_register_data' => $oauthData])
        ->post(route('auth.oauth.register.store'), [
            'role' => 'mahasiswa',
            'nomor_induk' => '11112222', // Even if different NIM is submitted
            'program_studi_id' => 1,
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
        'status_approval' => 'approved',
        'email_verified_at' => null, // unverified email!
    ]);

    $oauthData = [
        'provider' => 'google',
        'provider_id' => $provider->id,
        'external_id' => 'google-id-duplicate-email-123',
        'name' => 'Google New User Name',
        'email' => GOOGLE_NEW_EMAIL,
        'access_token' => Crypt::encryptString('mock-token'),
    ];

    $response = $this->withSession(['oauth_register_data' => $oauthData])
        ->post(route('auth.oauth.register.store'), [
            'role' => 'mahasiswa',
            'nomor_induk' => '11112222',
            'program_studi_id' => 1,
        ]);

    // Validation should fail due to unverified email
    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
});
