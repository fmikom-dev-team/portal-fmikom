<?php

namespace App\Jobs\Pipes;

use App\Models\Pipes\PipeSyncLog;
use App\Modules\WorkOs\Services\Pipes\SyncEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessConnectionSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $syncLog;

    // Retry settings for the entire sync orchestrator
    public $tries = 3;
    public $backoff = [60, 300, 600]; // 1m, 5m, 10m

    /**
     * Create a new job instance.
     */
    public function __construct(PipeSyncLog $syncLog)
    {
        $this->syncLog = $syncLog;
    }

    /**
     * Execute the job.
     */
    public function handle(SyncEngine $syncEngine)
    {
        try {
            $connection = $this->syncLog->connection;
            $providerSlug = $connection->provider->slug;

            // Resolve adapter dynamically
            $adapterClass = "\\App\\Services\\Pipes\\Adapters\\" . ucfirst($providerSlug) . "ProviderAdapter";
            
            if (!class_exists($adapterClass)) {
                throw new \Exception("Adapter class {$adapterClass} not found for provider {$providerSlug}");
            }

            // In Laravel 12 we can resolve via app()
            $adapter = app($adapterClass);

            $hasMore = true;
            $checkpoint = $this->syncLog->sync_checkpoint;
            $totalProcessed = 0;

            // Simple paging loop (in a real extreme scale, we might recursively dispatch jobs to avoid timeout)
            // But for a realistic enterprise approach, a 15-minute job timeout with batch dispatching is standard.
            while ($hasMore) {
                // Fetch a chunk/page of data from the provider
                $result = $adapter->fetchSyncData($connection, $checkpoint, $this->syncLog->sync_type);

                $items = $result['items'] ?? [];
                $hasMore = $result['has_more'] ?? false;
                $checkpoint = $result['next_checkpoint'] ?? $checkpoint;

                if (count($items) > 0) {
                    // Dispatch a worker job to actually write to DB / trigger webhooks
                    // Using batching or synchronous processing.
                    ProcessSyncBatch::dispatch($this->syncLog, $items)->onQueue('pipes-workers');
                    $totalProcessed += count($items);
                }

                // Safety: Update checkpoint iteratively to allow resuming if killed
                $this->syncLog->update(['sync_checkpoint' => $checkpoint]);
            }

            // Since ProcessSyncBatch are dispatched async, they will update records_processed individually.
            // We just mark the orchestrator as done, or we could use Laravel Bus::batch().
            // For simplicity, we just mark this completed now.
            $syncEngine->completeSync($this->syncLog, $checkpoint ?? [], $totalProcessed, 0);

        } catch (\Throwable $e) {
            Log::error("Pipes Sync Failed: " . $e->getMessage());
            $syncEngine->failSync($this->syncLog, $e);
            
            // Re-throw to trigger job failure and retries
            throw $e;
        }
    }
}
