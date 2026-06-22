<?php

namespace App\Modules\WorkOs\Services\Radar;

use App\Events\Radar\ThreatDetected;
use App\Models\Radar\RadarBlockedItem;
use App\Models\Radar\RadarDetection;
use App\Models\Radar\RadarDevice;
use App\Models\Radar\RadarProtection;
use App\Models\Radar\RadarSecurityEvent;
use App\Models\User;
use App\Notifications\WorkOsAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DetectionEngine
{
    /**
     * Inspect incoming request for threats.
     * Called on every protected route (login, register, etc.)
     */
    public function inspect(Request $request): void
    {
        // 1. Immediately block if IP is on blocklist
        $this->checkBlockedIp($request);

        // 2. Fingerprint the device (identify this visitor)
        $device = $this->fingerprintDevice($request);

        // 3. Log the raw security event
        $this->logEvent('request_inspected', $request, $device);

        // 4. Run threat detection rules in order of severity
        $this->evaluateBotDetection($request, $device);
        $this->evaluateBruteForce($request, $device);
        $this->evaluateUnrecognizedDevice($request, $device);
        $this->evaluateRepeatSignUp($request, $device);
        $this->evaluateBlockedDomain($request, $device);
    }

    // ─── Rule: Blocked IP ────────────────────────────────────────────────────

    protected function checkBlockedIp(Request $request): void
    {
        $ip = $request->ip();

        $isBlocked = Cache::remember("radar_blocked_ip_{$ip}", 60, function () use ($ip) {
            return RadarBlockedItem::where('type', 'IP')
                ->where('value', $ip)
                ->where('action', 'Block')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->exists();
        });

        if ($isBlocked) {
            abort(403, 'Your IP address has been blocked due to suspicious activity.');
        }
    }

    // ─── Rule: Bot Detection ─────────────────────────────────────────────────

    protected function evaluateBotDetection(Request $request, RadarDevice $device): void
    {
        $protection = $this->getProtection('bot_detection');
        if (! $protection) {
            return;
        }

        $userAgent = strtolower($request->userAgent() ?? '');

        // Known bot/automation signatures
        $knownBots = [
            'curl', 'wget', 'httpie', 'python-requests', 'python-urllib',
            'java/', 'go-http', 'axios/', 'libwww-perl', 'scrapy',
            'postman', 'insomnia', 'paw/', 'okhttp',
        ];

        $isBot = false;
        foreach ($knownBots as $bot) {
            if (str_contains($userAgent, $bot)) {
                $isBot = true;
                break;
            }
        }

        // Also detect missing user agent (headless / automated)
        if (empty(trim($userAgent))) {
            $isBot = true;
        }

        if ($isBot) {
            $action = $protection->auto_block ? 'Blocked' : 'Logged';
            $this->recordDetection($protection, $device, 'Bot detection', 'Medium', 65, $action, $request->ip(), [
                'user_agent' => $request->userAgent(),
                'path' => $request->path(),
            ]);

            if ($protection->auto_block) {
                abort(403, 'Automated request detected. Please use a browser to access this service.');
            }
        }
    }

    // ─── Rule: Brute Force Attack ────────────────────────────────────────────

    protected function evaluateBruteForce(Request $request, RadarDevice $device): void
    {
        $protection = $this->getProtection('brute_force');
        if (! $protection) {
            return;
        }

        $ip = $request->ip();
        $email = $request->input('email', '');
        $cacheKey = "radar_brute_ip_{$ip}";
        $threshold = $protection->threshold_config['max_attempts'] ?? 5;
        $windowMinutes = $protection->threshold_config['window_minutes'] ?? 15;

        // Increment attempt counter
        $attempts = Cache::get($cacheKey, 0) + 1;
        Cache::put($cacheKey, $attempts, now()->addMinutes($windowMinutes));

        if ($attempts >= $threshold) {
            $action = $protection->auto_block ? 'Blocked' : 'Challenged';
            $this->recordDetection($protection, $device, 'Brute force attack', 'High', min(95, 50 + $attempts * 5), $action, $ip, [
                'email' => $email,
                'attempts' => $attempts,
                'window' => "{$windowMinutes}m",
            ]);

            // Reset counter after flagging to avoid spamming detections
            Cache::put($cacheKey, 0, now()->addMinutes($windowMinutes));

            if ($protection->auto_block) {
                abort(429, 'Too many login attempts. Please try again later.');
            }
        }
    }

    // ─── Rule: Unrecognized Device ───────────────────────────────────────────

    protected function evaluateUnrecognizedDevice(Request $request, RadarDevice $device): void
    {
        $protection = $this->getProtection('unrecognized_device');
        if (! $protection) {
            return;
        }

        // If device was just created (wasRecentlyCreated = new device fingerprint)
        if (! $device->wasRecentlyCreated) {
            return;
        }
        if ($device->is_trusted) {
            return;
        }

        $email = $request->input('email', '');

        // Only flag if we know the user (device is new for a known email)
        if ($email && User::where('email', $email)->exists()) {
            $action = $protection->auto_block ? 'Blocked' : 'Challenged';
            $this->recordDetection($protection, $device, 'Unrecognized device', 'Low', 40, $action, $request->ip(), [
                'user' => $email,
                'user_agent' => $request->userAgent(),
            ]);
        }
    }

    // ─── Rule: Repeat Sign-Up ────────────────────────────────────────────────

    protected function evaluateRepeatSignUp(Request $request, RadarDevice $device): void
    {
        // Only fire on registration routes
        if (! str_contains($request->path(), 'register')) {
            return;
        }

        $protection = $this->getProtection('repeat_sign_up');
        if (! $protection) {
            return;
        }

        $ip = $request->ip();
        $cacheKey = "radar_signup_ip_{$ip}";
        $threshold = $protection->threshold_config['max_attempts'] ?? 3;

        $attempts = Cache::get($cacheKey, 0) + 1;
        Cache::put($cacheKey, $attempts, now()->addHours(1));

        if ($attempts >= $threshold) {
            $email = $request->input('email', '');
            $action = $protection->auto_block ? 'Blocked' : 'Logged';
            $this->recordDetection($protection, $device, 'Repeat sign up', 'Medium', 55, $action, $ip, [
                'email' => $email,
                'attempts' => $attempts,
            ]);

            Cache::put($cacheKey, 0, now()->addHours(1));
        }
    }

    // ─── Rule: Blocked Domain ────────────────────────────────────────────────

    protected function evaluateBlockedDomain(Request $request, RadarDevice $device): void
    {
        $email = $request->input('email', '');
        if (! $email || ! str_contains($email, '@')) {
            return;
        }

        $domain = strtolower(substr($email, strpos($email, '@') + 1));

        $isBlocked = Cache::remember("radar_blocked_domain_{$domain}", 300, function () use ($domain) {
            return RadarBlockedItem::where('type', 'Domain')
                ->where('action', 'Block')
                ->where(function ($q) use ($domain) {
                    $q->where('value', $domain)
                        ->orWhere('value', 'LIKE', "%{$domain}%");
                })
                ->exists();
        });

        if ($isBlocked) {
            $protection = $this->getProtection('domain_protections')
                ?? $this->getProtection('disposable_email_domains');
            if (! $protection) {
                return;
            }

            $this->recordDetection($protection, $device, 'Restriction enforced', 'Critical', 95, 'Blocked', $request->ip(), [
                'email' => $email,
                'domain' => $domain,
                'reason' => 'Blocked domain',
            ]);

            abort(422, "Email domain '{$domain}' is not allowed on this platform.");
        }
    }

    // ─── Device Fingerprinting ───────────────────────────────────────────────

    protected function fingerprintDevice(Request $request): RadarDevice
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent() ?? '';

        // Fingerprint = hash of IP + User-Agent (stable per device/browser)
        $fingerprint = hash('sha256', $ip.$userAgent);

        $device = RadarDevice::firstOrCreate(
            ['device_fingerprint' => $fingerprint],
            [
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'browser' => $this->parseBrowser($userAgent),
                'os' => $this->parseOs($userAgent),
                'country' => null, // Requires GeoIP — can integrate MaxMind/ip-api
                'city' => null,
                'is_trusted' => false,
                'last_seen_at' => now(),
            ]
        );

        // Update last_seen_at on each visit (only on existing devices)
        if (! $device->wasRecentlyCreated) {
            $device->update(['last_seen_at' => now()]);
        }

        return $device;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Get a protection rule, cached for 5 minutes.
     */
    protected function getProtection(string $code): ?RadarProtection
    {
        return Cache::remember("radar_protection_{$code}", 300, function () use ($code) {
            $p = RadarProtection::where('code', $code)->first();
            if (! $p || $p->status === 'Disabled') {
                return null;
            }

            return $p;
        });
    }

    /**
     * Record a detection and broadcast realtime event.
     */
    public function recordDetection(
        RadarProtection $protection,
        RadarDevice $device,
        string $type,
        string $severity,
        int $riskScore,
        string $action,
        string $ip,
        array $metadata
    ): void {
        $detection = RadarDetection::create([
            'radar_protection_id' => $protection->id,
            'radar_device_id' => $device->id,
            'detection_type' => $type,
            'severity' => $severity,
            'risk_score' => $riskScore,
            'action_taken' => $action,
            'ip_address' => $ip,
            'metadata' => $metadata,
        ]);

        // Broadcast real-time event to WorkOS Radar dashboard
        try {
            broadcast(new ThreatDetected($detection));
        } catch (\Throwable $e) {
            // Broadcast failure should not break the request
            \Log::warning('Radar broadcast failed: '.$e->getMessage());
        }

        // Send a WorkOS notification to all active Super Admins
        try {
            $superAdmins = User::where('user_type', 'super-admin')->where('is_active', true)->get();
            foreach ($superAdmins as $admin) {
                $admin->notify(new WorkOsAlert(
                    title: "Radar threat payload blocked: {$type}",
                    description: "Threat detected on IP {$ip} ({$device->os} {$device->browser}). Severity: {$severity}. Action taken: {$action}.",
                    severity: strtolower($severity) === 'critical' ? 'error' : (strtolower($severity) === 'high' ? 'error' : (strtolower($severity) === 'medium' ? 'warning' : 'info'))
                ));
            }
        } catch (\Throwable $e) {
            \Log::warning('WorkOS alert notification failed: '.$e->getMessage());
        }
    }

    protected function logEvent(string $type, Request $request, ?RadarDevice $device = null): void
    {
        try {
            RadarSecurityEvent::create([
                'user_id' => auth()->id(),
                'event_type' => $type,
                'ip_address' => $request->ip(),
                'device_fingerprint' => $device?->device_fingerprint,
                'event_data' => [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                ],
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Radar event log failed: '.$e->getMessage());
        }
    }

    protected function parseBrowser(string $ua): string
    {
        $ua = strtolower($ua);
        if (str_contains($ua, 'edg/')) {
            return 'Edge';
        }
        if (str_contains($ua, 'opr/')) {
            return 'Opera';
        }
        if (str_contains($ua, 'chrome/')) {
            return str_contains($ua, 'mobile') ? 'Chrome Mobile' : 'Chrome';
        }
        if (str_contains($ua, 'firefox/')) {
            return 'Firefox';
        }
        if (str_contains($ua, 'safari/')) {
            return str_contains($ua, 'mobile') ? 'Safari Mobile' : 'Safari';
        }
        if (str_contains($ua, 'curl/')) {
            return 'curl';
        }
        if (str_contains($ua, 'postman')) {
            return 'Postman';
        }
        if (str_contains($ua, 'python')) {
            return 'Python HTTP';
        }

        return 'Unknown';
    }

    protected function parseOs(string $ua): string
    {
        if (str_contains($ua, 'Windows')) {
            return 'Windows';
        }
        if (str_contains($ua, 'Macintosh') || str_contains($ua, 'Mac OS X')) {
            return 'macOS';
        }
        if (str_contains($ua, 'Android')) {
            return 'Android';
        }
        if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) {
            return 'iOS';
        }
        if (str_contains($ua, 'Linux')) {
            return 'Linux';
        }

        return 'Unknown';
    }
}
