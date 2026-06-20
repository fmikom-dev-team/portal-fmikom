<?php

use App\Models\JenisSurat;
use App\Models\Role;
use App\Models\SuratTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('template update synchronizes placeholders using the surat data contract', function () {
    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Kontrak Template',
        'slug' => 'surat-kontrak-template',
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
        'name' => 'Template Awal',
        'slug' => 'template-awal',
        'format' => 'html',
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

    $this->actingAs($admin)
        ->put(route('admin.templates.update', $jenisSurat->id), [
            'name' => 'Template Revisi',
            'template_body' => '<p>{{perihal}}</p><p>{{lampiran_keterangan}}</p><p>{{isi_ringkas}}</p>',
            'field_config' => $jenisSurat->field_config,
        ])
        ->assertRedirect(route('admin.templates.index', ['jenis_surat_id' => $jenisSurat->id]));

    $template->refresh();

    expect($template->version)->toBe(2);

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
        'placeholder_key' => 'isi_ringkas',
        'source_type' => 'surat_data',
        'source_key' => 'isi_ringkas',
    ]);
});

test('template update enforces account bound placeholder sources for signer and pemohon fields', function () {
    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Kontrak Aktor',
        'slug' => 'surat-kontrak-aktor',
        'is_active' => true,
        'field_config' => [],
    ]);

    $template = SuratTemplate::create([
        'jenis_surat_id' => $jenisSurat->id,
        'name' => 'Template Aktor',
        'slug' => 'template-aktor',
        'format' => 'html',
        'template_body' => '<p>{{nama_pemohon}}</p><p>{{nama_penanda_tangan}}</p><p>{{nomor_induk_penanda_tangan}}</p>',
        'version' => 1,
        'is_active' => true,
    ]);

    $template->placeholders()->createMany([
        [
            'placeholder_key' => 'nama_pemohon',
            'label' => 'Nama Pemohon',
            'source_type' => 'surat_data',
            'source_key' => 'nama_pemohon',
            'is_required' => true,
        ],
        [
            'placeholder_key' => 'nama_penanda_tangan',
            'label' => 'Nama Penanda Tangan',
            'source_type' => 'surat_data',
            'source_key' => 'nama_penanda_tangan',
            'is_required' => true,
        ],
        [
            'placeholder_key' => 'nomor_induk_penanda_tangan',
            'label' => 'Nomor Induk Penanda Tangan',
            'source_type' => 'surat_data',
            'source_key' => 'nomor_induk_penanda_tangan',
            'is_required' => true,
        ],
    ]);

    $this->actingAs($admin)
        ->put(route('admin.templates.update', $jenisSurat->id), [
            'name' => 'Template Aktor Revisi',
            'template_body' => '<p>{{nama_pemohon}}</p><p>{{nama_penanda_tangan}}</p><p>{{nomor_induk_penanda_tangan}}</p>',
            'field_config' => [],
        ])
        ->assertRedirect(route('admin.templates.index', ['jenis_surat_id' => $jenisSurat->id]));

    $this->assertDatabaseHas('surat_template_placeholders', [
        'surat_template_id' => $template->id,
        'placeholder_key' => 'nama_pemohon',
        'source_type' => 'user',
        'source_key' => 'name',
    ]);

    $this->assertDatabaseHas('surat_template_placeholders', [
        'surat_template_id' => $template->id,
        'placeholder_key' => 'nama_penanda_tangan',
        'source_type' => 'computed',
        'source_key' => 'nama_penanda_tangan',
    ]);

    $this->assertDatabaseHas('surat_template_placeholders', [
        'surat_template_id' => $template->id,
        'placeholder_key' => 'nomor_induk_penanda_tangan',
        'source_type' => 'computed',
        'source_key' => 'nomor_induk_penanda_tangan',
    ]);
});

