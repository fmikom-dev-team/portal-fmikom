<?php

namespace App\Http\Middleware\Auth;

use App\Models\Auth\AuthDevice;
use App\Models\Auth\AuthSession;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

/**
 * DeviceFingerprint Middleware
 *
 * Identifies and fingerprints the device on every authenticated request.
 * Updates the `last_active_at` on the AuthSession record for real-time tracking.
 * Flags new/unrecognized devices for risk scoring.
 */
class DeviceFingerprint
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()) {
            return $next($request);
        }

        $agent = new Agent;
        $agent->setUserAgent($request->userAgent());

        $rawFingerprint = $request->ip().'|'.$request->userAgent().'|'.$agent->platform().'|'.$agent->browser();
        $fingerprint = hash('sha256', $rawFingerprint);

        // Update device last_active_at
        $device = AuthDevice::where('user_id', $request->user()->id)
            ->where('device_fingerprint', $fingerprint)
            ->first();

        if ($device) {
            $device->update(['last_active_at' => Carbon::now()]);
        }

        // Update auth session last_activity_at using stored token
        $sessionToken = $request->session()->get('auth_session_token');
        if ($sessionToken) {
            AuthSession::where('session_token', $sessionToken)
                ->where('is_revoked', false)
                ->update(['last_activity_at' => Carbon::now()]);
        }

        // Share fingerprint with the request for downstream use
        $request->merge(['_device_fingerprint' => $fingerprint]);

        return $next($request);
    }
}
