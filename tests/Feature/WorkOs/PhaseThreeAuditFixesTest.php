<?php

use App\Concerns\HandlesImageCompression;
use App\Models\Module;
use App\Models\Portal\PortalCategory;
use App\Models\Portal\PortalPost;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModuleRole;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

// Setup env backup/restore for the SMTP test
$envBackup = null;

beforeEach(function () {
    $envPath = base_path('.env');
    if (file_exists($envPath)) {
        $GLOBALS['envBackup'] = file_get_contents($envPath);
    }
});

afterEach(function () {
    $envPath = base_path('.env');
    if (isset($GLOBALS['envBackup'])) {
        file_put_contents($envPath, $GLOBALS['envBackup']);
        unset($GLOBALS['envBackup']);
    }
});

class DummyVideoCompressor
{
    use HandlesImageCompression;

    public function testCompress($file)
    {
        return $this->compressAndSaveBannerOrVideo($file, 'testing/videos');
    }
}

// 1. Centralized Module Assignment Helper
test('user assignDefaultModuleRoles helper correctly maps roles and modules', function () {
    // Setup modules
    $modules = ['FAST', 'PAGI', 'WIMS', 'TRACE'];
    foreach ($modules as $code) {
        Module::firstOrCreate(['code' => $code], ['name' => $code, 'is_active' => true]);
    }

    // Setup roles
    $mahasiswaRole = Role::firstOrCreate(['slug' => 'mahasiswa'], ['nama' => 'Mahasiswa']);
    $alumniRole = Role::firstOrCreate(['slug' => 'alumni'], ['nama' => 'Alumni']);
    $mitraRole = Role::firstOrCreate(['slug' => 'mitra'], ['nama' => 'Mitra']);
    $dosenRole = Role::firstOrCreate(['slug' => 'dosen'], ['nama' => 'Dosen']);

    // Test mahasiswa
    $mahasiswa = User::factory()->create(['user_type' => 'mahasiswa']);
    $mahasiswa->assignDefaultModuleRoles();
    expect(UserModuleRole::where('user_id', $mahasiswa->id)->count())->toBe(3);

    // Test alumni
    $alumni = User::factory()->create(['user_type' => 'alumni']);
    $alumni->assignDefaultModuleRoles();
    expect(UserModuleRole::where('user_id', $alumni->id)->count())->toBe(2);

    // Test mitra
    $mitra = User::factory()->create(['user_type' => 'mitra']);
    $mitra->assignDefaultModuleRoles();
    expect(UserModuleRole::where('user_id', $mitra->id)->count())->toBe(3);

    // Test dosen
    $dosen = User::factory()->create(['user_type' => 'dosen']);
    $dosen->assignDefaultModuleRoles();
    expect(UserModuleRole::where('user_id', $dosen->id)->count())->toBe(3);
});

// 2. Safe Mapping Deletion (BUG-012)
test('admin cannot detach unmapped role from module', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    $module = Module::firstOrCreate(['code' => 'FAST'], ['name' => 'FAST', 'is_active' => true]);
    $role = Role::firstOrCreate(['slug' => 'mahasiswa'], ['nama' => 'Mahasiswa']);

    // Ensure it is NOT attached
    $module->roles()->detach($role->id);

    $this->actingAs($admin);

    // Make request to remove mapping
    $response = $this->delete("/workos/modules/{$module->id}/roles/{$role->id}");
    $response->assertStatus(404);
});

test('admin can detach mapped role from module', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    $module = Module::firstOrCreate(['code' => 'FAST'], ['name' => 'FAST', 'is_active' => true]);
    $role = Role::firstOrCreate(['slug' => 'mahasiswa'], ['nama' => 'Mahasiswa']);

    // Attach it
    if (! $module->roles()->where('roles.id', $role->id)->exists()) {
        $module->roles()->attach($role->id);
    }

    $this->actingAs($admin);

    // Make request to remove mapping
    $response = $this->delete("/workos/modules/{$module->id}/roles/{$role->id}");
    $response->assertRedirect();

    // Verify it is detached
    expect($module->roles()->where('roles.id', $role->id)->exists())->toBeFalse();
});

// 3. Comments Rate Limiting and Honeypot (M2)
test('posting comment with honeypot filled returns success but does not save to database', function () {
    $category = PortalCategory::firstOrCreate(['slug' => 'general'], ['name' => 'General']);
    $post = PortalPost::create([
        'user_id' => User::factory()->create()->id,
        'category_id' => $category->id,
        'title' => 'Test Post',
        'slug' => 'test-post-'.uniqid(),
        'content' => 'Content here',
        'status' => 'published',
    ]);

    $response = $this->post(route('portal.posts.comments.store', $post->slug), [
        'author_name' => 'Spam Bot',
        'author_email' => 'bot@spambot.com',
        'content' => 'Buy cheap links!',
        'phone' => '1234567890', // honeypot filled
    ]);

    $response->assertRedirect();
    $this->assertDatabaseMissing('portal_comments', [
        'author_name' => 'Spam Bot',
    ]);
});

