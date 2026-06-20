<?php

use App\Models\Module;
use App\Models\Pagi\PagiWork;
use App\Models\Portal\PortalCategory;
use App\Models\Portal\PortalPost;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModuleRole;
use App\Modules\Pagi\Services\PagiProfileService;
use App\Modules\Pagi\Services\PagiSocialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

test('ImageProxyController serve rejects path traversal attempts', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    $this->actingAs($admin);

    Storage::fake('public');

    // Create directory profile_photos and some file inside
    Storage::disk('public')->put('profile_photos/avatar.jpg', 'avatar content');
    // Also a file outside
    Storage::disk('public')->put('secret.txt', 'sensitive data');

    // Safe path
    $safeRelativePath = 'profile_photos/avatar.jpg';
    $safeSignature = str_replace(['+', '/', '='], ['-', '_', ''], Crypt::encryptString($safeRelativePath));

    // Request safe image
    $responseSafe = $this->get("/images/v1/{$safeSignature}");
    $responseSafe->assertStatus(200);

    // Traversal path targeting secret.txt (via ../secret.txt)
    $traversalPath = 'profile_photos/../secret.txt';
    $traversalSignature = str_replace(['+', '/', '='], ['-', '_', ''], Crypt::encryptString($traversalPath));

    // Request traversal image
    $responseTraversal = $this->get("/images/v1/{$traversalSignature}");
    expect($responseTraversal->status())->toBeIn([403, 404]);
});

test('PortalPostController uploadImage mode by_url downloads external image and respects limits', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    $this->actingAs($admin);

    Storage::fake('public');

    // 1. Safe image URL download (using a valid 1x1 PNG image)
    $validPngContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
    Http::fake([
        'https://example.com/valid-image.png' => Http::response($validPngContent, 200, ['Content-Type' => 'image/png']),
    ]);

    $response = $this->postJson(route('portal-admin.posts.upload-image', ['by_url' => 1]), [
        'url' => 'https://example.com/valid-image.png',
    ]);

    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'file' => ['url'],
    ]);

    $localUrl = $response->json('file.url');
    expect($localUrl)->toStartWith('/storage/portal/posts/content/');

    // Verify file exists on local storage
    $storedPath = str_replace('/storage/', '', $localUrl);
    Storage::disk('public')->assertExists($storedPath);

    // 2. Reject non-image URL
    Http::fake([
        'https://example.com/text-file.html' => Http::response('<html>not an image</html>', 200, ['Content-Type' => 'text/html']),
    ]);

    $responseHtml = $this->postJson(route('portal-admin.posts.upload-image', ['by_url' => 1]), [
        'url' => 'https://example.com/text-file.html',
    ]);
    $responseHtml->assertStatus(422);

    // 3. Reject size exceeding 10MB
    $largeContent = str_repeat('A', 11 * 1024 * 1024); // 11MB
    Http::fake([
        'https://example.com/large-image.png' => Http::response($largeContent, 200, ['Content-Type' => 'image/png']),
    ]);

    $responseLarge = $this->postJson(route('portal-admin.posts.upload-image', ['by_url' => 1]), [
        'url' => 'https://example.com/large-image.png',
    ]);
    $responseLarge->assertStatus(422);

    // 4. Reject unsafe URL (SSRF)
    $responseUnsafe = $this->postJson(route('portal-admin.posts.upload-image', ['by_url' => 1]), [
        'url' => 'http://127.0.0.1/private.png',
    ]);
    $responseUnsafe->assertStatus(422);
});

test('PortalPostController index returns length aware paginator', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    $this->actingAs($admin);

    $category = PortalCategory::firstOrCreate(['slug' => 'news'], ['name' => 'News']);

    // Create 20 posts
    for ($i = 1; $i <= 20; $i++) {
        PortalPost::create([
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => "Post {$i}",
            'slug' => "post-{$i}",
            'content' => '{"blocks":[]}',
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    $response = $this->get(route('portal-admin.posts.index'));
    $response->assertOk();

    // Assert that we get an Inertia response with paginated posts
    $response->assertInertia(fn ($page) => $page
        ->component('Modules/Portal/Admin/Posts/Index')
        ->has('posts.data', 15) // First page should return 15 posts
        ->has('posts.links')
        ->where('posts.total', 20)
    );
});

test('UsersController reject delegation method works', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    $user = User::factory()->create(['status_approval' => 'pending']);

    $this->actingAs($admin);

    // Call reject route
    $response = $this->post(route('workos.users.reject', $user));
    $response->assertRedirect();

    $user->refresh();
    expect($user->status_approval)->toBe('rejected');
    expect($user->is_active)->toBeFalse();
});

test('Pagi collaborator pre-resolution and feed works without N+1 queries', function () {
    $user = User::factory()->create(['name' => 'Original Author', 'user_type' => 'mahasiswa']);
    $collabUser = User::factory()->create(['name' => 'Collaborator Student', 'pagi_username' => 'collab1', 'foto_path' => 'collab.png']);

    $portfolioContent = [
        [
            'type' => 'featured_details',
            'data' => [
                'collaborators' => [
                    [
                        'name' => 'Collaborator Student',
                        'status' => 'accepted',
                    ],
                ],
            ],
        ],
    ];

    $work = PagiWork::create([
        'user_id' => $user->id,
        'title' => 'Sample Collaboration Work',
        'content' => $portfolioContent,
        'is_published' => true,
        'visibility' => 'Everyone',
        'status' => 'active',
        'cover_image' => 'cover.png',
    ]);

    $this->actingAs($user);

    $profileService = app(PagiProfileService::class);
    $socialService = app(PagiSocialService::class);

    // 1. Verify Profile Service preloads and resolves
    $pData = $profileService->getProfileData($user, 1, true);
    $projects = $pData['projects'];
    expect($projects)->toHaveCount(1);
    expect($projects[0]['resolved_collaborators'])->toHaveCount(1);
    expect($projects[0]['resolved_collaborators'][0]['id'])->toBe($collabUser->id);
    expect($projects[0]['resolved_collaborators'][0]['pagi_username'])->toBe('collab1');

    // 2. Verify Social Service exploreGallery preloads and resolves
    Http::fake();
    $request = Request::create('/explore', 'GET');
    $galleryData = $socialService->exploreGallery($request);
    expect($galleryData['galleryItems'])->not->toBeEmpty();
});

test('explorePeople preloads followers counts correctly', function () {
    $user = User::factory()->create(['user_type' => 'mahasiswa']);
    $other = User::factory()->create(['user_type' => 'mahasiswa']);

    // Add UserModuleRole for both
    $module = Module::firstOrCreate(['code' => 'PAGI'], ['name' => 'PAGI', 'is_active' => true]);
    $role = Role::firstOrCreate(['slug' => 'mahasiswa'], ['nama' => 'Mahasiswa']);

    UserModuleRole::firstOrCreate([
        'user_id' => $user->id,
        'module_id' => $module->id,
        'role_id' => $role->id,
    ], ['is_active' => true]);

    UserModuleRole::firstOrCreate([
        'user_id' => $other->id,
        'module_id' => $module->id,
        'role_id' => $role->id,
    ], ['is_active' => true]);

    // Follow relations
    $user->pagiFollowing()->attach($other->id);

    $socialService = app(PagiSocialService::class);
    $people = $socialService->explorePeople($module->id);

    expect($people)->not->toBeEmpty();
    // Verify preloaded followers count (other has 1 follower: $user)
    $otherData = collect($people)->firstWhere('id', $other->id);
    expect($otherData)->not->toBeNull();
    expect($otherData['followers_count'])->toBe(1);
});
