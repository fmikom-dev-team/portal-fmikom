<?php

namespace App\Modules\Wims\Services\Admin;

use App\Models\Magang\AssessmentTemplate;
use App\Models\Magang\PendaftaranMagang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AdminAssessmentTemplatePageService
{
    private const DEFAULT_COMPONENTS = [
        ['name' => 'Disiplin', 'weight_percentage' => 15],
        ['name' => 'Komunikasi', 'weight_percentage' => 10],
        ['name' => 'Kerja Tim', 'weight_percentage' => 15],
        ['name' => 'Kerja Mandiri', 'weight_percentage' => 10],
        ['name' => 'Penampilan', 'weight_percentage' => 10],
        ['name' => 'Perilaku', 'weight_percentage' => 20],
        ['name' => 'Pengetahuan / Kemampuan Adaptif', 'weight_percentage' => 20],
    ];

    public function build(Request $request): array
    {
        $search = trim((string) $request->string('search', ''));
        $selectedTemplateId = $request->integer('template') ?: null;

        $query = AssessmentTemplate::query()
            ->with('components')
            ->orderByDesc('is_active')
            ->orderByDesc('periode_mulai')
            ->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('components', function ($componentQuery) use ($search): void {
                        $componentQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $templates = $query
            ->get()
            ->map(fn (AssessmentTemplate $template) => $this->transformTemplate($template))
            ->values();

        $years = $this->resolveAvailableYears();

        return [
            'filters' => [
                'search' => $search,
                'template' => $selectedTemplateId,
            ],
            'summary' => [
                'total_templates' => AssessmentTemplate::count(),
                'active_templates' => AssessmentTemplate::where('is_active', true)->count(),
                'default_components' => count(self::DEFAULT_COMPONENTS),
                'available_years' => $years->count(),
            ],
            'templates' => $templates->all(),
            'years' => $years->values()->all(),
            'defaultComponents' => collect(self::DEFAULT_COMPONENTS)
                ->values()
                ->map(fn (array $component, int $index) => [
                    'name' => $component['name'],
                    'description' => null,
                    'weight_percentage' => (float) $component['weight_percentage'],
                    'sort_order' => $index + 1,
                ])
                ->all(),
        ];
    }

    private function resolveAvailableYears(): Collection
    {
        $registrationYears = PendaftaranMagang::query()
            ->select(['tanggal_mulai'])
            ->whereNotNull('tanggal_mulai')
            ->distinct()
            ->get()
            ->map(fn (PendaftaranMagang $pendaftaran) => $pendaftaran->tanggal_mulai?->format('Y'));

        $templateYears = AssessmentTemplate::query()
            ->select(['periode_mulai'])
            ->distinct()
            ->get()
            ->map(fn (AssessmentTemplate $template) => $template->periode_mulai?->format('Y'));

        $currentYear = (int) now()->format('Y');
        $fallbackYears = collect(range($currentYear - 1, $currentYear + 5))->map(fn (int $year) => (string) $year);

        return $registrationYears
            ->merge($templateYears)
            ->merge($fallbackYears)
            ->filter()
            ->unique()
            ->sortDesc()
            ->values()
            ->map(function (string $year) {
                $startDate = Carbon::createFromDate((int) $year, 1, 1)->toDateString();
                $endDate = Carbon::createFromDate((int) $year, 12, 31)->toDateString();

                return [
                    'value' => $year,
                    'label' => $year,
                    'periode_mulai' => $startDate,
                    'periode_selesai' => $endDate,
                    'periode_label' => $this->formatDateRange($startDate, $endDate),
                ];
            });
    }

    private function transformTemplate(AssessmentTemplate $template): array
    {
        $components = $template->components
            ->sortBy(['sort_order', 'id'])
            ->values()
            ->map(fn ($component) => [
                'id' => $component->id,
                'name' => $component->name,
                'description' => $component->description,
                'weight_percentage' => (float) $component->weight_percentage,
                'sort_order' => $component->sort_order,
            ])
            ->all();

        $totalWeight = round(
            collect($components)->sum(fn (array $component) => $component['weight_percentage']),
            2,
        );

        return [
            'id' => $template->id,
            'year' => $template->periode_mulai?->format('Y'),
            'name' => $template->name,
            'description' => $template->description,
            'is_active' => $template->is_active,
            'periode_mulai' => $template->periode_mulai?->toDateString(),
            'periode_selesai' => $template->periode_selesai?->toDateString(),
            'periode_label' => $this->formatDateRange($template->periode_mulai, $template->periode_selesai),
            'components' => $components,
            'component_count' => count($components),
            'total_weight' => $totalWeight,
            'created_at' => $template->created_at?->translatedFormat('d M Y H:i'),
        ];
    }

    private function formatDateRange(mixed $startDate, mixed $endDate): string
    {
        return sprintf(
            '%s - %s',
            $this->formatDate($startDate),
            $this->formatDate($endDate),
        );
    }

    private function formatDate(mixed $date): string
    {
        if (blank($date)) {
            return '-';
        }

        if ($date instanceof Carbon) {
            return $date->translatedFormat('d M Y');
        }

        return Carbon::parse($date)->translatedFormat('d M Y');
    }
}
