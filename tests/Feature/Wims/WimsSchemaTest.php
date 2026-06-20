<?php

use App\Models\Magang\AbsensiMagang;
use App\Models\Magang\LogbookMagang;
use App\Models\Magang\PendaftaranMagang;
use App\Models\Magang\PerusahaanMitra;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Schema;

it('adds the required wims columns to existing magang tables', function () {
    expect(Schema::hasColumns('perusahaan_mitras', [
        'user_id',
        'mitra_jabatan',
        'jam_masuk',
        'jam_pulang',
        'toleransi_terlambat_menit',
        'hari_kerja',
        'is_active',
    ]))->toBeTrue();

    expect(Schema::hasColumns('pendaftaran_magangs', [
        'perusahaan_diminati_nama',
        'perusahaan_diminati_alamat',
        'catatan_pengajuan',
        'catatan_revisi_admin',
        'laporan_akhir_path',
        'laporan_akhir_original_name',
        'laporan_akhir_uploaded_at',
    ]))->toBeTrue();

    expect(Schema::hasColumns('absensi_magangs', [
        'timestamp_masuk',
        'timestamp_keluar',
        'distance_masuk',
        'distance_keluar',
        'foto_bukti_checkout_path',
        'ip_address',
        'user_agent',
    ]))->toBeTrue();

    expect(Schema::hasColumns('logbook_magangs', [
        'jam_mulai',
        'jam_selesai',
        'status',
        'catatan_mitra',
        'reviewed_by_mitra_user_id',
        'reviewed_by_mitra_at',
    ]))->toBeTrue();
});

it('supports optional foreign keys for company user and logbook reviewer', function () {
    $mitraUser = User::factory()->create();
    $mahasiswa = User::factory()->create();
    $reviewer = User::factory()->create();

    $companyWithoutUser = PerusahaanMitra::create([
        'nama' => 'PT Tanpa Akun',
    ]);

    $companyWithUser = PerusahaanMitra::create([
        'nama' => 'PT Mitra',
        'user_id' => $mitraUser->id,
        'mitra_jabatan' => 'HR Manager',
    ]);

    $registration = PendaftaranMagang::create([
        'mahasiswa_id' => $mahasiswa->id,
        'perusahaan_id' => $companyWithUser->id,
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-30',
        'status' => 'pending',
    ]);

    $logbook = LogbookMagang::create([
        'pendaftaran_id' => $registration->id,
        'tanggal' => '2026-06-14',
        'aktivitas_harian' => 'Review requirement',
        'reviewed_by_mitra_user_id' => $reviewer->id,
        'reviewed_by_mitra_at' => '2026-06-14 09:00:00',
    ]);

    expect($companyWithoutUser->fresh()->user)->toBeNull();
    expect($companyWithUser->fresh()->user?->is($mitraUser))->toBeTrue();
    expect($logbook->fresh()->reviewedByMitra?->is($reviewer))->toBeTrue();
});

it('allows pendaftaran magang without an existing company record', function () {
    $mahasiswa = User::factory()->create();

    $registration = PendaftaranMagang::create([
        'mahasiswa_id' => $mahasiswa->id,
        'perusahaan_id' => null,
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-30',
        'status' => 'pending',
        'perusahaan_diminati_nama' => 'PT Usulan Mandiri',
        'perusahaan_diminati_alamat' => 'Jl. Contoh No. 1',
    ]);

    expect($registration->fresh()->perusahaan_id)->toBeNull();
});

