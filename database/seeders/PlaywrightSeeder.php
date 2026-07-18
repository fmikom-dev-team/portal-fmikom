<?php

namespace Database\Seeders;

use App\Enums\UserAccountStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * PlaywrightSeeder — Dedicated seeder for E2E testing.
 *
 * Creates test users with known credentials for each role.
 * Only runs in testing or local environments.
 * All users have status_approval = activated and password_changed_at set
 * to bypass OTP and force-change-password redirects.
 */
class PlaywrightSeeder extends Seeder
{
    /**
     * Known test credentials — mirrored in tests/e2e/utils/selectors.ts
     */
    public const USERS = [
        'super_admin' => [
            'name' => 'Test Super Admin',
            'email' => 'playwright.superadmin@fmikom.test',
            'password' => 'Playwright@SuperAdmin1',
            'type' => 'super-admin',
        ],
        'mahasiswa' => [
            'name' => 'Test Mahasiswa',
            'email' => 'playwright.mahasiswa@fmikom.test',
            'password' => 'Playwright@Mahasiswa1',
            'type' => 'mahasiswa',
        ],
        'dosen' => [
            'name' => 'Test Dosen',
            'email' => 'playwright.dosen@fmikom.test',
            'password' => 'Playwright@Dosen1',
            'type' => 'dosen',
        ],
        'alumni' => [
            'name' => 'Test Alumni',
            'email' => 'playwright.alumni@fmikom.test',
            'password' => 'Playwright@Alumni1',
            'type' => 'alumni',
        ],
        'pending' => [
            'name' => 'Test Pending User',
            'email' => 'playwright.pending@fmikom.test',
            'password' => 'Playwright@Pending1',
            'type' => 'mahasiswa',
        ],
        'rejected' => [
            'name' => 'Test Rejected User',
            'email' => 'playwright.rejected@fmikom.test',
            'password' => 'Playwright@Rejected1',
            'type' => 'mahasiswa',
        ],
    ];

    public function run(): void
    {
        // Ensure super-admin role exists
        Role::firstOrCreate(
            ['slug' => 'super-admin'],
            ['nama' => 'Super Admin']
        );
        Role::firstOrCreate(
            ['slug' => 'user'],
            ['nama' => 'User']
        );

        // ── Super Admin ────────────────────────────────────────────────────────
        $superAdmin = User::updateOrCreate(
            ['email' => self::USERS['super_admin']['email']],
            [
                'name' => self::USERS['super_admin']['name'],
                'password' => Hash::make(self::USERS['super_admin']['password']),
                'user_type' => self::USERS['super_admin']['type'],
                'status_approval' => UserAccountStatus::Activated,
                'is_active' => true,
            ]
        );
        $superAdmin->email_verified_at = now();
        $superAdmin->password_changed_at = now();
        $superAdmin->save();

        // ── Mahasiswa ─────────────────────────────────────────────────────────
        $mahasiswa = User::updateOrCreate(
            ['email' => self::USERS['mahasiswa']['email']],
            [
                'name' => self::USERS['mahasiswa']['name'],
                'password' => Hash::make(self::USERS['mahasiswa']['password']),
                'user_type' => self::USERS['mahasiswa']['type'],
                'status_approval' => UserAccountStatus::Activated,
                'is_active' => true,
                'nomor_induk' => '19280100001',
            ]
        );
        $mahasiswa->email_verified_at = now();
        $mahasiswa->password_changed_at = now();
        $mahasiswa->save();

        // ── Dosen ─────────────────────────────────────────────────────────────
        $dosen = User::updateOrCreate(
            ['email' => self::USERS['dosen']['email']],
            [
                'name' => self::USERS['dosen']['name'],
                'password' => Hash::make(self::USERS['dosen']['password']),
                'user_type' => self::USERS['dosen']['type'],
                'status_approval' => UserAccountStatus::Activated,
                'is_active' => true,
                'nomor_induk' => '19801001200012001',
            ]
        );
        $dosen->email_verified_at = now();
        $dosen->password_changed_at = now();
        $dosen->save();

        // ── Alumni ────────────────────────────────────────────────────────────
        $alumni = User::updateOrCreate(
            ['email' => self::USERS['alumni']['email']],
            [
                'name' => self::USERS['alumni']['name'],
                'password' => Hash::make(self::USERS['alumni']['password']),
                'user_type' => self::USERS['alumni']['type'],
                'status_approval' => UserAccountStatus::Activated,
                'is_active' => true,
                'tahun_lulus' => 2022,
            ]
        );
        $alumni->email_verified_at = now();
        $alumni->password_changed_at = now();
        $alumni->save();

        // ── Pending User (untuk test waiting room) ────────────────────────────
        $pending = User::updateOrCreate(
            ['email' => self::USERS['pending']['email']],
            [
                'name' => self::USERS['pending']['name'],
                'password' => Hash::make(self::USERS['pending']['password']),
                'user_type' => self::USERS['pending']['type'],
                'status_approval' => UserAccountStatus::Pending,
                'is_active' => false,
            ]
        );
        $pending->email_verified_at = now();
        $pending->password_changed_at = null;
        $pending->save();

        // ── Rejected User (untuk test rejection flow) ─────────────────────────
        $rejected = User::updateOrCreate(
            ['email' => self::USERS['rejected']['email']],
            [
                'name' => self::USERS['rejected']['name'],
                'password' => Hash::make(self::USERS['rejected']['password']),
                'user_type' => self::USERS['rejected']['type'],
                'status_approval' => UserAccountStatus::Rejected,
                'is_active' => false,
            ]
        );
        $rejected->email_verified_at = now();
        $rejected->password_changed_at = null;
        $rejected->save();

        // Insert password histories for all activated users to satisfy DB constraint
        $activatedEmails = [
            self::USERS['super_admin']['email'],
            self::USERS['mahasiswa']['email'],
            self::USERS['dosen']['email'],
            self::USERS['alumni']['email'],
        ];

        foreach ($activatedEmails as $email) {
            $user = User::where('email', $email)->first();
            if ($user && DB::table('auth_password_histories')->where('user_id', $user->id)->doesntExist()) {
                DB::table('auth_password_histories')->insert([
                    'user_id' => $user->id,
                    'password_hash' => $user->password,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if ($this->command) {
            $this->command->info('✅ PlaywrightSeeder completed — test users created.');
            $this->command->table(
                ['Role', 'Email', 'Password'],
                collect(self::USERS)->map(fn ($u, $key) => [
                    $key,
                    $u['email'],
                    $u['password'],
                ])->values()->toArray()
            );
        }
    }
}
