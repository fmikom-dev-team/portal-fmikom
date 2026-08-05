<?php

namespace Tests\Feature\WorkOs;

use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use App\Models\UserInvitation;
use App\Models\UserModuleRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserInvitationModuleRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard roles
        Role::firstOrCreate(['slug' => 'super-admin'], ['nama' => 'Super Admin']);
        Role::firstOrCreate(['slug' => 'staff'], ['nama' => 'Staff']);
        Role::firstOrCreate(['slug' => 'dosen'], ['nama' => 'Dosen']);

        // Create standard modules
        Module::firstOrCreate(['code' => 'FAST'], ['name' => 'FAST Module', 'is_active' => true]);
        Module::firstOrCreate(['code' => 'PAGI'], ['name' => 'PAGI Module', 'is_active' => true]);
        Module::firstOrCreate(['code' => 'TRACE'], ['name' => 'TRACE Module', 'is_active' => true]);
        Module::firstOrCreate(['code' => 'WIMS'], ['name' => 'WIMS Module', 'is_active' => true]);
    }

    public function test_assign_default_module_roles_gives_all_active_modules_to_super_admin(): void
    {
        $user = User::factory()->create([
            'user_type' => 'super_admin',
        ]);

        $user->assignDefaultModuleRoles();

        $activeModulesCount = Module::where('is_active', true)->count();
        $assignedModulesCount = UserModuleRole::where('user_id', $user->id)->count();

        $this->assertEquals($activeModulesCount, $assignedModulesCount);
    }

    public function test_accepting_invitation_assigns_default_module_roles(): void
    {
        $token = Str::random(64);
        $invitation = UserInvitation::create([
            'email' => 'invited.staff@example.com',
            'user_type' => 'staff',
            'token' => $token,
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->post('/user-invitations/accept', [
            'token' => $token,
            'name' => 'Invited Staff User',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertRedirect('/workos');

        $user = User::where('email', 'invited.staff@example.com')->first();
        $this->assertNotNull($user);

        $moduleRoles = UserModuleRole::where('user_id', $user->id)->get();
        $this->assertGreaterThan(0, $moduleRoles->count());
    }
}
