<?php

use App\Http\Middleware\EnsureFirstTimeLoginComplete;
use App\Models\Magang\PendaftaranMagang;
use App\Models\Magang\PerusahaanMitra;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModuleRole;
use App\Modules\Wims\Services\Shared\Portal\WimsModuleRoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->wimsModule = Module::query()->create([
        'code' => 'WIMS',
        'name' => 'Web-based Internship Management System',
        'description' => 'WIMS',
        'is_active' => true,
    ]);

    $this->pagiModule = Module::query()->create([
        'code' => 'PAGI',
        'name' => 'PAGI',
        'description' => 'PAGI',
        'is_active' => true,
    ]);

    $this->fastModule = Module::query()->create([
        'code' => 'FAST',
        'name' => 'FAST',
        'description' => 'FAST',
        'is_active' => true,
    ]);

    $this->traceModule = Module::query()->create([
        'code' => 'TRACE',
        'name' => 'TRACE',
        'description' => 'TRACE',
        'is_active' => true,
    ]);

    foreach ([
        'super-admin' => 'Super Admin',
        'admin' => 'Admin',
        'admin-universitas' => 'Admin Universitas',
        'admin-akademik' => 'Admin Akademik',
        'prodi' => 'Prodi',
        'mahasiswa' => 'Mahasiswa',
        'dosen' => 'Dosen',
        'mitra' => 'Mitra',
        'unknown' => 'Unknown',
    ] as $slug => $name) {
        Role::query()->create([
            'slug' => $slug,
            'nama' => $name,
        ]);
    }

    syncModuleRoles($this->wimsModule, [
        'super-admin',
        'admin',
        'admin-universitas',
        'admin-akademik',
        'prodi',
        'mahasiswa',
        'dosen',
        'mitra',
        'unknown',
    ]);

    syncModuleRoles($this->pagiModule, ['dosen', 'mahasiswa', 'mitra']);
    syncModuleRoles($this->fastModule, ['dosen', 'mahasiswa', 'mitra']);
    syncModuleRoles($this->traceModule, ['dosen', 'mahasiswa', 'mitra']);

    if (! Route::getRoutes()->getByName('tests.wims.context')) {
        Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:wims'])
            ->get('/_test/wims/context', function (Request $request) {
                return response()->json([
                    'resolved_module' => $request->attributes->get('resolved_module'),
                    'resolved_role' => $request->attributes->get('resolved_role'),
                ]);
            })
            ->name('tests.wims.context');
    }
});

it('validates official portal module context for WIMS access', function () {
    $withoutAssignment = portalReadyUser();
    $otherModuleOnly = portalReadyUser();
    $fastModuleOnly = portalReadyUser();
    $traceModuleOnly = portalReadyUser();
    $inactiveAssignment = portalReadyUser();
    $activeAssignment = portalReadyUser();

    assignModuleRole($otherModuleOnly, $this->pagiModule, 'mahasiswa');
    assignModuleRole($fastModuleOnly, $this->fastModule, 'mahasiswa');
    assignModuleRole($traceModuleOnly, $this->traceModule, 'mahasiswa');
    assignModuleRole($inactiveAssignment, $this->wimsModule, 'mahasiswa', false);
    assignModuleRole($activeAssignment, $this->wimsModule, 'mahasiswa');

    $this->actingAs($withoutAssignment)
        ->withSession(['active_module' => 'WIMS', 'active_role' => 'mahasiswa'])
        ->get('/wims')
        ->assertRedirect(route('dashboard', absolute: false));

    $this->actingAs($otherModuleOnly)
        ->withSession(['active_module' => 'WIMS', 'active_role' => 'mahasiswa'])
        ->get('/wims')
        ->assertRedirect(route('dashboard', absolute: false));

    $this->actingAs($fastModuleOnly)
        ->withSession(['active_module' => 'WIMS', 'active_role' => 'mahasiswa'])
        ->get('/wims')
        ->assertRedirect(route('dashboard', absolute: false));

    $this->actingAs($traceModuleOnly)
        ->withSession(['active_module' => 'WIMS', 'active_role' => 'mahasiswa'])
        ->get('/wims')
        ->assertRedirect(route('dashboard', absolute: false));

    $this->actingAs($inactiveAssignment)
        ->withSession(['active_module' => 'WIMS', 'active_role' => 'mahasiswa'])
        ->get('/wims')
        ->assertRedirect(route('dashboard', absolute: false));

    $this->actingAs($activeAssignment)
        ->withSession(['active_module' => 'WIMS', 'active_role' => 'mahasiswa'])
        ->get('/_test/wims/context')
        ->assertOk()
        ->assertJson([
            'resolved_module' => 'WIMS',
            'resolved_role' => 'mahasiswa',
        ]);
});

