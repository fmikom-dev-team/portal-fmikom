<?php

use App\Models\Magang\AssessmentComponent;
use App\Models\Magang\AssessmentSubmission;
use App\Models\Magang\AssessmentTemplate;
use App\Models\Magang\PendaftaranMagang;
use App\Models\Magang\PerusahaanMitra;
use App\Models\User;
use App\Modules\Wims\Requests\Admin\UpsertAssessmentTemplateRequest;
use App\Modules\Wims\Services\Admin\AdminAssessmentTemplateActionService;
use App\Modules\Wims\Services\Shared\Assessment\AssessmentSubmissionService;
use App\Modules\Wims\Services\Shared\Assessment\AssessmentTemplateResolverService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

function makeAssessmentTemplatePayload(array $overrides = []): array
{
    return array_replace_recursive([
        'assessor_role' => 'dosen',
        'name' => 'Template Penilaian',
        'description' => 'Template utama',
        'periode_mulai' => '2026-01-01',
        'periode_selesai' => '2026-12-31',
        'is_active' => true,
        'components' => [
            ['name' => 'Disiplin', 'weight_percentage' => 60, 'sort_order' => 1],
            ['name' => 'Komunikasi', 'weight_percentage' => 40, 'sort_order' => 2],
        ],
    ], $overrides);
}

function makeAssessmentRegistration(): array
{
    $assessor = User::factory()->create();
    $student = User::factory()->create();
    $company = PerusahaanMitra::create(['nama' => 'PT Workflow Assessment']);

    $registration = PendaftaranMagang::create([
        'mahasiswa_id' => $student->id,
        'perusahaan_id' => $company->id,
        'dosen_pembimbing_id' => $assessor->id,
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-30',
        'status' => 'selesai',
    ]);

    return [$assessor, $student, $company, $registration];
}

it('requires assessor role and rejects invalid role values in template request', function () {
    $request = new UpsertAssessmentTemplateRequest();

    $invalidRolePayload = makeAssessmentTemplatePayload(['assessor_role' => 'admin']);
    $validator = Validator::make($invalidRolePayload, $request->rules(), $request->messages());
    $request->merge($invalidRolePayload);
    $request->withValidator($validator);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('assessor_role'))->toBeTrue();

    $missingRolePayload = makeAssessmentTemplatePayload();
    unset($missingRolePayload['assessor_role']);
    $validator = Validator::make($missingRolePayload, $request->rules(), $request->messages());
    $request->replace($missingRolePayload);
    $request->withValidator($validator);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('assessor_role'))->toBeTrue();
});

it('rejects invalid component weights and invalid period ordering', function () {
    $request = new UpsertAssessmentTemplateRequest();
    $payload = makeAssessmentTemplatePayload([
        'periode_mulai' => '2026-12-31',
        'periode_selesai' => '2026-01-01',
        'components' => [
            ['name' => 'A', 'weight_percentage' => 120, 'sort_order' => 1],
            ['name' => 'B', 'weight_percentage' => -20, 'sort_order' => 2],
        ],
    ]);

    $validator = Validator::make($payload, $request->rules(), $request->messages());
    $request->merge($payload);
    $request->withValidator($validator);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('periode_selesai'))->toBeTrue()
        ->and($validator->errors()->has('components.0.weight_percentage'))->toBeTrue()
        ->and($validator->errors()->has('components.1.weight_percentage'))->toBeTrue();
});

it('rejects overlapping active templates for the same assessor role but allows different roles', function () {
    $service = app(AdminAssessmentTemplateActionService::class);
    $creator = User::factory()->create();

    $service->create(makeAssessmentTemplatePayload([
        'assessor_role' => 'dosen',
        'periode_mulai' => '2026-01-01',
        'periode_selesai' => '2026-06-30',
    ]), $creator->id);

    expect(fn () => $service->create(makeAssessmentTemplatePayload([
        'assessor_role' => 'dosen',
        'periode_mulai' => '2026-06-01',
        'periode_selesai' => '2026-12-31',
    ]), $creator->id))->toThrow(ValidationException::class);

    $mitraTemplate = $service->create(makeAssessmentTemplatePayload([
        'assessor_role' => 'mitra',
        'periode_mulai' => '2026-06-01',
        'periode_selesai' => '2026-12-31',
    ]), $creator->id);

    expect($mitraTemplate->assessor_role)->toBe('mitra');
});

it('resolver selects templates by role and date', function () {
    $dosenTemplate = AssessmentTemplate::create([
        'name' => 'Template Dosen',
        'assessor_role' => 'dosen',
        'periode_mulai' => '2026-01-01',
        'periode_selesai' => '2026-12-31',
        'is_active' => true,
    ]);

    $mitraTemplate = AssessmentTemplate::create([
        'name' => 'Template Mitra',
        'assessor_role' => 'mitra',
        'periode_mulai' => '2026-01-01',
        'periode_selesai' => '2026-12-31',
        'is_active' => true,
    ]);

    $resolver = app(AssessmentTemplateResolverService::class);

    expect($resolver->resolveForRoleAndDate('dosen', now()->setDate(2026, 6, 14))?->is($dosenTemplate))->toBeTrue()
        ->and($resolver->resolveForRoleAndDate('mitra', now()->setDate(2026, 6, 14))?->is($mitraTemplate))->toBeTrue();
});

