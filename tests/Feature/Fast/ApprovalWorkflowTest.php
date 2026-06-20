<?php

use App\Models\JenisSurat;
use App\Models\Role;
use App\Models\Surat;
use App\Models\SuratHistory;
use App\Models\SuratTemplate;
use App\Models\User;
use App\Services\FastApprovalWorkflowService;
use App\Services\SuratWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function createTestTemplateForJenisSurat(JenisSurat $jenisSurat): void
{
    $template = SuratTemplate::create([
        'jenis_surat_id' => $jenisSurat->id,
        'name' => 'Template Test '.$jenisSurat->nama,
        'slug' => 'template-test-'.$jenisSurat->id,
        'format' => 'html',
        'template_body' => '<p>{{nama_pemohon}} - {{nomor_surat}}</p>',
        'version' => 1,
        'is_active' => true,
    ]);

    $template->placeholders()->createMany([
        [
            'placeholder_key' => 'nama_pemohon',
            'label' => 'Nama Pemohon',
            'source_type' => 'user',
            'source_key' => 'name',
            'is_required' => true,
        ],
        [
            'placeholder_key' => 'nomor_surat',
            'label' => 'Nomor Surat',
            'source_type' => 'surat',
            'source_key' => 'nomor_surat',
            'is_required' => true,
        ],
    ]);
}

test('approval action writes approval flow and updates current surat status', function () {
    $kaprodiRole = Role::create([
        'nama' => 'Kaprodi',
        'slug' => 'kaprodi',
    ]);

    $approverRole = Role::create([
        'nama' => 'Administrasi',
        'slug' => 'administrasi',
    ]);

    $pemohonRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $approver = createUserWithType($approverRole->slug, [
    ]);

    $pemohon = createUserWithType($pemohonRole->slug, [
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Keterangan Mahasiswa Aktif',
        'approval_role_id' => $kaprodiRole->id,
        'perlu_approval' => true,
    ]);
    createTestTemplateForJenisSurat($jenisSurat);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Keperluan pengajuan beasiswa akademik semester berjalan.',
        'status' => Surat::STATUS_PENDING,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDays(2)->toDateString(),
    ]);

    app(FastApprovalWorkflowService::class)->approve(
        $surat->fresh(),
        FastApprovalWorkflowService::ROLE_ADMIN,
        $approver,
    );

    $this->assertDatabaseHas('surats', [
        'id' => $surat->id,
        'status' => Surat::STATUS_VALIDATED_ADMIN,
    ]);

    $this->assertDatabaseHas('surat_approval_flows', [
        'surat_id' => $surat->id,
        'approver_id' => $approver->id,
        'urutan' => 1,
        'role' => FastApprovalWorkflowService::ROLE_ADMIN,
        'status' => 'approved',
    ]);
});

test('admin validation prepares draft and nomor surat before final approver review', function () {
    $adminRole = Role::create([
        'nama' => 'Administrasi',
        'slug' => 'administrasi',
    ]);

    $dekanRole = Role::create([
        'nama' => 'Dekan',
        'slug' => 'dekan',
    ]);

    $pemohonRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $pemohon = createUserWithType($pemohonRole->slug, [
        'name' => 'Nadia Putri',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Fakultas Final Review',
        'approval_role_id' => $dekanRole->id,
        'perlu_approval' => true,
        'kode_surat' => 'SFFR',
    ]);
    createTestTemplateForJenisSurat($jenisSurat);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Perlu review dekan sebelum finalisasi surat.',
        'status' => Surat::STATUS_PENDING,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDays(2)->toDateString(),
    ]);

    app(FastApprovalWorkflowService::class)->approve(
        $surat->fresh(),
        FastApprovalWorkflowService::ROLE_ADMIN,
        $admin,
    );

    $surat = $surat->fresh();

    expect($surat->status)->toBe(Surat::STATUS_VALIDATED_ADMIN);
    expect($surat->nomor_surat)->not->toBeNull();
    expect($surat->rendered_snapshot)->toContain('Nadia Putri');
    expect($surat->generated_file_path)->toBeNull();
});

test('admin approval can finish surat immediately when no final approval role is required', function () {
    Storage::fake('public');

    $adminRole = Role::create([
        'nama' => 'Administrasi',
        'slug' => 'administrasi',
    ]);

    $pemohonRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $pemohon = createUserWithType($pemohonRole->slug, [
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Keterangan Internal',
        'perlu_approval' => false,
        'kode_surat' => 'INTERNAL',
    ]);
    createTestTemplateForJenisSurat($jenisSurat);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Keperluan administrasi internal yang cukup divalidasi admin.',
        'status' => Surat::STATUS_PENDING,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDays(2)->toDateString(),
    ]);

    app(FastApprovalWorkflowService::class)->approve(
        $surat->fresh(),
        FastApprovalWorkflowService::ROLE_ADMIN,
        $admin,
    );

    $this->assertDatabaseHas('surats', [
        'id' => $surat->id,
        'status' => Surat::STATUS_FINISHED,
        'generated_file_type' => 'pdf',
    ]);
    expect($surat->fresh()->rendered_snapshot)->toContain('B/');
    expect($surat->fresh()->generated_file_path)->not->toBeNull();
    Storage::disk('public')->assertExists((string) $surat->fresh()->generated_file_path);
});