it('redirects the WIMS entry route according to the resolved role', function () {
    $student = portalReadyUser();
    $lecturer = portalReadyUser();
    $partner = portalReadyUser();
    $adminRoles = [
        'super-admin' => portalReadyUser(),
        'admin' => portalReadyUser(),
        'admin-universitas' => portalReadyUser(),
        'admin-akademik' => portalReadyUser(),
        'prodi' => portalReadyUser(),
    ];
    $unknown = portalReadyUser();

    assignModuleRole($student, $this->wimsModule, 'mahasiswa');
    assignModuleRole($lecturer, $this->wimsModule, 'dosen');
    assignModuleRole($partner, $this->wimsModule, 'mitra');
    foreach ($adminRoles as $slug => $adminUser) {
        assignModuleRole($adminUser, $this->wimsModule, $slug);
    }
    assignModuleRole($unknown, $this->wimsModule, 'unknown');

    $this->actingAs($student)
        ->withSession(['active_module' => 'WIMS', 'active_role' => 'mahasiswa'])
        ->get('/wims')
        ->assertRedirect(route('wims.dashboard', absolute: false));

    $this->actingAs($lecturer)
        ->withSession(['active_module' => 'WIMS', 'active_role' => 'dosen'])
        ->get('/wims')
        ->assertRedirect(route('wims.dosen.dashboard', absolute: false));

    $this->actingAs($partner)
        ->withSession(['active_module' => 'WIMS', 'active_role' => 'mitra'])
        ->get('/wims')
        ->assertRedirect(route('wims.mitra.dashboard', absolute: false));

    foreach ($adminRoles as $slug => $adminUser) {
        $this->actingAs($adminUser)
            ->withSession(['active_module' => 'WIMS', 'active_role' => $slug])
            ->get('/wims')
            ->assertRedirect(route('wims.admin.dashboard', absolute: false));
    }

    $this->actingAs($unknown)
        ->withSession(['active_module' => 'WIMS', 'active_role' => 'unknown'])
        ->get('/wims')
        ->assertForbidden();
});

