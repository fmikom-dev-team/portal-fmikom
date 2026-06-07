<?php

namespace App\Modules\WorkOs\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth\AuthLoginAttempt;
use App\Models\Auth\AuthSession;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function events(Request $request)
    {
        return response()->json([
            'events' => AuthLoginAttempt::latest()->paginate(50),
        ]);
    }

    public function loginAttempts(Request $request)
    {
        return response()->json([
            'attempts' => AuthLoginAttempt::where('is_successful', false)
                ->latest()
                ->paginate(50),
        ]);
    }

    public function security(Request $request)
    {
        return response()->json([
            'high_risk_sessions' => AuthSession::where('risk_score', '>', 60)
                ->with(['user', 'device'])
                ->latest()
                ->paginate(50),
        ]);
    }
}
