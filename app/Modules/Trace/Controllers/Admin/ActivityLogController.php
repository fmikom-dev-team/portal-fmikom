<?php
namespace App\Modules\Trace\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tracer\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', 'like', $request->action . '%');
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return Inertia::render('Modules/Trace/Admin/ActivityLog', [
            'logs' => $query->paginate(25)->withQueryString(),
            'filters' => $request->only(['action', 'user_id', 'search', 'date_from', 'date_to']),
            'actionTypes' => ActivityLog::select('action')->distinct()->pluck('action'),
        ]);
    }
}
