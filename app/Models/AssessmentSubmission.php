<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentSubmission extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'total_score' => 'float',
            'submitted_at' => 'datetime',
        ];
    }

    public function pendaftaranMagang(): BelongsTo
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
        return $this->hasMany(AssessmentScore::class)
            ->orderBy('assessment_component_id')
            ->orderBy('id');
    }
}
