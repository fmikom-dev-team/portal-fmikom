<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hasUserType = Schema::hasColumn('users', 'user_type');
        $hasRoleTitle = Schema::hasColumn('users', 'role_title');

        $users = [
            [
                'name' => 'Admin',
                'email' => 'tiffanyhellen27@gmail.com',
                'password' => Hash::make('admin1234'),
                'status_approval' => 'approved',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fanny',
                'email' => 'hellentiffanyyy@gmail.com',
                'password' => Hash::make('fanny1234'),
                'status_approval' => 'approved',
                'nim_nip' => '22eo10001',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mrs. Laora',
                'email' => 'hellenfast1@gmail.com',
                'password' => Hash::make('laora1234'),
                'status_approval' => 'approved',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mrs. Anna',
                'email' => 'hellenfast2@gmail.com',
                'password' => Hash::make('anna1234'),
                'status_approval' => 'approved',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mrs. Moana',
                'email' => 'hellenfast3@gmail.com',
                'password' => Hash::make('moana1234'),
                'status_approval' => 'approved',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            if ($hasUserType) {
                $user['user_type'] = match ($user['email']) {
                    'tiffanyhellen27@gmail.com' => 'admin',
                    'hellentiffanyyy@gmail.com' => 'mahasiswa',
                    'hellenfast1@gmail.com' => 'dosen',
                    'hellenfast2@gmail.com' => 'kaprodi',
                    'hellenfast3@gmail.com' => 'dekan',
                    default => 'mahasiswa',
                };
            }

            if ($hasRoleTitle) {
                $user['role_title'] = match ($user['email']) {
                    'tiffanyhellen27@gmail.com' => 'Admin',
                    'hellentiffanyyy@gmail.com' => 'Mahasiswa',
                    'hellenfast1@gmail.com' => 'Dosen',
                    'hellenfast2@gmail.com' => 'Kaprodi',
                    'hellenfast3@gmail.com' => 'Dekan',
                    default => 'Mahasiswa',
                };
            }

            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                $user
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $emails = [
            'tiffanyhellen27@gmail.com',
            'hellentiffanyyy@gmail.com',
            'hellenfast1@gmail.com',
            'hellenfast2@gmail.com',
            'hellenfast3@gmail.com',
        ];

        DB::table('users')->whereIn('email', $emails)->delete();
    }
};
