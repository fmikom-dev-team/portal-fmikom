<?php

use App\Models\JenisSurat;
use App\Models\Role;
use App\Models\Surat;
use App\Models\SuratTemplate;
use App\Models\User;
use App\Services\FastApprovalWorkflowService;
use App\Services\SuratTemplateRendererService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function createOutgoingLetterTemplate(JenisSurat $jenisSurat): void
{
    $template = SuratTemplate::create([
        'jenis_surat_id' => $jenisSurat->id,
        'name' => 'Template '.$jenisSurat->nama,
        'slug' => 'template-'.$jenisSurat->id,
        'format' => 'html',
        'subject' => $jenisSurat->nama,
        'template_body' => <<<'HTML'
<section>
    <p>{{yth_list}}</p>
    <p>{{judul_surat}}</p>
    <p>{{isi_ringkas}}</p>
</section>
HTML,
        'version' => 1,
        'is_active' => true,
    ]);

    $template->placeholders()->createMany([
        [
            'placeholder_key' => 'yth_list',
            'label' => 'Daftar YTH',
            'source_type' => 'surat_data',
            'source_key' => 'yth_list',
            'is_required' => true,
        ],
        [
            'placeholder_key' => 'judul_surat',
            'label' => 'Judul Surat',
            'source_type' => 'surat_data',
            'source_key' => 'judul_surat',
            'is_required' => true,
        ],
        [
            'placeholder_key' => 'isi_ringkas',
            'label' => 'Isi Ringkas',
            'source_type' => 'surat_data',
            'source_key' => 'isi_ringkas',
            'is_required' => true,
        ],
    ]);
}

test('admin can open create surat page and preview outgoing letter with dynamic fields', function () {
    $this->withoutVite();

    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $dekanRole = Role::create([
        'nama' => 'Dekan',
        'slug' => 'dekan',
    ]);

    $admin = createUserWithType($adminRole->slug, [
        'name' => 'Admin FAST',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Keluar Observasi',
        'slug' => 'surat-keluar-observasi',
        'deskripsi' => 'Surat keluar untuk observasi instansi tujuan.',
        'approval_role_id' => $dekanRole->id,
        'perlu_approval' => true,
        'is_active' => true,
        'field_config' => [
            [
                'name' => 'yth_list',
                'label' => 'Daftar YTH',
                'type' => 'repeatable',
                'required' => true,
            ],
            [
                'name' => 'judul_surat',
                'label' => 'Judul Surat',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'isi_ringkas',
                'label' => 'Isi Ringkas',
                'type' => 'textarea',
                'required' => true,
            ],
        ],
    ]);
    createOutgoingLetterTemplate($jenisSurat);

    $this->actingAs($admin)
        ->get('/admin/surat/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/letters/Create')
            ->where('jenisSurats.0.nama', 'Surat Keluar Observasi'));

    $this->actingAs($admin)
        ->post('/admin/surat/preview', [
            'jenis_surat_id' => $jenisSurat->id,
            'keperluan' => 'Pengantar observasi untuk instansi tujuan.',
            'data' => [
                'yth_list' => ['Bapak Dekan', 'Ibu Kaprodi'],
                'judul_surat' => 'Permohonan Observasi',
                'isi_ringkas' => 'Mohon izin untuk melakukan observasi lapangan.',
            ],
        ])
        ->assertRedirect('/admin/surat/preview');

    $this->actingAs($admin)
        ->get('/admin/surat/preview')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/letters/Preview')
            ->where('formData.data.judul_surat', 'Permohonan Observasi')
            ->where('formData.data.yth_list.0', 'Bapak Dekan')
            ->where('formData.data.yth_list.1', 'Ibu Kaprodi'));
});

