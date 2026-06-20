<?php

use App\Models\Magang\PendaftaranMagang;
use App\Models\Magang\PerusahaanMitra;
use App\Models\User;
use App\Modules\Wims\Services\Mahasiswa\Registration\StudentRegistrationPageService;
use App\Modules\Wims\Services\Shared\Placement\PlacementActionService;

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

it('blocks student re-registration based on completed internship history instead of user legacy flags', function () {
    $student = User::factory()->create();

    makeRegistrationRuleRegistration($student, overrides: [
        'status' => 'selesai',
        'tanggal_mulai' => '2026-01-01',
        'tanggal_selesai' => '2026-01-31',
    ]);

    makeRegistrationRuleRegistration($student, overrides: [
        'status' => 'rejected',
        'tanggal_mulai' => '2026-05-01',
        'tanggal_selesai' => '2026-05-31',
    ]);

    $service = app(StudentRegistrationPageService::class);
    $payload = $service->build($student);
    $latestRegistration = $service->latestRegistration($student->id);

    expect($service->hasCompletedInternshipHistory($student->id))->toBeTrue()
        ->and($payload['pageState']['completed_once'])->toBeTrue()
        ->and($payload['pageState']['can_submit'])->toBeFalse()
        ->and($latestRegistration?->status)->toBe('rejected')
        ->and($service->canSubmitRegistration($latestRegistration, true))->toBeFalse();
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
