<?php

use App\Models\JenisSurat;
use App\Models\Module;
use App\Models\Role;
use App\Models\Surat;
use App\Models\SuratHistory;
use App\Models\User;
use App\Models\UserModuleRole;
use App\Modules\Fast\Services\Shared\NotificationFeedService;
use App\Modules\Fast\Services\Shared\SuratDocumentGeneratorService;
use App\Modules\Fast\Support\FastPermissionCatalog;
use App\Modules\Fast\Workflow\Actions\SuratWorkflowService;
use Illuminate\Support\Facades\URL;

function fastWorkflowJenisSurat(string $slug, string $approvalRole = 'kaprodi'): JenisSurat
{
    $role = Role::firstOrCreate(
        ['slug' => $approvalRole],
        ['nama' => ucfirst($approvalRole), 'deskripsi' => 'FAST test role'],
    );

    return JenisSurat::create([
        'nama' => 'Surat Workflow Test',
        'slug' => $slug,
        'deskripsi' => 'FAST workflow test',
        'field_config' => [],
        'approval_role_id' => $role->id,
        'perlu_approval' => true,
        'alur_pengajuan' => 'submission',
        'letter_mode' => 'personal',
        'is_active' => true,
    ]);
}

function fastWorkflowUser(string $role): User
{
    $user = User::factory()->create([
        'user_type' => $role,
        'email_verified_at' => now(),
    ]);

    $module = Module::firstOrCreate(['code' => 'FAST'], [
        'name' => 'FAST',
        'is_active' => true,
    ]);
    $roleModel = Role::firstOrCreate(
        ['slug' => $role],
        ['nama' => ucfirst($role), 'deskripsi' => 'FAST test role'],
    );

    UserModuleRole::create([
        'user_id' => $user->id,
        'module_id' => $module->id,
        'role_id' => $roleModel->id,
        'is_active' => true,
    ]);

    return $user;
}

function fakeFastDocumentGenerator(): void
{
    $generator = Mockery::mock(SuratDocumentGeneratorService::class);
    $generator->shouldReceive('prepareDraft')->zeroOrMoreTimes()->andReturnUsing(
        fn (Surat $surat): Surat => $surat,
    );
    $generator->shouldReceive('generate')->zeroOrMoreTimes()->andReturnUsing(
        fn (Surat $surat): Surat => $surat,
    );

    app()->instance(SuratDocumentGeneratorService::class, $generator);
}

it('runs a student submission through admin validation and kaprodi approval', function () {
    fakeFastDocumentGenerator();

    $student = fastWorkflowUser('mahasiswa');
    $admin = fastWorkflowUser('admin');
    $kaprodi = fastWorkflowUser('kaprodi');
    $jenisSurat = fastWorkflowJenisSurat('fast-core-workflow-approval-test');

    $this->actingAs($student);
    $surat = app(SuratWorkflowService::class)->submit($student, [
        'jenis_surat_id' => $jenisSurat->id,
        'keperluan' => 'Pengujian alur pengajuan surat FAST',
        'data' => [],
    ]);

    expect($surat->status)->toBe(Surat::STATUS_PENDING)
        ->and($surat->histories()->where('action', SuratHistory::ACTION_SUBMITTED)->exists())->toBeTrue();

    $this->actingAs($admin);
    $surat = app(SuratWorkflowService::class)->adminReview($surat->fresh(), $admin, [
        'decision' => 'validated',
        'admin_note' => 'Data lengkap.',
        'data' => [],
    ]);

    expect($surat->status)->toBe(Surat::STATUS_VALIDATED_ADMIN);

    $this->actingAs($kaprodi)->withSession(['active_role' => 'kaprodi']);
    $surat = app(SuratWorkflowService::class)->approve($surat->fresh(), $kaprodi, [
        'decision' => 'approved',
        'notes' => 'Disetujui untuk pengujian.',
    ]);

    expect($surat->status)->toBe(Surat::STATUS_APPROVED_KAPRODI)
        ->and($surat->approvalFlows()->where('role', 'kaprodi')->where('status', 'approved')->exists())->toBeTrue();
});

