<?php

namespace App\Modules\WorkOs\Services\Pipes\Adapters;

use App\Models\Pipes\PipeConnection;

class GoogleProviderAdapter extends BaseProviderAdapter
{
    /**
     * Fetch users/directory data as an example of sync.
     */
    public function fetchSyncData(PipeConnection $connection, ?array $checkpoint, string $syncType): array
    {
        $client = $this->getHttpClient($connection);

        // For Google, we might be syncing Workspace Users
        $endpoint = '/admin/directory/v1/users';
        $params = [
            'customer' => 'my_customer',
            'maxResults' => 100,
        ];

        // Resume from checkpoint (pageToken)
        if ($checkpoint && isset($checkpoint['pageToken'])) {
            $params['pageToken'] = $checkpoint['pageToken'];
        }

        $response = $client->get($endpoint, $params);

        if ($response->failed()) {
            // Google API specific error handling
            if ($response->status() === 403) {
                throw new \Exception('Insufficient permissions or scopes to sync Google Directory: '.$response->body());
            }
            throw new \Exception('Google Sync Failed: '.$response->body());
        }

        $data = $response->json();
        $rawUsers = $data['users'] ?? [];

        // Transform data to our standard schema
        $transformedItems = array_map([$this, 'transformItem'], $rawUsers);

        return [
            'items' => $transformedItems,
            'next_checkpoint' => isset($data['nextPageToken']) ? ['pageToken' => $data['nextPageToken']] : null,
            'has_more' => isset($data['nextPageToken']),
        ];
    }

    /**
     * Test the connection
     */
    public function testConnection(PipeConnection $connection): bool
    {
        try {
            $client = $this->getHttpClient($connection);
            // Simple call to user info to verify token works
            $response = $client->get('https://www.googleapis.com/oauth2/v3/userinfo');

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function transformItem(array $rawItem): array
    {
        return [
            'external_id' => $rawItem['id'] ?? null,
            'email' => $rawItem['primaryEmail'] ?? null,
            'first_name' => $rawItem['name']['givenName'] ?? null,
            'last_name' => $rawItem['name']['familyName'] ?? null,
            'is_active' => ! ($rawItem['suspended'] ?? false),
            'raw_payload' => $rawItem,
        ];
    }
}
