<?php

use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModuleRole;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('authenticated user can upload a certificate', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'user_type' => 'mahasiswa',
        'pagi_username' => 'testuser',
    ]);
    setupPagiContext($user);

    $response = $this
        ->actingAs($user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->post(route('module.pagi.certificates.store'), [
            'title' => 'AWS Certified Cloud Practitioner',
            'issuer' => 'Amazon Web Services',
            'date' => 'January 2026',
            'credentialId' => 'AWS-12345',
        ]);

    $response->assertJson([
        'success' => true,
        'message' => 'Certificate uploaded successfully!',
    ]);

    $user->refresh();
    $certs = $user->metadata['certificates'] ?? [];
    expect(count($certs))->toBe(3); // 2 default mock + 1 newly added
    expect($certs[2]['title'])->toBe('AWS Certified Cloud Practitioner');
    expect($certs[2]['issuer'])->toBe('Amazon Web Services');
});

test('authenticated user can update a certificate', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'user_type' => 'mahasiswa',
        'pagi_username' => 'testuser',
    ]);
    setupPagiContext($user);

    // Initialize with a custom certificate list in metadata
    $metadata = $user->metadata ?? [];
    $metadata['certificates'] = [
        ['id' => 10, 'title' => 'Original Certificate', 'issuer' => 'Original Issuer', 'date' => 'Original Date', 'credentialId' => '123'],
    ];
    $user->metadata = $metadata;
    $user->save();

    $response = $this
        ->actingAs($user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->put(route('module.pagi.certificates.update', ['id' => 10]), [
            'title' => 'Updated Certificate Title',
            'issuer' => 'Updated Issuer',
            'date' => 'Updated Date',
            'credentialId' => '456',
        ]);

    $response->assertJson([
        'success' => true,
        'message' => 'Certificate updated successfully!',
    ]);

    $user->refresh();
    $certs = $user->metadata['certificates'] ?? [];
    expect(count($certs))->toBe(1);
    expect($certs[0]['title'])->toBe('Updated Certificate Title');
    expect($certs[0]['issuer'])->toBe('Updated Issuer');
    expect($certs[0]['date'])->toBe('Updated Date');
    expect($certs[0]['credentialId'])->toBe('456');
});

test('authenticated user can delete a certificate', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'user_type' => 'mahasiswa',
        'pagi_username' => 'testuser',
    ]);
    setupPagiContext($user);

    // Initialize with a custom certificate
    $metadata = $user->metadata ?? [];
    $metadata['certificates'] = [
        ['id' => 10, 'title' => 'To Be Deleted', 'issuer' => 'Issuer', 'date' => 'Date', 'credentialId' => '123'],
    ];
    $user->metadata = $metadata;
    $user->save();

    $response = $this
        ->actingAs($user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->delete(route('module.pagi.certificates.destroy', ['id' => 10]));

    $response->assertJson([
        'success' => true,
        'message' => 'Certificate deleted successfully!',
    ]);

    $user->refresh();
    $certs = $user->metadata['certificates'] ?? [];
    expect(count($certs))->toBe(0);
});

test('legacy sertifikat routes redirect to certificates', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'user_type' => 'mahasiswa',
        'pagi_username' => 'maroep',
    ]);

    // Test query parameter redirect
    $response = $this->get('/pagi/maroep?tab=sertifikat');
    $response->assertRedirect('/pagi/maroep/certificates');

    // Test path redirect
    $response = $this->get('/pagi/maroep/sertifikat');
    $response->assertRedirect('/pagi/maroep/certificates');

    // Test path redirect with mixed case normalization
    $response = $this->get('/pagi/maroep/Certificates');
    $response->assertRedirect('/pagi/maroep/certificates');
});

test('uploaded files with php or js script tag are rejected', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'user_type' => 'mahasiswa',
        'pagi_username' => 'testuser',
    ]);
    setupPagiContext($user);

    // Create a malicious file content
    $maliciousFile = UploadedFile::fake()->createWithContent('malicious.pdf', '<?php echo "evil"; ?>');

    $response = $this
        ->actingAs($user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->post(route('module.pagi.certificates.store'), [
            'title' => 'AWS Certified Cloud Practitioner',
            'issuer' => 'Amazon Web Services',
            'date' => '2026-01',
            'newMedia' => [$maliciousFile],
        ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'message' => 'Security Error: Script signature detected in file content.',
    ]);
});

test('user can upload valid files and they are saved successfully', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'user_type' => 'mahasiswa',
        'pagi_username' => 'testuser',
    ]);
    setupPagiContext($user);

    $pdfFile = UploadedFile::fake()->create('proof.pdf', 500, 'application/pdf');
    $imageFile = UploadedFile::fake()->image('proof.png', 100, 100);

    $response = $this
        ->actingAs($user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->post(route('module.pagi.certificates.store'), [
            'title' => 'Advanced Laravel',
            'issuer' => 'Laracasts',
            'date' => '2026-06',
            'expirationDate' => '2027-06',
            'credentialId' => 'LAR-9988',
            'credentialUrl' => 'https://laracasts.com/verify/12345',
            'skills' => json_encode(['Laravel', 'PHP']),
            'newMedia' => [$pdfFile, $imageFile],
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
    ]);

    $user->refresh();
    $certs = $user->metadata['certificates'] ?? [];
    $uploadedCert = collect($certs)->firstWhere('title', 'Advanced Laravel');

    expect($uploadedCert)->not->toBeNull();
    expect($uploadedCert['issuer'])->toBe('Laracasts');
    expect($uploadedCert['expirationDate'])->toBe('2027-06');
    expect($uploadedCert['credentialUrl'])->toBe('https://laracasts.com/verify/12345');
    expect($uploadedCert['skills'])->toBe(['Laravel', 'PHP']);
    expect(count($uploadedCert['media']))->toBe(2);
    expect($uploadedCert['media'][0]['name'])->toBe('proof.pdf');
    expect($uploadedCert['media'][1]['name'])->toBe('proof.webp');
});

test('user cannot upload more than 3 files', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'user_type' => 'mahasiswa',
        'pagi_username' => 'testuser',
    ]);
    setupPagiContext($user);

    $response = $this
        ->actingAs($user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->post(route('module.pagi.certificates.store'), [
            'title' => 'Advanced Laravel',
            'issuer' => 'Laracasts',
            'date' => '2026-06',
            'newMedia' => [
                UploadedFile::fake()->create('proof1.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('proof2.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('proof3.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('proof4.pdf', 100, 'application/pdf'),
            ],
        ]);

    $response->assertStatus(302); // Redirect back due to validation error
    $response->assertSessionHasErrors('newMedia');
});

function setupPagiContext(User $user): void
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
