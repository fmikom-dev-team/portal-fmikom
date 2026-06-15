<?php

use App\Models\Magang\AssessmentComponent;
use App\Models\Magang\AssessmentScore;
use App\Models\Magang\AssessmentSubmission;
use App\Models\Magang\AssessmentTemplate;
use App\Models\Magang\PendaftaranMagang;
use App\Models\Magang\PerusahaanMitra;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

it('creates the required assessment tables with expected columns', function () {
    expect(Schema::hasColumns('assessment_templates', [
        'name',
        'description',
        'assessor_role',
        'periode_mulai',
        'periode_selesai',
        'is_active',
        'created_by',
        'created_at',
        'updated_at',
    ]))->toBeTrue();

    expect(Schema::hasColumns('assessment_components', [
        'assessment_template_id',
        'name',
        'description',
        'weight_percentage',
        'sort_order',
        'created_at',
        'updated_at',
    ]))->toBeTrue();

    expect(Schema::hasColumns('assessment_submissions', [
        'pendaftaran_magang_id',
        'assessment_template_id',
        'assessor_id',
        'assessor_role',
        'total_score',
        'status',
        'notes',
        'submitted_at',
        'created_at',
        'updated_at',
    ]))->toBeTrue();

    expect(Schema::hasColumns('assessment_scores', [
        'assessment_submission_id',
        'assessment_component_id',
        'score',
        'weighted_score',
        'note',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

it('distinguishes assessment templates for dosen and mitra roles and casts attributes correctly', function () {
    $creator = User::factory()->create();

    $dosenTemplate = AssessmentTemplate::create([
        'name' => 'Template Dosen 2026',
        'assessor_role' => 'dosen',
        'periode_mulai' => '2026-01-01',
        'periode_selesai' => '2026-12-31',
        'is_active' => 1,
        'created_by' => $creator->id,
    ])->fresh();

    $mitraTemplate = AssessmentTemplate::create([
        'name' => 'Template Mitra 2026',
        'assessor_role' => 'mitra',
        'periode_mulai' => '2026-01-01',
        'periode_selesai' => '2026-12-31',
        'is_active' => false,
    ])->fresh();

    expect($dosenTemplate->assessor_role)->toBe('dosen')
        ->and($mitraTemplate->assessor_role)->toBe('mitra')
        ->and($dosenTemplate->periode_mulai)->toBeInstanceOf(CarbonInterface::class)
        ->and($dosenTemplate->periode_selesai)->toBeInstanceOf(CarbonInterface::class)
        ->and($dosenTemplate->is_active)->toBeBool()
        ->and($dosenTemplate->createdBy?->is($creator))->toBeTrue()
        ->and(AssessmentTemplate::forAssessorRole('dosen')->count())->toBe(1)
        ->and(AssessmentTemplate::active()->count())->toBe(1)
        ->and($dosenTemplate->isApplicableForDate(now()->setDate(2026, 6, 14)))->toBeTrue();
});

it('connects assessment components to templates', function () {
    $template = AssessmentTemplate::create([
        'name' => 'Template Komponen',
        'assessor_role' => 'dosen',
        'periode_mulai' => '2026-01-01',
        'periode_selesai' => '2026-12-31',
        'is_active' => true,
    ]);

    $component = AssessmentComponent::create([
        'assessment_template_id' => $template->id,
        'name' => 'Komunikasi',
        'description' => 'Kemampuan komunikasi',
        'weight_percentage' => 25,
        'sort_order' => 2,
    ])->fresh();

    expect($template->fresh()->components)->toHaveCount(1)
        ->and($component->template?->is($template))->toBeTrue()
        ->and($component->weight_percentage)->toBe('25.00');
});

it('connects submissions to pendaftaran template assessor and scores', function () {
    $assessor = User::factory()->create();
    $mahasiswa = User::factory()->create();
    $company = PerusahaanMitra::create(['nama' => 'PT Assessment']);

    $registration = PendaftaranMagang::create([
        'mahasiswa_id' => $mahasiswa->id,
        'perusahaan_id' => $company->id,
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-30',
        'status' => 'selesai',
    ]);

    $template = AssessmentTemplate::create([
        'name' => 'Template Submission',
        'assessor_role' => 'dosen',
        'periode_mulai' => '2026-01-01',
        'periode_selesai' => '2026-12-31',
        'is_active' => true,
    ]);

    $component = AssessmentComponent::create([
        'assessment_template_id' => $template->id,
        'name' => 'Disiplin',
        'weight_percentage' => 100,
        'sort_order' => 1,
    ]);

    $submission = AssessmentSubmission::create([
        'pendaftaran_magang_id' => $registration->id,
        'assessment_template_id' => $template->id,
        'assessor_id' => $assessor->id,
        'assessor_role' => 'dosen',
        'total_score' => 88.5,
        'status' => 'draft',
        'submitted_at' => '2026-06-20 08:00:00',
    ])->fresh();

    $score = AssessmentScore::create([
        'assessment_submission_id' => $submission->id,
        'assessment_component_id' => $component->id,
        'score' => 88.5,
        'weighted_score' => 88.5,
        'note' => 'Baik',
    ])->fresh();

    expect($submission->pendaftaran?->is($registration))->toBeTrue()
        ->and($submission->template?->is($template))->toBeTrue()
        ->and($submission->assessor?->is($assessor))->toBeTrue()
        ->and($submission->scores)->toHaveCount(1)
        ->and($score->submission?->is($submission))->toBeTrue()
        ->and($score->component?->is($component))->toBeTrue()
        ->and($submission->submitted_at)->toBeInstanceOf(CarbonInterface::class)
        ->and($submission->total_score)->toBe('88.50')
        ->and($score->score)->toBe('88.50')
        ->and($score->weighted_score)->toBe('88.50')
        ->and($submission->isSubmitted())->toBeFalse()
        ->and($submission->canBeEdited())->toBeTrue();

    $submission->update([
        'status' => 'submitted',
        'submitted_at' => '2026-06-21 09:30:00',
    ]);

    expect($submission->fresh()->isSubmitted())->toBeTrue()
        ->and($submission->fresh()->canBeEdited())->toBeFalse();
});

it('prevents duplicate submissions for same registration template assessor and role', function () {
    $assessor = User::factory()->create();
    $mahasiswa = User::factory()->create();
    $company = PerusahaanMitra::create(['nama' => 'PT Unique Submission']);

    $registration = PendaftaranMagang::create([
        'mahasiswa_id' => $mahasiswa->id,
        'perusahaan_id' => $company->id,
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-30',
        'status' => 'selesai',
    ]);

    $template = AssessmentTemplate::create([
        'name' => 'Template Unique',
        'assessor_role' => 'mitra',
        'periode_mulai' => '2026-01-01',
        'periode_selesai' => '2026-12-31',
        'is_active' => true,
    ]);

    AssessmentSubmission::create([
        'pendaftaran_magang_id' => $registration->id,
        'assessment_template_id' => $template->id,
        'assessor_id' => $assessor->id,
        'assessor_role' => 'mitra',
        'status' => 'draft',
    ]);

    expect(fn () => AssessmentSubmission::create([
        'pendaftaran_magang_id' => $registration->id,
        'assessment_template_id' => $template->id,
        'assessor_id' => $assessor->id,
        'assessor_role' => 'mitra',
        'status' => 'submitted',
    ]))->toThrow(QueryException::class);
});

it('prevents duplicate scores for the same component in one submission', function () {
    $assessor = User::factory()->create();
    $mahasiswa = User::factory()->create();
    $company = PerusahaanMitra::create(['nama' => 'PT Unique Score']);

    $registration = PendaftaranMagang::create([
        'mahasiswa_id' => $mahasiswa->id,
        'perusahaan_id' => $company->id,
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-30',
        'status' => 'selesai',
    ]);

    $template = AssessmentTemplate::create([
        'name' => 'Template Score',
        'assessor_role' => 'dosen',
        'periode_mulai' => '2026-01-01',
        'periode_selesai' => '2026-12-31',
        'is_active' => true,
    ]);

    $component = AssessmentComponent::create([
        'assessment_template_id' => $template->id,
        'name' => 'Komponen 1',
        'weight_percentage' => 100,
        'sort_order' => 1,
    ]);

    $submission = AssessmentSubmission::create([
        'pendaftaran_magang_id' => $registration->id,
        'assessment_template_id' => $template->id,
        'assessor_id' => $assessor->id,
        'assessor_role' => 'dosen',
        'status' => 'draft',
    ]);

    AssessmentScore::create([
        'assessment_submission_id' => $submission->id,
        'assessment_component_id' => $component->id,
        'score' => 75,
        'weighted_score' => 75,
    ]);

    expect(fn () => AssessmentScore::create([
        'assessment_submission_id' => $submission->id,
        'assessment_component_id' => $component->id,
        'score' => 80,
        'weighted_score' => 80,
    ]))->toThrow(QueryException::class);
});

it('cascades score deletion when submission is removed and restricts referenced parents', function () {
    $assessor = User::factory()->create();
    $mahasiswa = User::factory()->create();
    $company = PerusahaanMitra::create(['nama' => 'PT Restrict']);

    $registration = PendaftaranMagang::create([
        'mahasiswa_id' => $mahasiswa->id,
        'perusahaan_id' => $company->id,
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-30',
        'status' => 'selesai',
    ]);

    $template = AssessmentTemplate::create([
        'name' => 'Template Restrict',
        'assessor_role' => 'mitra',
        'periode_mulai' => '2026-01-01',
        'periode_selesai' => '2026-12-31',
        'is_active' => true,
    ]);

    $component = AssessmentComponent::create([
        'assessment_template_id' => $template->id,
        'name' => 'Komponen Restrict',
        'weight_percentage' => 100,
        'sort_order' => 1,
    ]);

    $submission = AssessmentSubmission::create([
        'pendaftaran_magang_id' => $registration->id,
        'assessment_template_id' => $template->id,
        'assessor_id' => $assessor->id,
        'assessor_role' => 'mitra',
        'status' => 'draft',
    ]);

    $score = AssessmentScore::create([
        'assessment_submission_id' => $submission->id,
        'assessment_component_id' => $component->id,
        'score' => 90,
        'weighted_score' => 90,
    ]);

    expect(fn () => $component->delete())->toThrow(QueryException::class);
    expect(fn () => $registration->delete())->toThrow(QueryException::class);
    expect(fn () => $assessor->delete())->toThrow(QueryException::class);

    $submission->delete();

    expect(AssessmentScore::query()->whereKey($score->id)->exists())->toBeFalse();
});

it('keeps portal data intact and preserves legacy penilaian relation', function () {
    $mahasiswa = User::factory()->create();
    $company = PerusahaanMitra::create(['nama' => 'PT Portal']);

    $registration = PendaftaranMagang::create([
        'mahasiswa_id' => $mahasiswa->id,
        'perusahaan_id' => $company->id,
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-30',
        'status' => 'selesai',
    ]);

    $template = AssessmentTemplate::create([
        'name' => 'Template Portal',
        'assessor_role' => 'dosen',
        'periode_mulai' => '2026-01-01',
        'periode_selesai' => '2026-12-31',
        'is_active' => true,
    ]);

    expect($registration->fresh()->perusahaan_id)->toBe($company->id)
        ->and(method_exists($registration, 'penilaian'))->toBeTrue()
        ->and($registration->assessmentSubmissions())->not->toBeNull()
        ->and($template->name)->toBe('Template Portal');
});
