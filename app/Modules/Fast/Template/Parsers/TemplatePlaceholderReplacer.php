<?php

namespace App\Modules\Fast\Template\Parsers;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TemplatePlaceholderReplacer
{
    /**
     * @param  array<string, mixed>  $placeholderValues
     */
    public static function replace(
        string $content,
        array $placeholderValues,
        bool $convertNewlinesToBreaks = true,
        bool $stripUnknownPlaceholders = true
    ): string {
        foreach ($placeholderValues as $key => $value) {
            if (! is_string($key) || $key === '' || str_starts_with($key, '__')) {
                continue;
            }

            $rawValue = static::stringifyValue($key, $value);

            $isYth = Str::contains(Str::lower($key), 'yth');

            if ($isYth) {
                // Untuk yth: e() akan escape <br> jadi &lt;br&gt; - restore kembali
                $escapedValue = str_replace(['&lt;br&gt;', '&lt;br /&gt;'], '<br>', e($rawValue));
            } else {
                $escapedValue = e($rawValue);
                if ($convertNewlinesToBreaks) {
                    $escapedValue = nl2br($escapedValue, false);
                }
            }

            $content = str_replace(
                ['{{' . $key . '}}', '{{ ' . $key . ' }}'],
                $escapedValue,
                $content
            );
        }

        if ($stripUnknownPlaceholders) {
            $content = preg_replace('/{{\s*[\w\.]+\s*}}/', '', $content) ?? $content;
        }

        // Bersihkan duplikat Yth. jika template sudah ada Yth. hardcoded sebelum placeholder yth*
        $content = preg_replace('/Yth\.(?:\s*|<br\s*\/?>|\&nbsp;|\n|\r|\xa0)*Yth\./i', 'Yth.', $content) ?? $content;

        return static::removeEmptyParagraphs($content);
    }

    public static function stringifyValue(string $key, mixed $value): string
    {
        if (is_array($value)) {
            return static::formatArrayValue($key, $value);
        }

        if ($value instanceof Collection) {
            return static::formatArrayValue($key, $value->all());
        }

        $str = (string) ($value ?? '');

        // Key yth selalu diformat via formatArrayValue agar Yth. + line break konsisten
        if ($str !== '' && Str::contains(Str::lower($key), 'yth')) {
            return static::formatArrayValue($key, [$str]);
        }

        return $str;
    }

    /**
     * @param  array<int, mixed>  $items
     */
    protected static function formatArrayValue(string $key, array $items): string
    {
        $normalizedItems = collect($items)
            ->map(static fn (mixed $item): string => trim((string) $item))
            ->filter(static fn (string $item): bool => $item !== '')
            ->values();

        if ($normalizedItems->isEmpty()) {
            return '';
        }

        $isYthList = Str::contains(Str::lower($key), 'yth');

        if ($isYthList && $normalizedItems->count() === 1) {
            return "Yth.<br>" . $normalizedItems->first();
        }

        if ($isYthList) {
            return "Yth.<br>" . $normalizedItems
                ->values()
                ->map(fn (string $item, int $index): string => ($index + 1) . '. ' . $item)
                ->implode("<br>");
        }

        if ($normalizedItems->count() === 1) {
            return $normalizedItems->first();
        }

        return $normalizedItems
            ->values()
            ->map(fn (string $item, int $index): string => ($index + 1) . '. ' . $item)
            ->implode("\n");
    }

    protected static function removeEmptyParagraphs(string $content): string
    {
        return preg_replace('/<p\b[^>]*>\s*(?:<br\s*\/?>|\&nbsp;|\s)*<\/p>/i', '', $content) ?? $content;
    }
}

