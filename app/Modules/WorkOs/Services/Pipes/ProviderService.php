<?php

namespace App\Modules\WorkOs\Services\Pipes;

use App\Models\Pipes\PipeProvider;
use Illuminate\Support\Str;

class ProviderService
{
    /**
     * Get all providers for the catalog
     */
    public function getCatalog($search = null, $status = null)
    {
        $query = PipeProvider::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('name')->paginate(20);
    }

    /**
     * Setup a new provider
     */
    public function createProvider(array $data)
    {
        $data['slug'] = Str::slug($data['name']);

        // Defaults
        $data['status'] = $data['status'] ?? 'disabled';
        $data['oauth_type'] = $data['oauth_type'] ?? 'oauth2';

        return PipeProvider::create($data);
    }

    /**
     * Update existing provider
     */
    public function updateProvider(PipeProvider $provider, array $data)
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $provider->update($data);

        return $provider;
    }
}
