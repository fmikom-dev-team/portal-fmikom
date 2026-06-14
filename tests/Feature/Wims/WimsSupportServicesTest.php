<?php

use App\Models\Magang\AbsensiMagang;
use App\Models\Magang\AssessmentComponent;
use App\Models\Magang\AssessmentSubmission;
use App\Models\Magang\AssessmentTemplate;
use App\Models\Magang\HariLibur;
use App\Models\Magang\KetidakhadiranMagang;
use App\Models\Magang\LogbookMagang;
use App\Models\Magang\PendaftaranMagang;
use App\Models\Magang\PerusahaanMitra;
use App\Models\User;
use App\Modules\Wims\Services\Mahasiswa\Logbook\LogbookActionService;
use App\Modules\Wims\Services\Mahasiswa\Report\StudentFinalReportActionService;
use App\Modules\Wims\Services\Mahasiswa\Report\StudentFinalReportFileService;
use App\Modules\Wims\Services\Shared\Absence\KetidakhadiranService;
use App\Modules\Wims\Services\Shared\Assessment\FinalReportAccessService;
use App\Modules\Wims\Services\Shared\Attendance\AttendanceService;
use App\Modules\Wims\Services\Shared\Attendance\AttendanceSyncService;
use App\Modules\Wims\Services\Shared\Monitoring\MonitoringAlertService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

function makeOperationalCompany(array $overrides = []): PerusahaanMitra
{
    return PerusahaanMitra::create(array_merge([
        'nama' => 'PT Operasional',
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius_valid_meter' => 200,
        'jam_masuk' => '08:00:00',
        'toleransi_terlambat_menit' => 15,
        'hari_kerja' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
    ], $overrides));
}

function makeActiveOperationalRegistration(?User $student = null, ?User $lecturer = null, ?PerusahaanMitra $company = null): array
{
    $student ??= User::factory()->create();
    $lecturer ??= User::factory()->create();
    $company ??= makeOperationalCompany();

    $registration = PendaftaranMagang::create([
        'mahasiswa_id' => $student->id,
        'perusahaan_id' => $company->id,
        'dosen_pembimbing_id' => $lecturer->id,
        'tanggal_mulai' => '2026-06-15',
        'tanggal_selesai' => '2026-06-19',
        'status' => 'aktif',
    ]);

    return [$student, $lecturer, $company, $registration];
}

it('resolves the new shared WIMS services from the container', function () {
    expect(app(AttendanceService::class))->toBeInstanceOf(AttendanceService::class)
        ->and(app(AttendanceSyncService::class))->toBeInstanceOf(AttendanceSyncService::class)
        ->and(app(KetidakhadiranService::class))->toBeInstanceOf(KetidakhadiranService::class)
        ->and(app(MonitoringAlertService::class))->toBeInstanceOf(MonitoringAlertService::class);
});

it('keeps attendance sync idempotent and skips holidays plus non working days', function () {
    [, , $company, $registration] = makeActiveOperationalRegistration();
    HariLibur::create([
        'tanggal' => '2026-06-16',
        'nama' => 'Libur Uji',
        'is_active' => true,
    ]);

    $company->update(['hari_kerja' => ['monday']]);

    $syncService = app(AttendanceSyncService::class);
    $syncService->syncForRegistration($registration->fresh('perusahaan'), now()->setDate(2026, 6, 18));
    $firstCount = AbsensiMagang::where('pendaftaran_id', $registration->id)->count();
    $syncService->syncForRegistration($registration->fresh('perusahaan'), now()->setDate(2026, 6, 18));

    $attendanceRows = AbsensiMagang::where('pendaftaran_id', $registration->id)->get();

    expect($attendanceRows->count())->toBe($firstCount)
        ->and($attendanceRows->contains(fn (AbsensiMagang $attendance) => $attendance->tanggal?->toDateString() === '2026-06-16'))->toBeFalse();
});

it('syncs approved absence into izin or sakit and ignores pending absence', function () {
    [$student, , $company, $registration] = makeActiveOperationalRegistration();

    KetidakhadiranMagang::create([
        'pendaftaran_id' => $registration->id,
        'mahasiswa_id' => $student->id,
        'perusahaan_id' => $company->id,
        'tanggal_mulai' => '2026-06-16',
        'tanggal_selesai' => '2026-06-16',
        'jenis' => 'izin',
        'alasan' => 'Acara keluarga',
        'status' => 'approved',
    ]);

    KetidakhadiranMagang::create([
        'pendaftaran_id' => $registration->id,
        'mahasiswa_id' => $student->id,
        'perusahaan_id' => $company->id,
        'tanggal_mulai' => '2026-06-17',
        'tanggal_selesai' => '2026-06-17',
        'jenis' => 'sakit',
        'alasan' => 'Masih menunggu',
        'status' => 'pending',
    ]);

    app(AttendanceSyncService::class)->syncForRegistration($registration->fresh('perusahaan'), now()->setDate(2026, 6, 18));

    expect(AbsensiMagang::where('pendaftaran_id', $registration->id)->whereDate('tanggal', '2026-06-16')->value('status'))->toBe('izin')
        ->and(AbsensiMagang::where('pendaftaran_id', $registration->id)->whereDate('tanggal', '2026-06-17')->value('status'))->toBe('alfa');
});

