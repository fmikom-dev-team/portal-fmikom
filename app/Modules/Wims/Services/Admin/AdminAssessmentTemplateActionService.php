<?php

namespace App\Modules\Wims\Services\Admin;

use App\Models\Magang\AssessmentTemplate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminAssessmentTemplateActionService
{
    public function create(array $validated, ?int $userId): AssessmentTemplate
    {
        return DB::transaction(function () use ($validated, $userId): AssessmentTemplate {
            $payload = $this->normalizedPayload($validated);
            $this->assertNoActiveOverlap($payload);

            $template = AssessmentTemplate::create([
                'name' => $payload['name'],
                'description' => $payload['description'],
                'assessor_role' => $payload['assessor_role'],
                'periode_mulai' => $payload['periode_mulai'],
                'periode_selesai' => $payload['periode_selesai'],
                'is_active' => $payload['is_active'],
                'created_by' => $userId,
            ]);

            $this->syncComponents($template, $payload['components']);

            return $template->fresh('components');
        });
    }

    public function update(AssessmentTemplate $template, array $validated): void
    {
        DB::transaction(function () use ($template, $validated): void {
            $payload = $this->normalizedPayload($validated);
            $this->assertNoActiveOverlap($payload, $template->id);

            $template->update([
                'name' => $payload['name'],
                'description' => $payload['description'],
                'assessor_role' => $payload['assessor_role'],
                'periode_mulai' => $payload['periode_mulai'],
                'periode_selesai' => $payload['periode_selesai'],
                'is_active' => $payload['is_active'],
            ]);

            $this->syncComponents($template, $payload['components']);
        });
    }

    public function delete(AssessmentTemplate $template): void
    {
        $template->delete();
    }

    public function normalizedPayload(array $validated): array
    {
        $year = isset($validated['year']) ? (int) $validated['year'] : null;
        $periodeMulai = filled($validated['periode_mulai'] ?? null)
            ? Carbon::parse($validated['periode_mulai'])->startOfDay()->toDateString()
            : Carbon::create($year, 1, 1)->startOfDay()->toDateString();
        $periodeSelesai = filled($validated['periode_selesai'] ?? null)
            ? Carbon::parse($validated['periode_selesai'])->startOfDay()->toDateString()
            : Carbon::create($year, 12, 31)->startOfDay()->toDateString();
        $resolvedYear = (int) Carbon::parse($periodeMulai)->format('Y');
        $assessorRole = (string) $validated['assessor_role'];
        $roleLabel = $assessorRole === 'dosen' ? 'Dosen' : 'Mitra';
        $templateName = filled($validated['name'] ?? null)
            ? trim((string) $validated['name'])
            : "Template Penilaian {$roleLabel} {$resolvedYear}";

        return [
            'year' => $resolvedYear,
            'name' => $templateName,
            'description' => filled($validated['description'] ?? null)
                ? trim((string) $validated['description'])
                : null,
            'assessor_role' => $assessorRole,
            'periode_mulai' => $periodeMulai,
            'periode_selesai' => $periodeSelesai,
            'is_active' => (bool) $validated['is_active'],
            'components' => collect($validated['components'])
                ->values()
                ->map(fn (array $component, int $index) => [
                    'id' => isset($component['id']) ? (int) $component['id'] : null,
                    'name' => trim((string) $component['name']),
                    'description' => filled($component['description'] ?? null)
                        ? trim((string) $component['description'])
                        : null,
                    'weight_percentage' => round((float) $component['weight_percentage'], 2),
                    'sort_order' => $component['sort_order'] ?? ($index + 1),
                ])
                ->all(),
        ];
    }

    public function hasLockedComponentChanges(AssessmentTemplate $template, array $validated): bool
    {
        if (! $this->hasFinalSubmissions($template)) {
            return false;
        }

        return $this->hasStructuralChanges($template, $this->normalizedPayload($validated));
    }

    public function hasSubmissions(AssessmentTemplate $template): bool
    {
        return $template->submissions()->exists();
    }

    public function hasFinalSubmissions(AssessmentTemplate $template): bool
    {
        return $template->submissions()
            ->where('status', 'submitted')
            ->exists();
    }

    private function syncComponents(AssessmentTemplate $template, array $components): void
    {
        $existingComponents = $template->components()->get()->keyBy('id');
        $retainedIds = [];

        foreach (collect($components)->values() as $index => $component) {
            $payload = [
                'name' => $component['name'],
                'description' => $component['description'],
                'weight_percentage' => $component['weight_percentage'],
                'sort_order' => $component['sort_order'] ?? ($index + 1),
            ];

            $componentId = $component['id'] ?? null;

            if ($componentId && $existingComponents->has($componentId)) {
                $existingComponents[$componentId]->update($payload);
                $retainedIds[] = (int) $componentId;

                continue;
            }

            $createdComponent = $template->components()->create($payload);
            $retainedIds[] = (int) $createdComponent->id;
        }

        $template->components()
            ->whereNotIn('id', $retainedIds)
            ->delete();
    }

    private function hasStructuralChanges(AssessmentTemplate $template, array $payload): bool
    {
        if ($template->assessor_role !== $payload['assessor_role']) {
            return true;
        }

        if (
            $template->periode_mulai?->toDateString() !== $payload['periode_mulai']
            || $template->periode_selesai?->toDateString() !== $payload['periode_selesai']
        ) {
            return true;
        }

        $currentComponents = $template->components()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn ($component) => [
                'id' => (int) $component->id,
                'name' => $component->name,
                'description' => $component->description,
                'weight_percentage' => round((float) $component->weight_percentage, 2),
                'sort_order' => (int) $component->sort_order,
            ])
            ->values()
            ->all();

        $incomingComponents = collect($payload['components'])
            ->map(fn (array $component) => [
                'id' => (int) ($component['id'] ?? 0),
                'name' => $component['name'],
                'description' => $component['description'],
                'weight_percentage' => round((float) $component['weight_percentage'], 2),
                'sort_order' => (int) ($component['sort_order'] ?? 0),
            ])
            ->values()
            ->all();

        return $currentComponents !== $incomingComponents;
    }

    private function assertNoActiveOverlap(array $payload, ?int $ignoreTemplateId = null): void
    {
        if (! $payload['is_active']) {
            return;
        }

        $overlapExists = AssessmentTemplate::query()
            ->when($ignoreTemplateId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreTemplateId))
            ->where('assessor_role', $payload['assessor_role'])
            ->where('is_active', true)
            ->whereDate('periode_mulai', '<=', $payload['periode_selesai'])
            ->whereDate('periode_selesai', '>=', $payload['periode_mulai'])
            ->exists();

        if (! $overlapExists) {
            return;
        }

        $roleLabel = $payload['assessor_role'] === 'dosen' ? 'dosen' : 'mitra';

        throw ValidationException::withMessages([
            'periode_mulai' => "Sudah ada template aktif {$roleLabel} yang periodenya tumpang tindih dengan periode tersebut.",
        ]);
    }
}
