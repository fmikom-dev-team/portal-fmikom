<?php

namespace App\Modules\WorkOs\Services\AuthPlatform;

use App\Models\Module;
use App\Models\User;

class JwtTemplateCompiler
{
    /**
     * Compile the template and return context with results.
     */
    public function compile(string $template, User $user, ?Module $module): array
    {
        $context = $this->buildContext($user, $module);

        $compiled = preg_replace_callback('/\{\{\s*([^\}]+)\s*\}\}/', function (array $matches) use ($context): string {
            return $this->resolveExpression(trim($matches[1]), $context);
        }, $template);

        // Format beautifully if valid JSON
        $decoded = json_decode($compiled, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $compiled = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        return [
            'compiled' => $compiled,
            'user' => $context['user'],
            'organization' => $context['organization'],
        ];
    }

    /**
     * Build the context array for compilation.
     */
    private function buildContext(User $user, ?Module $module): array
    {
        $firstName = explode(' ', $user->name)[0] ?? 'Someone';
        $lastName = count(explode(' ', $user->name)) > 1 ? implode(' ', array_slice(explode(' ', $user->name), 1)) : 'Unknown';

        return [
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'metadata' => [
                    'language' => 'id-ID',
                ],
            ],
            'organization' => [
                'name' => $module?->name ?? 'FMIKOM Staging Org',
                'code' => $module?->code ?? 'fmikom-staging',
                'metadata' => [
                    'tier' => 'enterprise',
                ],
                'domains' => [
                    ['domain' => 'fmikom.org'],
                ],
            ],
        ];
    }

    /**
     * Resolve a single expression with fallback support.
     */
    private function resolveExpression(string $expression, array $context): string
    {
        // Handle fallback operator "||"
        $parts = explode('||', $expression);

        foreach ($parts as $part) {
            $part = trim($part);

            // String literal check
            if (preg_match('/^[\'"](.*)[\'"]$/', $part, $strMatch)) {
                return $strMatch[1];
            }

            // Resolve path from context
            $val = data_get($context, $part);
            if ($val !== null && $val !== '') {
                return is_array($val) ? json_encode($val) : (string) $val;
            }
        }

        return '';
    }
}
