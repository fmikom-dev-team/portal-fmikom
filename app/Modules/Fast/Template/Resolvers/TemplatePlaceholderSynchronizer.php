<?php

namespace App\Modules\Fast\Template\Resolvers;

use App\Models\SuratTemplate;
use App\Modules\Fast\DTOs\SuratDataContract;

class TemplatePlaceholderSynchronizer
{
    /**
     * @param  array<int, array<string, mixed>>  $fieldConfig
     */
    public static function syncTemplate(SuratTemplate $template, array $fieldConfig = []): void
    {
        $templateKeys = static::extractPlaceholderKeys(
            (string) ($template->template_header ?? ''),
            (string) ($template->template_body ?? ''),
            (string) ($template->template_footer ?? ''),
        );

        $fieldKeys = collect($fieldConfig)
            ->filter(fn ($field): bool => is_array($field) && filled($field['name'] ?? null))
            ->map(fn (array $field): string => trim((string) $field['name']))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $keys = collect(array_merge($templateKeys, $fieldKeys))
            ->filter(fn ($key): bool => is_string($key) && $key !== '')
            ->unique()
            ->values()
            ->all();

        $existing = $template->placeholders()->get()->keyBy('placeholder_key');
        $fieldMap = collect($fieldConfig)
            ->filter(fn ($field): bool => is_array($field) && filled($field['name'] ?? null))
            ->mapWithKeys(fn (array $field): array => [(string) $field['name'] => $field])
            ->all();

        foreach ($keys as $key) {
            $template->placeholders()->updateOrCreate(
                ['placeholder_key' => $key],
                static::buildPlaceholderPayload($key, $fieldMap[$key] ?? null, $existing->get($key)),
            );
        }

        if ($keys !== []) {
            $template->placeholders()
                ->whereNotIn('placeholder_key', $keys)
                ->delete();
        } else {
            $template->placeholders()->delete();
        }
    }

    /**
     * @return array<int, string>
     */
    public static function extractPlaceholderKeys(string ...$contents): array
    {
        $keys = [];

        foreach ($contents as $content) {
            preg_match_all('/{{\s*([\w\.]+)\s*}}/', $content, $matches);

            foreach ($matches[1] ?? [] as $key) {
                if (! is_string($key) || $key === '' || str_starts_with($key, '__')) {
                    continue;
                }

                $keys[] = $key;
            }
        }

        return collect($keys)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>|null  $field
     */
    protected static function buildPlaceholderPayload(string $key, ?array $field, mixed $existing): array
    {
        $inferred = SuratDataContract::placeholderDefinition($key, $field);
        $forceContractSource = in_array($key, SuratDataContract::accountBoundFieldKeys(), true);

        return [
            'label' => (string) (data_get($existing, 'label') ?: $inferred['label']),
            'source_type' => (string) ($forceContractSource ? $inferred['source_type'] : (data_get($existing, 'source_type') ?: $inferred['source_type'])),
            'source_key' => (string) ($forceContractSource ? $inferred['source_key'] : (data_get($existing, 'source_key') ?: $inferred['source_key'])),
            'is_required' => (bool) (data_get($existing, 'is_required') ?? $inferred['is_required']),
            'default_value' => data_get($existing, 'default_value', $inferred['default_value']),
            'description' => (string) ($forceContractSource ? $inferred['description'] : (data_get($existing, 'description') ?: $inferred['description'])),
        ];
    }
}


