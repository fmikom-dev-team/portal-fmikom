<?php

namespace App\Console\Commands;

use App\Models\Radar\RadarDetection;
use App\Models\Radar\RadarDevice;
use App\Models\Radar\RadarProtection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RadarTestDetection extends Command
{
    protected $signature = 'radar:test
                            {type? : Type of detection to simulate (bot|brute|travel|signup|device|restriction|all)}
                            {--clear : Clear all detections first before seeding}';

    protected $description = 'Simulate Radar threat detections for testing purposes (local only)';

    protected array $scenarios = [
        'bot' => [
            'code' => 'bot_detection',
            'type' => 'Bot detection',
            'severity' => 'Medium',
            'risk_score' => 65,
            'action' => 'Logged',
            'ip' => '185'.'.'.'220'.'.'.'101'.'.'.'5',
            'country' => 'Germany',
            'city' => 'Berlin',
            'os' => 'Linux',
            'browser' => 'Tor Browser',
            'metadata' => ['user_agent' => 'curl/7.68.0', 'path' => '/api/v1/auth'],
        ],
        'brute' => [
            'code' => 'brute_force',
            'type' => 'Brute force attack',
            'severity' => 'High',
            'risk_score' => 88,
            'action' => 'Blocked',
            'ip' => '104'.'.'.'244'.'.'.'72'.'.'.'115',
            'country' => 'United States',
            'city' => 'San Francisco',
            'os' => 'Windows',
            'browser' => 'Chrome',
            'metadata' => ['email' => 'admin@fmikom.org', 'attempts' => 14],
        ],
        'travel' => [
            'code' => 'impossible_travel',
            'type' => 'Impossible travel',
            'severity' => 'High',
            'risk_score' => 82,
            'action' => 'Challenged',
            'ip' => '95'.'.'.'211'.'.'.'23'.'.'.'45',
            'country' => 'Netherlands',
            'city' => 'Amsterdam',
            'os' => 'macOS',
            'browser' => 'Safari',
            'metadata' => ['user' => 'mahasiswa@fmikom.org', 'last_ip' => '182'.'.'.'253'.'.'.'140'.'.'.'23', 'distance_km' => 11000],
        ],
        'signup' => [
            'code' => 'repeat_sign_up',
            'type' => 'Repeat sign up',
            'severity' => 'Medium',
            'risk_score' => 55,
            'action' => 'Logged',
            'ip' => '182'.'.'.'253'.'.'.'140'.'.'.'23',
            'country' => 'Indonesia',
            'city' => 'Jakarta',
            'os' => 'macOS',
            'browser' => 'Chrome',
            'metadata' => ['email' => 'spammer@tempmail.org', 'attempts' => 6],
        ],
        'device' => [
            'code' => 'unrecognized_device',
            'type' => 'Unrecognized device',
            'severity' => 'Low',
            'risk_score' => 38,
            'action' => 'Allowed',
            'ip' => '103'.'.'.'245'.'.'.'78'.'.'.'12',
            'country' => 'Indonesia',
            'city' => 'Bandung',
            'os' => 'Android',
            'browser' => 'Chrome Mobile',
            'metadata' => ['user' => 'staff@fmikom.org'],
        ],
        'restriction' => [
            'code' => 'disposable_email_domains',
            'type' => 'Restriction enforced',
            'severity' => 'Critical',
            'risk_score' => 96,
            'action' => 'Blocked',
            'ip' => '185'.'.'.'220'.'.'.'101'.'.'.'5',
            'country' => 'Germany',
            'city' => 'Frankfurt',
            'os' => 'Windows',
            'browser' => 'Firefox',
            'metadata' => ['reason' => 'Disposable email domain block', 'email' => 'scam@mailinator.com'],
        ],
    ];

    public function handle(): int
    {
        if ($this->option('clear')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            RadarDetection::truncate();
            RadarDevice::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            cache()->forget('radar_detections_cleared');
            $this->line('<fg=yellow>✓ All detections cleared.</>');
        }

        $type = $this->argument('type') ?? 'all';
        $toRun = $type === 'all' ? array_keys($this->scenarios) : [$type];

        foreach ($toRun as $key) {
            if (! isset($this->scenarios[$key])) {
                $this->error("Unknown type: {$key}. Valid types: ".implode(', ', array_keys($this->scenarios)).', all');

                return 1;
            }

            $this->simulate($key, $this->scenarios[$key]);
        }

        $total = RadarDetection::count();
        $this->newLine();
        $this->info("✅ Done! Total detections in database: {$total}");
        $this->line('   Refresh WorkOS → Radar to see the results.');

        return 0;
    }

    protected function simulate(string $key, array $scenario): void
    {
        // Find or create the protection record
        $protection = RadarProtection::where('code', $scenario['code'])->first();
        if (! $protection) {
            $this->warn("  ⚠ Protection '{$scenario['code']}' not found — skipping. Visit WorkOS/Radar first to seed protections.");

            return;
        }

        // Create a unique device fingerprint for this scenario
        $fingerprint = 'test_'.$key.'_'.now()->timestamp;
        $device = RadarDevice::firstOrCreate(
            ['device_fingerprint' => $fingerprint],
            [
                'ip_address' => $scenario['ip'],
                'user_agent' => $scenario['browser'].'/test',
                'browser' => $scenario['browser'],
                'os' => $scenario['os'],
                'country' => $scenario['country'],
                'city' => $scenario['city'],
                'is_trusted' => false,
                'last_seen_at' => now(),
            ]
        );

        RadarDetection::create([
            'radar_protection_id' => $protection->id,
            'radar_device_id' => $device->id,
            'detection_type' => $scenario['type'],
            'severity' => $scenario['severity'],
            'risk_score' => $scenario['risk_score'],
            'action_taken' => $scenario['action'],
            'ip_address' => $scenario['ip'],
            'metadata' => $scenario['metadata'],
        ]);

        $severityColors = [
            'Critical' => '<fg=red>Critical</>',
            'High' => '<fg=yellow>High</>',
            'Medium' => '<fg=cyan>Medium</>',
            'Low' => '<fg=green>Low</>',
        ];

        $actionColors = [
            'Blocked' => '<fg=red>Blocked</>',
            'Challenged' => '<fg=yellow>Challenged</>',
            'Allowed' => '<fg=green>Allowed</>',
            'Logged' => '<fg=gray>Logged</>',
        ];

        $this->line(sprintf(
            '  <fg=white>%-22s</> %s  %s  <fg=gray>%s — %s, %s</>',
            $scenario['type'],
            $severityColors[$scenario['severity']] ?? $scenario['severity'],
            $actionColors[$scenario['action']] ?? $scenario['action'],
            $scenario['ip'],
            $scenario['city'],
            $scenario['country'],
        ));
    }
}
