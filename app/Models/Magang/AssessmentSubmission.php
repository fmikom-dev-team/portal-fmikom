<?php

namespace App\Models\Magang;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentSubmission extends Model
{
    protected $fillable = [
        'pendaftaran_magang_id',
        'assessment_template_id',
        'assessor_id',
        'assessor_role',
        'total_score',
        'status',
        'notes',
        'submitted_at',
    ];

    protected $casts = [
        'total_score' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(PendaftaranMagang::class, 'pendaftaran_magang_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AssessmentTemplate::class, 'assessment_template_id');
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(AssessmentScore::class, 'assessment_submission_id')
            ->orderBy('assessment_component_id')
            ->orderBy('id');
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function canBeEdited(): bool
    {
        return ! $this->isSubmitted();
    }
}
