<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Audit\AuditApiRequest;
use App\Models\Audit\AuditLog;
use App\Models\Audit\AuditSecurityIncident;
use Illuminate\Http\Request;

class AuditLogsController extends Controller
{
    /**
     * Display the WorkOS style Audit Logs Index
     */
    public function index(Request $request)
    {
        if ($request->header('X-Inertia') || ! $request->expectsJson()) {
            return app(DashboardController::class)->index($request);
        }

        return response()->json([
            'stats' => [
                'total_events' => AuditLog::query()->count('*'),
                'active_users' => AuditLog::query()->whereNotNull('actor_id', 'and')->distinct()->count('actor_id'),
                'security_incidents' => AuditSecurityIncident::query()->count('*'),
            ],
            'recent_events' => AuditLog::with('actor:id,name,email')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ]);
    }

    /**
     * Display the Events list page with server-side pagination and filters
     */
    public function events(Request $request)
    {
        if ($request->header('X-Inertia') || ! $request->expectsJson()) {
            return app(DashboardController::class)->index($request);
        }

        $query = AuditLog::with('actor:id,name,email');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('event_type', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('actor', function ($q2) use ($search) {
                        $q2->where('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('actor_id')) {
            $query->where('actor_id', $request->actor_id);
        }

        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        $events = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'events' => $events,
        ]);
    }

    /**
     * Security Logs & System Error Inspector endpoint
     */
    public function securityLogs(Request $request)
    {
        if ($request->header('X-Inertia') || ! $request->expectsJson()) {
            return app(DashboardController::class)->index($request);
        }

        $query = AuditSecurityIncident::with(['user:id,name,email', 'auditLog']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('incident_type', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('details->message', 'like', "%{$search}%")
                    ->orWhere('details->file', 'like', "%{$search}%")
                    ->orWhere('details->root_cause', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('email', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        // Severity filter
        if ($request->filled('severity') && $request->severity !== 'all') {
            $query->where('severity', $request->severity);
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('mitigation_status', $request->status);
        }

        // Stats summary for the top metric cards
        $stats = [
            'total_incidents' => AuditSecurityIncident::query()->count('*'),
            'critical_errors' => AuditSecurityIncident::query()->where('severity', 'critical')->count('*'),
            'open_issues' => AuditSecurityIncident::query()->where('mitigation_status', 'open')->count('*'),
            'resolved_issues' => AuditSecurityIncident::query()->where('mitigation_status', 'resolved')->count('*'),
            'today_count' => AuditSecurityIncident::query()->where('created_at', '>=', now()->startOfDay())->count('*'),
        ];

        $incidents = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'incidents' => $incidents,
            'stats' => $stats,
        ]);
    }

    /**
     * Update an incident's mitigation status.
     */
    public function updateIncidentStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|string|in:open,investigating,resolved,auto_blocked',
        ]);

        $incident = AuditSecurityIncident::findOrFail($id);
        $incident->update([
            'mitigation_status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Status insiden berhasil diperbarui.',
            'incident' => $incident,
        ]);
    }

    /**
     * Delete an incident record.
     */
    public function destroyIncident(string $id)
    {
        $incident = AuditSecurityIncident::findOrFail($id);
        $incident->delete();

        return response()->json([
            'message' => 'Log insiden berhasil dihapus.',
        ]);
    }

    /**
     * Clear all or resolved security incidents.
     */
    public function clearSecurityIncidents(Request $request)
    {
        $scope = $request->input('scope', 'all');

        if ($scope === 'resolved') {
            AuditSecurityIncident::query()->where('mitigation_status', 'resolved')->delete();
        } else {
            AuditSecurityIncident::query()->delete();
        }

        return response()->json([
            'message' => 'Log insiden berhasil dibersihkan.',
        ]);
    }

    /**
     * Clear all audit logs, security incidents, and API request logs.
     */
    public function clear(Request $request)
    {
        AuditSecurityIncident::query()->delete();
        AuditLog::query()->delete();
        AuditApiRequest::query()->delete();

        return back()->with('success', 'Audit logs cleared successfully.');
    }
}