it('records a revision request from the configured approval role', function () {
    fakeFastDocumentGenerator();

    $student = fastWorkflowUser('mahasiswa');
    $admin = fastWorkflowUser('admin');
    $kaprodi = fastWorkflowUser('kaprodi');
    $jenisSurat = fastWorkflowJenisSurat('fast-core-workflow-revision-test');

    $this->actingAs($student);
    $surat = app(SuratWorkflowService::class)->submit($student, [
        'jenis_surat_id' => $jenisSurat->id,
        'keperluan' => 'Pengujian alur revisi surat FAST',
        'data' => [],
    ]);

    $this->actingAs($admin);
    $surat = app(SuratWorkflowService::class)->adminReview($surat->fresh(), $admin, [
        'decision' => 'validated',
        'data' => [],
    ]);

    $this->actingAs($kaprodi)->withSession(['active_role' => 'kaprodi']);
    $surat = app(SuratWorkflowService::class)->approve($surat->fresh(), $kaprodi, [
        'decision' => 'revision_requested',
        'rejection_reason' => 'Mohon lengkapi data pendukung.',
    ]);

    expect($surat->status)->toBe(Surat::STATUS_REVISION_REQUESTED)
        ->and($surat->revisi_ke)->toBe(1)
        ->and($surat->catatan_revisi)->toBe('Mohon lengkapi data pendukung.');
});

it('supports dekan final rejection after admin validation', function () {
    fakeFastDocumentGenerator();

    $student = fastWorkflowUser('mahasiswa');
    $admin = fastWorkflowUser('admin');
    $dekan = fastWorkflowUser('dekan');
    $jenisSurat = fastWorkflowJenisSurat('fast-core-workflow-dekan-reject-test', 'dekan');

    $this->actingAs($student);
    $surat = app(SuratWorkflowService::class)->submit($student, [
        'jenis_surat_id' => $jenisSurat->id,
        'keperluan' => 'Pengujian penolakan final surat FAST',
        'data' => [],
    ]);

    $this->actingAs($admin);
    $surat = app(SuratWorkflowService::class)->adminReview($surat->fresh(), $admin, [
        'decision' => 'validated',
        'data' => [],
    ]);

    $this->actingAs($dekan)->withSession(['active_role' => 'dekan']);
    $surat = app(SuratWorkflowService::class)->approve($surat->fresh(), $dekan, [
        'decision' => 'rejected_final',
        'rejection_reason' => 'Dokumen tidak memenuhi ketentuan fakultas.',
    ]);

    expect($surat->status)->toBe(Surat::STATUS_REJECTED_APPROVER)
        ->and($surat->rejection_reason)->toBe('Dokumen tidak memenuhi ketentuan fakultas.')
        ->and($surat->approvalFlows()->where('role', 'dekan')->where('status', 'rejected_final')->exists())->toBeTrue();
});

it('exposes only valid finished documents through QR verification', function () {
    $student = User::factory()->create(['user_type' => 'mahasiswa']);
    $jenisSurat = fastWorkflowJenisSurat('fast-core-workflow-qr-test');
    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $student->id,
        'keperluan' => 'Pengujian verifikasi QR surat FAST',
        'status' => Surat::STATUS_FINISHED,
        'nomor_surat' => 'B/QR/TEST/2026',
        'qr_token' => 'fast-qr-token-test',
        'isi_surat' => json_encode(['data' => []], JSON_THROW_ON_ERROR),
        'tanggal_pengajuan' => now(),
    ]);

    $this->get(route('qr.verify', $surat->qr_token))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('found', true)
            ->where('valid', true)
            ->where('surat.nomor_surat', 'B/QR/TEST/2026'));

    $surat->update(['status' => Surat::STATUS_PENDING]);

    $this->get(URL::route('qr.verify', $surat->qr_token))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('found', true)
            ->where('valid', false)
            ->where('surat.status', 'belum_divalidasi'));
});