test('only configured final approval role can approve validated surat', function () {
    $adminRole = Role::create([
        'nama' => 'Administrasi',
        'slug' => 'administrasi',
    ]);

    $kaprodiRole = Role::create([
        'nama' => 'Kaprodi',
        'slug' => 'kaprodi',
    ]);

    $dekanRole = Role::create([
        'nama' => 'Dekan',
        'slug' => 'dekan',
    ]);

    $pemohonRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $pemohon = createUserWithType($pemohonRole->slug, [
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Fakultas',
        'approval_role_id' => $dekanRole->id,
        'perlu_approval' => true,
    ]);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Keperluan surat resmi tingkat fakultas.',
        'status' => Surat::STATUS_VALIDATED_ADMIN,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDays(2)->toDateString(),
    ]);

    expect($surat->fresh()->canBeApprovedByRole(FastApprovalWorkflowService::ROLE_KAPRODI))->toBeFalse();
    expect($surat->fresh()->canBeApprovedByRole(FastApprovalWorkflowService::ROLE_DEKAN))->toBeTrue();
});

test('user dashboard returns rejection reason from latest rejected approval flow', function () {
    $this->withoutVite();

    $pemohonRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $adminRole = Role::create([
        'nama' => 'Administrasi',
        'slug' => 'administrasi',
    ]);

    $pemohon = createUserWithType($pemohonRole->slug, [
        'name' => 'Fanny',
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Rekomendasi Akademik',
        'field_config' => [],
    ]);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Pengajuan surat untuk kebutuhan seleksi program pertukaran pelajar.',
        'status' => Surat::STATUS_REJECTED_ADMIN,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDays(5)->toDateString(),
    ]);

    $surat->approvalFlows()->create([
        'approver_id' => $admin->id,
        'urutan' => 1,
        'role' => FastApprovalWorkflowService::ROLE_ADMIN,
        'status' => 'rejected_final',
        'keterangan' => 'Ditolak ADMIN',
        'catatan' => 'Dokumen pendukung belum lengkap.',
        'approved_at' => now(),
        'tanggal_aksi' => now(),
    ]);

    $this->actingAs($pemohon)
        ->get(route('fast.user.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('fast/user/Dashboard')
            ->where('userType.label', 'Mahasiswa')
            ->where('userProfile.identifierLabel', 'NIM')
            ->where('latest.0.status', Surat::STATUS_REJECTED_ADMIN)
            ->where('latest.0.rejectionReason', 'Dokumen pendukung belum lengkap.'));
});

test('dosen dashboard uses the same page with lecturer specific profile metadata', function () {
    $this->withoutVite();

    $dosenRole = Role::create([
        'nama' => 'Dosen',
        'slug' => 'dosen',
    ]);

    $dosen = createUserWithType($dosenRole->slug, [
        'name' => 'Rina Pratama',
        'nim_nip' => '1987001122',
    ]);

    $this->actingAs($dosen)
        ->get(route('fast.user.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('fast/user/Dashboard')
            ->where('userType.label', 'Dosen')
            ->where('userProfile.identifierLabel', 'NIP')
            ->where('userProfile.identifierValue', '1987001122'));
});

test('admin dashboard only shows pending user submissions in the widget', function () {
    $this->withoutVite();

    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $mahasiswaRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $pemohon = createUserWithType($mahasiswaRole->slug, [
        'name' => 'Fanny',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Akademik',
    ]);

    Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'type' => 'pengajuan',
        'keperluan' => 'Pengajuan surat pertama.',
        'status' => Surat::STATUS_PENDING,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDay()->toDateString(),
    ]);

    Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'type' => 'pengajuan',
        'keperluan' => 'Pengajuan surat kedua.',
        'status' => Surat::STATUS_VALIDATED_ADMIN,
        'tanggal_pengajuan' => now()->subMinute(),
        'tanggal_kebutuhan' => now()->addDays(2)->toDateString(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/dashboard/Index')
            ->missing('filters.status')
            ->has('surats.data', 1)
            ->where('surats.data.0.status', Surat::STATUS_PENDING));
});

