<?php

use App\Models\Magang\AssessmentSubmission;
use App\Models\Magang\AssessmentTemplate;
use App\Models\Magang\PendaftaranMagang;
use App\Models\Magang\PerusahaanMitra;
use App\Models\User;
use App\Modules\Wims\Services\Mahasiswa\Dashboard\StudentDashboardPageService;
use App\Modules\Wims\Services\Mahasiswa\Registration\StudentRegistrationPageService;
use App\Modules\Wims\Services\Shared\Placement\PlacementActionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function makeRegistrationRuleCompany(array $overrides = []): PerusahaanMitra
{
    return PerusahaanMitra::query()->create(array_merge([
        'nama' => 'PT Aturan WIMS',
        'is_active' => true,
    ], $overrides));
}

function makeRegistrationRuleRegistration(
    User $student,
    ?User $lecturer = null,
    ?PerusahaanMitra $company = null,
    array $overrides = [],
): PendaftaranMagang {
    $lecturer ??= User::factory()->create();
    $company ??= makeRegistrationRuleCompany();

    return PendaftaranMagang::query()->create(array_merge([
        'mahasiswa_id' => $student->id,
        'perusahaan_id' => $company->id,
        'dosen_pembimbing_id' => $lecturer->id,
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-30',
        'status' => 'pending',
    ], $overrides));
}

