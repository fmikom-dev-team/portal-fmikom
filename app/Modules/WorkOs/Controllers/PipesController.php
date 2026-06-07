<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Pipes\PipeProvider;
use App\Models\Pipes\PipeConnection;
use App\Services\Pipes\ProviderService;
use App\Services\Pipes\OAuthEngine;

class PipesController extends Controller
{
    protected $providerService;
    protected $oauthEngine;

    public function __construct(ProviderService $providerService, OAuthEngine $oauthEngine)
    {
        $this->providerService = $providerService;
        $this->oauthEngine = $oauthEngine;
    }

    /**
     * Dashboard Overview API / Data
     */
    public function dashboard(Request $request)
    {
        $stats = [
            'connected_providers' => PipeProvider::where('status', 'enabled')->count(),
            'active_connections' => PipeConnection::where('status', 'connected')->count(),
            'failed_syncs' => \App\Models\Pipes\PipeSyncLog::where('status', 'failed')->where('created_at', '>=', now()->subDays(7))->count(),
            'total_organizations' => PipeConnection::whereNotNull('organization_id')->distinct('organization_id')->count(),
        ];

        // Fetch paginated providers for the catalog
        $providers = $this->providerService->getCatalog($request->search, $request->status);

        return response()->json([
            'stats' => $stats,
            'providers' => $providers
        ]);
    }

    /**
     * Get Connections
     */
    public function connections(Request $request)
    {
        $connections = PipeConnection::with(['provider', 'organization', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'connections' => $connections
        ]);
    }

    /**
     * Provider Catalog / Providers list
     */
    public function providers(Request $request)
    {
        $providers = $this->providerService->getCatalog($request->search, $request->status);
        return response()->json([
            'providers' => $providers
        ]);
    }

    /**
     * Show Provider Details
     */
    public function showProvider(PipeProvider $provider)
    {
        $provider->load('scopes');
        $activeConnectionsCount = $provider->connections()->where('status', 'connected')->count();

        return response()->json([
            'provider' => $provider,
            'active_connections_count' => $activeConnectionsCount
        ]);
    }

    /**
     * Update Provider Configuration
     */
    public function updateProvider(Request $request, PipeProvider $provider)
    {
        $validated = $request->validate([
            'status' => 'required|in:enabled,disabled',
            'client_id' => 'nullable|string',
            'client_secret' => 'nullable|string',
            'description' => 'nullable|string'
        ]);

        $this->providerService->updateProvider($provider, $validated);

        return response()->json([
            'message' => 'Provider updated successfully',
            'provider' => $provider
        ]);
    }

    /**
     * Connect a Provider (Initiate OAuth)
     */
    public function connectProvider(Request $request, PipeProvider $provider)
    {
        // Get redirect URI
        $redirectUri = route('workos.pipes.oauth.callback', ['provider' => $provider->slug]);
        
        $scopes = $provider->scopes()->pluck('name')->toArray();

        // Pass organization_id or user_id in the state so we know who is connecting
        $statePayload = base64_encode(json_encode([
            'org_id' => $request->organization_id,
            'user_id' => $request->user_id ?? auth()->id(),
            'rand' => \Illuminate\Support\Str::random(20)
        ]));

        $url = $this->oauthEngine->getAuthorizationUrl($provider, $redirectUri, $scopes, $statePayload);

        return response()->json([
            'authorization_url' => $url
        ]);
    }

    /**
     * OAuth Callback
     */
    public function oauthCallback(Request $request, $providerSlug)
    {
        $provider = PipeProvider::where('slug', $providerSlug)->firstOrFail();
        
        $code = $request->code;
        $statePayload = $request->state;

        $redirectUri = route('workos.pipes.oauth.callback', ['provider' => $provider->slug]);

        try {
            // This will exchange code for token
            $tokenData = $this->oauthEngine->handleCallback($provider, $code, $redirectUri, $statePayload);

            $stateData = json_decode(base64_decode($statePayload), true);

            // Create or update connection
            $connection = PipeConnection::updateOrCreate(
                [
                    'provider_id' => $provider->id,
                    'organization_id' => $stateData['org_id'] ?? null,
                    'user_id' => $stateData['user_id'] ?? null,
                ],
                [
                    'status' => 'connected',
                    'health_status' => 'healthy',
                    'granted_scopes' => explode(' ', $tokenData['scope'] ?? ''),
                ]
            );

            // Save tokens
            \App\Models\Pipes\PipeConnectionToken::create([
                'connection_id' => $connection->id,
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'expires_at' => isset($tokenData['expires_in']) ? now()->addSeconds($tokenData['expires_in']) : null,
                'token_type' => $tokenData['token_type'] ?? 'Bearer',
            ]);

            return redirect()->route('workos.dashboard')->with('success', 'Successfully connected to ' . $provider->name);

        } catch (\Exception $e) {
            \Log::error("OAuth Error: " . $e->getMessage());
            return redirect()->route('workos.dashboard')->with('error', 'Failed to connect: ' . $e->getMessage());
        }
    }

    /**
     * Trigger a manual sync for a connection
     */
    public function syncConnection(PipeConnection $connection, \App\Services\Pipes\SyncEngine $syncEngine)
    {
        try {
            $syncLog = $syncEngine->triggerSync($connection, 'full');
            
            if (!$syncLog) {
                return response()->json([
                    'message' => 'A sync is already running for this connection.'
                ], 422);
            }

            return response()->json([
                'message' => 'Sync triggered successfully.',
                'sync_log' => $syncLog
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to trigger sync: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Workflows
     */
    public function workflows(Request $request)
    {
        $workflows = \App\Models\Pipes\PipeWorkflow::with(['organization', 'triggers', 'actions'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'workflows' => $workflows
        ]);
    }
}