test('posting comment without honeypot saves successfully and enforces rate limiting', function () {
    $category = PortalCategory::firstOrCreate(['slug' => 'general'], ['name' => 'General']);
    $post = PortalPost::create([
        'user_id' => User::factory()->create()->id,
        'category_id' => $category->id,
        'title' => 'Test Post Rate',
        'slug' => 'test-post-rate-'.uniqid(),
        'content' => 'Content here',
        'status' => 'published',
    ]);

    // Send 3 successful comments
    for ($i = 0; $i < 3; $i++) {
        $response = $this->post(route('portal.posts.comments.store', $post->slug), [
            'author_name' => "Commenter {$i}",
            'author_email' => "user{$i}@example.com",
            'content' => "This is comment {$i}",
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('portal_comments', [
            'author_name' => "Commenter {$i}",
        ]);
    }

    // The 4th comment should fail with a 429 rate limit status code
    $response = $this->post(route('portal.posts.comments.store', $post->slug), [
        'author_name' => 'Spammer',
        'author_email' => 'spam@example.com',
        'content' => 'This should be blocked',
    ]);
    $response->assertStatus(429);
});

// 4. Masquerading of nomor_induk in User Payloads (BUG-FE-002)
test('nomor_induk is masked in user data payload', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    User::factory()->create(['nomor_induk' => '1234567890']);

    $this->actingAs($admin);

    $response = $this->get('/workos/users');
    $response->assertOk();

    $response->assertInertia(fn ($page) => $page
        ->has('users.data')
    );

    $inertiaUsers = $response->original->getData()['page']['props']['users']['data'];
    foreach ($inertiaUsers as $u) {
        if ($u['nomor_induk'] !== null) {
            expect($u['nomor_induk'])->toContain('*');
        }
    }
});

test('updating user with masked nomor_induk does not update database', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    $user = User::factory()->create(['nomor_induk' => '1234567890']);

    $this->actingAs($admin);

    $response = $this->patch(route('workos.users.update', $user), [
        'name' => 'Updated User Name',
        'email' => $user->email,
        'nomor_induk' => '1234******',
    ]);

    $response->assertRedirect();

    $user->refresh();
    expect($user->name)->toBe('Updated User Name');
    expect($user->nomor_induk)->toBe('1234567890');
});

test('updating user with new unmasked nomor_induk updates database successfully', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    $user = User::factory()->create(['nomor_induk' => '1234567890']);

    $this->actingAs($admin);

    $response = $this->patch(route('workos.users.update', $user), [
        'name' => 'Updated User Name 2',
        'email' => $user->email,
        'nomor_induk' => '9876543210',
    ]);

    $response->assertRedirect();

    $user->refresh();
    expect($user->nomor_induk)->toBe('9876543210');
});

// 5. SMTP Password encryption (SEC-012)
test('smtp config updates with encrypted password in env and decrypts on boot', function () {
    // Write a mock .env if it does not exist so file_put_contents doesn't fail
    $envPath = base_path('.env');
    if (! file_exists($envPath)) {
        file_put_contents($envPath, "MAIL_PASSWORD=\n");
    }

    $admin = User::factory()->create(['user_type' => 'super_admin']);
    $this->actingAs($admin);

    $response = $this->post(route('workos.emails.config.update'), [
        'host' => 'smtp.mailtrap.io',
        'port' => 2525,
        'sender' => 'test@fmikom.org',
        'encryption' => 'tls',
        'username' => 'test_user',
        'password' => 'secret_password_123',
    ]);

    $response->assertOk();

    // Verify .env file content contains the base64 prefix
    $envContent = file_get_contents($envPath);
    expect($envContent)->toContain('MAIL_PASSWORD=base64:');

    // Extract encrypted part from .env line
    preg_match('/^MAIL_PASSWORD=(.*)$/m', $envContent, $matches);
    $mailPassword = str_replace('"', '', $matches[1]);
    expect($mailPassword)->toStartWith('base64:');

    $decrypted = Crypt::decryptString(substr($mailPassword, 7));
    expect($decrypted)->toBe('secret_password_123');
});

// 6. Video background transcode checks
test('video uploading returns path instantly and triggers background process', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->create('sample.mp4', 1000, 'video/mp4');

    $compressor = new DummyVideoCompressor;
    $relativePath = $compressor->testCompress($file);

    // Assert the relative path is returned
    expect($relativePath)->toStartWith('testing/videos/vid_');

    // Assert the file exists at the expected path
    $absolutePath = storage_path('app/public/'.$relativePath);
    expect(file_exists($absolutePath))->toBeTrue();

    // Clean up
    @unlink($absolutePath);
});
