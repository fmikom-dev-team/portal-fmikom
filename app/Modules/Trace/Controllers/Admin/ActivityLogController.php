<?php

namespace App\Modules\Trace\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tracer\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ActivityLogController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = ActivityLog::with('user')->latest();

        if ($request->filled('action')) {
            $escaped = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $request->action);
            $query->where('action', 'like', $escaped.'%');
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $escaped = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $request->search);
            $query->where('description', 'like', '%'.$escaped.'%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $actionTypes = Cache::remember('trace_activity_action_types', now()->addMinutes(10), function () {
            return ActivityLog::select('action')->distinct()->pluck('action');
        });

        return Inertia::render('Modules/Trace/Admin/ActivityLog', [
            'logs' => $query->paginate(25)->withQueryString(),
            'filters' => $request->only(['action', 'user_id', 'search', 'date_from', 'date_to']),
            'actionTypes' => $actionTypes,
        ]);
    }
}
