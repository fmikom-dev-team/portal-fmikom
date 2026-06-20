<?php

use App\Models\Module;
use App\Models\Pagi\PagiWork;
use App\Models\Pagi\PagiWorkComment;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModuleRole;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

function setupSocialNormalizationTestContext(User $user, string $roleSlug = 'mahasiswa'): void
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

test('artisan data migration command copies JSON columns to normalized tables and rolls back successfully', function () {
    // We rollback the drop migration to temporarily recreate the likes and comments columns in the test DB
    $steps = DB::table('migrations')
        ->where('migration', '>=', '2026_06_15_000001_drop_json_likes_and_comments_from_pagi_works_table')
        ->count();
    Artisan::call('migrate:rollback', ['--step' => $steps]);

    try {
        // 1. Create a work with legacy JSON likes & comments
        $user1 = User::factory()->create(['email_verified_at' => now(), 'password_changed_at' => now(), 'user_type' => 'mahasiswa', 'pagi_username' => 'user1']);
        $user2 = User::factory()->create(['email_verified_at' => now(), 'password_changed_at' => now(), 'user_type' => 'mahasiswa', 'pagi_username' => 'user2']);
        $user3 = User::factory()->create(['email_verified_at' => now(), 'password_changed_at' => now(), 'user_type' => 'mahasiswa', 'pagi_username' => 'user3']);

        setupSocialNormalizationTestContext($user1);
        setupSocialNormalizationTestContext($user2);
        setupSocialNormalizationTestContext($user3);

        $workId = DB::table('pagi_works')->insertGetId([
            'user_id' => $user1->id,
            'title' => 'Test Migration Work',
            'is_published' => true,
            'visibility' => 'Everyone',
            'content' => json_encode([]),
            'likes' => json_encode([$user2->id, $user3->id]),
            'comments' => json_encode([
                [
                    'id' => 'comment-1',
                    'user_id' => $user2->id,
                    'name' => 'User Two',
                    'avatar' => null,
                    'body' => 'Original Comment Body',
                    'created_at' => '2026-06-01T10:00:00.000000Z',
                    'likes' => [$user3->id],
                    'replies' => [
                        [
                            'id' => 'reply-1',
                            'user_id' => $user3->id,
                            'name' => 'User Three',
                            'avatar' => null,
                            'body' => 'Original Reply Body',
                            'created_at' => '2026-06-01T10:05:00.000000Z',
                            'likes' => [$user2->id],
                        ],
                    ],
                ],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $work = PagiWork::find($workId);

        // Verify raw DB values are set (bypassing model attributes)
        $rawLikes = DB::table('pagi_works')->where('id', $work->id)->value('likes');
        $rawComments = DB::table('pagi_works')->where('id', $work->id)->value('comments');
        expect(json_decode($rawLikes, true))->toEqual([$user2->id, $user3->id]);
        expect(json_decode($rawComments, true))->toHaveCount(1);

        // Run migrate command
        $this->artisan('pagi:migrate-social-data')->assertExitCode(0);

        // Verify normalized tables populated
        $this->assertDatabaseHas('pagi_work_likes', ['work_id' => $work->id, 'user_id' => $user2->id]);
        $this->assertDatabaseHas('pagi_work_likes', ['work_id' => $work->id, 'user_id' => $user3->id]);

        $this->assertDatabaseHas('pagi_work_comments', [
            'work_id' => $work->id,
            'uuid' => 'comment-1',
            'user_id' => $user2->id,
            'parent_id' => null,
            'body' => 'Original Comment Body',
        ]);

        $commentId = DB::table('pagi_work_comments')->where('uuid', 'comment-1')->value('id');

        $this->assertDatabaseHas('pagi_comment_likes', [
            'comment_id' => $commentId,
            'user_id' => $user3->id,
        ]);

        $this->assertDatabaseHas('pagi_work_comments', [
            'work_id' => $work->id,
            'uuid' => 'reply-1',
            'user_id' => $user3->id,
            'parent_id' => $commentId,
            'body' => 'Original Reply Body',
        ]);

        $replyId = DB::table('pagi_work_comments')->where('uuid', 'reply-1')->value('id');

        $this->assertDatabaseHas('pagi_comment_likes', [
            'comment_id' => $replyId,
            'user_id' => $user2->id,
        ]);

        // Test rollback command
        // Clear likes/comments columns in DB first to ensure we write back
        DB::table('pagi_works')->where('id', $work->id)->update([
            'likes' => null,
            'comments' => null,
        ]);

        $this->artisan('pagi:migrate-social-data --rollback')->assertExitCode(0);

        // Verify likes/comments columns populated again from normalized tables
        $rolledLikes = DB::table('pagi_works')->where('id', $work->id)->value('likes');
        $rolledComments = DB::table('pagi_works')->where('id', $work->id)->value('comments');

        expect(json_decode($rolledLikes, true))->toContain($user2->id, $user3->id);
        $commentsArr = json_decode($rolledComments, true);
        expect($commentsArr)->toHaveCount(1);
        expect($commentsArr[0]['id'])->toBe('comment-1');
        expect($commentsArr[0]['body'])->toBe('Original Comment Body');
        expect($commentsArr[0]['replies'])->toHaveCount(1);
        expect($commentsArr[0]['replies'][0]['id'])->toBe('reply-1');
        expect($commentsArr[0]['replies'][0]['body'])->toBe('Original Reply Body');
    } finally {
        Artisan::call('migrate');
    }
});

test('like preview work toggles relational like and updates quiet compatibility JSON', function () {
    $creator = User::factory()->create(['email_verified_at' => now(), 'password_changed_at' => now(), 'user_type' => 'mahasiswa', 'pagi_username' => 'creator']);
    $liker = User::factory()->create(['email_verified_at' => now(), 'password_changed_at' => now(), 'user_type' => 'mahasiswa', 'pagi_username' => 'liker']);

    setupSocialNormalizationTestContext($creator);
    setupSocialNormalizationTestContext($liker);

    $work = PagiWork::create([
        'user_id' => $creator->id,
        'title' => 'Sample Work',
        'is_published' => true,
        'visibility' => 'Everyone',
        'content' => [],
    ]);

    // 1. Like
    $response = $this
        ->actingAs($liker)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'mahasiswa'])
        ->post(route('module.pagi.preview.like', ['preview' => $work->id]));

    $response->assertOk();
    $response->assertJson([
        'liked' => true,
        'likes' => 1,
    ]);

    $this->assertDatabaseHas('pagi_work_likes', [
        'work_id' => $work->id,
        'user_id' => $liker->id,
    ]);

    // Verify backward compatibility accessor
    expect($work->fresh()->likes)->toEqual([$liker->id]);

    // 2. Unlike
    $response = $this
        ->actingAs($liker)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'mahasiswa'])
        ->post(route('module.pagi.preview.like', ['preview' => $work->id]));

    $response->assertOk();
    $response->assertJson([
        'liked' => false,
        'likes' => 0,
    ]);

    $this->assertDatabaseMissing('pagi_work_likes', [
        'work_id' => $work->id,
        'user_id' => $liker->id,
    ]);

    expect($work->fresh()->likes)->toEqual([]);
});

