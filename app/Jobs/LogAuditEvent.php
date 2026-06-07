<?php

namespace App\Jobs;

use App\Models\Audit\AuditLog;
use App\Models\Audit\AuditSecurityIncident;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogAuditEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Check if it's a security incident flag
        $isSecurityIncident = $this->payload['is_security_incident'] ?? false;
        unset($this->payload['is_security_incident']); // Remove internal flag

        $timestamp = $this->payload['timestamp'] ?? now();
        unset($this->payload['timestamp']);

        $auditLog = AuditLog::create(array_merge($this->payload, ['created_at' => $timestamp]));

        if ($isSecurityIncident) {
            AuditSecurityIncident::create([
                'audit_log_id' => $auditLog->id,
                'incident_type' => $this->payload['metadata']['incident_type'] ?? 'unknown',
                'user_id' => $this->payload['actor_id'],
                'ip_address' => $this->payload['ip_address'] ?? '0.0.0.0',
                'severity' => $this->payload['severity'],
                'details' => $this->payload['metadata']['details'] ?? [],
            ]);
        }

        // Broadcast the event to WebSockets for real-time dashboard updates
        // \App\Events\AuditLogCreated::dispatch($auditLog);
    }
}
