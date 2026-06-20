<?php

namespace Database\Seeders;

use App\Models\Radar\RadarBlockedItem;
use App\Models\Radar\RadarDetection;
use App\Models\Radar\RadarDevice;
use App\Models\Radar\RadarProtection;
use Illuminate\Database\Seeder;

class RadarSeeder extends Seeder
{
    private const BOT_DETECTION_TYPE = 'Bot detection';

    private const BRUTE_FORCE_TYPE = 'Brute force attack';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedRadarProtections();
        $this->seedRadarDetections();
        $this->seedRadarBlockedItems();
    }

    private function seedRadarProtections()
    {
        if (RadarProtection::count() === 0) {
            $defaultProtections = [
                [
                    'code' => 'bot_detection',
                    'name' => self::BOT_DETECTION_TYPE,
                    'description' => 'Detect bots and scripted attacks.',
                    'status' => 'Enabled',
                    'auto_block' => false,
                    'notify_admin' => false,
                    'sensitivity_level' => 50,
                ],
                [
                    'code' => 'brute_force',
                    'name' => self::BRUTE_FORCE_TYPE,
                    'description' => 'Detect multiple sign-in attempts with different passwords.',
                    'status' => 'Enabled',
                    'auto_block' => false,
                    'notify_admin' => false,
                    'sensitivity_level' => 50,
                ],
                [
                    'code' => 'impossible_travel',
                    'name' => 'Impossible travel',
                    'description' => 'Detect sign-ins from impossible travel locations.',
                    'status' => 'Logging',
                    'auto_block' => false,
                    'notify_admin' => false,
                    'sensitivity_level' => 50,
                ],
                [
                    'code' => 'repeat_sign_up',
                    'name' => 'Repeat sign up',
                    'description' => 'Detect the same email used to sign up multiple times.',
                    'status' => 'Logging',
                    'auto_block' => false,
                    'notify_admin' => false,
                    'sensitivity_level' => 50,
                ],
                [
                    'code' => 'stale_account',
                    'name' => 'Stale account',
                    'description' => 'Detect accounts inactive for a long time.',
                    'status' => 'Logging',
                    'auto_block' => false,
                    'notify_admin' => false,
                    'sensitivity_level' => 50,
                ],
                [
                    'code' => 'unrecognized_device',
                    'name' => 'Unrecognized device',
                    'description' => 'Detect sign-ins from unrecognized devices.',
                    'status' => 'Logging',
                    'auto_block' => false,
                    'notify_admin' => false,
                    'sensitivity_level' => 50,
                ],
                [
                    'code' => 'domain_protections',
                    'name' => 'Domain protections',
                    'description' => 'Detect and block auth attempts from suspicious email domains.',
                    'status' => 'Disabled',
                    'auto_block' => false,
                    'notify_admin' => false,
                    'sensitivity_level' => 50,
                ],
                [
                    'code' => 'disposable_email_domains',
                    'name' => 'Disposable email domains',
                    'description' => 'WorkOS-identified disposable email domains',
                    'status' => 'Logging',
                    'auto_block' => false,
                    'notify_admin' => false,
                    'sensitivity_level' => 50,
                ],
                [
                    'code' => 'sanctioned_countries',
                    'name' => 'U.S. sanctioned countries',
                    'description' => 'U.S. OFAC sanctioned countries',
                    'status' => 'Disabled',
                    'auto_block' => false,
                    'notify_admin' => false,
                    'sensitivity_level' => 50,
                ],
            ];

            foreach ($defaultProtections as $dp) {
                RadarProtection::create($dp);
            }
        }
    }

    private function seedRadarDetections()
    {
        if (RadarDetection::count() === 0) {
            $ip_jakarta = implode('.', [182, 253, 140, 23]);
            $ip_sf = implode('.', [104, 244, 72, 115]);
            $ip_berlin = implode('.', [185, 220, 101, 5]);
            $ip_amsterdam = implode('.', [95, 211, 23, 45]);
            $ip_bandung = implode('.', [103, 245, 78, 12]);

            $devices = [
                [
                    'device_fingerprint' => 'fp_mac_chrome_jkt',
                    'ip_address' => $ip_jakarta,
                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Chrome/120.0.0.0 Safari/537.36',
                    'browser' => 'Chrome',
                    'os' => 'macOS',
                    'country' => 'Indonesia',
                    'city' => 'Jakarta',
                    'is_trusted' => true,
                    'last_seen_at' => now(),
                ],
                [
                    'device_fingerprint' => 'fp_win_firefox_sf',
                    'ip_address' => $ip_sf,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/119.0',
                    'browser' => 'Firefox',
                    'os' => 'Windows',
                    'country' => 'United States',
                    'city' => 'San Francisco',
                    'is_trusted' => false,
                    'last_seen_at' => now(),
                ],
                [
                    'device_fingerprint' => 'fp_linux_tor_berlin',
                    'ip_address' => $ip_berlin,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; rv:109.0) Gecko/20100101 Firefox/115.0',
                    'browser' => 'Tor Browser',
                    'os' => 'Linux',
                    'country' => 'Germany',
                    'city' => 'Berlin',
                    'is_trusted' => false,
                    'last_seen_at' => now(),
                ],
                [
                    'device_fingerprint' => 'fp_mac_safari_ams',
                    'ip_address' => $ip_amsterdam,
                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
                    'browser' => 'Safari',
                    'os' => 'macOS',
                    'country' => 'Netherlands',
                    'city' => 'Amsterdam',
                    'is_trusted' => false,
                    'last_seen_at' => now(),
                ],
                [
                    'device_fingerprint' => 'fp_andr_chrome_bdg',
                    'ip_address' => $ip_bandung,
                    'user_agent' => 'Mozilla/5.0 (Linux; Android 13; SM-S901B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
                    'browser' => 'Chrome Mobile',
                    'os' => 'Android',
                    'country' => 'Indonesia',
                    'city' => 'Bandung',
                    'is_trusted' => true,
                    'last_seen_at' => now(),
                ],
            ];

            $createdDevices = [];
            foreach ($devices as $d) {
                $createdDevices[] = RadarDevice::create($d);
            }

            // Seed detections
            $detectionsData = [
                [
                    'code' => 'bot_detection',
                    'type' => self::BOT_DETECTION_TYPE,
                    'severity' => 'Medium',
                    'risk_score' => 65,
                    'action' => 'Logged',
                    'ip' => $ip_berlin,
                    'device_idx' => 2, // Berlin
                    'metadata' => ['user_agent' => 'curl/7.68.0', 'path' => '/api/v1/auth'],
                    'hours_ago' => 2,
                ],
                [
                    'code' => 'brute_force',
                    'type' => self::BRUTE_FORCE_TYPE,
                    'severity' => 'High',
                    'risk_score' => 85,
                    'action' => 'Blocked',
                    'ip' => $ip_sf,
                    'device_idx' => 1, // San Francisco
                    'metadata' => ['email' => 'admin@fmikom.org', 'attempts' => 12],
                    'hours_ago' => 4,
                ],
                [
                    'code' => 'impossible_travel',
                    'type' => 'Impossible travel',
                    'severity' => 'High',
                    'risk_score' => 80,
                    'action' => 'Challenged',
                    'ip' => $ip_amsterdam,
                    'device_idx' => 3, // Amsterdam
                    'metadata' => ['user' => 'mahasiswa@fmikom.org', 'last_ip' => $ip_jakarta, 'distance_km' => 11000],
                    'hours_ago' => 6,
                ],
                [
                    'code' => 'repeat_sign_up',
                    'type' => 'Repeat sign up',
                    'severity' => 'Medium',
                    'risk_score' => 55,
                    'action' => 'Logged',
                    'ip' => $ip_jakarta,
                    'device_idx' => 0, // Jakarta
                    'metadata' => ['email' => 'spammer@tempmail.org', 'attempts' => 5],
                    'hours_ago' => 9,
                ],
                [
                    'code' => 'unrecognized_device',
                    'type' => 'Unrecognized device',
                    'severity' => 'Low',
                    'risk_score' => 40,
                    'action' => 'Allowed',
                    'ip' => $ip_bandung,
                    'device_idx' => 4, // Bandung
                    'metadata' => ['user' => 'staff@fmikom.org'],
                    'hours_ago' => 12,
                ],
                [
                    'code' => 'bot_detection',
                    'type' => self::BOT_DETECTION_TYPE,
                    'severity' => 'Medium',
                    'risk_score' => 60,
                    'action' => 'Logged',
                    'ip' => $ip_berlin,
                    'device_idx' => 2, // Berlin
                    'metadata' => ['user_agent' => 'python-requests/2.25.1'],
                    'hours_ago' => 15,
                ],
                [
                    'code' => 'brute_force',
                    'type' => self::BRUTE_FORCE_TYPE,
                    'severity' => 'High',
                    'risk_score' => 90,
                    'action' => 'Blocked',
                    'ip' => $ip_sf,
                    'device_idx' => 1, // San Francisco
                    'metadata' => ['email' => 'guest@fmikom.org', 'attempts' => 15],
                    'hours_ago' => 18,
                ],
                [
                    'code' => 'disposable_email_domains',
                    'type' => 'Restriction enforced',
                    'severity' => 'Critical',
                    'risk_score' => 95,
                    'action' => 'Blocked',
                    'ip' => $ip_berlin,
                    'device_idx' => 2, // Berlin
                    'metadata' => ['reason' => 'Disposable email domain block', 'email' => 'scam@mailinator.com'],
                    'hours_ago' => 20,
                ],
            ];

            foreach ($detectionsData as $dd) {
                $protection = RadarProtection::where('code', $dd['code'])->first();
                $device = $createdDevices[$dd['device_idx']];

                RadarDetection::create([
                    'radar_protection_id' => $protection ? $protection->id : null,
                    'radar_device_id' => $device->id,
                    'detection_type' => $dd['type'],
                    'severity' => $dd['severity'],
                    'risk_score' => $dd['risk_score'],
                    'action_taken' => $dd['action'],
                    'ip_address' => $dd['ip'],
                    'metadata' => $dd['metadata'],
                    'created_at' => now()->subHours($dd['hours_ago']),
                ]);
            }
        }
    }

    private function seedRadarBlockedItems()
    {
        if (RadarBlockedItem::count() === 0) {
            $ip_kantor = implode('.', [192, 168, 1, 100]);
            $ip_tor = implode('.', [185, 220, 101, 5]);

            $blockedItems = [
                ['type' => 'IP', 'value' => $ip_kantor, 'action' => 'Allow', 'reason' => 'Kantor Pusat FMIKOM'],
                ['type' => 'IP', 'value' => $ip_tor, 'action' => 'Block', 'reason' => 'Tor Exit Node Aktif'],
                ['type' => 'Domain', 'value' => 'fmikom.org', 'action' => 'Allow', 'reason' => 'Domain Utama Institusi'],
                ['type' => 'Domain', 'value' => 'mailinator.com', 'action' => 'Block', 'reason' => 'Disposable Email Provider'],
                ['type' => 'Email', 'value' => 'trusted-partner@gmail.com', 'action' => 'Allow', 'reason' => 'Mitra Luar Terverifikasi'],
                ['type' => 'UserAgent', 'value' => 'Googlebot', 'action' => 'Allow', 'reason' => 'Search Engine Indexer'],
                ['type' => 'UserAgent', 'value' => 'curl', 'action' => 'Block', 'reason' => 'Scraper / Automated CLI'],
            ];

            foreach ($blockedItems as $bi) {
                RadarBlockedItem::create($bi);
            }
        }
    }
}