test('template duplication rebuilds placeholders from the same contract source', function () {
    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Kontrak Duplikat',
        'slug' => 'surat-kontrak-duplikat',
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

    SuratTemplate::create([
        'jenis_surat_id' => $jenisSurat->id,
        'name' => 'Template Kontrak Duplikat',
        'slug' => 'template-kontrak-duplikat',
        'format' => 'html',
        'template_body' => '<p>{{perihal}}</p><p>{{isi_ringkas}}</p>',
        'version' => 1,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.templates.duplicate', $jenisSurat->id))
        ->assertRedirect();

    $duplicateJenisSurat = JenisSurat::query()
        ->where('nama', 'Surat Kontrak Duplikat (Salinan)')
        ->firstOrFail();

    $duplicateTemplate = SuratTemplate::query()
        ->where('jenis_surat_id', $duplicateJenisSurat->id)
        ->latest('id')
        ->firstOrFail();

    $this->assertDatabaseHas('surat_template_placeholders', [
        'surat_template_id' => $duplicateTemplate->id,
        'placeholder_key' => 'perihal',
        'source_type' => 'surat_data',
        'source_key' => 'perihal',
    ]);

    $this->assertDatabaseHas('surat_template_placeholders', [
        'surat_template_id' => $duplicateTemplate->id,
        'placeholder_key' => 'isi_ringkas',
        'source_type' => 'surat_data',
        'source_key' => 'isi_ringkas',
    ]);
});

test('template update preserves repeatable field metadata for admin form rendering', function () {
    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Metadata Field',
        'slug' => 'surat-metadata-field',
        'is_active' => true,
        'field_config' => [],
    ]);

    SuratTemplate::create([
        'jenis_surat_id' => $jenisSurat->id,
        'name' => 'Template Metadata',
        'slug' => 'template-metadata',
        'format' => 'html',
        'template_body' => '<p>{{daftar_penerima}}</p>',
        'version' => 1,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.templates.update', $jenisSurat->id), [
            'name' => 'Template Metadata Revisi',
            'template_body' => '<p>{{daftar_penerima}}</p>',
            'field_config' => [
                [
                    'name' => 'daftar_penerima',
                    'label' => 'Daftar Penerima',
                    'type' => 'repeatable',
                    'required' => true,
                    'repeatable' => true,
                    'add_label' => 'Tambah Penerima',
                    'item_label' => 'Penerima',
                    'placeholder' => 'Nama penerima',
                    'help' => 'Isi satu atau lebih penerima surat.',
                    'options' => [],
                ],
            ],
        ])
        ->assertRedirect(route('admin.templates.index', ['jenis_surat_id' => $jenisSurat->id]));

    $jenisSurat->refresh();
    $field = $jenisSurat->field_config[0] ?? null;

    expect($field)->not->toBeNull();
    expect($field['type'] ?? null)->toBe('repeatable');
    expect($field['repeatable'] ?? null)->toBeTrue();
    expect($field['add_label'] ?? null)->toBe('Tambah Penerima');
    expect($field['item_label'] ?? null)->toBe('Penerima');
    expect($field['placeholder'] ?? null)->toBe('Nama penerima');
    expect($field['help'] ?? null)->toBe('Isi satu atau lebih penerima surat.');
});