it('does not overwrite manual attendance rows during sync', function () {
    [, , , $registration] = makeActiveOperationalRegistration();

    AbsensiMagang::create([
        'pendaftaran_id' => $registration->id,
        'tanggal' => '2026-06-15',
        'waktu_masuk' => '08:00:00',
        'timestamp_masuk' => '2026-06-15 08:00:00',
        'status' => 'hadir',
    ]);

    app(AttendanceSyncService::class)->syncForRegistration($registration->fresh('perusahaan'), now()->setDate(2026, 6, 17));

    expect(AbsensiMagang::where('pendaftaran_id', $registration->id)->whereDate('tanggal', '2026-06-15')->first()?->status)->toBe('hadir');
});

it('builds monitoring alerts from derived status without mutating presentation status into database', function () {
    [$student, $lecturer, , $registration] = makeActiveOperationalRegistration();

    LogbookMagang::create([
        'pendaftaran_id' => $registration->id,
        'tanggal' => '2026-06-15',
        'aktivitas_harian' => 'Aktivitas awal',
        'status' => 'revisi',
    ]);

    $warnings = app(MonitoringAlertService::class)->getWarningsForLecturer($lecturer, 1, now()->setDate(2026, 6, 17));

    expect($warnings)->toHaveCount(1)
        ->and($warnings->first()['student_id'])->toBe($student->id)
        ->and($warnings->first()['missing_types'])->toContain('logbook_perlu_revisi')
        ->and($registration->fresh()->status)->toBe('aktif');
});

it('stores and replaces final report files safely, and rejects missing downloads', function () {
    Storage::fake('public');
    File::cleanDirectory(storage_path('framework/testing/disks/public'));

    [$student, , , $registration] = makeActiveOperationalRegistration();
    $registration->update(['status' => 'selesai']);

    $actionService = app(StudentFinalReportActionService::class);
    $fileService = app(StudentFinalReportFileService::class);

    $firstFile = UploadedFile::fake()->createWithContent('laporan-awal.pdf', 'first-final-report-content');
    $actionService->upload($registration, $firstFile);
    $firstUpload = $registration->fresh();
    $firstPath = $firstUpload->laporan_akhir_path;

    expect($firstPath)->not->toBeNull()
        ->and($firstUpload->laporan_akhir_original_name)->toBe('laporan-awal.pdf')
        ->and($firstUpload->laporan_akhir_uploaded_at)->not->toBeNull()
        ->and(Storage::disk('public')->get($firstPath))->toBe('first-final-report-content');

    unset($firstFile);
    gc_collect_cycles();
    Storage::forgetDisk('public');
    Storage::persistentFake('public');

    $secondFile = UploadedFile::fake()->createWithContent('laporan-revisi.pdf', 'second-final-report-content');
    $actionService->upload($registration->fresh(), $secondFile);
    $updatedRegistration = $registration->fresh();
    expect($updatedRegistration->laporan_akhir_path)->not->toBe($firstPath)
        ->and(Storage::disk('public')->exists($updatedRegistration->laporan_akhir_path))->toBeTrue()
        ->and(Storage::disk('public')->get($updatedRegistration->laporan_akhir_path))->toBe('second-final-report-content')
        ->and(Storage::disk('public')->exists($firstPath))->toBeFalse();

    $updatedRegistration->update(['laporan_akhir_path' => 'laporan-akhir/hilang.pdf']);

    expect(fn () => $fileService->download($updatedRegistration->fresh()))->toThrow(NotFoundHttpException::class);
});

it('stores attendance photos, absence proof, and logbook photos on the configured disk', function () {
    Storage::fake('public');
    File::cleanDirectory(storage_path('framework/testing/disks/public'));

    [$student, , , $registration] = makeActiveOperationalRegistration();

    $attendanceAction = app(\App\Modules\Wims\Services\Mahasiswa\Attendance\AttendanceActionService::class);
    $absenceAction = app(\App\Modules\Wims\Services\Mahasiswa\Absence\StudentAbsenceActionService::class);
    $logbookAction = app(LogbookActionService::class);

    $locationResult = ['distance' => 50.0, 'is_valid' => true];
    $attendance = $attendanceAction->createCheckIn(
        $registration->fresh('perusahaan'),
        -6.2,
        106.8,
        UploadedFile::fake()->image('checkin.jpg'),
        '127.0.0.1',
        'PHPUnit',
        $locationResult,
        now()->setDate(2026, 6, 15)->setTime(8, 0),
    );
    $attendanceAction->completeCheckOut(
        $attendance->fresh(),
        -6.2,
        106.8,
        UploadedFile::fake()->image('checkout.jpg'),
        $locationResult,
        now()->setDate(2026, 6, 15)->setTime(17, 0),
    );

    $absenceAction->submit($registration->fresh('perusahaan'), $student->id, [
        'tanggal_mulai' => '2026-06-16',
        'tanggal_selesai' => '2026-06-16',
        'jenis' => 'izin',
        'alasan' => 'Keperluan keluarga',
    ], UploadedFile::fake()->create('bukti.pdf', 80, 'application/pdf'));

    $logbookAction->create($registration, [
        'jam_mulai' => '08:00',
        'jam_selesai' => '12:00',
        'aktivitas_harian' => 'Menyusun dokumentasi',
        'kompetensi_dicapai' => 'Dokumentasi',
    ], [UploadedFile::fake()->image('logbook.jpg')]);

    expect(Storage::disk('public')->allFiles())->not->toBeEmpty();
});

