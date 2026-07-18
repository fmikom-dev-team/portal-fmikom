<?php

namespace App\Jobs;

use App\Models\WorkOsWebhook;
use App\Models\WorkOsWebhookDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DispatchWorkOsWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $eventType;

    protected $payload;

    public function __construct(string $eventType, array $payload)
    {
        $this->eventType = $eventType;
        $this->payload = $payload;
    }

    public function handle()
    {
        $webhooks = WorkOsWebhook::where('is_active', true)->get();

        foreach ($webhooks as $webhook) {
            if (! in_array($this->eventType, $webhook->events)) {
                continue;
            }

            $payloadJson = json_encode($this->payload);
            $timestamp = time();
            $signature = hash_hmac('sha256', $timestamp.'.'.$payloadJson, $webhook->secret);

            $startTime = microtime(true);
            $status = null;
            $body = null;

            try {
                $response = Http::timeout(5)
                    ->withHeaders([
                        'X-WorkOS-Signature' => "t={$timestamp},v1={$signature}",
                        'Content-Type' => 'application/json',
                    ])
                    ->post($webhook->url, $this->payload);

                $status = $response->status();
                $body = $response->body();
            } catch (\Throwable $e) {
                $status = 500;
                $body = $e->getMessage();
            }

            $latency = round((microtime(true) - $startTime) * 1000);

            WorkOsWebhookDelivery::create([
                'webhook_id' => $webhook->id,
                'event_type' => $this->eventType,
                'payload' => $this->payload,
                'response_status' => $status,
                'response_body' => substr($body, 0, 500),
                'latency_ms' => $latency,
            ]);
        }
    }
}
