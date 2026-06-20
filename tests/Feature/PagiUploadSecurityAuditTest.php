<?php

use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModuleRole;
use App\Services\VirusScannerService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'user_type' => 'mahasiswa',
        'pagi_username' => 'auditstudent',
    ]);
    setupAuditPagiContext($this->user);
});

test('svg upload cleans stored xss scripts and attributes', function () {
    $svgWithScript = '<?xml version="1.0" standalone="no"?>
<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)">
  <script type="text/javascript">alert(2)</script>
  <circle cx="50" cy="50" r="40" fill="red" />
  <animate xlink:href="#x" attributeName="href" values="javascript:alert(3)"/>
</svg>';

    $file = UploadedFile::fake()->createWithContent('logo.svg', $svgWithScript)->mimeType('image/svg+xml');

    $response = $this
        ->actingAs($this->user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->post(route('module.pagi.certificates.org-logo.upload'), [
            'name' => 'FMIKOM Test Org',
            'logo' => $file,
        ]);

    $response->assertStatus(200);
    $data = $response->json();
    expect($data['success'])->toBeTrue();

    // Verify sanitized SVG is stored and lacks malicious elements
    $path = str_replace(asset('storage/'), '', $data['url']);
    expect(Storage::disk('public')->exists($path))->toBeTrue();

    $storedContent = Storage::disk('public')->get($path);
    expect($storedContent)->not->toContain('<script');
    expect($storedContent)->not->toContain('onload');
    expect($storedContent)->not->toContain('javascript:');
    expect($storedContent)->toContain('<circle'); // Make sure safe content is kept
});

test('php shell disguised as image is rejected', function () {
    $phpFile = UploadedFile::fake()->createWithContent('shell.php', '<?php phpinfo(); ?>')->mimeType('image/jpeg');

    $response = $this
        ->actingAs($this->user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->post(route('module.pagi.profile.update'), [
            'foto' => $phpFile,
        ]);

    $response->assertStatus(302); // Redirect due to validation failure
    $response->assertSessionHasErrors('foto');
});

test('corrupted or fake image file is rejected', function () {
    // A text file that claims to be a PNG
    $fakePng = UploadedFile::fake()->createWithContent('fake.png', 'Not a real image contents at all. Just text.')->mimeType('image/png');

    $response = $this
        ->actingAs($this->user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->post(route('module.pagi.profile.update'), [
            'foto' => $fakePng,
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('foto');
});

test('clamav scanner blocks upload when virus is detected', function () {
    // Mock VirusScannerService to return unsafe
    $this->mock(VirusScannerService::class, function ($mock) {
        $mock->shouldReceive('scan')
            ->once()
            ->andReturn([
                'safe' => false,
                'reason' => 'Ancaman terdeteksi oleh antivirus: Eicar-Test-Signature',
            ]);
    });

    $file = UploadedFile::fake()->image('clean_image.png');

    $response = $this
        ->actingAs($this->user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->post(route('module.pagi.profile.update'), [
            'foto' => $file,
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('foto');
});

function setupAuditPagiContext(User $user): void
{
    $module = Module::firstOrCreate(['code' => 'PAGI'], [
        'name' => 'PAGI',
        'is_active' => true,
    ]);
    $role = Role::firstOrCreate(['slug' => 'mahasiswa'], [
        'nama' => 'Mahasiswa',
    ]);
    UserModuleRole::firstOrCreate([
        'user_id' => $user->id,
        'module_id' => $module->id,
        'role_id' => $role->id,
    ], [
        'is_active' => true,
    ]);
}