it('saves draft submissions partially and computes weighted score on backend', function () {
    [$assessor, , , $registration] = makeAssessmentRegistration();
    $template = AssessmentTemplate::create([
        'name' => 'Template Draft',
        'assessor_role' => 'dosen',
        'periode_mulai' => '2026-01-01',
        'periode_selesai' => '2026-12-31',
        'is_active' => true,
    ]);

    $componentA = AssessmentComponent::create([
        'assessment_template_id' => $template->id,
        'name' => 'Komponen A',
        'weight_percentage' => 60,
        'sort_order' => 1,
    ]);
    AssessmentComponent::create([
        'assessment_template_id' => $template->id,
        'name' => 'Komponen B',
        'weight_percentage' => 40,
        'sort_order' => 2,
    ]);
    $template->load('components');

    $service = app(AssessmentSubmissionService::class);
    $service->saveSubmission($registration, $template, $assessor, 'dosen', null, [
        'action' => 'draft',
        'notes' => 'Masih draft',
        'scores' => [
            ['component_id' => $componentA->id, 'score' => 80, 'note' => 'Baik'],
        ],
    ]);

    $submission = AssessmentSubmission::query()->firstOrFail();

    expect($submission->status)->toBe('draft')
        ->and((float) $submission->total_score)->toBe(48.0)
        ->and($submission->scores)->toHaveCount(1)
        ->and((float) $submission->scores->first()->weighted_score)->toBe(48.0)
        ->and($submission->submitted_at)->toBeNull();
});

it('requires full component coverage for submitted assessments and enforces matching role', function () {
    [$assessor, , , $registration] = makeAssessmentRegistration();
    $template = AssessmentTemplate::create([
        'name' => 'Template Submit',
        'assessor_role' => 'dosen',
        'periode_mulai' => '2026-01-01',
        'periode_selesai' => '2026-12-31',
        'is_active' => true,
    ]);

    $componentA = AssessmentComponent::create([
        'assessment_template_id' => $template->id,
        'name' => 'Komponen A',
        'weight_percentage' => 50,
        'sort_order' => 1,
    ]);
    $componentB = AssessmentComponent::create([
        'assessment_template_id' => $template->id,
        'name' => 'Komponen B',
        'weight_percentage' => 50,
        'sort_order' => 2,
    ]);
    $template->load('components');

    $service = app(AssessmentSubmissionService::class);

    expect(fn () => $service->saveSubmission($registration, $template, $assessor, 'mitra', null, [
        'action' => 'draft',
        'scores' => [
            ['component_id' => $componentA->id, 'score' => 80],
        ],
    ]))->toThrow(ValidationException::class);

    expect(fn () => $service->saveSubmission($registration, $template, $assessor, 'dosen', null, [
        'action' => 'submitted',
        'scores' => [
            ['component_id' => $componentA->id, 'score' => 80],
        ],
    ]))->toThrow(ValidationException::class);

    expect(AssessmentSubmission::count())->toBe(0);

    $service->saveSubmission($registration, $template, $assessor, 'dosen', null, [
        'action' => 'submitted',
        'scores' => [
            ['component_id' => $componentA->id, 'score' => 80],
            ['component_id' => $componentB->id, 'score' => 90],
        ],
    ]);

    $submission = AssessmentSubmission::query()->firstOrFail();

    expect($submission->status)->toBe('submitted')
        ->and((float) $submission->total_score)->toBe(85.0)
        ->and($submission->submitted_at)->not->toBeNull();
});

it('rejects component ids from another template and prevents editing submitted submissions', function () {
    [$assessor, , , $registration] = makeAssessmentRegistration();
    $template = AssessmentTemplate::create([
        'name' => 'Template 1',
        'assessor_role' => 'dosen',
        'periode_mulai' => '2026-01-01',
        'periode_selesai' => '2026-12-31',
        'is_active' => true,
    ]);
    $otherTemplate = AssessmentTemplate::create([
        'name' => 'Template 2',
        'assessor_role' => 'dosen',
        'periode_mulai' => '2027-01-01',
        'periode_selesai' => '2027-12-31',
        'is_active' => false,
    ]);

    $componentA = AssessmentComponent::create([
        'assessment_template_id' => $template->id,
        'name' => 'Komponen A',
        'weight_percentage' => 100,
        'sort_order' => 1,
    ]);
    $foreignComponent = AssessmentComponent::create([
        'assessment_template_id' => $otherTemplate->id,
        'name' => 'Komponen Lain',
        'weight_percentage' => 100,
        'sort_order' => 1,
    ]);
    $template->load('components');

    $service = app(AssessmentSubmissionService::class);

    expect(fn () => $service->saveSubmission($registration, $template, $assessor, 'dosen', null, [
        'action' => 'draft',
        'scores' => [
            ['component_id' => $componentA->id, 'score' => 90],
            ['component_id' => $foreignComponent->id, 'score' => 80],
        ],
    ]))->toThrow(ValidationException::class);

    $service->saveSubmission($registration, $template, $assessor, 'dosen', null, [
        'action' => 'submitted',
        'scores' => [
            ['component_id' => $componentA->id, 'score' => 90],
        ],
    ]);

    $submission = AssessmentSubmission::query()->firstOrFail();

    expect(fn () => $service->saveSubmission($registration, $template, $assessor, 'dosen', $submission, [
        'action' => 'draft',
        'scores' => [
            ['component_id' => $componentA->id, 'score' => 70],
        ],
    ]))->toThrow(ValidationException::class);
});

