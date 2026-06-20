<?php

/** @var TestCase $this */

use App\Models\Fakultas;
use App\Models\Module;
use App\Models\Pagi\PagiMessage;
use App\Models\Pagi\PagiWork;
use App\Models\ProgramStudi;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModuleRole;
use Tests\TestCase;

function setupPagiAuditTestContext(User $user, string $roleSlug = 'mahasiswa'): void
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

test('unauthorized users cannot react to messages in private chats (react IDOR)', function () {
    /** @var TestCase $this */
    $sender = User::factory()->create(['user_type' => 'mahasiswa', 'pagi_username' => 'sender']);
    $receiver = User::factory()->create(['user_type' => 'mahasiswa', 'pagi_username' => 'receiver']);
    $attacker = User::factory()->create(['user_type' => 'mahasiswa', 'pagi_username' => 'attacker']);

    setupPagiAuditTestContext($sender);
    setupPagiAuditTestContext($receiver);
    setupPagiAuditTestContext($attacker);

    $message = PagiMessage::create([
        'conversation_id' => PagiMessage::conversationId($sender->id, $receiver->id),
        'sender_id' => $sender->id,
        'receiver_id' => $receiver->id,
        'body' => 'Secret message',
        'reactions' => [],
    ]);

    // Attacker attempts to react -> Should get 403
    $responseAttacker = $this
        ->actingAs($attacker)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'mahasiswa'])
        ->post(route('module.pagi.messages.react', ['message' => $message->id]), [
            'emoji' => '👍',
        ]);
    $responseAttacker->assertStatus(403);

    // Sender reacts -> Should succeed (200)
    $responseSender = $this
        ->actingAs($sender)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'mahasiswa'])
        ->post(route('module.pagi.messages.react', ['message' => $message->id]), [
            'emoji' => '👍',
        ]);
    $responseSender->assertStatus(200);

    // Receiver reacts -> Should succeed (200)
    $responseReceiver = $this
        ->actingAs($receiver)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'mahasiswa'])
        ->post(route('module.pagi.messages.react', ['message' => $message->id]), [
            'emoji' => '❤️',
        ]);
    $responseReceiver->assertStatus(200);
});

test('unauthorized users cannot accept/decline collaboration (collaboration IDOR)', function () {
    /** @var TestCase $this */
    $owner = User::factory()->create(['user_type' => 'mahasiswa', 'pagi_username' => 'owner']);
    $collaborator = User::factory()->create(['user_type' => 'mahasiswa', 'name' => 'John Doe', 'pagi_username' => 'johndoe']);
    $attacker = User::factory()->create(['user_type' => 'mahasiswa', 'name' => 'Attacker', 'pagi_username' => 'attacker']);

    setupPagiAuditTestContext($owner);
    setupPagiAuditTestContext($collaborator);
    setupPagiAuditTestContext($attacker);

    $work = PagiWork::create([
        'user_id' => $owner->id,
        'title' => 'Project Alpha',
        'content' => [
            [
                'type' => 'featured_details',
                'collaborators' => [
                    [
                        'user_id' => $collaborator->id,
                        'name' => 'John Doe',
                        'status' => 'pending',
                    ],
                ],
            ],
        ],
        'is_published' => true,
    ]);

    // Attacker tries to accept -> 403
    $responseAccept = $this
        ->actingAs($attacker)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'mahasiswa'])
        ->post(route('module.pagi.editor.collaboration.accept', ['editor' => $work->id]));
    $responseAccept->assertStatus(403);

    // Attacker tries to decline -> 403
    $responseDecline = $this
        ->actingAs($attacker)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'mahasiswa'])
        ->post(route('module.pagi.editor.collaboration.decline', ['editor' => $work->id]));
    $responseDecline->assertStatus(403);

    // Collaborator accepts -> success and preserves user_id
    $responseSuccess = $this
        ->actingAs($collaborator)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'mahasiswa'])
        ->post(route('module.pagi.editor.collaboration.accept', ['editor' => $work->id]));
    $responseSuccess->assertStatus(200);

    // Verify status updated and user_id is preserved
    $updatedWork = PagiWork::query()->find($work->id, ['*']);
    $collabs = $updatedWork->content[0]['collaborators'];
    $this->assertEquals('accepted', $collabs[0]['status']);
    $this->assertEquals($collaborator->id, $collabs[0]['user_id']);
});