test('template update strips reserved recipient field config from dynamic fields', function () {
    $this->withoutVite();

    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Field Recipient',
        'slug' => 'surat-field-recipient',
        'is_active' => true,
        'field_config' => [],
    ]);

    SuratTemplate::create([
        'jenis_surat_id' => $jenisSurat->id,
        'name' => 'Template Recipient',
        'slug' => 'template-recipient',
        'format' => 'html',
        'template_body' => '<p>{{kepada_yth}}</p><p>{{isi_ringkas}}</p>',
        'version' => 1,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.templates.update', $jenisSurat->id), [
            'name' => 'Template Recipient Revisi',
            'template_body' => '<p>{{kepada_yth}}</p><p>{{isi_ringkas}}</p>',
            'field_config' => [
                [
                    'name' => 'kepada_yth',
                    'label' => 'Kepada Yth',
                    'type' => 'recipient',
                    'required' => true,
                ],
                [
                    'name' => 'isi_ringkas',
                    'label' => 'Isi Ringkas',
                    'type' => 'textarea',
                    'required' => true,
                ],
            ],
        ])
        ->assertRedirect(route('admin.templates.index', ['jenis_surat_id' => $jenisSurat->id]));

    $jenisSurat->refresh();

    expect($jenisSurat->field_config)->toBeArray();
    expect($jenisSurat->field_config)->toHaveCount(1);
    expect($jenisSurat->field_config[0]['name'] ?? null)->toBe('isi_ringkas');

    $this->actingAs($admin)
        ->get(route('admin.templates.index', ['jenis_surat_id' => $jenisSurat->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/templates/Index')
            ->where('selectedJenisSurat.field_config', [
                [
                    'name' => 'isi_ringkas',
                    'label' => 'Isi Ringkas',
                    'type' => 'textarea',
                    'required' => true,
                    'editable_role' => 'mahasiswa',
                    'mode_form_pemohon' => 'editable',
                    'sumber_data' => 'data_pemohon',
                    'placeholder' => '',
                    'help' => '',
                    'options' => [],
                    'repeatable' => false,
                    'add_label' => 'Tambah',
                    'item_label' => 'Item',
                ],
            ]));
});

test('template update strips account bound field config from dynamic fields', function () {
    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Field Akun',
        'slug' => 'surat-field-akun',
        'is_active' => true,
        'field_config' => [],
    ]);

    SuratTemplate::create([
        'jenis_surat_id' => $jenisSurat->id,
        'name' => 'Template Field Akun',
        'slug' => 'template-field-akun',
        'format' => 'html',
        'template_body' => '<p>{{nama_penanda_tangan}}</p><p>{{nama_mahasiswa}}</p>',
        'version' => 1,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.templates.update', $jenisSurat->id), [
            'name' => 'Template Field Akun Revisi',
            'template_body' => '<p>{{nama_penanda_tangan}}</p><p>{{nama_mahasiswa}}</p>',
            'field_config' => [
                [
                    'name' => 'nama_penanda_tangan',
                    'label' => 'Nama Penanda Tangan',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'nama_mahasiswa',
                    'label' => 'Nama Mahasiswa',
                    'type' => 'text',
                    'required' => true,
                ],
            ],
        ])
        ->assertRedirect(route('admin.templates.index', ['jenis_surat_id' => $jenisSurat->id]));

    $jenisSurat->refresh();

    expect($jenisSurat->field_config)->toBeArray();
    expect($jenisSurat->field_config)->toHaveCount(1);
    expect($jenisSurat->field_config[0]['name'] ?? null)->toBe('nama_mahasiswa');
});

test('template update rejects duplicate dynamic field keys', function () {
    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $admin = createUserWithType($adminRole->slug, [
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Duplicate Field',
        'slug' => 'surat-duplicate-field',
        'is_active' => true,
        'field_config' => [],
    ]);

    SuratTemplate::create([
        'jenis_surat_id' => $jenisSurat->id,
        'name' => 'Template Duplicate Field',
        'slug' => 'template-duplicate-field',
        'format' => 'html',
        'template_body' => '<p>{{nama}}</p>',
        'version' => 1,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->from(route('admin.templates.index', ['jenis_surat_id' => $jenisSurat->id]))
        ->put(route('admin.templates.update', $jenisSurat->id), [
            'name' => 'Template Duplicate Field',
            'template_body' => '<p>{{nama}}</p>',
            'field_config' => [
                [
                    'name' => 'nama',
                    'label' => 'Nama Penanda Tangan',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'nama',
                    'label' => 'Nama Mahasiswa',
                    'type' => 'text',
                    'required' => true,
                ],
            ],
        ])
        ->assertRedirect(route('admin.templates.index', ['jenis_surat_id' => $jenisSurat->id]))
        ->assertSessionHasErrors(['field_config']);

    expect($jenisSurat->fresh()->field_config)->toBe([]);
});


