<?php

namespace App\Modules\Trace\Services;

use Illuminate\Support\Facades\Log;

class AuditLogService
{
    /**
     * Log user action for audit trail
     */
    public static function log(string $action, string $entity, $entityId = null, array $data = []): void
    {
        Log::channel('audit')->info($action, [
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'entity' => $entity,
            'entity_id' => $entityId,
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Log security event
     */
    public static function security(string $event, array $context = []): void
    {
        Log::channel('security')->warning($event, array_merge([
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ], $context));
    }

    /**
     * Log performance metric
     */
    public static function performance(string $metric, float $value, array $context = []): void
    {
        Log::channel('performance')->info($metric, array_merge([
            'metric' => $metric,
            'value' => $value,
            'url' => request()->fullUrl(),
            'timestamp' => now()->toIso8601String(),
        ], $context));
    }
}