test('pengajuan masuk shows pending and approver revision submissions only', function () {
    $this->withoutVite();

    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $mahasiswaRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $admin = createUserWithType($adminRole->slug, [
        'name' => 'Admin',
    ]);

    $pemohon = createUserWithType($mahasiswaRole->slug, [
        'name' => 'Hellen Tiffani',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Akademik Masuk',
    ]);

    $pendingPengajuan = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'type' => 'pengajuan',
        'keperluan' => 'Pengajuan user yang masih pending.',
        'status' => Surat::STATUS_PENDING,
        'tanggal_pengajuan' => now(),
    ]);

    $revisionPengajuan = Surat::create([
        'jenis_surat_id' => JenisSurat::create([
            'nama' => 'Surat Revisi Kaprodi Masuk',
            'approval_role_id' => Role::create([
                'nama' => 'Kaprodi',
                'slug' => 'kaprodi-masuk',
            ])->id,
            'perlu_approval' => true,
        ])->id,
        'pemohon_id' => $pemohon->id,
        'type' => 'pengajuan',
        'keperluan' => 'Pengajuan user yang dikembalikan approver.',
        'status' => Surat::STATUS_REVISION_REQUESTED,
        'validated_by_admin_id' => $admin->id,
        'validated_by_admin_at' => now()->subHour(),
        'tanggal_pengajuan' => now()->subSecond(),
    ]);

    Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'type' => 'pengajuan',
        'keperluan' => 'Pengajuan user yang sudah selesai.',
        'status' => Surat::STATUS_FINISHED,
        'tanggal_pengajuan' => now()->subMinute(),
    ]);

    Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $admin->id,
        'type' => 'surat_keluar',
        'keperluan' => 'Surat keluar buatan admin.',
        'status' => Surat::STATUS_PENDING,
        'tanggal_pengajuan' => now()->subMinutes(2),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.surat.index', ['status' => 'all']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/letters/Index')
            ->has('surats.data', 2)
            ->where('surats.data.0.id', $pendingPengajuan->id)
            ->where('surats.data.0.pemohon.name', 'Hellen Tiffani')
            ->where('surats.data.1.id', $revisionPengajuan->id)
            ->where('surats.data.1.status', Surat::STATUS_REVISION_REQUESTED)
            ->where('surats.data.1.revision_label', 'Dikembalikan Kaprodi')
            ->where('surats.data.1.can_edit', true)
            ->where('summary.total', 2)
            ->where('summary.pending', 1));
});

test('admin detail surat exposes explicit rejection and revision labels', function () {
    $this->withoutVite();

    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $kaprodiRole = Role::create([
        'nama' => 'Kaprodi',
        'slug' => 'kaprodi',
    ]);

    $mahasiswaRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $admin = createUserWithType($adminRole->slug, [
        'name' => 'Admin',
    ]);

    $kaprodi = createUserWithType($kaprodiRole->slug, [
        'name' => 'Bill Gates',
    ]);

    $pemohon = createUserWithType($mahasiswaRole->slug, [
        'name' => 'Hellen Tiffani',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Akademik Revisi',
    ]);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'type' => 'pengajuan',
        'keperluan' => 'Perlu revisi approver.',
        'status' => Surat::STATUS_REVISION_REQUESTED,
        'tanggal_pengajuan' => now(),
        'revisi_ke' => 1,
        'catatan_revisi' => 'Perbaiki lampiran akademik.',
        'validated_by_admin_id' => $admin->id,
        'validated_by_admin_at' => now()->subHour(),
    ]);

    $surat->approvalFlows()->create([
        'approver_id' => $admin->id,
        'urutan' => 1,
        'role' => 'admin',
        'status' => 'approved',
        'keterangan' => 'Validasi Admin',
        'approved_at' => now()->subHour(),
        'tanggal_aksi' => now()->subHour(),
    ]);

    $surat->approvalFlows()->create([
        'approver_id' => $kaprodi->id,
        'urutan' => 2,
        'role' => 'kaprodi',
        'status' => 'revision_requested',
        'keterangan' => 'Dikembalikan KAPRODI untuk revisi',
        'catatan' => 'Perbaiki lampiran akademik.',
        'approved_at' => now()->subMinutes(30),
        'tanggal_aksi' => now()->subMinutes(30),
    ]);

    SuratHistory::create([
        'surat_id' => $surat->id,
        'user_id' => $kaprodi->id,
        'action' => 'revised',
        'action_label' => 'Surat direvisi (revisi ke-1)',
        'keterangan' => 'Perbaiki lampiran akademik.',
    ]);

    $this->actingAs($admin)
        ->get("/admin/surat/{$surat->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/dashboard/Show')
            ->where('latest_rejection.label', 'Dikembalikan Kaprodi')
            ->where('latest_rejection.type', 'revision')
            ->where('approval_timeline.1.label', 'Dikembalikan Kaprodi')
            ->where('history_timeline.0.label', 'Surat direvisi (revisi ke-1)'));
});

test('riwayat surat keluar only shows non final admin outgoing letters', function () {
    $this->withoutVite();

    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $mahasiswaRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $adminA = createUserWithType($adminRole->slug, [
        'name' => 'Admin A',
    ]);

    $adminB = createUserWithType($adminRole->slug, [
        'name' => 'Admin B',
    ]);

    $pemohon = createUserWithType($mahasiswaRole->slug, [
        'name' => 'Mahasiswa',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Keluar Admin',
    ]);

    $validatedOutgoing = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $adminA->id,
        'type' => 'surat_keluar',
        'keperluan' => 'Masih dalam proses.',
        'status' => Surat::STATUS_VALIDATED_ADMIN,
        'tanggal_pengajuan' => now(),
    ]);

    Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $adminB->id,
        'type' => 'surat_keluar',
        'keperluan' => 'Sudah final.',
        'status' => Surat::STATUS_FINISHED,
        'tanggal_pengajuan' => now()->subMinute(),
    ]);

    Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'type' => 'pengajuan',
        'keperluan' => 'Pengajuan user.',
        'status' => Surat::STATUS_PENDING,
        'tanggal_pengajuan' => now()->subMinutes(2),
    ]);

    $this->actingAs($adminA)
        ->get(route('admin.history'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/history/Index')
            ->has('surats.data', 1)
            ->where('surats.data.0.id', $validatedOutgoing->id)
            ->where('surats.data.0.pemohon.name', 'Admin A'));
});

