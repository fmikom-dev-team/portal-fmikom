<?php

use App\Models\Module;
use App\Models\Portal\PortalCategory;
use App\Models\Portal\PortalComment;
use App\Models\Portal\PortalPost;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModuleRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function setupPagiTestContext(User $user): void
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

test('pagi chat send message and active chats relational tracking', function () {
    $sender = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'user_type' => 'mahasiswa',
        'pagi_username' => 'sender',
    ]);
    $receiver = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'user_type' => 'mahasiswa',
        'pagi_username' => 'receiver',
    ]);

    setupPagiTestContext($sender);
    setupPagiTestContext($receiver);

    $response = $this
        ->actingAs($sender)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->post(route('module.pagi.messages.store'), [
            'receiver_id' => $receiver->id,
            'body' => 'Hello partner!',
        ]);

    $response->assertStatus(201);

    // Verify active chats records created
    $this->assertDatabaseHas('pagi_active_chats', [
        'user_id' => $sender->id,
        'partner_id' => $receiver->id,
    ]);
    $this->assertDatabaseHas('pagi_active_chats', [
        'user_id' => $receiver->id,
        'partner_id' => $sender->id,
    ]);
});

test('pagi block and unblock user', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'user_type' => 'mahasiswa',
        'pagi_username' => 'user1',
    ]);
    $partner = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'user_type' => 'mahasiswa',
        'pagi_username' => 'partner1',
    ]);

    setupPagiTestContext($user);
    setupPagiTestContext($partner);

    // 1. Block partner
    $responseBlock = $this
        ->actingAs($user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->post(route('module.pagi.messages.block'), [
            'partner_id' => $partner->id,
        ]);

    $responseBlock->assertStatus(200);
    $this->assertDatabaseHas('pagi_blocks', [
        'user_id' => $user->id,
        'blocked_id' => $partner->id,
    ]);

    // Test sending message when blocked
    $responseSend = $this
        ->actingAs($user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->post(route('module.pagi.messages.store'), [
            'receiver_id' => $partner->id,
            'body' => 'Block test',
        ]);
    $responseSend->assertStatus(403);

    // 2. Unblock partner
    $responseUnblock = $this
        ->actingAs($user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->post(route('module.pagi.messages.unblock'), [
            'partner_id' => $partner->id,
        ]);

    $responseUnblock->assertStatus(200);
    $this->assertDatabaseMissing('pagi_blocks', [
        'user_id' => $user->id,
        'blocked_id' => $partner->id,
    ]);
});

test('pagi pin, archive, unread toggle conversation', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'user_type' => 'mahasiswa',
        'pagi_username' => 'chatuser',
    ]);
    $partner = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'user_type' => 'mahasiswa',
        'pagi_username' => 'chatpartner',
    ]);

    setupPagiTestContext($user);
    setupPagiTestContext($partner);

    // Pin
    $responsePin = $this
        ->actingAs($user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->post(route('module.pagi.messages.pin'), [
            'partner_id' => $partner->id,
        ]);
    $responsePin->assertStatus(200)->assertJson(['is_pinned' => true]);
    $this->assertDatabaseHas('pagi_pinned_chats', [
        'user_id' => $user->id,
        'partner_id' => $partner->id,
    ]);

    // Unpin
    $responseUnpin = $this
        ->actingAs($user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->post(route('module.pagi.messages.pin'), [
            'partner_id' => $partner->id,
        ]);
    $responseUnpin->assertStatus(200)->assertJson(['is_pinned' => false]);
    $this->assertDatabaseMissing('pagi_pinned_chats', [
        'user_id' => $user->id,
        'partner_id' => $partner->id,
    ]);

    // Archive
    $responseArchive = $this
        ->actingAs($user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->post(route('module.pagi.messages.archive'), [
            'partner_id' => $partner->id,
        ]);
    $responseArchive->assertStatus(200)->assertJson(['is_archived' => true]);
    $this->assertDatabaseHas('pagi_archived_chats', [
        'user_id' => $user->id,
        'partner_id' => $partner->id,
    ]);

    // Unread manual
    $responseUnread = $this
        ->actingAs($user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->post(route('module.pagi.messages.unread'), [
            'partner_id' => $partner->id,
        ]);
    $responseUnread->assertStatus(200)->assertJson(['status' => 'unread']);
    $this->assertDatabaseHas('pagi_unread_chats', [
        'user_id' => $user->id,
        'partner_id' => $partner->id,
    ]);
});

test('pagi clear and delete conversation', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'user_type' => 'mahasiswa',
        'pagi_username' => 'deluser',
    ]);
    $partner = User::factory()->create([
        'email_verified_at' => now(),
        'password_changed_at' => now(),
        'user_type' => 'mahasiswa',
        'pagi_username' => 'delpartner',
    ]);

    setupPagiTestContext($user);
    setupPagiTestContext($partner);

    // Clear
    $responseClear = $this
        ->actingAs($user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->delete(route('module.pagi.messages.clear'), [
            'partner_id' => $partner->id,
        ]);
    $responseClear->assertStatus(200);
    $this->assertDatabaseHas('pagi_cleared_chats', [
        'user_id' => $user->id,
        'partner_id' => $partner->id,
    ]);

    // Delete conversation
    $responseDelete = $this
        ->actingAs($user)
        ->withSession([
            'active_module' => 'pagi',
            'active_role' => 'mahasiswa',
        ])
        ->delete(route('module.pagi.messages.delete-conversation'), [
            'partner_id' => $partner->id,
        ]);
    $responseDelete->assertStatus(200);
});