test('admin can store outgoing surat without approval and pdf is generated immediately', function () {
    Storage::fake('public');
    File::ensureDirectoryExists(storage_path('framework/testing/views'));
    config()->set('view.compiled', storage_path('framework/testing/views'));

    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $dekanRole = Role::create([
        'nama' => 'Dekan',
        'slug' => 'dekan',
    ]);

    $admin = createUserWithType($adminRole->slug, [
        'name' => 'Admin FAST',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Keluar Fakultas',
        'slug' => 'surat-keluar-fakultas',
        'kode_surat' => 'SKF',
        'perlu_approval' => false,
        'is_active' => true,
        'field_config' => [
            [
                'name' => 'yth_list',
                'label' => 'Daftar YTH',
                'type' => 'repeatable',
                'required' => true,
            ],
            [
                'name' => 'judul_surat',
                'label' => 'Judul Surat',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'isi_ringkas',
                'label' => 'Isi Ringkas',
                'type' => 'textarea',
                'required' => true,
            ],
        ],
    ]);
    createOutgoingLetterTemplate($jenisSurat);

    $this->actingAs($admin)
        ->post('/admin/surat/store', [
            'jenis_surat_id' => $jenisSurat->id,
            'keperluan' => 'Pengantar surat keluar fakultas.',
            'data' => [
                'yth_list' => ['Bapak Dekan', 'Ibu Kaprodi'],
                'judul_surat' => 'Undangan Kegiatan',
                'isi_ringkas' => 'Kami mengundang partisipasi pada kegiatan fakultas.',
            ],
        ])
        ->assertRedirect('/admin/dashboard')
        ->assertSessionHas('success');

    $surat = Surat::query()->latest('id')->firstOrFail();

    expect($surat->type)->toBe('surat_keluar');
    expect($surat->status)->toBe(Surat::STATUS_FINISHED);
    expect($surat->generated_file_type)->toBe('pdf');
    expect($surat->generated_file_path)->not->toBeNull();
    expect($surat->qr_token)->not->toBeNull();

    $this->assertDatabaseHas('surat_data', [
        'surat_id' => $surat->id,
        'field_name' => 'judul_surat',
        'field_value' => 'Undangan Kegiatan',
    ]);

    Storage::disk('public')->assertExists((string) $surat->fresh()->generated_file_path);
});