test('arsip surat only shows finished final documents from submissions and outgoing letters', function () {
    $this->withoutVite();

    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $mahasiswaRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $pemohon = createUserWithType($mahasiswaRole->slug, [
        'name' => 'Hellen Tiffani',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Arsip',
    ]);

    $finishedPengajuan = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'type' => 'pengajuan',
        'nomor_surat' => 'B/100/ARSIP/2026',
        'keperluan' => 'Final pengajuan.',
        'status' => Surat::STATUS_FINISHED,
        'generated_file_path' => 'surat/finished-pengajuan.pdf',
        'tanggal_pengajuan' => now()->subDays(2),
        'tanggal_selesai' => now()->subDay(),
    ]);

    $finishedOutgoing = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $admin->id,
        'type' => 'surat_keluar',
        'nomor_surat' => 'B/101/ARSIP/2026',
        'keperluan' => 'Final surat keluar.',
        'status' => Surat::STATUS_FINISHED,
        'generated_file_path' => 'surat/finished-outgoing.pdf',
        'tanggal_pengajuan' => now()->subDays(3),
        'tanggal_selesai' => now(),
    ]);

    Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $admin->id,
        'type' => 'surat_keluar',
        'nomor_surat' => 'B/102/ARSIP/2026',
        'keperluan' => 'Belum final.',
        'status' => Surat::STATUS_VALIDATED_ADMIN,
        'generated_file_path' => null,
        'tanggal_pengajuan' => now()->subHour(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.archive.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/archive/Index')
            ->has('surats.data', 2)
            ->where('surats.data.0.id', $finishedOutgoing->id)
            ->where('surats.data.1.id', $finishedPengajuan->id));
});

test('approval dashboard shows only validated surat for the signed in approval role', function () {
    $this->withoutVite();

    $kaprodiRole = Role::create([
        'nama' => 'Kaprodi',
        'slug' => 'kaprodi',
    ]);

    $dekanRole = Role::create([
        'nama' => 'Dekan',
        'slug' => 'dekan',
    ]);

    $mahasiswaRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $kaprodi = createUserWithType($kaprodiRole->slug, [
    ]);

    $pemohon = createUserWithType($mahasiswaRole->slug, [
        'name' => 'Fanny',
    ]);

    $suratUntukKaprodi = Surat::create([
        'jenis_surat_id' => JenisSurat::create([
            'nama' => 'Surat Kaprodi',
            'approval_role_id' => $kaprodiRole->id,
            'perlu_approval' => true,
        ])->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Butuh approval kaprodi.',
        'status' => Surat::STATUS_VALIDATED_ADMIN,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDay()->toDateString(),
    ]);

    Surat::create([
        'jenis_surat_id' => JenisSurat::create([
            'nama' => 'Surat Dekan',
            'approval_role_id' => $dekanRole->id,
            'perlu_approval' => true,
        ])->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Butuh approval dekan.',
        'status' => Surat::STATUS_VALIDATED_ADMIN,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDays(2)->toDateString(),
    ]);

    Surat::create([
        'jenis_surat_id' => $suratUntukKaprodi->jenis_surat_id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Masih pending admin.',
        'status' => Surat::STATUS_PENDING,
        'tanggal_pengajuan' => now()->subDay(),
        'tanggal_kebutuhan' => now()->addDays(3)->toDateString(),
    ]);

    $this->actingAs($kaprodi)
        ->get(route('approval.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('approval/Index')
            ->where('role.name', 'Kaprodi')
            ->where('filters.status', Surat::STATUS_VALIDATED_ADMIN)
            ->where('summary.waiting', 1)
            ->has('surats.data', 1)
            ->where('surats.data.0.id', $suratUntukKaprodi->id)
            ->where('surats.data.0.status', Surat::STATUS_VALIDATED_ADMIN));
});

test('approver can fetch draft preview metadata for validated surat', function () {
    $kaprodiRole = Role::create([
        'nama' => 'Kaprodi',
        'slug' => 'kaprodi',
    ]);

    $adminRole = Role::create([
        'nama' => 'Administrasi',
        'slug' => 'administrasi',
    ]);

    $mahasiswaRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $kaprodi = createUserWithType($kaprodiRole->slug, [
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $pemohon = createUserWithType($mahasiswaRole->slug, [
        'name' => 'Rizky Mahendra',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Review Kaprodi',
        'approval_role_id' => $kaprodiRole->id,
        'perlu_approval' => true,
        'kode_surat' => 'SRK',
    ]);
    createTestTemplateForJenisSurat($jenisSurat);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Surat perlu direview kaprodi.',
        'status' => Surat::STATUS_PENDING,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDay()->toDateString(),
    ]);

    app(FastApprovalWorkflowService::class)->approve(
        $surat->fresh(),
        FastApprovalWorkflowService::ROLE_ADMIN,
        $admin,
    );

    $this->actingAs($kaprodi)
        ->get(route('approval.surat.show', $surat->id))
        ->assertOk()
        ->assertJsonPath('nomor_surat', $surat->fresh()->nomor_surat)
        ->assertJsonPath('draft_preview_url', route('documents.surat.generated-document', $surat->id, false));
});

test('kaprodi can approve validated surat from approval dashboard', function () {
    Storage::fake('public');

    $kaprodiRole = Role::create([
        'nama' => 'Kaprodi',
        'slug' => 'kaprodi',
    ]);

    $mahasiswaRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $kaprodi = createUserWithType($kaprodiRole->slug, [
    ]);

    $pemohon = createUserWithType($mahasiswaRole->slug, [
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Pengajuan Kaprodi',
        'approval_role_id' => $kaprodiRole->id,
        'perlu_approval' => true,
        'kode_surat' => 'KAPRODI',
    ]);
    createTestTemplateForJenisSurat($jenisSurat);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Perlu persetujuan kaprodi.',
        'status' => Surat::STATUS_VALIDATED_ADMIN,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDay()->toDateString(),
    ]);

    $this->actingAs($kaprodi)
        ->post(route('approval.surat.approve', $surat->id))
        ->assertRedirect();

    $this->assertDatabaseHas('surats', [
        'id' => $surat->id,
        'status' => Surat::STATUS_FINISHED,
        'generated_file_type' => 'pdf',
    ]);

    expect($surat->fresh()->rendered_snapshot)->toContain('B/');
    Storage::disk('public')->assertExists((string) $surat->fresh()->generated_file_path);
});