test('comments, replies, and comments/replies likes are stored in normalized tables and readable via compatibility layer', function () {
    $creator = User::factory()->create(['email_verified_at' => now(), 'password_changed_at' => now(), 'user_type' => 'mahasiswa', 'pagi_username' => 'creator']);
    $commenter1 = User::factory()->create(['email_verified_at' => now(), 'password_changed_at' => now(), 'user_type' => 'mahasiswa', 'pagi_username' => 'commenter1']);
    $commenter2 = User::factory()->create(['email_verified_at' => now(), 'password_changed_at' => now(), 'user_type' => 'mahasiswa', 'pagi_username' => 'commenter2']);

    setupSocialNormalizationTestContext($creator);
    setupSocialNormalizationTestContext($commenter1);
    setupSocialNormalizationTestContext($commenter2);

    $work = PagiWork::create([
        'user_id' => $creator->id,
        'title' => 'Interactive Work',
        'is_published' => true,
        'visibility' => 'Everyone',
        'content' => [],
    ]);

    // 1. Post a new comment
    $response = $this
        ->actingAs($commenter1)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'mahasiswa'])
        ->post(route('module.pagi.preview.comment', ['preview' => $work->id]), [
            'body' => 'Ini adalah komentar pertama saya.',
        ]);

    $response->assertOk();

    // Check comment created in DB
    $this->assertDatabaseHas('pagi_work_comments', [
        'work_id' => $work->id,
        'user_id' => $commenter1->id,
        'parent_id' => null,
        'body' => 'Ini adalah komentar pertama saya.',
    ]);

    $commentRecord = PagiWorkComment::where('work_id', $work->id)->whereNull('parent_id')->first();
    expect($commentRecord)->not->toBeNull();

    // 2. Like the comment
    $response = $this
        ->actingAs($commenter2)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'mahasiswa'])
        ->post(route('module.pagi.preview.comment.like', [
            'preview' => $work->id,
            'comment' => $commentRecord->uuid,
        ]));

    $response->assertOk();

    $this->assertDatabaseHas('pagi_comment_likes', [
        'comment_id' => $commentRecord->id,
        'user_id' => $commenter2->id,
    ]);

    // 3. Reply to the comment
    $response = $this
        ->actingAs($commenter2)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'mahasiswa'])
        ->post(route('module.pagi.preview.comment.reply', [
            'preview' => $work->id,
            'comment' => $commentRecord->uuid,
        ]), [
            'body' => 'Membalas komentar pertama.',
            'reply_to_user_id' => $commenter1->id,
        ]);

    $response->assertOk();

    $this->assertDatabaseHas('pagi_work_comments', [
        'work_id' => $work->id,
        'user_id' => $commenter2->id,
        'parent_id' => $commentRecord->id,
        'body' => 'Membalas komentar pertama.',
    ]);

    $replyRecord = PagiWorkComment::where('parent_id', $commentRecord->id)->first();
    expect($replyRecord)->not->toBeNull();

    // 4. Like the reply
    $response = $this
        ->actingAs($commenter1)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'mahasiswa'])
        ->post(route('module.pagi.preview.comment.reply.like', [
            'preview' => $work->id,
            'comment' => $commentRecord->uuid,
            'reply' => $replyRecord->uuid,
        ]));

    $response->assertOk();

    $this->assertDatabaseHas('pagi_comment_likes', [
        'comment_id' => $replyRecord->id,
        'user_id' => $commenter1->id,
    ]);

    // 5. Verify compatibility accessor output matches structure
    $commentsArray = $work->fresh()->comments;
    expect($commentsArray)->toHaveCount(1);
    expect($commentsArray[0]['id'])->toBe($commentRecord->uuid);
    expect($commentsArray[0]['body'])->toBe('Ini adalah komentar pertama saya.');
    expect($commentsArray[0]['likes'])->toEqual([$commenter2->id]);
    expect($commentsArray[0]['replies'])->toHaveCount(1);
    expect($commentsArray[0]['replies'][0]['id'])->toBe($replyRecord->uuid);
    expect($commentsArray[0]['replies'][0]['body'])->toBe('Membalas komentar pertama.');
    expect($commentsArray[0]['replies'][0]['likes'])->toEqual([$commenter1->id]);
});
