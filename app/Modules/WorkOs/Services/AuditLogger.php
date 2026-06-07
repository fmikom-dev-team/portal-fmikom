<?php

namespace App\Modules\WorkOs\Services;

use App\Jobs\LogAuditEvent;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Log a standard audit event asynchronously.
     */
    public static function log(string $eventType, string $severity = 'info', array $metadata = [], $target = null, ?string $correlationId = null)
    {
        $payload = self::buildPayload($eventType, $severity, $metadata, $target, $correlationId);

        // Dispatch to background queue for processing to ensure performance
        LogAuditEvent::dispatch($payload);
    }

    /**
     * Log a critical security incident.
     */
    public static function securityIncident(string $incidentType, string $severity = 'high', array $details = [])
    {
        $payload = self::buildPayload('security.incident', $severity, ['incident_type' => $incidentType, 'details' => $details]);
        $payload['is_security_incident'] = true;

        LogAuditEvent::dispatch($payload);
    }

    /**
     * Build the standardized payload capturing the request context.
     */
    protected static function buildPayload(string $eventType, string $severity, array $metadata, $target, ?string $correlationId): array
    {
        $request = request();

        $actorId = auth()->check() ? auth()->id() : null;
        $organizationId = null; // Extract from session/context if applicable

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