test('final rendered surat uses approver account as signer placeholder source', function () {
    $dekanRole = Role::create([
        'nama' => 'Dekan',
        'slug' => 'dekan',
    ]);

    $mahasiswaRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $dekan = createUserWithType($dekanRole->slug, [
        'name' => 'Thomas Alva',
        'nim_nip' => '87654321',
        'nomor_induk' => '87654321',
        'status_approval' => 'approved',
        'is_active' => true,
    ]);

    $pemohon = createUserWithType($mahasiswaRole->slug, [
        'name' => 'Hellen Tiffani',
        'nim_nip' => '22EO10001',
        'nomor_induk' => '22EO10001',
        'status_approval' => 'approved',
        'is_active' => true,
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Signer Otomatis',
        'approval_role_id' => $dekanRole->id,
        'perlu_approval' => true,
        'kode_surat' => 'SSO',
    ]);

    $template = SuratTemplate::create([
        'jenis_surat_id' => $jenisSurat->id,
        'name' => 'Template Signer Otomatis',
        'slug' => 'template-signer-otomatis',
        'format' => 'html',
        'template_body' => '<p>{{nama_pemohon}}</p><p>{{nama_penanda_tangan}}</p><p>{{nomor_induk_penanda_tangan}}</p><p>{{jabatan_penanda_tangan}}</p>',
        'version' => 1,
        'is_active' => true,
    ]);

    \App\Support\TemplatePlaceholderSynchronizer::syncTemplate($template, []);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'nomor_surat' => 'B/0001/SSO/FMIKOM/2026',
        'keperluan' => 'Perlu tanda tangan dekan.',
        'status' => Surat::STATUS_APPROVED_DEKAN,
        'approved_by_id' => $dekan->id,
        'approved_at' => now(),
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDay()->toDateString(),
    ]);

    $rendered = app(\App\Services\SuratTemplateRendererService::class)->renderForSurat($surat->fresh(), false);

    expect($rendered['html'])->toContain('Hellen Tiffani');
    expect($rendered['html'])->toContain('Thomas Alva');
    expect($rendered['html'])->toContain('87654321');
    expect($rendered['html'])->toContain('Dekan');
});

test('approver revision request uses revision_requested status and stores revision metadata', function () {
    $kaprodiRole = Role::create([
        'nama' => 'Kaprodi',
        'slug' => 'kaprodi',
    ]);

    $mahasiswaRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $kaprodi = createUserWithType($kaprodiRole->slug, [
    ]);

    $pemohon = createUserWithType($mahasiswaRole->slug, [
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Revisi Kaprodi',
        'approval_role_id' => $kaprodiRole->id,
        'perlu_approval' => true,
        'kode_surat' => 'RVK',
    ]);
    createTestTemplateForJenisSurat($jenisSurat);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Perlu revisi dari kaprodi.',
        'status' => Surat::STATUS_VALIDATED_ADMIN,
        'tanggal_pengajuan' => now(),
    ]);

    $this->actingAs($kaprodi)
        ->post(route('approval.surat.reject', $surat->id), [
            'reason' => 'Tambahkan data pendukung akademik terbaru.',
        ])
        ->assertRedirect();

    $surat->refresh();

    expect($surat->status)->toBe(Surat::STATUS_REVISION_REQUESTED);
    expect($surat->revisi_ke)->toBe(1);
    expect($surat->catatan_revisi)->toBe('Tambahkan data pendukung akademik terbaru.');
    expect($surat->rejection_reason)->toBeNull();

    $this->assertDatabaseHas('surat_approval_flows', [
        'surat_id' => $surat->id,
        'role' => FastApprovalWorkflowService::ROLE_KAPRODI,
        'status' => 'revision_requested',
        'catatan' => 'Tambahkan data pendukung akademik terbaru.',
    ]);

    $this->assertDatabaseHas('surat_histories', [
        'surat_id' => $surat->id,
        'action' => SuratHistory::ACTION_REVISED,
    ]);
});

