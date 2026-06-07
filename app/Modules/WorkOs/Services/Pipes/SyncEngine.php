<?php

namespace App\Modules\WorkOs\Services\Pipes;

use App\Jobs\Pipes\ProcessConnectionSync;
use App\Models\Pipes\PipeConnection;
use App\Models\Pipes\PipeSyncLog;
use Illuminate\Support\Facades\Log;

class SyncEngine
{
    /**
     * Trigger a background sync for a specific connection.
     */
    public function triggerSync(PipeConnection $connection, string $syncType = 'incremental')
    {
        // 1. Check if sync is already running
        $activeSync = PipeSyncLog::where('connection_id', $connection->id)
            ->where('status', 'started')
            ->exists();

        if ($activeSync) {
            Log::info("Pipes SyncEngine: Sync already running for connection {$connection->id}");

            return false;
        }

        // 2. Fetch the latest checkpoint if incremental
        $checkpoint = null;
        if ($syncType === 'incremental') {
            $lastSuccess = PipeSyncLog::where('connection_id', $connection->id)
                ->where('status', 'completed')
                ->latest('completed_at')
                ->first();

            $checkpoint = $lastSuccess ? $lastSuccess->sync_checkpoint : null;
        }

        // 3. Create a new Sync Log record
        $syncLog = PipeSyncLog::create([
            'connection_id' => $connection->id,
            'sync_type' => $syncType,
            'status' => 'started',
            'started_at' => now(),
            'sync_checkpoint' => $checkpoint,
        ]);

        // 4. Dispatch the Master Sync Job
        ProcessConnectionSync::dispatch($syncLog)->onQueue('pipes-sync');

        return $syncLog;
    }

    /**
     * Mark a sync as completed
     */
    public function completeSync(PipeSyncLog $syncLog, array $newCheckpoint, int $recordsProcessed, int $recordsFailed)
    {
        $syncLog->update([
            'status' => 'completed',
            'completed_at' => now(),
            'sync_checkpoint' => $newCheckpoint,
            'records_processed' => $syncLog->records_processed + $recordsProcessed,
            'records_failed' => $syncLog->records_failed + $recordsFailed,
            'latency_ms' => now()->diffInMilliseconds($syncLog->started_at),
        ]);

        $syncLog->connection->update([
            'last_sync_at' => now(),
            'health_status' => 'healthy',
        ]);
    }

    /**
     * Mark a sync as failed
     */
    public function failSync(PipeSyncLog $syncLog, \Throwable $exception)
    {
        $syncLog->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $exception->getMessage()."\n".$exception->getTraceAsString(),
            'latency_ms' => now()->diffInMilliseconds($syncLog->started_at),
        ]);

        $syncLog->connection->update([
            'health_status' => 'failing',
        ]);
    }
}
