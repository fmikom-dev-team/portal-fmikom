<?php

use App\Models\Magang\HariLibur;
use App\Models\Magang\KetidakhadiranMagang;
use App\Models\Magang\LogbookMagang;
use App\Models\Magang\LogbookPhoto;
use App\Models\Magang\PendaftaranMagang;
use App\Models\Magang\PerusahaanMitra;
use App\Models\Magang\SuratPenetapan;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Schema;

it('creates the required operational wims tables with expected columns', function () {
    expect(Schema::hasColumns('hari_liburs', [
        'tanggal',
        'nama',
        'is_active',
        'created_at',
        'updated_at',
    ]))->toBeTrue();

    expect(Schema::hasColumns('ketidakhadiran_magangs', [
        'pendaftaran_id',
        'mahasiswa_id',
        'perusahaan_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis',
        'alasan',
        'bukti_path',
        'status',
        'reviewed_by_mitra_user_id',
        'submitted_at',
        'reviewed_by_mitra_at',
        'cancelled_at',
        'catatan_mitra',
    ]))->toBeTrue();

    expect(Schema::hasColumns('logbook_photos', [
        'logbook_id',
        'file_path',
        'created_at',
    ]))->toBeTrue();

    expect(Schema::hasColumns('surat_penetapans', [
        'pendaftaran_id',
        'requested_by',
        'status',
        'provider',
        'fast_reference_id',
        'nomor_surat',
        'file_url',
        'requested_at',
        'generated_at',
        'error_message',
        'payload_snapshot',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

it('casts hari libur attributes correctly', function () {
    $holiday = HariLibur::create([
        'tanggal' => '2026-08-17',
        'nama' => 'Hari Kemerdekaan',
        'is_active' => 1,
    ])->fresh();

    expect($holiday->tanggal)->toBeInstanceOf(CarbonInterface::class)
        ->and($holiday->is_active)->toBeBool()
        ->and($holiday->is_active)->toBeTrue();
});

it('provides ketidakhadiran relations and optional reviewer correctly', function () {
    $mahasiswa = User::factory()->create();
    $reviewer = User::factory()->create();
    $company = PerusahaanMitra::create(['nama' => 'PT Operasional']);

    $registration = PendaftaranMagang::create([
        'mahasiswa_id' => $mahasiswa->id,
        'perusahaan_id' => $company->id,
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-30',
        'status' => 'aktif',
    ]);

    $absence = KetidakhadiranMagang::create([
        'pendaftaran_id' => $registration->id,
        'mahasiswa_id' => $mahasiswa->id,
        'perusahaan_id' => $company->id,
        'tanggal_mulai' => '2026-06-14',
        'tanggal_selesai' => '2026-06-15',
        'jenis' => 'izin',
        'alasan' => 'Keperluan keluarga',
        'status' => 'pending',
        'reviewed_by_mitra_user_id' => null,
        'submitted_at' => '2026-06-13 20:00:00',
    ]);

    expect($absence->fresh()->pendaftaran?->is($registration))->toBeTrue()
        ->and($absence->fresh()->mahasiswa?->is($mahasiswa))->toBeTrue()
        ->and($absence->fresh()->perusahaan?->is($company))->toBeTrue()
        ->and($absence->fresh()->reviewedByMitra)->toBeNull();

    $absence->update([
        'reviewed_by_mitra_user_id' => $reviewer->id,
        'reviewed_by_mitra_at' => '2026-06-14 09:00:00',
        'status' => 'approved',
    ]);

    expect($absence->fresh()->reviewedByMitra?->is($reviewer))->toBeTrue()
        ->and($absence->fresh()->reviewed_by_mitra_at)->toBeInstanceOf(CarbonInterface::class);
});

it('persists the main ketidakhadiran status values used by wims', function () {
    $mahasiswa = User::factory()->create();
    $company = PerusahaanMitra::create(['nama' => 'PT Status']);

    $registration = PendaftaranMagang::create([
        'mahasiswa_id' => $mahasiswa->id,
        'perusahaan_id' => $company->id,
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-30',
        'status' => 'aktif',
    ]);

    foreach (['pending', 'approved', 'rejected', 'cancelled'] as $index => $status) {
        $record = KetidakhadiranMagang::create([
            'pendaftaran_id' => $registration->id,
            'mahasiswa_id' => $mahasiswa->id,
            'perusahaan_id' => $company->id,
            'tanggal_mulai' => now()->addDays($index)->toDateString(),
            'tanggal_selesai' => now()->addDays($index)->toDateString(),
            'jenis' => $status === 'approved' ? 'sakit' : 'izin',
            'alasan' => 'Status ' . $status,
            'status' => $status,
        ]);

        expect($record->fresh()->status)->toBe($status);
    }
});

it('connects logbook photos to logbook and cascades deletion in testing', function () {
    $mahasiswa = User::factory()->create();
    $company = PerusahaanMitra::create(['nama' => 'PT Logbook']);

    $registration = PendaftaranMagang::create([
        'mahasiswa_id' => $mahasiswa->id,
        'perusahaan_id' => $company->id,
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-30',
        'status' => 'aktif',
    ]);

    $logbook = LogbookMagang::create([
        'pendaftaran_id' => $registration->id,
        'tanggal' => '2026-06-14',
        'aktivitas_harian' => 'Dokumentasi pekerjaan',
        'status' => 'pending',
    ]);

    $photo = LogbookPhoto::create([
        'logbook_id' => $logbook->id,
        'file_path' => 'logbook/foto-1.jpg',
    ]);

    expect($logbook->fresh()->photos)->toHaveCount(1)
        ->and($photo->fresh()->logbook?->is($logbook))->toBeTrue();

    $logbook->delete();

    expect(LogbookPhoto::query()->whereKey($photo->id)->exists())->toBeFalse();
});

it('connects surat penetapan one to one and casts payload snapshot as array', function () {
    $admin = User::factory()->create();
    $mahasiswa = User::factory()->create();
    $company = PerusahaanMitra::create(['nama' => 'PT Surat']);

    $registration = PendaftaranMagang::create([
        'mahasiswa_id' => $mahasiswa->id,
        'perusahaan_id' => $company->id,
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-30',
        'status' => 'aktif',
    ]);

    $letter = SuratPenetapan::create([
        'pendaftaran_id' => $registration->id,
        'requested_by' => $admin->id,
        'status' => 'requested',
        'provider' => 'fast',
        'requested_at' => '2026-06-14 08:00:00',
        'payload_snapshot' => [
            'pendaftaran_id' => $registration->id,
            'perusahaan' => ['nama' => $company->nama],
        ],
    ])->fresh();

    expect($registration->fresh()->suratPenetapan?->is($letter))->toBeTrue()
        ->and($letter->requestedBy?->is($admin))->toBeTrue()
        ->and($letter->payload_snapshot)->toBeArray()
        ->and($letter->requested_at)->toBeInstanceOf(CarbonInterface::class);
});

it('allows optional requested by and absence reviewer foreign keys to be null', function () {
    $mahasiswa = User::factory()->create();
    $company = PerusahaanMitra::create(['nama' => 'PT Nullable']);

    $registration = PendaftaranMagang::create([
        'mahasiswa_id' => $mahasiswa->id,
        'perusahaan_id' => $company->id,
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-30',
        'status' => 'aktif',
    ]);

    $absence = KetidakhadiranMagang::create([
        'pendaftaran_id' => $registration->id,
        'mahasiswa_id' => $mahasiswa->id,
        'perusahaan_id' => $company->id,
        'tanggal_mulai' => '2026-06-14',
        'tanggal_selesai' => '2026-06-14',
        'jenis' => 'izin',
        'alasan' => 'Keperluan mendadak',
        'status' => 'pending',
        'reviewed_by_mitra_user_id' => null,
    ]);

    $letter = SuratPenetapan::create([
        'pendaftaran_id' => $registration->id,
        'requested_by' => null,
        'status' => 'failed',
        'provider' => 'fast',
        'error_message' => 'Menunggu integrasi',
    ]);

    expect($absence->fresh()->reviewedByMitra)->toBeNull()
        ->and($letter->fresh()->requestedBy)->toBeNull();
});

it('preserves existing portal magang records when operational support records are added', function () {
    $mahasiswa = User::factory()->create();
    $company = PerusahaanMitra::create([
        'nama' => 'PT Existing',
        'alamat' => 'Jl. Existing',
    ]);

    $registration = PendaftaranMagang::create([
        'mahasiswa_id' => $mahasiswa->id,
        'perusahaan_id' => $company->id,
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-30',
        'status' => 'aktif',
    ]);

    HariLibur::create([
        'tanggal' => '2026-06-20',
        'nama' => 'Libur Uji',
        'is_active' => true,
    ]);

    SuratPenetapan::create([
        'pendaftaran_id' => $registration->id,
        'status' => 'requested',
        'provider' => 'fast',
    ]);

    expect($company->fresh()->nama)->toBe('PT Existing')
        ->and($registration->fresh()->perusahaan_id)->toBe($company->id)
        ->and($registration->fresh()->suratPenetapan)->not->toBeNull();
});