test('admin rejection remains final rejection without revision metadata', function () {
    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $mahasiswaRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $pemohon = createUserWithType($mahasiswaRole->slug, [
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Ditolak Admin',
        'perlu_approval' => false,
    ]);
    createTestTemplateForJenisSurat($jenisSurat);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Dokumen tidak layak proses.',
        'status' => Surat::STATUS_PENDING,
        'tanggal_pengajuan' => now(),
    ]);

    $this->actingAs($admin)
        ->post(route('admin.surat.reject', $surat->id), [
            'reason' => 'Lampiran wajib belum lengkap.',
        ])
        ->assertRedirect();

    $surat->refresh();

    expect($surat->status)->toBe(Surat::STATUS_REJECTED_ADMIN);
    expect($surat->revisi_ke)->toBe(0);
    expect($surat->catatan_revisi)->toBeNull();
    expect($surat->rejection_reason)->toBe('Lampiran wajib belum lengkap.');

    $this->assertDatabaseHas('surat_histories', [
        'surat_id' => $surat->id,
        'action' => SuratHistory::ACTION_REJECTED,
    ]);
});

test('admin can resend approver revision to validated admin while preserving approval role', function () {
    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $dekanRole = Role::create([
        'nama' => 'Dekan',
        'slug' => 'dekan',
    ]);

    $pemohonRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $pemohon = createUserWithType($pemohonRole->slug, [
        'name' => 'Putra Mahesa',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Revisi Dekan',
        'approval_role_id' => $dekanRole->id,
        'perlu_approval' => true,
        'kode_surat' => 'RVD',
    ]);
    createTestTemplateForJenisSurat($jenisSurat);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Naskah perlu diperbarui lalu dikirim ulang.',
        'status' => Surat::STATUS_REVISION_REQUESTED,
        'validated_by_admin_id' => $admin->id,
        'validated_by_admin_at' => now()->subDay(),
        'revisi_ke' => 1,
        'catatan_revisi' => 'Perbaiki data akademik pada draft.',
        'tanggal_pengajuan' => now()->subDays(2),
        'isi_surat' => json_encode([
            'jenis_surat_id' => $jenisSurat->id,
            'jenis_surat' => $jenisSurat->nama,
            'keperluan' => 'Naskah perlu diperbarui lalu dikirim ulang.',
            'data' => [],
        ], JSON_THROW_ON_ERROR),
    ]);

    $surat->approvalFlows()->create([
        'approver_id' => $admin->id,
        'urutan' => 3,
        'role' => FastApprovalWorkflowService::ROLE_DEKAN,
        'status' => 'revision_requested',
        'keterangan' => 'Dikembalikan DEKAN untuk revisi',
        'catatan' => 'Perbaiki data akademik pada draft.',
        'approved_at' => now()->subDay(),
        'tanggal_aksi' => now()->subDay(),
    ]);

    $updated = app(SuratWorkflowService::class)->editRejected($surat->fresh(['jenisSurat', 'dataEntries', 'approvalFlows']), $admin, [
        'data' => [],
        'keperluan' => 'Naskah sudah diperbarui dan siap dikirim ulang.',
    ]);

    expect($updated->status)->toBe(Surat::STATUS_VALIDATED_ADMIN);
    expect($updated->catatan_revisi)->toBeNull();
    expect($updated->revisi_ke)->toBe(1);
    expect($updated->finalApprovalRoleSlug())->toBe('dekan');
    expect($updated->rendered_snapshot)->toContain('Putra Mahesa');
});