test('admin outgoing preview and store use the same manual surat data contract fields', function () {
    $this->withoutVite();
    Storage::fake('public');
    $compiledPath = storage_path('framework/testing/views-contract-fields');
    File::ensureDirectoryExists($compiledPath);
    config()->set('view.compiled', $compiledPath);

    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $dekanRole = Role::create([
        'nama' => 'Dekan',
        'slug' => 'dekan',
    ]);

    $admin = createUserWithType($adminRole->slug, [
        'name' => 'Admin FAST',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Keluar Dispensasi',
        'slug' => 'surat-keluar-dispensasi',
        'kode_surat' => 'SKD',
        'approval_role_id' => $dekanRole->id,
        'perlu_approval' => true,
        'is_active' => true,
        'field_config' => [
            [
                'name' => 'isi_ringkas',
                'label' => 'Isi Ringkas',
                'type' => 'textarea',
                'required' => true,
            ],
        ],
    ]);

    $template = SuratTemplate::create([
        'jenis_surat_id' => $jenisSurat->id,
        'name' => 'Template '.$jenisSurat->nama,
        'slug' => 'template-'.$jenisSurat->slug,
        'format' => 'html',
        'subject' => $jenisSurat->nama,
        'template_body' => json_encode([
            [
                'type' => 'header_surat',
                'nomor' => '{{nomor_surat}}',
                'lampiran' => '-',
                'perihal' => 'Template Lama',
                'kota' => '{{kota_surat}}',
                'tanggal' => '{{tanggal_surat_panjang}}',
            ],
            [
                'type' => 'kepada_yth',
                'penerima' => ['Template Aktif'],
                'lokasi' => 'di-',
                'tempat' => 'Tempat',
            ],
            [
                'type' => 'paragraf',
                'teks' => '{{isi_ringkas}}',
                'align' => 'justify',
            ],
        ], JSON_THROW_ON_ERROR),
        'version' => 1,
        'is_active' => true,
    ]);

    $template->placeholders()->createMany([
        [
            'placeholder_key' => 'nomor_surat',
            'label' => 'Nomor Surat',
            'source_type' => 'surat',
            'source_key' => 'nomor_surat',
            'is_required' => true,
        ],
        [
            'placeholder_key' => 'isi_ringkas',
            'label' => 'Isi Ringkas',
            'source_type' => 'surat_data',
            'source_key' => 'isi_ringkas',
            'is_required' => true,
        ],
        [
            'placeholder_key' => 'perihal',
            'label' => 'Perihal',
            'source_type' => 'surat_data',
            'source_key' => 'perihal',
            'is_required' => false,
        ],
        [
            'placeholder_key' => 'lampiran_keterangan',
            'label' => 'Keterangan Lampiran',
            'source_type' => 'surat_data',
            'source_key' => 'lampiran_keterangan',
            'is_required' => false,
        ],
        [
            'placeholder_key' => 'kepada_yth',
            'label' => 'Kepada Yth',
            'source_type' => 'surat_data',
            'source_key' => 'kepada_yth',
            'is_required' => false,
        ],
    ]);

    $payload = [
        'jenis_surat_id' => $jenisSurat->id,
        'keperluan' => 'Pengantar dispensasi kegiatan.',
        'perihal' => 'Permohonan Dispensasi',
        'lampiran_keterangan' => '1 (satu) lembar',
        'kepada_yth' => ['Dekan FMIKOM', 'Kaprodi Informatika'],
        'data' => [
            'isi_ringkas' => 'Mohon izin untuk mengikuti kegiatan akademik di luar kampus.',
        ],
    ];

    $previewResponse = $this->actingAs($admin)
        ->post('/admin/surat/preview', $payload)
        ->assertRedirect('/admin/surat/preview');

    $previewResponse->assertSessionHas('admin_surat_preview', function (array $state) {
        return ($state['payload']['perihal'] ?? null) === 'Permohonan Dispensasi'
            && ($state['payload']['lampiran_keterangan'] ?? null) === '1 (satu) lembar'
            && ($state['payload']['kepada_yth'][0] ?? null) === 'Dekan FMIKOM'
            && ($state['payload']['kepada_yth'][1] ?? null) === 'Kaprodi Informatika'
            && ($state['payload']['data']['perihal'] ?? null) === 'Permohonan Dispensasi'
            && ($state['payload']['data']['lampiran_keterangan'] ?? null) === '1 (satu) lembar'
            && ($state['payload']['data']['kepada_yth'][0] ?? null) === 'Dekan FMIKOM';
    });

    $rendered = app(SuratTemplateRendererService::class)->renderJenisSuratPreview(
        $jenisSurat->fresh('template.placeholders'),
        [
            'isi_ringkas' => 'Mohon izin untuk mengikuti kegiatan akademik di luar kampus.',
            'perihal' => 'Permohonan Dispensasi',
            'lampiran_keterangan' => '1 (satu) lembar',
            'kepada_yth' => ['Dekan FMIKOM', 'Kaprodi Informatika'],
        ],
        [
            'tanggal_surat' => now(),
            'kota_surat' => 'Cilacap',
            'surat' => [
                'nomor_surat' => 'AUTO/GENERATED/AFTER/APPROVAL',
            ],
        ],
    );

    expect($rendered['html'])->toContain('Permohonan Dispensasi');
    expect($rendered['html'])->toContain('1 (satu) lembar');
    expect($rendered['html'])->toContain('Dekan FMIKOM');
    expect($rendered['html'])->toContain('Kaprodi Informatika');

    $this->actingAs($admin)
        ->post('/admin/surat/store', $payload)
        ->assertRedirect('/admin/dashboard')
        ->assertSessionHas('success');

    $surat = Surat::query()->latest('id')->firstOrFail();

    expect($surat->status)->toBe(Surat::STATUS_VALIDATED_ADMIN);

    $this->assertDatabaseHas('surat_data', [
        'surat_id' => $surat->id,
        'field_name' => 'perihal',
        'field_value' => 'Permohonan Dispensasi',
    ]);

    $this->assertDatabaseHas('surat_data', [
        'surat_id' => $surat->id,
        'field_name' => 'lampiran_keterangan',
        'field_value' => '1 (satu) lembar',
    ]);

    $this->assertDatabaseHas('surat_data', [
        'surat_id' => $surat->id,
        'field_name' => 'kepada_yth',
        'field_value' => json_encode(['Dekan FMIKOM', 'Kaprodi Informatika'], JSON_THROW_ON_ERROR),
    ]);
});

