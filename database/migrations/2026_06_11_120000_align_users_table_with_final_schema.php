<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $missingColumns = [
            'role_title',
            'user_type',
            'tanggal_lahir',
            'tahun_lulus',
            'banner_path',
            'bio',
            'location',
            'metadata',
            'website',
            'twitter',
            'linkedin',
            'github',
        ];

        if ($this->hasAnyMissingColumn($missingColumns)) {
            Schema::table('users', function (Blueprint $table): void {
                if (! Schema::hasColumn('users', 'role_title')) {
                    $table->string('role_title')->nullable()->after('name');
                }

                if (! Schema::hasColumn('users', 'user_type')) {
                    $table->string('user_type', 50)
                        ->default('mahasiswa')
                        ->comment('Identity layer: mahasiswa, alumni, mitra, dosen, staff, super_admin')
                        ->after('email');
                }

                if (! Schema::hasColumn('users', 'tanggal_lahir')) {
                    $table->date('tanggal_lahir')->nullable()->after('nim_nip');
                }

                if (! Schema::hasColumn('users', 'tahun_lulus')) {
                    $table->year('tahun_lulus')->nullable()->after('tanggal_lahir');
                }

                if (! Schema::hasColumn('users', 'banner_path')) {
                    $table->string('banner_path')->nullable()->after('foto_path');
                }

                if (! Schema::hasColumn('users', 'bio')) {
                    $table->text('bio')->nullable()->after('is_active');
                }

                if (! Schema::hasColumn('users', 'location')) {
                    $table->string('location')->nullable()->after('bio');
                }

                if (! Schema::hasColumn('users', 'metadata')) {
                    $table->json('metadata')->nullable()->after('location');
                }

                if (! Schema::hasColumn('users', 'website')) {
                    $table->string('website')->nullable()->after('metadata');
                }

                if (! Schema::hasColumn('users', 'twitter')) {
                    $table->string('twitter')->nullable()->after('website');
                }

                if (! Schema::hasColumn('users', 'linkedin')) {
                    $table->string('linkedin')->nullable()->after('twitter');
                }

                if (! Schema::hasColumn('users', 'github')) {
                    $table->string('github')->nullable()->after('linkedin');
                }
            });
        }

        $roleMap = DB::table('roles')
            ->select('id', 'nama', 'slug')
            ->get()
            ->keyBy('id');

        DB::table('users')
            ->select('id', 'role_id', 'role_title', 'user_type', 'nomor_induk', 'nim_nip')
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($roleMap): void {
                foreach ($users as $user) {
                    $role = $user->role_id ? $roleMap->get($user->role_id) : null;
                    $updates = [];
                    $resolvedUserType = match ($role?->slug) {
                        'super-admin' => 'super_admin',
                        'dosen' => 'dosen',
                        'mitra' => 'mitra',
                        'user' => 'mahasiswa',
                        default => null,
                    };

                    if ($user->role_title === null && $role?->nama) {
                        $updates['role_title'] = $role->nama;
                    }

                    if (
                        $resolvedUserType !== null
                        && ($user->user_type === null || $user->user_type === '' || $user->user_type !== $resolvedUserType)
                    ) {
                        $updates['user_type'] = $resolvedUserType;
                    } elseif ($resolvedUserType === null && ($user->user_type === null || $user->user_type === '')) {
                        $updates['user_type'] = 'mahasiswa';
                    }

                    if (($user->nomor_induk === null || $user->nomor_induk === '') && filled($user->nim_nip)) {
                        $updates['nomor_induk'] = $user->nim_nip;
                    }

                    if ($updates !== []) {
                        DB::table('users')
                            ->where('id', $user->id)
                            ->update($updates);
                    }
                }
            });

        DB::statement(<<<'SQL'
ALTER TABLE `users`
    MODIFY COLUMN `tanggal_lahir` date NULL AFTER `nomor_induk`,
    MODIFY COLUMN `tahun_lulus` year NULL AFTER `tanggal_lahir`,
    MODIFY COLUMN `status_approval` enum('pending','approved','rejected') NOT NULL DEFAULT 'approved' AFTER `tahun_lulus`,
    MODIFY COLUMN `email_verified_at` timestamp NULL DEFAULT NULL AFTER `status_approval`,
    MODIFY COLUMN `role_id` bigint unsigned NULL AFTER `updated_at`,
    MODIFY COLUMN `program_studi_id` bigint unsigned NULL AFTER `role_id`,
    MODIFY COLUMN `nim_nip` varchar(255) NULL AFTER `program_studi_id`,
    MODIFY COLUMN `no_telepon` varchar(255) NULL AFTER `nim_nip`,
    MODIFY COLUMN `foto_path` varchar(255) NULL AFTER `no_telepon`,
    MODIFY COLUMN `banner_path` varchar(255) NULL AFTER `foto_path`,
    MODIFY COLUMN `is_active` tinyint(1) NOT NULL DEFAULT '1' AFTER `banner_path`,
    MODIFY COLUMN `bio` text NULL AFTER `is_active`,
    MODIFY COLUMN `location` varchar(255) NULL AFTER `bio`,
    MODIFY COLUMN `metadata` json NULL AFTER `location`,
    MODIFY COLUMN `website` varchar(255) NULL AFTER `metadata`,
    MODIFY COLUMN `twitter` varchar(255) NULL AFTER `website`,
    MODIFY COLUMN `linkedin` varchar(255) NULL AFTER `twitter`,
    MODIFY COLUMN `github` varchar(255) NULL AFTER `linkedin`,
    MODIFY COLUMN `has_completed_pkl` tinyint(1) NOT NULL DEFAULT '0' AFTER `github`,
    MODIFY COLUMN `pkl_completed_at` timestamp NULL DEFAULT NULL AFTER `has_completed_pkl`
SQL);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $dropColumns = collect([
                'role_title',
                'user_type',
                'tanggal_lahir',
                'tahun_lulus',
                'banner_path',
                'bio',
                'location',
                'metadata',
                'website',
                'twitter',
                'linkedin',
                'github',
            ])->filter(fn (string $column) => Schema::hasColumn('users', $column))->values()->all();

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }

    private function hasAnyMissingColumn(array $columns): bool
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn('users', $column)) {
                return true;
            }
        }

        return false;
    }
};
