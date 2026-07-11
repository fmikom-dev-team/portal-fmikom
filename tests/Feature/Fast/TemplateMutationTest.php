<?php

use App\Models\JenisSurat;
use App\Models\SuratTemplate;
use App\Modules\Fast\Services\Admin\TemplateMutationService;
use Illuminate\Http\Request;

it('persists dynamic fields even when the key is derived from the label', function () {
    $jenisSurat = JenisSurat::create([
        'nama' => 'Permohonan Cuti Mahasiswa',
        'slug' => 'permohonan-cuti-mahasiswa-test',
        'deskripsi' => 'Test template',
        'template_file_path' => null,
        'field_config' => [],
        'perlu_approval' => true,
        'alur_pengajuan' => 'submission',
        'letter_mode' => 'personal',
        'is_active' => true,
    ]);

    SuratTemplate::create([
        'jenis_surat_id' => $jenisSurat->id,
        'name' => $jenisSurat->nama,
        'slug' => 'template-permohonan-cuti-mahasiswa-test',
        'format' => 'html',
        'template_header' => '',
        'template_body' => '[]',
        'template_footer' => '',
        'subject' => 'personal',
        'version' => 1,
        'is_active' => true,
        'source_reference' => null,
        'css_style' => '',
    ]);

    $request = Request::create('/admin/templates/'.$jenisSurat->id, 'PUT', [
        'template_body' => '[]',
        'jenis_surat_nama' => $jenisSurat->nama,
        'field_config' => [
            [
                'name' => '',
                'label' => 'Alasan Cuti Tambahan',
                'type' => 'textarea',
                'required' => true,
                'repeatable' => false,
                'add_label' => 'Tambah',
                'item_label' => 'Item',
                'placeholder' => '',
                'help' => '',
                'options' => [],
                'sumber_data' => 'data_pemohon',
                'editable_role' => 'mahasiswa',
                'mode_form_pemohon' => 'editable',
            ],
        ],
        'letter_mode' => 'personal',
    ]);

    app(TemplateMutationService::class)->update($request, $jenisSurat);

    $saved = $jenisSurat->fresh();

    expect($saved?->field_config)->toHaveCount(1)
        ->and($saved?->field_config[0]['name'] ?? null)->toBe('alasan_cuti_tambahan')
        ->and($saved?->field_config[0]['label'] ?? null)->toBe('Alasan Cuti Tambahan')
        ->and($saved?->field_config[0]['type'] ?? null)->toBe('textarea');
});

it('clears dynamic fields when the submitted field_config is empty', function () {
    $jenisSurat = JenisSurat::create([
        'nama' => 'Permohonan Cuti Mahasiswa',
        'slug' => 'permohonan-cuti-mahasiswa-clear-test',
        'deskripsi' => 'Test template',
        'template_file_path' => null,
        'field_config' => [
            [
                'name' => 'alasan_cuti_lama',
                'label' => 'Alasan Cuti Lama',
                'type' => 'textarea',
                'required' => true,
                'placeholder' => '',
                'help' => '',
                'options' => [],
                'repeatable' => false,
                'add_label' => 'Tambah',
                'item_label' => 'Item',
                'sumber_data' => 'data_pemohon',
                'editable_role' => 'mahasiswa',
                'mode_form_pemohon' => 'editable',
            ],
        ],
        'perlu_approval' => true,
        'alur_pengajuan' => 'submission',
        'letter_mode' => 'personal',
        'is_active' => true,
    ]);

    SuratTemplate::create([
        'jenis_surat_id' => $jenisSurat->id,
        'name' => $jenisSurat->nama,
        'slug' => 'template-permohonan-cuti-mahasiswa-clear-test',
        'format' => 'html',
        'template_header' => '',
        'template_body' => '[]',
        'template_footer' => '',
        'subject' => 'personal',
        'version' => 1,
        'is_active' => true,
        'source_reference' => null,
        'css_style' => '',
    ]);

    $request = Request::create('/admin/templates/'.$jenisSurat->id, 'PUT', [
        'template_body' => '[]',
        'jenis_surat_nama' => $jenisSurat->nama,
        'field_config' => [],
        'letter_mode' => 'personal',
    ]);

    app(TemplateMutationService::class)->update($request, $jenisSurat);

    expect($jenisSurat->fresh()?->field_config)->toBe([]);
});