test('admin can resend legacy approver revision without validated_by_admin columns when admin approval flow exists', function () {
    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $dekanRole = Role::create([
        'nama' => 'Dekan',
        'slug' => 'dekan',
    ]);

    $pemohonRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $pemohon = createUserWithType($pemohonRole->slug, [
        'name' => 'Legacy Mahasiswa',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Legacy Revisi',
        'approval_role_id' => $dekanRole->id,
        'perlu_approval' => true,
        'kode_surat' => 'SLR',
    ]);
    createTestTemplateForJenisSurat($jenisSurat);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Legacy revisi tanpa kolom validated_by_admin.',
        'status' => Surat::STATUS_REVISION_REQUESTED,
        'validated_by_admin_id' => null,
        'validated_by_admin_at' => null,
        'revisi_ke' => 1,
        'catatan_revisi' => 'Lengkapi data.',
        'tanggal_pengajuan' => now()->subDays(2),
        'isi_surat' => json_encode([
            'jenis_surat_id' => $jenisSurat->id,
            'jenis_surat' => $jenisSurat->nama,
            'keperluan' => 'Legacy revisi tanpa kolom validated_by_admin.',
            'data' => [],
        ], JSON_THROW_ON_ERROR),
    ]);

    $surat->approvalFlows()->create([
        'approver_id' => $admin->id,
        'urutan' => 1,
        'role' => FastApprovalWorkflowService::ROLE_ADMIN,
        'status' => 'approved',
        'keterangan' => 'Validasi Admin',
        'approved_at' => now()->subDay(),
        'tanggal_aksi' => now()->subDay(),
    ]);

    $surat->approvalFlows()->create([
        'approver_id' => $admin->id,
        'urutan' => 3,
        'role' => FastApprovalWorkflowService::ROLE_DEKAN,
        'status' => 'revision_requested',
        'keterangan' => 'Dikembalikan DEKAN untuk revisi',
        'catatan' => 'Lengkapi data.',
        'approved_at' => now()->subHours(12),
        'tanggal_aksi' => now()->subHours(12),
    ]);

    $updated = app(SuratWorkflowService::class)->editRejected($surat->fresh(['jenisSurat', 'dataEntries', 'approvalFlows']), $admin, [
        'data' => [],
        'keperluan' => 'Legacy revisi sudah diperbarui.',
    ]);

    expect($updated->status)->toBe(Surat::STATUS_VALIDATED_ADMIN);
    expect($updated->validated_by_admin_id)->toBe($admin->id);
    expect($updated->validated_by_admin_at)->not->toBeNull();
});

test('admin update revisi redirects to surat detail after resend', function () {
    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $dekanRole = Role::create([
        'nama' => 'Dekan',
        'slug' => 'dekan',
    ]);

    $pemohonRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $pemohon = createUserWithType($pemohonRole->slug, [
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Redirect Revisi',
        'approval_role_id' => $dekanRole->id,
        'perlu_approval' => true,
        'kode_surat' => 'SRR',
    ]);
    createTestTemplateForJenisSurat($jenisSurat);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'type' => 'pengajuan',
        'keperluan' => 'Perlu perbaikan naskah.',
        'status' => Surat::STATUS_REVISION_REQUESTED,
        'validated_by_admin_id' => $admin->id,
        'validated_by_admin_at' => now()->subDay(),
        'revisi_ke' => 1,
        'catatan_revisi' => 'Perbaiki naskah.',
        'tanggal_pengajuan' => now()->subDays(2),
        'isi_surat' => json_encode([
            'jenis_surat_id' => $jenisSurat->id,
            'jenis_surat' => $jenisSurat->nama,
            'keperluan' => 'Perlu perbaikan naskah.',
            'data' => [],
        ], JSON_THROW_ON_ERROR),
    ]);

    $surat->approvalFlows()->create([
        'approver_id' => $admin->id,
        'urutan' => 3,
        'role' => FastApprovalWorkflowService::ROLE_DEKAN,
        'status' => 'revision_requested',
        'keterangan' => 'Dikembalikan DEKAN untuk revisi',
        'catatan' => 'Perbaiki naskah.',
        'approved_at' => now()->subDay(),
        'tanggal_aksi' => now()->subDay(),
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.surat.update', $surat->id), [
            'jenis_surat_id' => $jenisSurat->id,
            'keperluan' => 'Naskah diperbaiki dan dikirim ulang.',
            'form_data' => [],
        ])
        ->assertRedirect(route('admin.surat.show', $surat->id));
});

