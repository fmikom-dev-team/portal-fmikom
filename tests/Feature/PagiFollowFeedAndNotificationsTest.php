<?php

use App\Models\Module;
use App\Models\Pagi\PagiFollow;
use App\Models\Pagi\PagiWork;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModuleRole;
use App\Notifications\PagiNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;

function setupPagiFollowTestContext(User $user, string $roleSlug = 'mahasiswa'): void
{
    $module = Module::firstOrCreate(['code' => 'PAGI'], [
        'name' => 'PAGI',
        'is_active' => true,
    ]);
    $role = Role::firstOrCreate(['slug' => $roleSlug], [
        'nama' => ucfirst($roleSlug),
    ]);
    UserModuleRole::firstOrCreate([
        'user_id' => $user->id,
        'module_id' => $module->id,
        'role_id' => $role->id,
    ], [
        'is_active' => true,
    ]);
}

test('user following list and feed projects work correctly', function () {
    // 1. Create three users
    $userA = User::factory()->create(['email_verified_at' => now(), 'password_changed_at' => now(), 'user_type' => 'alumni', 'pagi_username' => 'alumni_user']);
    $userB = User::factory()->create(['email_verified_at' => now(), 'password_changed_at' => now(), 'user_type' => 'mahasiswa', 'pagi_username' => 'mahasiswa_user_b']);
    $userC = User::factory()->create(['email_verified_at' => now(), 'password_changed_at' => now(), 'user_type' => 'mahasiswa', 'pagi_username' => 'mahasiswa_user_c']);

    setupPagiFollowTestContext($userA, 'alumni');
    setupPagiFollowTestContext($userB, 'mahasiswa');
    setupPagiFollowTestContext($userC, 'mahasiswa');

    // Create works for B and C
    $workB = PagiWork::create([
        'user_id' => $userB->id,
        'title' => 'Project from B',
        'is_published' => true,
        'visibility' => 'Everyone',
        'content' => [],
    ]);

    $workC = PagiWork::create([
        'user_id' => $userC->id,
        'title' => 'Project from C',
        'is_published' => true,
        'visibility' => 'Everyone',
        'content' => [],
    ]);

    // 2. User A follows User B
    $response = $this
        ->actingAs($userA)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'alumni'])
        ->post(route('module.pagi.users.follow', ['user' => $userB->id]));

    $response->assertOk();
    $response->assertJsonPath('following', true);

    expect(PagiFollow::where('follower_id', $userA->id)->where('following_id', $userB->id)->exists())->toBeTrue();

    // 3. User A checks dashboard followingFeedProjects
    $dashboardResponse = $this
        ->actingAs($userA)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'alumni'])
        ->get(route('module.pagi.dashboard'));

    $dashboardResponse->assertOk();
    $props = $dashboardResponse->original->getData()['page']['props'];

    $followingProjects = collect($props['followingFeedProjects']);
    expect($followingProjects)->not->toBeEmpty();
    expect($followingProjects->firstWhere('id', $workB->id))->not->toBeNull();
    expect($followingProjects->firstWhere('id', $workC->id))->toBeNull(); // C should not be in following feed
});

test('followers are notified when a new work is published', function () {
    $follower = User::factory()->create(['email_verified_at' => now(), 'password_changed_at' => now(), 'user_type' => 'alumni', 'pagi_username' => 'follower_alumni']);
    $creator = User::factory()->create(['email_verified_at' => now(), 'password_changed_at' => now(), 'user_type' => 'mahasiswa', 'pagi_username' => 'creator_student']);

    setupPagiFollowTestContext($follower, 'alumni');
    setupPagiFollowTestContext($creator, 'mahasiswa');

    // Follow
    PagiFollow::create([
        'follower_id' => $follower->id,
        'following_id' => $creator->id,
    ]);

    Notification::fake();

    // Store a published work via editor endpoint
    $response = $this
        ->actingAs($creator)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'mahasiswa'])
        ->post(route('module.pagi.editor.store'), [
            'title' => 'My New Awesome Work',
            'is_published' => true,
            'visibility' => 'Everyone',
            'content' => [
                ['type' => 'text', 'value' => 'Hello world'],
            ],
            'cover_image' => UploadedFile::fake()->image('cover.jpg'),
            'category' => 'Graphic Design',
            'tags' => 'design,art',
            'tools_used' => 'Figma',
            'description' => 'This is a description of my work',
        ]);

    $response->assertRedirect();

    // Assert notification sent to follower
    Notification::assertSentTo(
        $follower,
        PagiNotification::class,
        function ($notification, $channels) {
            return $notification->type === 'new_work' &&
                   str_contains($notification->message, 'mempublikasikan karya baru');
        }
    );
});