test('comments cascade delete when a post is deleted (DB-003)', function () {
    $user = User::factory()->create();
    $category = PortalCategory::create([
        'name' => 'News',
        'slug' => 'news',
    ]);
    $post = PortalPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Sample Post',
        'slug' => 'sample-post',
        'content' => 'Sample body',
        'status' => PortalPost::STATUS_PUBLISHED,
    ]);

    $comment = PortalComment::create([
        'post_id' => $post->id,
        'author_name' => 'Commenter',
        'content' => 'Nice post!',
    ]);

    $this->assertDatabaseHas('portal_comments', [
        'id' => $comment->id,
    ]);

    // Delete post and assert comment deleted
    $post->delete();

    $this->assertDatabaseMissing('portal_comments', [
        'id' => $comment->id,
    ]);
});

test('password history transaction rollback safety (DB-004)', function () {
    $user = User::factory()->create([
        'password' => Hash::make('original-password'),
    ]);

    // Standard save outside transaction should insert password history because there is no outer transaction, or it commits instantly.
    $user->update(['password' => Hash::make('new-password-1')]);

    $this->assertDatabaseHas('auth_password_histories', [
        'user_id' => $user->id,
        'password_hash' => $user->password,
    ]);

    // Rollback test: password history should not be written during active transaction (before commit)
    DB::beginTransaction();

    $user->update(['password' => Hash::make('rolledback-password')]);

    // Since it's wrapped in afterCommit, it shouldn't be in the DB yet
    $this->assertDatabaseMissing('auth_password_histories', [
        'user_id' => $user->id,
        'password_hash' => $user->password,
    ]);

    DB::rollBack();

    // After rollback, it should definitely still be missing
    $this->assertDatabaseMissing('auth_password_histories', [
        'user_id' => $user->id,
        'password_hash' => $user->password,
    ]);

    // Commit test: when transaction commits, it should be successfully inserted
    DB::beginTransaction();

    $user->update(['password' => Hash::make('committed-password')]);

    $this->assertDatabaseMissing('auth_password_histories', [
        'user_id' => $user->id,
        'password_hash' => $user->password,
    ]);

    DB::commit();

    // After commit, the afterCommit callback runs and inserts the history
    $this->assertDatabaseHas('auth_password_histories', [
        'user_id' => $user->id,
        'password_hash' => $user->password,
    ]);
});