it('renders attendance and logbook PDFs from blade views', function () {
    $attendancePdf = Pdf::loadView('pdf.attendance-history', [
        'student' => [
            'name' => 'Mahasiswa Uji',
            'nim' => '123456',
            'program_studi' => 'Informatika',
        ],
        'internship' => [
            'company' => 'PT PDF',
            'period' => '15 Jun 2026 - 19 Jun 2026',
            'supervisor_lecturer' => 'Dosen Uji',
            'mentor' => 'Mitra Uji',
        ],
        'rows' => [
            [
                'number' => 1,
                'date' => '15 Juni 2026',
                'check_in' => '08:00:00',
                'check_out' => '17:00:00',
                'status' => 'Hadir',
                'remark' => 'Lokasi tervalidasi',
            ],
        ],
    ])->output();

    $logbookPdf = Pdf::loadView('pdf.logbook-history', [
        'student' => [
            'name' => 'Mahasiswa Uji',
            'nim' => '123456',
            'program_studi' => 'Informatika',
        ],
        'internship' => [
            'company' => 'PT PDF',
            'period' => '15 Jun 2026 - 19 Jun 2026',
            'supervisor_lecturer' => 'Dosen Uji',
            'mentor' => 'Mitra Uji',
        ],
        'rows' => [
            [
                'number' => 1,
                'date' => '15 Juni 2026',
                'start_time' => '08:00',
                'end_time' => '17:00',
                'activity' => 'Implementasi backend',
                'competency' => 'Laravel',
                'status' => 'Disetujui',
                'mentor_note' => 'Baik',
            ],
        ],
    ])->output();

    expect(strlen($attendancePdf))->toBeGreaterThan(0)
        ->and(strlen($logbookPdf))->toBeGreaterThan(0);
});

it('exports assessment pdf from the new submission model flow', function () {
    Storage::fake('public');

    [$assessor, $student, $company, $registration] = makeActiveOperationalRegistration();
    $registration->update(['status' => 'selesai']);

    AbsensiMagang::create([
        'pendaftaran_id' => $registration->id,
        'tanggal' => '2026-06-15',
        'waktu_masuk' => '08:00:00',
        'waktu_keluar' => '17:00:00',
        'timestamp_masuk' => '2026-06-15 08:00:00',
        'timestamp_keluar' => '2026-06-15 17:00:00',
        'status' => 'hadir',
    ]);

    LogbookMagang::create([
        'pendaftaran_id' => $registration->id,
        'tanggal' => '2026-06-15',
        'jam_mulai' => '08:00:00',
        'jam_selesai' => '17:00:00',
        'aktivitas_harian' => 'Implementasi backend',
        'kompetensi_dicapai' => 'Laravel',
        'status' => 'approved',
    ]);

    $template = AssessmentTemplate::create([
        'name' => 'Template PDF',
        'assessor_role' => 'dosen',
        'periode_mulai' => '2026-01-01',
        'periode_selesai' => '2026-12-31',
        'is_active' => true,
    ]);
    $component = AssessmentComponent::create([
        'assessment_template_id' => $template->id,
        'name' => 'Komunikasi',
        'weight_percentage' => 100,
        'sort_order' => 1,
    ]);
    $submission = AssessmentSubmission::create([
        'pendaftaran_magang_id' => $registration->id,
        'assessment_template_id' => $template->id,
        'assessor_id' => $assessor->id,
        'assessor_role' => 'dosen',
        'total_score' => 88,
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);
    $submission->scores()->create([
        'assessment_component_id' => $component->id,
        'score' => 88,
        'weighted_score' => 88,
    ]);

    $assessmentResponse = app(\App\Modules\Wims\Services\Admin\AdminAssessmentRecapExportService::class)
        ->download($registration->fresh(), 'dosen');

    expect($assessmentResponse->headers->get('content-type'))->toContain('application/pdf')
        ->and($company->nama)->toBe('PT Operasional');
});
