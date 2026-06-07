<?php

namespace App\Jobs\Pipes;

use App\Models\Pipes\PipeSyncLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessSyncBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $syncLog;
    public $items;

    public $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(PipeSyncLog $syncLog, array $items)
    {
        $this->syncLog = $syncLog;
        $this->items = $items;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $processed = 0;
        $failed = 0;

        foreach ($this->items as $item) {
            try {
                // Here we would typically route this to a specific handler that knows
                // how to insert/update the data into the internal database tables,
                // fire webhooks to subscribers, or trigger workflow automations.
                
                // Simulated processing logic:
                // 1. Fire an event that data was synced
                // event(new \App\Events\Pipes\DataSynced($this->syncLog->connection, $item));

                // 2. Evaluate if any pipe_workflows are triggered
                // $workflowEngine->evaluateTriggers($this->syncLog->connection, $item);

                $processed++;
            } catch (\Exception $e) {
                Log::error("Failed to process sync item: " . $e->getMessage());
                $failed++;
            }
        }

        // Increment the counters atomically
        if ($processed > 0) {
            $this->syncLog->increment('records_processed', $processed);
        }
        if ($failed > 0) {
            $this->syncLog->increment('records_failed', $failed);
        }
    }
}
