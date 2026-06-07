<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Audit\AuditApiRequest;
use App\Models\Audit\AuditLog;
use App\Models\Audit\AuditSecurityIncident;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogsController extends Controller
{
    /**
     * Display the WorkOS style Audit Logs Index
     */
    public function index(Request $request)
    {
        return Inertia::render('Modules/Radar/AuditLogs/Index', [
            'stats' => [
                'total_events' => AuditLog::count(),
                'active_users' => AuditLog::whereNotNull('actor_id')->distinct('actor_id')->count(),
                'security_incidents' => AuditSecurityIncident::count(),
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
     * Security Logs endpoint
     */
    public function securityLogs(Request $request)
    {
        $query = AuditSecurityIncident::with(['user:id,name,email', 'auditLog']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('incident_type', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('email', 'like', "%{$search}%");
                    });
            });
        }

        $incidents = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'incidents' => $incidents,
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
