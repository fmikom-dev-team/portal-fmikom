<?php

use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModuleRole;
use App\Modules\Pagi\Services\PagiSocialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('explorePeople excludes inactive users, unapproved users, users without pagi_username, and mahasiswa without published works', function () {
    $module = Module::create(['code' => 'PAGI', 'name' => 'PAGI Module', 'is_active' => true]);
    $mahasiswaRole = Role::create(['nama' => 'Mahasiswa', 'slug' => 'mahasiswa']);

    // 1. Inactive User (should be excluded)
    $inactiveUser = User::factory()->create([
        'name' => 'Inactive User',
        'is_active' => false,
        'status_approval' => 'approved',
        'pagi_username' => 'inactiveuser',
    ]);
    UserModuleRole::create(['user_id' => $inactiveUser->id, 'module_id' => $module->id, 'role_id' => $mahasiswaRole->id, 'is_active' => true]);

    // 2. User without pagi_username (should be excluded)
    $noUsernameUser = User::factory()->create([
        'name' => 'No Username User',
        'is_active' => true,
        'status_approval' => 'approved',
        'pagi_username' => null,
    ]);
    UserModuleRole::create(['user_id' => $noUsernameUser->id, 'module_id' => $module->id, 'role_id' => $mahasiswaRole->id, 'is_active' => true]);

    // 3. Mahasiswa without published works (should be excluded)
    $noWorkMahasiswa = User::factory()->create([
        'name' => 'No Work Mahasiswa',
        'is_active' => true,
        'status_approval' => 'approved',
        'pagi_username' => 'noworkuser',
    ]);
    UserModuleRole::create(['user_id' => $noWorkMahasiswa->id, 'module_id' => $module->id, 'role_id' => $mahasiswaRole->id, 'is_active' => true]);

    // 4. Mahasiswa WITH published work (should be INCLUDED)
    $validMahasiswa = User::factory()->create([
        'name' => 'Valid Mahasiswa',
        'is_active' => true,
        'status_approval' => 'approved',
        'pagi_username' => 'validmahasiswa',
        'foto_path' => 'avatars/valid.jpg',
    ]);
    UserModuleRole::create(['user_id' => $validMahasiswa->id, 'module_id' => $module->id, 'role_id' => $mahasiswaRole->id, 'is_active' => true]);

    DB::table('pagi_works')->insert([
        'user_id' => $validMahasiswa->id,
        'title' => 'Valid Project',
        'is_published' => true,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = new PagiSocialService;
    $result = $service->explorePeople($module->id);

    $ids = array_column($result, 'id');

    expect($ids)->toContain($validMahasiswa->id);
    expect($ids)->toContain($noWorkMahasiswa->id);
    expect($ids)->not()->toContain($inactiveUser->id);
    expect($ids)->not()->toContain($noUsernameUser->id);
});
