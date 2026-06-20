<?php

namespace App\Modules\WorkOs\Services\AuthPlatform;

use App\Models\Auth\AuthDevice;
use App\Models\Auth\AuthSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

class SessionEngine
{
    protected RiskEngine $riskEngine;

    public function __construct(RiskEngine $riskEngine)
    {
        $this->riskEngine = $riskEngine;
    }

    /**
     * Create a secure session for the user and return the AuthSession model.
     */
    public function createSession(User $user, Request $request): AuthSession
    {
        $agent = new Agent;
        $agent->setUserAgent($request->userAgent());

        $ip = $request->ip();

        // 1. Resolve Device Fingerprinting
        $device = $this->resolveDevice($user, $agent, $ip, $request->userAgent());

        // 2. Resolve Geolocation
        $geo = $this->resolveGeolocation($ip);

        // 3. Evaluate Risk
        $riskScore = $this->riskEngine->calculateRisk($user, $device, $ip, $geo);

        // 4. Resolve Laravel Session ID (fallback to random hash if no session, e.g., console/tests)
        $token = $request->hasSession() ? $request->session()->getId() : hash('sha256', Str::random(60).time());

        // 5. Persist Session
        $session = AuthSession::create([
            'user_id' => $user->id,
            'device_id' => $device->id,
            'session_token' => $token,
            'ip_address' => $ip,
            'user_agent' => $request->userAgent(),
            'geolocation' => $geo,
            'is_revoked' => false,
            'risk_score' => $riskScore,
            'expires_at' => Carbon::now()->addMinutes(config('session.lifetime')),
            'last_activity_at' => Carbon::now(),
        ]);

        return $session;
    }

    /**
     * Revoke a specific session
     */
    public function revokeSession(AuthSession $session)
    {
        $session->update(['is_revoked' => true]);
    }

    /**
     * Revoke all sessions for a user except current
     */
    public function revokeOtherSessions(User $user, string $currentSessionId)
    {
        AuthSession::where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->update(['is_revoked' => true]);
    }

    /**
     * Determine device fingerprint and store/update device history.
     */
    protected function resolveDevice(User $user, Agent $agent, string $ip, ?string $userAgent): AuthDevice
    {
        // Simple hash to represent the device. In production, we'd use Canvas fingerprinting from frontend too.
        $rawFingerprint = $ip.'|'.$userAgent.'|'.$agent->platform().'|'.$agent->browser();
        $fingerprint = hash('sha256', $rawFingerprint);

        $device = AuthDevice::firstOrCreate(
            [
                'user_id' => $user->id,
                'device_fingerprint' => $fingerprint,
            ],
            [
                'os' => $agent->platform() ? $agent->platform().' '.$agent->version($agent->platform()) : 'Unknown',
                'browser' => $agent->browser() ? $agent->browser().' '.$agent->version($agent->browser()) : 'Unknown',
                'is_trusted' => false,
            ]
        );

        $device->update(['last_active_at' => Carbon::now()]);

        return $device;
    }

    /**
     * Resolve geolocation array from IP using a mock or free geo provider
     */
    protected function resolveGeolocation(string $ip): array
    {
        // Ignore local IPs
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return ['country' => 'Localhost', 'city' => 'Local', 'lat' => 0, 'lng' => 0];
        }

        // Production setup would call MaxMind GeoIP or ip-api.com
        return [
            'country' => 'Unknown',
            'city' => 'Unknown',
            'lat' => null,
            'lng' => null,
        ];
    }
}
