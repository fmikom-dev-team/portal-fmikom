<?php

namespace App\Modules\WorkOs\Services;

use App\Jobs\LogAuditEvent;
use App\Models\Module;
use Illuminate\Support\Facades\Cache;

class AuditLogger
{
    public static function log(string $eventType, string $severity = 'info', array $metadata = [], $target = null, ?string $correlationId = null)
    {
        $payload = self::buildPayload($eventType, $severity, $metadata, $target, $correlationId);

        if (app()->isLocal() || app()->runningUnitTests()) {
            LogAuditEvent::dispatchSync($payload);
        } else {
            LogAuditEvent::dispatch($payload);
        }
    }

    /**
     * Log a critical security incident.
     */
    public static function securityIncident(string $incidentType, string $severity = 'high', array $details = [])
    {
        $payload = self::buildPayload('security.incident', $severity, ['incident_type' => $incidentType, 'details' => $details]);
        $payload['is_security_incident'] = true;

        if (app()->isLocal() || app()->runningUnitTests()) {
            LogAuditEvent::dispatchSync($payload);
        } else {
            LogAuditEvent::dispatch($payload);
        }
    }

    protected static function buildPayload(string $eventType, string $severity, array $metadata, $target, ?string $correlationId): array
    {
        $request = request();

        $actorId = auth()->check() ? auth()->id() : null;

        // Dynamically resolve organization_id
        $organizationId = null;
        if ($target && is_object($target)) {
            if ($target instanceof Module) {
                $organizationId = $target->id;
            } elseif (isset($target->organization_id)) {
                $organizationId = $target->organization_id;
            }
        }

        if (! $organizationId && isset($metadata['organization_id'])) {
            $organizationId = $metadata['organization_id'];
        }

        if (! $organizationId && session()->has('active_module')) {
            $moduleCode = session('active_module');
            $organizationId = Cache::remember("module_id_by_code_{$moduleCode}", 3600, function () use ($moduleCode) {
                $m = Module::where('code', $moduleCode)->first();

                return $m ? $m->id : null;
            });
        }

        $targetType = null;
        $targetId = null;
        if ($target && is_object($target)) {
            $targetType = get_class($target);
            $targetId = $target->getKey();
        }

        return [
            'event_type' => $eventType,
            'severity' => $severity,
            'actor_id' => $actorId,
            'organization_id' => $organizationId,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'ip_address' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'request_method' => $request ? $request->method() : null,
            'request_path' => $request ? $request->path() : null,
            'correlation_id' => $correlationId ?? ($request ? $request->header('X-Correlation-ID') : null),
            'metadata' => self::redactSensitiveData($metadata),
            'timestamp' => now()->toDateTimeString(),
        ];
    }

    /**
     * Recursively redact sensitive data before logging.
     */
    protected static function redactSensitiveData(array $data): array
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'token', 'secret', 'card_number', 'cvv'];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::redactSensitiveData($value);
            } elseif (in_array(strtolower($key), $sensitiveKeys)) {
                $data[$key] = '[REDACTED]';
            }
        }

        return $data;
    }
}