it('casts wims model attributes to the expected types', function () {
    $mitraUser = User::factory()->create();
    $mahasiswa = User::factory()->create();
    $reviewer = User::factory()->create();

    $company = PerusahaanMitra::create([
        'nama' => 'PT Cast',
        'user_id' => $mitraUser->id,
        'toleransi_terlambat_menit' => 15,
        'hari_kerja' => ['monday', 'friday'],
        'is_active' => 1,
    ]);

    $registration = PendaftaranMagang::create([
        'mahasiswa_id' => $mahasiswa->id,
        'perusahaan_id' => $company->id,
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-30',
        'status' => 'aktif',
        'laporan_akhir_uploaded_at' => '2026-06-14 10:15:00',
    ]);

    $attendance = AbsensiMagang::create([
        'pendaftaran_id' => $registration->id,
        'tanggal' => '2026-06-14',
        'timestamp_masuk' => '2026-06-14 08:00:00',
        'timestamp_keluar' => '2026-06-14 17:00:00',
        'distance_masuk' => 12.5,
        'distance_keluar' => 14.75,
        'lokasi_valid' => true,
    ]);

    $logbook = LogbookMagang::create([
        'pendaftaran_id' => $registration->id,
        'tanggal' => '2026-06-14',
        'aktivitas_harian' => 'Audit data',
        'reviewed_by_mitra_user_id' => $reviewer->id,
        'reviewed_by_mitra_at' => '2026-06-14 18:00:00',
    ]);

    expect($company->fresh()->hari_kerja)->toBeArray()
        ->and($company->fresh()->is_active)->toBeBool()
        ->and($company->fresh()->toleransi_terlambat_menit)->toBeInt();

    expect($registration->fresh()->laporan_akhir_uploaded_at)->toBeInstanceOf(CarbonInterface::class);

    expect($attendance->fresh()->timestamp_masuk)->toBeInstanceOf(CarbonInterface::class)
        ->and($attendance->fresh()->timestamp_keluar)->toBeInstanceOf(CarbonInterface::class)
        ->and($attendance->fresh()->distance_masuk)->toBeFloat()
        ->and($attendance->fresh()->distance_keluar)->toBeFloat()
        ->and($attendance->fresh()->lokasi_valid)->toBeBool();

    expect($logbook->fresh()->reviewed_by_mitra_at)->toBeInstanceOf(CarbonInterface::class);
});

it('resolves check in and check out timestamps for new and legacy attendance records', function () {
    $mahasiswa = User::factory()->create();
    $company = PerusahaanMitra::create(['nama' => 'PT Absensi']);

    $registration = PendaftaranMagang::create([
        'mahasiswa_id' => $mahasiswa->id,
        'perusahaan_id' => $company->id,
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-30',
        'status' => 'aktif',
    ]);

    $newAttendance = AbsensiMagang::create([
        'pendaftaran_id' => $registration->id,
        'tanggal' => '2026-06-14',
        'waktu_masuk' => '08:00:00',
        'waktu_keluar' => '17:00:00',
        'timestamp_masuk' => '2026-06-14 08:05:00',
        'timestamp_keluar' => '2026-06-14 17:10:00',
    ]);

    $legacyAttendance = AbsensiMagang::create([
        'pendaftaran_id' => $registration->id,
        'tanggal' => '2026-06-13',
        'waktu_masuk' => '08:10:00',
        'waktu_keluar' => '16:50:00',
    ]);

    expect($newAttendance->resolvedCheckInAt()?->format('Y-m-d H:i:s'))->toBe('2026-06-14 08:05:00')
        ->and($newAttendance->resolvedCheckOutAt()?->format('Y-m-d H:i:s'))->toBe('2026-06-14 17:10:00')
        ->and($legacyAttendance->resolvedCheckInAt()?->format('Y-m-d H:i:s'))->toBe('2026-06-13 08:10:00')
        ->and($legacyAttendance->resolvedCheckOutAt()?->format('Y-m-d H:i:s'))->toBe('2026-06-13 16:50:00');
});

it('preserves existing portal magang records after the schema extension', function () {
    $mahasiswa = User::factory()->create();
    $company = PerusahaanMitra::create([
        'nama' => 'PT Existing Portal',
        'alamat' => 'Jl. Portal',
    ]);

    $registration = PendaftaranMagang::create([
        'mahasiswa_id' => $mahasiswa->id,
        'perusahaan_id' => $company->id,
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-30',
        'status' => 'pending',
    ]);

    $attendance = AbsensiMagang::create([
        'pendaftaran_id' => $registration->id,
        'tanggal' => '2026-06-14',
        'waktu_masuk' => '08:00:00',
        'status' => 'hadir',
    ]);

    $logbook = LogbookMagang::create([
        'pendaftaran_id' => $registration->id,
        'tanggal' => '2026-06-14',
        'aktivitas_harian' => 'Portal baseline activity',
    ]);

    expect($company->fresh()->nama)->toBe('PT Existing Portal')
        ->and($registration->fresh()->status)->toBe('pending')
        ->and($attendance->fresh()->status)->toBe('hadir')
        ->and($logbook->fresh()->aktivitas_harian)->toBe('Portal baseline activity');
});
