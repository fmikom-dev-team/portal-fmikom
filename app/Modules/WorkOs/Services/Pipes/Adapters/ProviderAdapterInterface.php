<?php

namespace App\Modules\WorkOs\Services\Pipes\Adapters;

use App\Models\Pipes\PipeConnection;

interface ProviderAdapterInterface
{
    /**
     * Fetch a paginated batch of data for synchronization
     *
     * @param PipeConnection $connection
     * @param array|null $checkpoint The cursor or timestamp to resume from
     * @param string $syncType "incremental" or "full"
     * @return array Returns an array containing 'items', 'next_checkpoint', 'has_more'
     */
    public function fetchSyncData(PipeConnection $connection, ?array $checkpoint, string $syncType): array;

    /**
     * Test the connection to the provider to ensure tokens and scopes are valid.
     */
    public function testConnection(PipeConnection $connection): bool;
}