test('prodi role is restricted to warning users and moderating works within their study program', function () {
    /** @var TestCase $this */
    $fakultas = Fakultas::firstOrCreate(['kode' => 'FIK'], ['nama' => 'Fakultas Ilmu Komputer']);
    $prodi1 = ProgramStudi::firstOrCreate(['kode' => 'IF'], ['nama' => 'Informatika', 'fakultas_id' => $fakultas->id]);
    $prodi2 = ProgramStudi::firstOrCreate(['kode' => 'SI'], ['nama' => 'Sistem Informasi', 'fakultas_id' => $fakultas->id]);

    $prodiAdmin = User::factory()->create([
        'user_type' => 'prodi',
        'program_studi_id' => $prodi1->id,
    ]);
    $studentSameProdi = User::factory()->create([
        'user_type' => 'mahasiswa',
        'program_studi_id' => $prodi1->id,
    ]);
    $studentDiffProdi = User::factory()->create([
        'user_type' => 'mahasiswa',
        'program_studi_id' => $prodi2->id,
    ]);

    setupPagiAuditTestContext($prodiAdmin, 'prodi');
    setupPagiAuditTestContext($studentSameProdi);
    setupPagiAuditTestContext($studentDiffProdi);

    $workSame = PagiWork::create([
        'user_id' => $studentSameProdi->id,
        'title' => 'My Prodi Work',
        'is_published' => true,
    ]);
    $workDiff = PagiWork::create([
        'user_id' => $studentDiffProdi->id,
        'title' => 'Other Prodi Work',
        'is_published' => true,
    ]);

    // 1. Warn User
    // Warn user in different prodi -> 403
    $this->actingAs($prodiAdmin)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'prodi'])
        ->post(route('module.pagi.admin.users.warn', ['user' => $studentDiffProdi->id]), ['reason' => 'Invalid'])
        ->assertStatus(403);

    // Warn user in same prodi -> success (302 redirect)
    $this->actingAs($prodiAdmin)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'prodi'])
        ->post(route('module.pagi.admin.users.warn', ['user' => $studentSameProdi->id]), ['reason' => 'Fine'])
        ->assertStatus(302);

    // 2. Hide Content
    // Hide content in different prodi -> 403
    $this->actingAs($prodiAdmin)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'prodi'])
        ->post(route('module.pagi.admin.content.moderate', ['work' => $workDiff->id]), ['reason' => 'Violation', 'action' => 'hide'])
        ->assertStatus(403);

    // Hide content in same prodi -> 302
    $this->actingAs($prodiAdmin)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'prodi'])
        ->post(route('module.pagi.admin.content.moderate', ['work' => $workSame->id]), ['reason' => 'Ok', 'action' => 'hide'])
        ->assertStatus(302);
});

test('reset moderation endpoint is restricted to super-admin and admin roles only', function () {
    /** @var TestCase $this */
    $superAdmin = User::factory()->create(['user_type' => 'super-admin']);
    $prodiAdmin = User::factory()->create(['user_type' => 'prodi']);

    setupPagiAuditTestContext($superAdmin, 'super-admin');
    setupPagiAuditTestContext($prodiAdmin, 'prodi');

    // Prodi tries to reset moderation -> 403
    $this->actingAs($prodiAdmin)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'prodi'])
        ->post(route('module.pagi.admin.reset-moderation'))
        ->assertStatus(403);

    // Super Admin tries to reset moderation -> success (302)
    $this->actingAs($superAdmin)
        ->withSession(['active_module' => 'pagi', 'active_role' => 'super-admin'])
        ->post(route('module.pagi.admin.reset-moderation'))
        ->assertStatus(302);
});
