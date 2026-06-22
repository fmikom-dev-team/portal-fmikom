<?php

use App\Models\Magang\AssessmentSubmission;
use App\Modules\Wims\Support\AssessmentSummary;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class);

it('summarizes dosen and mitra submission states without database access', function () {
    $dosenDraft = new AssessmentSubmission([
        'assessor_role' => 'dosen',
        'status' => 'draft',
        'total_score' => 78.5,
    ]);

    $mitraSubmitted = new AssessmentSubmission([
        'assessor_role' => 'mitra',
        'status' => 'submitted',
        'total_score' => 82.0,
        'submitted_at' => Carbon::parse('2026-06-14 10:00:00'),
    ]);

    $summary = AssessmentSummary::fromSubmissions([$dosenDraft, $mitraSubmitted]);

    expect($summary['status_key'])->toBe('final_mitra')
        ->and($summary['mitra']['score'])->toBe(82.0)
        ->and($summary['dosen']['status_key'])->toBe('draft')
        ->and($summary['is_complete'])->toBeFalse();
});