test('admin rejected surat edit uses the same manual surat contract fields', function () {
    $this->withoutVite();

    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $dekanRole = Role::create([
        'nama' => 'Dekan',
        'slug' => 'dekan',
    ]);

    $admin = createUserWithType($adminRole->slug, [
        'name' => 'Admin FAST',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Revisi Dispensasi',
        'slug' => 'surat-revisi-dispensasi',
        'approval_role_id' => $dekanRole->id,
        'perlu_approval' => true,
        'is_active' => true,
        'field_config' => [
            [
                'name' => 'isi_ringkas',
                'label' => 'Isi Ringkas',
                'type' => 'textarea',
                'required' => true,
            ],
        ],
    ]);

    $template = SuratTemplate::create([
        'jenis_surat_id' => $jenisSurat->id,
        'name' => 'Template Revisi '.$jenisSurat->nama,
        'slug' => 'template-revisi-'.$jenisSurat->slug,
        'format' => 'html',
        'subject' => $jenisSurat->nama,
        'template_body' => '<p>{{perihal}}</p><p>{{lampiran_keterangan}}</p><p>{{isi_ringkas}}</p>',
        'version' => 1,
        'is_active' => true,
    ]);

    $template->placeholders()->createMany([
        [
            'placeholder_key' => 'perihal',
            'label' => 'Perihal',
            'source_type' => 'surat_data',
            'source_key' => 'perihal',
            'is_required' => false,
        ],
        [
            'placeholder_key' => 'lampiran_keterangan',
            'label' => 'Keterangan Lampiran',
            'source_type' => 'surat_data',
            'source_key' => 'lampiran_keterangan',
            'is_required' => false,
        ],
        [
            'placeholder_key' => 'isi_ringkas',
            'label' => 'Isi Ringkas',
            'source_type' => 'surat_data',
            'source_key' => 'isi_ringkas',
            'is_required' => true,
        ],
    ]);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $admin->id,
        'type' => 'surat_keluar',
        'keperluan' => 'Draft dispensasi perlu direvisi.',
        'status' => Surat::STATUS_REVISION_REQUESTED,
        'validated_by_admin_id' => $admin->id,
        'validated_by_admin_at' => now()->subDay(),
        'revisi_ke' => 1,
        'catatan_revisi' => 'Perbarui tujuan surat dan lampiran.',
        'tanggal_pengajuan' => now()->subDays(2),
        'isi_surat' => json_encode([
            'jenis_surat_id' => $jenisSurat->id,
            'jenis_surat' => $jenisSurat->nama,
            'keperluan' => 'Draft dispensasi perlu direvisi.',
            'data' => [
                'perihal' => 'Permohonan Dispensasi Lama',
                'lampiran_keterangan' => '1 berkas lama',
                'kepada_yth' => ['Tujuan Lama'],
                'isi_ringkas' => 'Isi lama surat revisi.',
            ],
        ], JSON_THROW_ON_ERROR),
    ]);

    $surat->dataEntries()->createMany([
        [
            'field_name' => 'perihal',
            'field_value' => 'Permohonan Dispensasi Lama',
        ],
        [
            'field_name' => 'lampiran_keterangan',
            'field_value' => '1 berkas lama',
        ],
        [
            'field_name' => 'kepada_yth',
            'field_value' => json_encode(['Tujuan Lama'], JSON_THROW_ON_ERROR),
        ],
        [
            'field_name' => 'isi_ringkas',
            'field_value' => 'Isi lama surat revisi.',
        ],
    ]);

    $surat->approvalFlows()->create([
        'approver_id' => $admin->id,
        'urutan' => 1,
        'role' => FastApprovalWorkflowService::ROLE_DEKAN,
        'status' => 'revision_requested',
        'keterangan' => 'Dikembalikan untuk revisi',
        'catatan' => 'Perbarui tujuan surat dan lampiran.',
        'approved_at' => now()->subDay(),
        'tanggal_aksi' => now()->subDay(),
    ]);

    $this->actingAs($admin)
        ->get("/admin/surat/{$surat->id}/edit")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/letters/Edit')
            ->where('formData.perihal', 'Permohonan Dispensasi Lama')
            ->where('formData.lampiran_keterangan', '1 berkas lama')
            ->where('formData.kepada_yth.0', 'Tujuan Lama')
            ->where('formData.data.isi_ringkas', 'Isi lama surat revisi.'));

    $this->actingAs($admin)
        ->patch("/admin/surat/{$surat->id}", [
            'jenis_surat_id' => $jenisSurat->id,
            'keperluan' => 'Draft dispensasi sudah diperbarui.',
            'perihal' => 'Permohonan Dispensasi Baru',
            'form_data' => [
                'isi_ringkas' => 'Isi revisi terbaru untuk diajukan ulang.',
                'lampiran_keterangan' => '2 lembar',
                'kepada_yth' => ['Dekan FMIKOM', 'Kaprodi Informatika'],
            ],
        ])
        ->assertRedirect(route('admin.surat.show', $surat->id))
        ->assertSessionHas('success');

    $surat->refresh();

    expect($surat->status)->toBe(Surat::STATUS_VALIDATED_ADMIN);
    expect($surat->catatan_revisi)->toBeNull();

    $this->assertDatabaseHas('surat_data', [
        'surat_id' => $surat->id,
        'field_name' => 'perihal',
        'field_value' => 'Permohonan Dispensasi Baru',
    ]);

    $this->assertDatabaseHas('surat_data', [
        'surat_id' => $surat->id,
        'field_name' => 'lampiran_keterangan',
        'field_value' => '2 lembar',
    ]);

    $this->assertDatabaseHas('surat_data', [
        'surat_id' => $surat->id,
        'field_name' => 'kepada_yth',
        'field_value' => json_encode(['Dekan FMIKOM', 'Kaprodi Informatika'], JSON_THROW_ON_ERROR),
    ]);
});

