<?php

namespace App\Services\Wims\Admin;

use App\Models\AssessmentTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminAssessmentTemplateActionService
{
    public function create(array $validated, ?int $userId): AssessmentTemplate
    {
        return DB::transaction(function () use ($validated, $userId): AssessmentTemplate {
            $payload = $this->normalizedPayload($validated);

            $template = AssessmentTemplate::create([
                'name' => $payload['name'],
                'description' => $payload['description'],
                'periode_mulai' => $payload['periode_mulai'],
                'periode_selesai' => $payload['periode_selesai'],
                'is_active' => $payload['is_active'],
                'created_by' => $userId,
            ]);

            $this->syncComponents($template, $payload['components']);
            $this->syncActiveTemplateState($template);

            return $template->fresh('components');
        });
    }

    public function update(AssessmentTemplate $template, array $validated): void
    {
        DB::transaction(function () use ($template, $validated): void {
            $payload = $this->normalizedPayload($validated);

            $template->update([
                'name' => $payload['name'],
                'description' => $payload['description'],
                'periode_mulai' => $payload['periode_mulai'],
                'periode_selesai' => $payload['periode_selesai'],
                'is_active' => $payload['is_active'],
            ]);

            $this->syncComponents($template, $payload['components']);
            $this->syncActiveTemplateState($template);
        });
    }

    public function delete(AssessmentTemplate $template): void
    {
        $template->delete();
    }

    public function normalizedPayload(array $validated): array
    {
        $year = (int) $validated['year'];
        $periodeMulai = Carbon::create($year, 1, 1)->startOfDay()->toDateString();
        $periodeSelesai = Carbon::create($year, 12, 31)->startOfDay()->toDateString();
        $templateName = filled($validated['name'] ?? null)
            ? trim((string) $validated['name'])
            : "Template Penilaian PKL {$year}";

        return [
            'year' => $year,
            'name' => $templateName,
            'description' => filled($validated['description'] ?? null)
                ? trim((string) $validated['description'])
                : null,
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
        if (! $template->submissions()->exists()) {
            return false;
        }

        return $this->hasComponentChanges(
            $template,
            $this->normalizedPayload($validated)['components'],
        );
    }

    public function hasSubmissions(AssessmentTemplate $template): bool
    {
        return $template->submissions()->exists();
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

    private function syncActiveTemplateState(AssessmentTemplate $template): void
    {
        if (! $template->is_active) {
            return;
        }

        $year = (int) $template->periode_mulai?->format('Y');

        AssessmentTemplate::query()
            ->whereKeyNot($template->id)
            ->whereYear('periode_mulai', $year)
            ->whereYear('periode_selesai', $year)
            ->update(['is_active' => false]);
    }

    private function hasComponentChanges(AssessmentTemplate $template, array $components): bool
    {
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

        $incomingComponents = collect($components)
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
}