test('admin can resend approver revision when text fields contain numeric-looking strings', function () {
    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $dekanRole = Role::create([
        'nama' => 'Dekan',
        'slug' => 'dekan',
    ]);

    $pemohonRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $pemohon = createUserWithType($pemohonRole->slug, [
        'name' => 'Hellen Tiffani',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Undangan PKL',
        'approval_role_id' => $dekanRole->id,
        'perlu_approval' => true,
        'kode_surat' => 'SUP',
        'field_config' => [
            ['name' => 'nama', 'label' => 'Nama', 'type' => 'text', 'required' => true],
            ['name' => 'nim', 'label' => 'NIM', 'type' => 'text', 'required' => true],
            ['name' => 'prodi', 'label' => 'Prodi', 'type' => 'text', 'required' => true],
        ],
    ]);
    createTestTemplateForJenisSurat($jenisSurat);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'type' => 'surat_keluar',
        'keperluan' => 'Untuk penempatan PKL',
        'status' => Surat::STATUS_REVISION_REQUESTED,
        'validated_by_admin_id' => $admin->id,
        'validated_by_admin_at' => now()->subDay(),
        'revisi_ke' => 1,
        'catatan_revisi' => 'Kurang pas',
        'tanggal_pengajuan' => now()->subDays(2),
        'isi_surat' => json_encode([
            'jenis_surat_id' => $jenisSurat->id,
            'jenis_surat' => $jenisSurat->nama,
            'keperluan' => 'Untuk penempatan PKL',
            'data' => [
                'nama' => 'Hellen Tiffani',
                'nim' => '22527812',
                'prodi' => 'Informatika',
                'perihal' => 'Penempatan Lokasi PKL',
                'lampiran_keterangan' => '1',
                'kepada_yth' => ['Ketua Program Studi Informatika'],
            ],
            'created_by' => [
                'id' => $admin->id,
                'name' => $admin->name,
            ],
        ], JSON_THROW_ON_ERROR),
    ]);

    $surat->dataEntries()->createMany([
        ['field_name' => 'nama', 'field_value' => 'Hellen Tiffani'],
        ['field_name' => 'nim', 'field_value' => '22527812'],
        ['field_name' => 'prodi', 'field_value' => 'Informatika'],
        ['field_name' => 'perihal', 'field_value' => 'Penempatan Lokasi PKL'],
        ['field_name' => 'lampiran_keterangan', 'field_value' => '1'],
        ['field_name' => 'kepada_yth', 'field_value' => json_encode(['Ketua Program Studi Informatika'], JSON_THROW_ON_ERROR)],
    ]);

    $surat->approvalFlows()->create([
        'approver_id' => $admin->id,
        'urutan' => 3,
        'role' => FastApprovalWorkflowService::ROLE_DEKAN,
        'status' => 'revision_requested',
        'keterangan' => 'Dikembalikan DEKAN untuk revisi',
        'catatan' => 'Kurang pas',
        'approved_at' => now()->subDay(),
        'tanggal_aksi' => now()->subDay(),
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.surat.update', $surat->id), [
            'jenis_surat_id' => $jenisSurat->id,
            'keperluan' => 'Untuk penempatan PKL',
            'perihal' => 'Penempatan Lokasi PKL',
            'form_data' => [
                'nama' => 'Hellen Tiffani',
                'nim' => '22527812',
                'prodi' => 'Informatika',
                'lampiran_keterangan' => '1',
                'kepada_yth' => ['Ketua Program Studi Informatika'],
            ],
        ])
        ->assertRedirect(route('admin.surat.show', $surat->id))
        ->assertSessionHas('success');

    $surat->refresh();

    expect($surat->status)->toBe(Surat::STATUS_VALIDATED_ADMIN);
    expect($surat->catatan_revisi)->toBeNull();
});

test('admin can preview rendered template for a surat', function () {
    $this->withoutVite();

    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $mahasiswaRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $pemohon = createUserWithType($mahasiswaRole->slug, [
        'name' => 'Rina Pratama',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Permohonan Akademik',
        'kode_surat' => 'AKD',
    ]);
    createTestTemplateForJenisSurat($jenisSurat);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'nomor_surat' => 'B/0001/AKD/FMIKOM/2026',
        'keperluan' => 'Preview template surat.',
        'status' => Surat::STATUS_PENDING,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDay()->toDateString(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.surat.template-preview', $surat->id))
        ->assertOk()
        ->assertSee('Rina Pratama')
        ->assertSee('B/0001/AKD/FMIKOM/2026');
});

test('admin can manage active template per jenis surat', function () {
    $this->withoutVite();

    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Permohonan Akademik',
        'slug' => 'permohonan-akademik',
    ]);
    createTestTemplateForJenisSurat($jenisSurat);

    $this->actingAs($admin)
        ->get(route('admin.templates.index', ['jenis_surat_id' => $jenisSurat->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/templates/Index')
            ->where('selectedJenisSurat.id', $jenisSurat->id)
            ->where('selectedJenisSurat.template.name', 'Template Test '.$jenisSurat->nama)
            ->has('selectedJenisSurat.template.placeholders', 2));
});

test('admin can update active template and preview generated pdf document', function () {
    $this->withoutVite();
    Storage::fake('public');

    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $mahasiswaRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $pemohon = createUserWithType($mahasiswaRole->slug, [
        'name' => 'Dian Larasati',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Permohonan Akademik',
        'slug' => 'permohonan-akademik',
        'kode_surat' => 'AKD',
        'perlu_approval' => false,
    ]);
    createTestTemplateForJenisSurat($jenisSurat);

    $this->actingAs($admin)
        ->put(route('admin.templates.update', $jenisSurat->id), [
            'name' => 'Template Akademik Revisi',
            'source_reference' => 'ref.docx',
            'subject' => 'Surat Akademik',
            'template_header' => '<p>Header</p>',
            'template_body' => '<p>{{nama_pemohon}}</p><p>{{nomor_surat}}</p>',
            'template_footer' => '<p>Footer</p>',
        ])
        ->assertRedirect(route('admin.templates.index', ['jenis_surat_id' => $jenisSurat->id]));

    $template = $jenisSurat->template()->firstOrFail();

    expect($template->name)->toBe('Template Akademik Revisi');
    expect($template->version)->toBe(2);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Perlu surat akademik.',
        'status' => Surat::STATUS_PENDING,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDay()->toDateString(),
    ]);

    $this->actingAs($admin)
        ->post(route('admin.surat.approve', $surat->id))
        ->assertRedirect();

    $surat = $surat->fresh();

    Storage::disk('public')->assertExists((string) $surat->generated_file_path);

    $this->actingAs($admin)
        ->get(route('admin.surat.generated-document', $surat->id))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});


