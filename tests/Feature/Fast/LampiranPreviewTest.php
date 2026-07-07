<?php

use App\Models\JenisSurat;
use App\Models\Surat;
use App\Models\SuratLampiran;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

it('lets signed public attachment links render the student attachment', function () {
    Storage::fake('local');

    $student = User::factory()->create([
        'user_type' => 'mahasiswa',
        'email_verified_at' => now(),
    ]);
    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Keterangan',
        'slug' => 'surat-keterangan-preview-test',
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
        'keperluan' => 'Pengujian lampiran',
        'status' => Surat::STATUS_VALIDATED_ADMIN,
        'isi_surat' => json_encode(['data' => ['nama' => $student->name]]),
        'tanggal_pengajuan' => now(),
    ]);

    Storage::disk('local')->put('surat-lampirans/student-attachment.pdf', '%PDF-1.4 test attachment');

    $lampiran = SuratLampiran::create([
        'surat_id' => $surat->id,
        'nama_file' => 'student-attachment.pdf',
        'file_path' => 'surat-lampirans/student-attachment.pdf',
        'tipe' => 'application/pdf',
    ]);

    $url = URL::temporarySignedRoute(
        'documents.public.lampiran.preview',
        now()->addMinutes(15),
        ['id' => $lampiran->id],
    );

    $this->get($url)
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});

it('lets approvers preview the same attachment through the approval route', function () {
    Storage::fake('local');

    $student = User::factory()->create([
        'user_type' => 'mahasiswa',
        'email_verified_at' => now(),
    ]);
    $approver = User::factory()->create([
        'user_type' => 'kaprodi',
        'email_verified_at' => now(),
    ]);
    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Keterangan',
        'slug' => 'surat-keterangan-approval-preview-test',
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
        'keperluan' => 'Pengujian lampiran',
        'status' => Surat::STATUS_VALIDATED_ADMIN,
        'isi_surat' => json_encode(['data' => ['nama' => $student->name]]),
        'tanggal_pengajuan' => now(),
    ]);

    Storage::disk('local')->put('surat-lampirans/approver-attachment.pdf', '%PDF-1.4 test attachment');

    $lampiran = SuratLampiran::create([
        'surat_id' => $surat->id,
        'nama_file' => 'approver-attachment.pdf',
        'file_path' => 'surat-lampirans/approver-attachment.pdf',
        'tipe' => 'application/pdf',
    ]);

    $this->actingAs($approver)
        ->withSession([
            'active_module' => 'FAST',
            'active_role' => 'kaprodi',
        ])
        ->get('/approval/lampiran/'.$lampiran->id.'/preview')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});