it('builds a requester notification for a finished surat', function () {
    $student = fastWorkflowUser('mahasiswa');
    $jenisSurat = fastWorkflowJenisSurat('fast-core-workflow-notification-test');
    Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $student->id,
        'keperluan' => 'Pengujian notifikasi surat FAST',
        'status' => Surat::STATUS_FINISHED,
        'tanggal_pengajuan' => now(),
        'tanggal_selesai' => now(),
        'isi_surat' => json_encode(['data' => []], JSON_THROW_ON_ERROR),
    ]);

    $feed = app(NotificationFeedService::class)->build($student, 'mahasiswa');

    expect($feed['count'])->toBeGreaterThanOrEqual(1)
        ->and($feed['items'][0]['title'])->toBe('Surat Workflow Test');
});

it('renders the admin dashboard, history, and archive pages for an admin assignment', function () {
    $admin = fastWorkflowUser('admin');

    $this->actingAs($admin)->withSession([
        'active_module' => 'FAST',
        'active_role' => 'admin',
    ]);

    $this->get('/admin/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Modules/Fast/Admin/dashboard/Index'));

    $this->get('/admin/history')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Modules/Fast/Admin/history/Index'));

    $this->get('/admin/archive')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Modules/Fast/Admin/archive/Index'));
});

it('keeps FAST admin role permissions and policy access aligned', function () {
    foreach (['admin', 'super-admin', 'admin-universitas', 'admin-akademik', 'prodi'] as $role) {
        $user = fastWorkflowUser($role);

        expect(FastPermissionCatalog::has($user, 'fast.admin.dashboard.view', $role))->toBeTrue()
            ->and($this->actingAs($user)->get('/admin/dashboard')->status())->toBe(200);
    }
});

it('renders the shared FAST approval page for a kaprodi assignment', function () {
    $kaprodi = fastWorkflowUser('kaprodi');

    $this->actingAs($kaprodi)->withSession([
        'active_module' => 'FAST',
        'active_role' => 'kaprodi',
    ])->get('/kaprodi/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Modules/Fast/Shared/approval/Index'));
});

it('does not allow a revoked approval assignment to use the old session role', function () {
    $approver = fastWorkflowUser('kaprodi');
    $assignment = UserModuleRole::query()
        ->where('user_id', $approver->id)
        ->where('is_active', true)
        ->firstOrFail();
    $assignment->update(['is_active' => false]);

    $this->actingAs($approver)->withSession([
        'active_module' => 'FAST',
        'active_role' => 'kaprodi',
    ])->get('/approval/dashboard')
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('error');
});

it('lets a super-admin use the assigned mahasiswa FAST context', function () {
    $superAdmin = fastWorkflowUser('super-admin');
    $module = Module::query()->where('code', 'FAST')->firstOrFail();
    $mahasiswaRole = Role::firstOrCreate(
        ['slug' => 'mahasiswa'],
        ['nama' => 'Mahasiswa', 'deskripsi' => 'FAST test role'],
    );

    UserModuleRole::firstOrCreate([
        'user_id' => $superAdmin->id,
        'module_id' => $module->id,
        'role_id' => $mahasiswaRole->id,
    ], ['is_active' => true]);

    $this->actingAs($superAdmin)->withSession([
        'active_module' => 'FAST',
        'active_role' => 'mahasiswa',
    ])->get('/mahasiswa/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Modules/Fast/Mahasiswa/Dashboard'));
});

it('does not allow mahasiswa context to open dosen FAST routes', function () {
    $mahasiswa = fastWorkflowUser('mahasiswa');

    $this->actingAs($mahasiswa)->withSession([
        'active_module' => 'FAST',
        'active_role' => 'mahasiswa',
    ])->get('/dosen/dashboard')
        ->assertForbidden();
});