function submitRegistrationRuleAssessment(PendaftaranMagang $registration, User $assessor, string $role): void
{
    $template = AssessmentTemplate::query()->create([
        'name' => "Template {$role}",
        'assessor_role' => $role,
        'periode_mulai' => '2026-01-01',
        'periode_selesai' => '2026-12-31',
        'is_active' => true,
    ]);

    AssessmentSubmission::query()->create([
        'pendaftaran_magang_id' => $registration->id,
        'assessment_template_id' => $template->id,
        'assessor_id' => $assessor->id,
        'assessor_role' => $role,
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);
}

it('requires final assessments before allowing student re-registration after completed internship history', function () {
    $student = User::factory()->create();
    $lecturer = User::factory()->create();
    $mitra = User::factory()->create();
    $company = makeRegistrationRuleCompany(['user_id' => $mitra->id]);

    makeRegistrationRuleRegistration($student, $lecturer, $company, [
        'status' => 'selesai',
        'tanggal_mulai' => '2026-01-01',
        'tanggal_selesai' => '2026-01-31',
    ]);

    $latestRegistration = makeRegistrationRuleRegistration($student, $lecturer, $company, [
        'status' => 'selesai',
        'tanggal_mulai' => '2026-05-01',
        'tanggal_selesai' => '2026-05-31',
    ]);

    $service = app(StudentRegistrationPageService::class);
    $payload = $service->build($student);

    expect($service->hasCompletedInternshipHistory($student->id))->toBeTrue()
        ->and($latestRegistration?->status)->toBe('selesai')
        ->and($payload['selected_period_id'])->toBe($latestRegistration?->id)
        ->and($payload['pageState']['can_submit'])->toBeFalse()
        ->and($payload['pageState']['next_registration_assessment']['blocking_reasons'])->toHaveCount(2);

    submitRegistrationRuleAssessment($latestRegistration, $lecturer, 'dosen');

    expect($service->canSubmitRegistration($latestRegistration->fresh(), true))->toBeFalse();

    submitRegistrationRuleAssessment($latestRegistration, $mitra, 'mitra');

    expect($service->canSubmitRegistration($latestRegistration->fresh(), true))->toBeTrue()
        ->and($service->build($student)['pageState']['can_submit'])->toBeTrue();
});

it('persists the next registration and locks its form after the submission succeeds', function () {
    Storage::fake('local');

    $student = User::factory()->create();
    $lecturer = User::factory()->create();
    $mitra = User::factory()->create();
    $company = makeRegistrationRuleCompany(['user_id' => $mitra->id]);
    $completedRegistration = makeRegistrationRuleRegistration($student, $lecturer, $company, [
        'status' => 'selesai',
        'tanggal_mulai' => '2026-05-01',
        'tanggal_selesai' => '2026-05-31',
    ]);
    submitRegistrationRuleAssessment($completedRegistration, $lecturer, 'dosen');
    submitRegistrationRuleAssessment($completedRegistration, $mitra, 'mitra');

    $actionService = app(\App\Modules\Wims\Services\Mahasiswa\Registration\StudentRegistrationActionService::class);
    $registration = $actionService->create(
        $student,
        $actionService->buildPayload([
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-31',
            'status_kip' => 'bukan_kip',
            'sks_ditempuh' => 120,
            'bidang_minat' => 'Software Development',
            'ukuran_seragam' => 'M',
        ]),
        UploadedFile::fake()->create('proposal.pdf', 100, 'application/pdf'),
        UploadedFile::fake()->create('transkrip.pdf', 100, 'application/pdf'),
    );
    $page = app(StudentRegistrationPageService::class)->build($student);

    expect($registration->status)->toBe('pending')
        ->and(PendaftaranMagang::query()->whereKey($registration->id)->exists())->toBeTrue()
        ->and($page['selected_period_id'])->toBe($registration->id)
        ->and($page['registration']['id'])->toBe($registration->id)
        ->and($page['pageState']['can_submit'])->toBeFalse()
        ->and($page['pageState']['is_locked'])->toBeTrue()
        ->and($page['pageState']['is_new_submission'])->toBeFalse();
});

it('does not accept a final assessment from an assessor not assigned to the registration', function () {
    $student = User::factory()->create();
    $lecturer = User::factory()->create();
    $mitra = User::factory()->create();
    $company = makeRegistrationRuleCompany(['user_id' => $mitra->id]);
    $registration = makeRegistrationRuleRegistration($student, $lecturer, $company, [
        'status' => 'selesai',
    ]);

    submitRegistrationRuleAssessment($registration, User::factory()->create(), 'dosen');
    submitRegistrationRuleAssessment($registration, User::factory()->create(), 'mitra');

    $eligibility = app(StudentRegistrationPageService::class)->canSubmitRegistration($registration->fresh());

    expect($eligibility)->toBeFalse();
});

it('hides daily activity shortcuts after the internship period and shows next registration only after final assessments', function () {
    $student = User::factory()->create();
    $lecturer = User::factory()->create();
    $mitra = User::factory()->create();
    $company = makeRegistrationRuleCompany(['user_id' => $mitra->id]);
    $registration = makeRegistrationRuleRegistration($student, $lecturer, $company, [
        'status' => 'selesai',
        'tanggal_mulai' => '2026-05-01',
        'tanggal_selesai' => '2026-05-31',
    ]);

    $dashboard = app(StudentDashboardPageService::class);

    expect($dashboard->build($student)['registration'])
        ->toMatchArray([
            'dashboard_state' => 'completed',
            'can_register_next' => false,
        ]);

    submitRegistrationRuleAssessment($registration, $lecturer, 'dosen');
    submitRegistrationRuleAssessment($registration, $mitra, 'mitra');

    expect($dashboard->build($student)['registration'])
        ->toMatchArray([
            'dashboard_state' => 'completed',
            'can_register_next' => true,
        ]);
});

it('treats an active registration whose end date has passed as completed on the dashboard', function () {
    $student = User::factory()->create();
    $registration = makeRegistrationRuleRegistration($student, overrides: [
        'status' => 'aktif',
        'tanggal_mulai' => '2026-05-01',
        'tanggal_selesai' => '2026-05-31',
    ]);

    expect(app(StudentDashboardPageService::class)->build($student)['registration'])
        ->toMatchArray([
            'id' => $registration->id,
            'dashboard_state' => 'completed',
            'can_register_next' => false,
        ]);
});

it('marks placement complete by updating registration history without touching legacy user completion columns', function () {
    $student = User::factory()->create();
    $registration = makeRegistrationRuleRegistration($student, overrides: [
        'status' => 'aktif',
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-10',
    ]);

    app(PlacementActionService::class)->complete($registration->fresh('mahasiswa'));

    expect($registration->fresh()->status)->toBe('selesai')
        ->and(app(StudentRegistrationPageService::class)->hasCompletedInternshipHistory($student->id))->toBeTrue();
});

it('resolves a selected registration only for its owning student and normalizes dependent preferences', function () {
    $student = User::factory()->create();
    $otherStudent = User::factory()->create();
    $registration = makeRegistrationRuleRegistration($student, overrides: [
        'status' => 'revisi',
    ]);
    $otherRegistration = makeRegistrationRuleRegistration($otherStudent, overrides: [
        'status' => 'revisi',
    ]);
    $service = app(StudentRegistrationPageService::class);

    $payload = app(\App\Modules\Wims\Services\Mahasiswa\Registration\StudentRegistrationActionService::class)
        ->buildPayload([
            'bidang_minat' => 'Software Development',
            'bidang_minat_lainnya' => 'Nilai lama yang harus dibuang',
            'ukuran_seragam' => 'M',
            'ukuran_seragam_custom' => 'Nilai custom lama yang harus dibuang',
        ]);

    expect($service->registrationForStudent($student->id, $registration->id)?->id)->toBe($registration->id)
        ->and($service->registrationForStudent($student->id, $otherRegistration->id))->toBeNull()
        ->and($payload['bidang_minat_lainnya'])->toBeNull()
        ->and($payload['ukuran_seragam_custom'])->toBeNull();
});