test('manual surat contract stays consistent across template update create preview store final render and rejected edit', function () {
    $this->withoutVite();
    Storage::fake('public');

    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $dekanRole = Role::create([
        'nama' => 'Dekan',
        'slug' => 'dekan',
    ]);

    $admin = createUserWithType($adminRole->slug, [
        'name' => 'Admin FAST',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Kontrak Menyeluruh',
        'slug' => 'surat-kontrak-menyeluruh',
        'kode_surat' => 'SKM',
        'approval_role_id' => $dekanRole->id,
        'perlu_approval' => true,
        'is_active' => true,
        'field_config' => [
            [
                'name' => 'perihal',
                'label' => 'Perihal Bocor',
                'type' => 'text',
                'required' => false,
            ],
            [
                'name' => 'lampiran_keterangan',
                'label' => 'Lampiran Bocor',
                'type' => 'text',
                'required' => false,
            ],
            [
                'name' => 'kepada_yth',
                'label' => 'Kepada Bocor',
                'type' => 'repeatable',
                'required' => false,
            ],
            [
                'name' => 'penerima_manual',
                'label' => 'Recipient Lama',
                'type' => 'recipient',
                'required' => false,
            ],
            [
                'name' => 'isi_ringkas',
                'label' => 'Isi Ringkas',
                'type' => 'textarea',
                'required' => true,
                'help' => 'Isi ringkasan surat.',
            ],
        ],
    ]);

    $template = SuratTemplate::create([
        'jenis_surat_id' => $jenisSurat->id,
        'name' => 'Template '.$jenisSurat->nama,
        'slug' => 'template-'.$jenisSurat->slug,
        'format' => 'html',
        'subject' => $jenisSurat->nama,
        'template_body' => '<p>{{isi_ringkas}}</p>',
        'version' => 1,
        'is_active' => true,
    ]);

    $template->placeholders()->create([
        'placeholder_key' => 'isi_ringkas',
        'label' => 'Isi Ringkas',
        'source_type' => 'surat_data',
        'source_key' => 'isi_ringkas',
        'is_required' => true,
    ]);

    $templateBody = json_encode([
        [
            'type' => 'header_surat',
            'nomor' => '{{nomor_surat}}',
            'lampiran' => '{{lampiran_keterangan}}',
            'perihal' => '{{perihal}}',
            'kota' => '{{kota_surat}}',
            'tanggal' => '{{tanggal_surat_panjang}}',
        ],
        [
            'type' => 'kepada_yth',
            'penerima' => ['{{kepada_yth}}'],
            'lokasi' => 'di-',
            'tempat' => 'Tempat',
        ],
        [
            'type' => 'paragraf',
            'teks' => '{{isi_ringkas}}',
            'align' => 'justify',
        ],
    ], JSON_THROW_ON_ERROR);

    $this->actingAs($admin)
        ->put(route('admin.templates.update', $jenisSurat->id), [
            'name' => 'Template Kontrak Final',
            'template_body' => $templateBody,
            'field_config' => $jenisSurat->field_config,
        ])
        ->assertRedirect(route('admin.templates.index', ['jenis_surat_id' => $jenisSurat->id]));

    $jenisSurat->refresh();
    $template = $jenisSurat->template()->with('placeholders')->firstOrFail();

    expect($jenisSurat->field_config)->toHaveCount(1);
    expect($jenisSurat->field_config[0]['name'] ?? null)->toBe('isi_ringkas');

    $this->assertDatabaseHas('surat_template_placeholders', [
        'surat_template_id' => $template->id,
        'placeholder_key' => 'perihal',
        'source_type' => 'surat_data',
        'source_key' => 'perihal',
    ]);

    $this->assertDatabaseHas('surat_template_placeholders', [
        'surat_template_id' => $template->id,
        'placeholder_key' => 'lampiran_keterangan',
        'source_type' => 'surat_data',
        'source_key' => 'lampiran_keterangan',
    ]);

    $this->assertDatabaseHas('surat_template_placeholders', [
        'surat_template_id' => $template->id,
        'placeholder_key' => 'kepada_yth',
        'source_type' => 'surat_data',
        'source_key' => 'kepada_yth',
    ]);

    $this->actingAs($admin)
        ->get("/admin/surat/form/{$jenisSurat->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/letters/Form')
            ->where('formData.perihal', '')
            ->where('formData.lampiran_keterangan', '')
            ->where('jenisSurat.field_config', [
                [
                    'name' => 'isi_ringkas',
                    'label' => 'Isi Ringkas',
                    'type' => 'textarea',
                    'required' => true,
                    'editable_role' => 'mahasiswa',
                    'mode_form_pemohon' => 'editable',
                    'sumber_data' => 'data_pemohon',
                    'placeholder' => '',
                    'help' => 'Isi ringkasan surat.',
                    'options' => [],
                    'repeatable' => false,
                    'add_label' => 'Tambah',
                    'item_label' => 'Item',
                ],
            ]));

    $payload = [
        'jenis_surat_id' => $jenisSurat->id,
        'keperluan' => 'Pengajuan kontrak menyeluruh.',
        'perihal' => 'Permohonan Dispensasi Kontrak',
        'lampiran_keterangan' => '3 lampiran',
        'kepada_yth' => ['Dekan FMIKOM', 'Kaprodi Informatika'],
        'data' => [
            'isi_ringkas' => 'Mohon persetujuan dispensasi untuk kegiatan akademik.',
        ],
    ];

    $previewResponse = $this->actingAs($admin)
        ->post('/admin/surat/preview', $payload)
        ->assertRedirect('/admin/surat/preview');

    $previewResponse->assertSessionHas('admin_surat_preview', function (array $state) {
        return ($state['payload']['perihal'] ?? null) === 'Permohonan Dispensasi Kontrak'
            && ($state['payload']['lampiran_keterangan'] ?? null) === '3 lampiran'
            && ($state['payload']['kepada_yth'][0] ?? null) === 'Dekan FMIKOM'
            && ($state['payload']['data']['isi_ringkas'] ?? null) === 'Mohon persetujuan dispensasi untuk kegiatan akademik.'
            && ($state['payload']['data']['perihal'] ?? null) === 'Permohonan Dispensasi Kontrak';
    });

    $previewRender = app(SuratTemplateRendererService::class)->renderJenisSuratPreview(
        $jenisSurat->fresh('template.placeholders'),
        [
            'isi_ringkas' => 'Mohon persetujuan dispensasi untuk kegiatan akademik.',
            'perihal' => 'Permohonan Dispensasi Kontrak',
            'lampiran_keterangan' => '3 lampiran',
            'kepada_yth' => ['Dekan FMIKOM', 'Kaprodi Informatika'],
        ],
        [
            'tanggal_surat' => now(),
            'kota_surat' => 'Cilacap',
            'surat' => [
                'nomor_surat' => 'AUTO/GENERATED/AFTER/APPROVAL',
            ],
        ],
    );

    expect($previewRender['html'])->toContain('Permohonan Dispensasi Kontrak');
    expect($previewRender['html'])->toContain('3 lampiran');
    expect($previewRender['html'])->toContain('Dekan FMIKOM');
    expect($previewRender['html'])->toContain('Kaprodi Informatika');
    expect($previewRender['html'])->toContain('Mohon persetujuan dispensasi untuk kegiatan akademik.');

    $this->actingAs($admin)
        ->post('/admin/surat/store', $payload)
        ->assertRedirect('/admin/dashboard')
        ->assertSessionHas('success');

    $surat = Surat::query()->latest('id')->firstOrFail();

    expect($surat->status)->toBe(Surat::STATUS_VALIDATED_ADMIN);

    $this->assertDatabaseHas('surat_data', [
        'surat_id' => $surat->id,
        'field_name' => 'perihal',
        'field_value' => 'Permohonan Dispensasi Kontrak',
    ]);

    $surat->forceFill([
        'nomor_surat' => 'B/0042/SKM/VI/2026',
        'status' => Surat::STATUS_FINISHED,
    ])->save();

    $finalRender = app(SuratTemplateRendererService::class)->renderForSurat(
        $surat->fresh(['jenisSurat.template.placeholders', 'pemohon', 'dataEntries'])
    );

    expect($finalRender['html'])->toContain('B/0042/SKM/VI/2026');
    expect($finalRender['html'])->toContain('Permohonan Dispensasi Kontrak');
    expect($finalRender['html'])->toContain('3 lampiran');
    expect($finalRender['html'])->toContain('Dekan FMIKOM');
    expect($finalRender['html'])->toContain('Kaprodi Informatika');
    expect($finalRender['html'])->toContain('Mohon persetujuan dispensasi untuk kegiatan akademik.');

    $surat->forceFill([
        'status' => Surat::STATUS_REVISION_REQUESTED,
        'validated_by_admin_id' => $admin->id,
        'validated_by_admin_at' => now()->subDay(),
        'catatan_revisi' => 'Perbarui isi ringkas.',
        'revisi_ke' => 1,
    ])->save();

    $surat->approvalFlows()->create([
        'approver_id' => $admin->id,
        'urutan' => 1,
        'role' => FastApprovalWorkflowService::ROLE_DEKAN,
        'status' => 'revision_requested',
        'keterangan' => 'Dikembalikan untuk revisi',
        'catatan' => 'Perbarui isi ringkas.',
        'approved_at' => now()->subDay(),
        'tanggal_aksi' => now()->subDay(),
    ]);

    $this->actingAs($admin)
        ->get("/admin/surat/{$surat->id}/edit")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/letters/Edit')
            ->where('formData.perihal', 'Permohonan Dispensasi Kontrak')
            ->where('formData.lampiran_keterangan', '3 lampiran')
            ->where('formData.kepada_yth.0', 'Dekan FMIKOM')
            ->where('jenisSurat.field_config', [
                [
                    'name' => 'isi_ringkas',
                    'label' => 'Isi Ringkas',
                    'type' => 'textarea',
                    'required' => true,
                    'editable_role' => 'mahasiswa',
                    'mode_form_pemohon' => 'editable',
                    'sumber_data' => 'data_pemohon',
                    'placeholder' => '',
                    'help' => 'Isi ringkasan surat.',
                    'options' => [],
                    'repeatable' => false,
                    'add_label' => 'Tambah',
                    'item_label' => 'Item',
                ],
            ]));

    $this->actingAs($admin)
        ->patch("/admin/surat/{$surat->id}", [
            'jenis_surat_id' => $jenisSurat->id,
            'keperluan' => 'Pengajuan kontrak menyeluruh yang telah direvisi.',
            'perihal' => 'Permohonan Dispensasi Kontrak Revisi',
            'form_data' => [
                'isi_ringkas' => 'Isi revisi kontrak agar siap disetujui ulang.',
                'lampiran_keterangan' => '4 lampiran',
                'kepada_yth' => ['Dekan FMIKOM', 'BAAK'],
            ],
        ])
        ->assertRedirect(route('admin.surat.show', $surat->id))
        ->assertSessionHas('success');

    $surat->refresh();

    expect($surat->status)->toBe(Surat::STATUS_VALIDATED_ADMIN);

    $this->assertDatabaseHas('surat_data', [
        'surat_id' => $surat->id,
        'field_name' => 'perihal',
        'field_value' => 'Permohonan Dispensasi Kontrak Revisi',
    ]);

    $this->assertDatabaseHas('surat_data', [
        'surat_id' => $surat->id,
        'field_name' => 'lampiran_keterangan',
        'field_value' => '4 lampiran',
    ]);

    $this->assertDatabaseHas('surat_data', [
        'surat_id' => $surat->id,
        'field_name' => 'kepada_yth',
        'field_value' => json_encode(['Dekan FMIKOM', 'BAAK'], JSON_THROW_ON_ERROR),
    ]);
});