it('isolates WIMS role groups by the official module context middleware', function () {
    $student = portalReadyUser();
    $lecturer = portalReadyUser();
    $partner = portalReadyUser();
    $admin = portalReadyUser();

    assignModuleRole($student, $this->wimsModule, 'mahasiswa');
    assignModuleRole($lecturer, $this->wimsModule, 'dosen');
    assignModuleRole($partner, $this->wimsModule, 'mitra');
    assignModuleRole($admin, $this->wimsModule, 'admin');

    $this->actingAs($student)
        ->withSession(['active_module' => 'WIMS', 'active_role' => 'mahasiswa'])
        ->get(route('wims.admin.dashboard'))
        ->assertForbidden();

    $this->actingAs($student)
        ->withSession(['active_module' => 'WIMS', 'active_role' => 'mahasiswa'])
        ->get(route('wims.dosen.dashboard'))
        ->assertForbidden();

    $this->actingAs($student)
        ->withSession(['active_module' => 'WIMS', 'active_role' => 'mahasiswa'])
        ->get(route('wims.mitra.dashboard'))
        ->assertForbidden();

    $this->actingAs($lecturer)
        ->withSession(['active_module' => 'WIMS', 'active_role' => 'dosen'])
        ->get(route('wims.admin.dashboard'))
        ->assertForbidden();

    $this->actingAs($partner)
        ->withSession(['active_module' => 'WIMS', 'active_role' => 'mitra'])
        ->get(route('wims.admin.dashboard'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->withSession(['active_module' => 'WIMS', 'active_role' => 'admin'])
        ->get(route('wims.mitra.dashboard'))
        ->assertForbidden();
});

it('creates or reactivates mitra assignments without duplicating users or removing other module roles', function () {
    $companyForExistingUser = PerusahaanMitra::query()->create([
        'nama' => 'PT Existing Mitra',
        'is_active' => true,
    ]);

    $existingUser = portalReadyUser([
        'email' => 'mitra-existing@example.com',
        'name' => 'Existing Mitra User',
        'user_type' => 'staff',
    ]);

    assignModuleRole($existingUser, $this->pagiModule, 'mitra');
    assignModuleRole($existingUser, $this->wimsModule, 'mitra', false);

    $admin = portalReadyUser();
    assignModuleRole($admin, $this->wimsModule, 'admin');

    $this->actingAs($admin)
        ->withSession(['active_module' => 'WIMS', 'active_role' => 'admin'])
        ->from('/wims/admin/perusahaan')
        ->post(route('wims.admin.companies.account.store', $companyForExistingUser), [
            'name' => 'Ignored Name',
            'email' => $existingUser->email,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'no_telepon' => '08123',
            'jabatan' => 'HR Manager',
            'is_active' => true,
        ])
        ->assertRedirect('/wims/admin/perusahaan');

    expect(User::query()->where('email', $existingUser->email)->count())->toBe(1);

    $this->assertDatabaseHas('perusahaan_mitras', [
        'id' => $companyForExistingUser->id,
        'user_id' => $existingUser->id,
        'mitra_jabatan' => 'HR Manager',
    ]);

    $this->assertDatabaseHas('user_module_roles', [
        'user_id' => $existingUser->id,
        'module_id' => $this->wimsModule->id,
        'role_id' => Role::query()->where('slug', 'mitra')->firstOrFail()->id,
        'is_active' => true,
    ]);

    $this->assertDatabaseHas('user_module_roles', [
        'user_id' => $existingUser->id,
        'module_id' => $this->pagiModule->id,
        'role_id' => Role::query()->where('slug', 'mitra')->firstOrFail()->id,
    ]);

    $companyForNewUser = PerusahaanMitra::query()->create([
        'nama' => 'PT New Mitra',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->withSession(['active_module' => 'WIMS', 'active_role' => 'admin'])
        ->from('/wims/admin/perusahaan')
        ->post(route('wims.admin.companies.account.store', $companyForNewUser), [
            'name' => 'New Mitra User',
            'email' => 'mitra-new@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'no_telepon' => '08124',
            'jabatan' => 'Supervisor',
            'is_active' => true,
        ])
        ->assertRedirect('/wims/admin/perusahaan');

    $newUser = User::query()->where('email', 'mitra-new@example.com')->firstOrFail();

    $this->assertDatabaseHas('perusahaan_mitras', [
        'id' => $companyForNewUser->id,
        'user_id' => $newUser->id,
    ]);

    $this->assertDatabaseHas('user_module_roles', [
        'user_id' => $newUser->id,
        'module_id' => $this->wimsModule->id,
        'role_id' => Role::query()->where('slug', 'mitra')->firstOrFail()->id,
        'is_active' => true,
    ]);
});

it('limits placement-related role queries to active WIMS assignments only', function () {
    $activeWimsDosen = portalReadyUser(['name' => 'Active WIMS Dosen']);
    $inactiveWimsDosen = portalReadyUser(['name' => 'Inactive WIMS Dosen']);
    $otherModuleDosen = portalReadyUser(['name' => 'Other Module Dosen']);
    $activeWimsMitra = portalReadyUser(['name' => 'Active WIMS Mitra']);

    assignModuleRole($activeWimsDosen, $this->wimsModule, 'dosen');
    assignModuleRole($inactiveWimsDosen, $this->wimsModule, 'dosen', false);
    assignModuleRole($otherModuleDosen, $this->pagiModule, 'dosen');
    assignModuleRole($activeWimsMitra, $this->wimsModule, 'mitra');

    $service = app(WimsModuleRoleService::class);

    $dosenIds = app(\App\Modules\Wims\Services\Shared\Placement\PlacementIndexService::class)
        ->buildOptions()['dosen'];

    expect(collect($dosenIds)->pluck('id')->all())->toBe([$activeWimsDosen->id]);
    expect($service->usersForRole('mitra')->pluck('users.id')->all())->toBe([$activeWimsMitra->id]);

    $company = PerusahaanMitra::query()->create([
        'nama' => 'PT Placement',
        'is_active' => true,
    ]);

    $student = portalReadyUser();
    $admin = portalReadyUser();
    assignModuleRole($student, $this->wimsModule, 'mahasiswa');
    assignModuleRole($admin, $this->wimsModule, 'admin');

    $registration = PendaftaranMagang::query()->create([
        'mahasiswa_id' => $student->id,
        'perusahaan_id' => $company->id,
        'status' => 'approved',
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-30',
    ]);

    $this->actingAs($admin)
        ->withSession(['active_module' => 'WIMS', 'active_role' => 'admin'])
        ->from('/wims/admin/penempatan')
        ->put(route('wims.admin.placements.update', $registration), [
            'perusahaan_id' => $company->id,
            'dosen_pembimbing_id' => $otherModuleDosen->id,
        ])
        ->assertSessionHasErrors('dosen_pembimbing_id');

    $this->actingAs($admin)
        ->withSession(['active_module' => 'WIMS', 'active_role' => 'admin'])
        ->from('/wims/admin/penempatan')
        ->put(route('wims.admin.placements.update', $registration), [
            'perusahaan_id' => $company->id,
            'dosen_pembimbing_id' => $activeWimsDosen->id,
        ])
        ->assertSessionHasNoErrors();

    expect($registration->fresh()->dosen_pembimbing_id)->toBe($activeWimsDosen->id);
});

it('registers WIMS routes with modular controllers and official middleware only', function () {
    $expectedRoutes = [
        'module.wims.dashboard',
        'wims.dashboard',
        'wims.admin.dashboard',
        'wims.admin.companies.index',
        'wims.admin.registrations.index',
        'wims.admin.placements.index',
        'wims.dosen.dashboard',
        'wims.mitra.dashboard',
    ];

    foreach ($expectedRoutes as $routeName) {
        $route = Route::getRoutes()->getByName($routeName);

        expect($route)->not->toBeNull();
        expect($route->getActionName())->toStartWith('App\\Modules\\Wims\\Controllers\\');
        expect($route->gatherMiddleware())->toContain(EnsureFirstTimeLoginComplete::class);
        expect(collect($route->gatherMiddleware())->contains(fn (string $middleware) => str_contains($middleware, 'module.context:wims')))->toBeTrue();
        expect($route->gatherMiddleware())->not->toContain('wims.access');
        expect(collect($route->gatherMiddleware())->contains(fn (string $middleware) => str_contains($middleware, 'wims.role')))->toBeFalse();
    }
});

function portalReadyUser(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'is_active' => true,
    ], $attributes));
}

function assignModuleRole(User $user, Module $module, string $roleSlug, bool $isActive = true): UserModuleRole
{
    $role = Role::query()->where('slug', $roleSlug)->firstOrFail();

    $module->roles()->syncWithoutDetaching([$role->id => ['is_default' => false]]);

    return UserModuleRole::query()->create([
        'user_id' => $user->id,
        'module_id' => $module->id,
        'role_id' => $role->id,
        'is_active' => $isActive,
    ]);
}

function syncModuleRoles(Module $module, array $roleSlugs): void
{
    $roleIds = Role::query()
        ->whereIn('slug', $roleSlugs)
        ->pluck('id')
        ->mapWithKeys(fn (int $roleId) => [$roleId => ['is_default' => false]])
        ->all();

    $module->roles()->syncWithoutDetaching($roleIds);
}
