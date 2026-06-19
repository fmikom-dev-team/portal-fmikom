<?php

namespace App\Models\Magang;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'assessor_role',
        'periode_mulai',
        'periode_selesai',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'periode_mulai' => 'date',
        'periode_selesai' => 'date',
        'is_active' => 'boolean',
    ];

    public function components(): HasMany
    {
        return $this->hasMany(AssessmentComponent::class, 'assessment_template_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssessmentSubmission::class, 'assessment_template_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForAssessorRole(Builder $query, string $role): Builder
    {
        return $query->where('assessor_role', $role);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isApplicableForDate(CarbonInterface $date): bool
    {
        if (! $this->periode_mulai || ! $this->periode_selesai) {
            return false;
        }

        $currentDate = $date->copy()->startOfDay();

        return $currentDate->greaterThanOrEqualTo($this->periode_mulai->copy()->startOfDay())
            && $currentDate->lessThanOrEqualTo($this->periode_selesai->copy()->startOfDay());
    }

    public function appliesToAssessorRole(string $role): bool
    {
        if ($this->assessor_role === 'both') {
            return in_array($role, ['dosen', 'mitra'], true);
        }

        return $this->assessor_role === $role;
    }

    public function applicableAssessorRoles(): array
    {
        if ($this->assessor_role === 'both') {
            return ['dosen', 'mitra'];
        }

        return [$this->assessor_role];
    }
}