test('admin preview can render distinct signer and student names for surat keterangan lulus style template', function () {
    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $dekanRole = Role::create([
        'nama' => 'Dekan',
        'slug' => 'dekan',
    ]);

    $admin = createUserWithType($adminRole->slug, [
        'name' => 'Admin FAST',
    ]);

    $dekan = createUserWithType($dekanRole->slug, [
        'name' => 'Thomas Alva',
        'nim_nip' => '87654321',
        'nomor_induk' => '87654321',
        'status_approval' => 'approved',
        'is_active' => true,
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Keterangan Lulus Uji',
        'slug' => 'surat-keterangan-lulus-uji',
        'approval_role_id' => $dekanRole->id,
        'perlu_approval' => true,
        'is_active' => true,
        'field_config' => [
            [
                'name' => 'nama_mahasiswa',
                'label' => 'Nama Mahasiswa',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'nim',
                'label' => 'NIM',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'tahun_lulus',
                'label' => 'Tahun Lulus',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'tempat_tanggal_lahir',
                'label' => 'Tempat/Tanggal Lahir',
                'type' => 'text',
                'required' => true,
            ],
        ],
    ]);

    $template = SuratTemplate::create([
        'jenis_surat_id' => $jenisSurat->id,
        'name' => 'Template '.$jenisSurat->nama,
        'slug' => 'template-'.$jenisSurat->slug,
        'format' => 'html',
        'subject' => $jenisSurat->nama,
        'template_body' => json_encode([
            [
                'type' => 'tabel_data',
                'rows' => [
                    ['label' => 'Nama', 'nilai' => '{{nama_penanda_tangan}}'],
                    ['label' => 'Nomor Induk', 'nilai' => '{{nomor_induk_penanda_tangan}}'],
                    ['label' => 'Jabatan', 'nilai' => '{{jabatan_penanda_tangan}}'],
                ],
            ],
            [
                'type' => 'tabel_data',
                'rows' => [
                    ['label' => 'Nama', 'nilai' => '{{nama_mahasiswa}}'],
                    ['label' => 'NIM', 'nilai' => '{{nim}}'],
                    ['label' => 'Tahun Lulus', 'nilai' => '{{tahun_lulus}}'],
                    ['label' => 'Tempat, Tanggal Lahir', 'nilai' => '{{tempat_tanggal_lahir}}'],
                ],
            ],
        ], JSON_THROW_ON_ERROR),
        'version' => 1,
        'is_active' => true,
    ]);

    \App\Support\TemplatePlaceholderSynchronizer::syncTemplate($template, $jenisSurat->field_config);

    $payload = [
        'jenis_surat_id' => $jenisSurat->id,
        'keperluan' => 'Uji surat keterangan lulus.',
        'data' => [
            'nama_mahasiswa' => 'Hellen',
            'nim' => '202231001',
            'tahun_lulus' => '2026',
            'tempat_tanggal_lahir' => 'Cilacap, 1 Januari 2004',
        ],
    ];

    $this->actingAs($admin)
        ->post('/admin/surat/preview', $payload)
        ->assertRedirect('/admin/surat/preview');

    $rendered = app(SuratTemplateRendererService::class)->renderJenisSuratPreview(
        $jenisSurat->fresh('template.placeholders'),
        $payload['data'],
        [
            'approval_role_slug' => 'dekan',
            'tanggal_surat' => now(),
            'kota_surat' => 'Cilacap',
            'surat' => [
                'nomor_surat' => 'AUTO/GENERATED/AFTER/APPROVAL',
            ],
            'user' => [
                'name' => $admin->name,
            ],
            'signer' => [
                'name' => $dekan->name,
                'email' => $dekan->email,
                'nomor_induk' => $dekan->nomor_induk,
                'jabatan' => 'Dekan',
                'program_studi' => '',
            ],
        ],
    );

    expect($rendered['html'])->toContain('Thomas Alva');
    expect($rendered['html'])->toContain('87654321');
    expect($rendered['html'])->toContain('Dekan');
    expect($rendered['html'])->toContain('Admin FAST');
});

test('component renderer can place nomor surat under centered title without header surat', function () {
    $html = \App\Services\SuratKomponenRenderer::render([
        [
            'type' => 'judul',
            'teks' => 'SURAT KETERANGAN LULUS',
            'align' => 'center',
            'font_size' => '12pt',
            'bold' => true,
            'underline' => true,
        ],
        [
            'type' => 'subjudul',
            'teks' => 'Nomor: {{nomor_surat}}',
            'font_size' => '11pt',
        ],
    ], [
        'nomor_surat' => 'B/138/Ybk.041.10/PK.05.00/2026',
    ]);

    expect($html)->toContain('SURAT KETERANGAN LULUS');
    expect($html)->toContain('Nomor: B/138/Ybk.041.10/PK.05.00/2026');
    expect($html)->toContain('text-align: center');
    expect($html)->toContain('text-decoration: underline');
});


