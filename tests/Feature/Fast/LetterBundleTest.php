<?php

use App\Models\JenisSurat;
use App\Models\Surat;
use App\Models\User;
use App\Modules\Fast\Services\Shared\OutgoingLetterAttachmentService;
use App\Modules\Fast\Services\Shared\SuratDocumentGeneratorService;

it('builds an attachment bundle section that can be appended to the final surat pdf', function () {
    $student = User::factory()->create([
        'user_type' => 'mahasiswa',
        'email_verified_at' => now(),
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Keterangan',
        'slug' => 'surat-keterangan-bundle-test',
        'deskripsi' => 'Test',
        'field_config' => [],
        'perlu_approval' => true,
        'alur_pengajuan' => 'submission',
        'letter_mode' => 'personal',
        'is_active' => true,
    ]);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $student->id,
        'keperluan' => 'Pengujian bundle lampiran',
        'status' => Surat::STATUS_FINISHED,
        'nomor_surat' => 'B/001/TEST/2026',
        'isi_surat' => json_encode([
            'data' => [
                'lampiran_mode' => 'student_list',
                'lampiran_judul' => 'DAFTAR NAMA MAHASISWA',
                'lampiran_orientation' => 'portrait',
                'lampiran_label_no' => 'No',
                'lampiran_label_nama' => 'Nama Mahasiswa',
                'lampiran_label_nim' => 'NIM',
                'lampiran_label_prodi' => 'Program Studi',
                'lampiran_columns' => [
                    ['key' => 'col_1', 'label' => 'No', 'align' => 'center', 'bold' => true],
                    ['key' => 'col_2', 'label' => 'Nama Mahasiswa', 'align' => 'left', 'bold' => true],
                ],
                'lampiran_rows' => [
                    ['col_1' => '1', 'col_2' => 'Siti Aisyah'],
                ],
            ],
        ], JSON_THROW_ON_ERROR),
        'tanggal_pengajuan' => now(),
        'tanggal_selesai' => now(),
        'generated_at' => now(),
    ]);

    /** @var OutgoingLetterAttachmentService $service */
    $service = app(OutgoingLetterAttachmentService::class);
    $section = $service->buildBundleAttachmentSection($surat->fresh(['jenisSurat']));

    expect($section)->not->toBeNull();
    expect($section['orientation'])->toBe('portrait');
    expect($section['html'])->toContain('Siti Aisyah');
    expect($section['html'])->toContain('lampiran-table');
});

it('uses the mPDF path for landscape attachment bundles when mPDF is available', function () {
    $student = User::factory()->create([
        'user_type' => 'mahasiswa',
        'email_verified_at' => now(),
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Keterangan',
        'slug' => 'surat-keterangan-landscape-bundle-test',
        'deskripsi' => 'Test',
        'field_config' => [],
        'perlu_approval' => true,
        'alur_pengajuan' => 'submission',
        'letter_mode' => 'personal',
        'is_active' => true,
    ]);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $student->id,
        'keperluan' => 'Pengujian bundle lampiran landscape',
        'status' => Surat::STATUS_FINISHED,
        'nomor_surat' => 'B/002/TEST/2026',
        'isi_surat' => json_encode([
            'data' => [
                'lampiran_mode' => 'student_list',
                'lampiran_judul' => 'DAFTAR NAMA MAHASISWA',
                'lampiran_orientation' => 'landscape',
                'lampiran_label_no' => 'No',
                'lampiran_label_nama' => 'Nama Mahasiswa',
                'lampiran_label_nim' => 'NIM',
                'lampiran_label_prodi' => 'Program Studi',
                'lampiran_columns' => [
                    ['key' => 'col_1', 'label' => 'No', 'align' => 'center', 'bold' => true],
                    ['key' => 'col_2', 'label' => 'Nama Mahasiswa', 'align' => 'left', 'bold' => true],
                ],
                'lampiran_rows' => [
                    ['col_1' => '1', 'col_2' => 'Siti Aisyah'],
                ],
            ],
        ], JSON_THROW_ON_ERROR),
        'tanggal_pengajuan' => now(),
        'tanggal_selesai' => now(),
        'generated_at' => now(),
    ]);

    $attachmentService = app(OutgoingLetterAttachmentService::class);
    $section = $attachmentService->buildBundleAttachmentSection($surat->fresh(['jenisSurat']));

    expect($section)->not->toBeNull();
    expect($section['orientation'])->toBe('landscape');

    $service = app(SuratDocumentGeneratorService::class);
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('shouldUseBrowserPdfForAttachmentSection');
    $method->setAccessible(true);

    expect($method->invoke($service, $section))->toBeFalse();
});
