<?php

use App\Models\Fakultas;
use App\Models\Module;
use App\Models\ProgramStudi;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModuleRole;

function setupPagiSettingsContext(User $user): void
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

test('pagi settings page loads correctly and passes program study lists', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'user_type' => 'mahasiswa',
        'pagi_username' => 'testuser',
    ]);
    setupPagiSettingsContext($user);

    $fakultas = Fakultas::create(['nama' => 'Fakultas Ilmu Komputer', 'kode' => 'FIK']);
    $prodi = ProgramStudi::create(['nama' => 'Informatika', 'kode' => 'IF', 'fakultas_id' => $fakultas->id]);

    $response = $this
        ->actingAs($user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->get(route('module.pagi.settings'));

    $response->assertOk();

    $viewProps = $response->original->getData()['page']['props'];
    expect($viewProps['programStudis'])->not->toBeEmpty();

    // Find the Informatika prodi from the returned props
    $prodiProp = collect($viewProps['programStudis'])->firstWhere('nama', 'Informatika');
    expect($prodiProp)->not->toBeNull();
    expect($prodiProp['fakultas_nama'])->toBe('Fakultas Ilmu Komputer');
});

test('user can update academic information with sanitization', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'user_type' => 'mahasiswa',
        'pagi_username' => 'testuser',
    ]);
    setupPagiSettingsContext($user);

    $fakultas = Fakultas::create(['nama' => 'Fakultas Ilmu Komputer', 'kode' => 'FIK']);
    $prodi = ProgramStudi::create(['nama' => 'Informatika', 'kode' => 'IF', 'fakultas_id' => $fakultas->id]);

    $response = $this
        ->actingAs($user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->post(route('module.pagi.settings.update'), [
            'email' => $user->email,
            'nim_nip' => '<b>22.11.5555</b><script>alert("hack")</script>',
            'program_studi_id' => $prodi->id,
        ]);

    $response->assertRedirect();
    $user->refresh();

    // Check if script and HTML tags are completely stripped
    expect($user->nomor_induk)->toBe('22.11.5555alert("hack")');
    expect($user->program_studi_id)->toBe($prodi->id);
});
