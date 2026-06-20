<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'role_title')) {
                $table->string('role_title')->nullable()->after('name');
            }

            if (! Schema::hasColumn('users', 'user_type')) {
                $table->string('user_type')->default('mahasiswa')->after('role_title');
            }

            if (! Schema::hasColumn('users', 'tanggal_lahir')) {
                $table->date('tanggal_lahir')->nullable()->after('nomor_induk');
            }

            if (! Schema::hasColumn('users', 'tahun_lulus')) {
                $table->year('tahun_lulus')->nullable()->after('tanggal_lahir');
            }

            if (! Schema::hasColumn('users', 'otp_code')) {
                $table->string('otp_code')->nullable()->after('password_changed_at');
            }

            if (! Schema::hasColumn('users', 'otp_expires_at')) {
                $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            }

            if (! Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable()->after('otp_expires_at');
            }

            if (! Schema::hasColumn('users', 'banner_path')) {
                $table->string('banner_path')->nullable()->after('foto_path');
            }

            if (! Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('banner_path');
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

        DB::table('users')
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    $normalizedUserType = self::normalizeUserType((string) ($user->user_type ?? ''));

                    if ($normalizedUserType === '') {
                        $normalizedUserType = 'mahasiswa';
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'role_title' => $user->role_title ?? self::displayRoleTitle($normalizedUserType),
                            'user_type' => $normalizedUserType,
                            'banner_path' => $user->banner_path ?? null,
                            'bio' => $user->bio ?? null,
                            'location' => $user->location ?? null,
                            'metadata' => $user->metadata ?? null,
                            'website' => $user->website ?? null,
                            'twitter' => $user->twitter ?? null,
                            'linkedin' => $user->linkedin ?? null,
                            'github' => $user->github ?? null,
                            'otp_code' => $user->otp_code ?? null,
                            'otp_expires_at' => $user->otp_expires_at ?? null,
                            'password_changed_at' => $user->password_changed_at ?? null,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $columns = [
                'role_title',
                'user_type',
                'tanggal_lahir',
                'tahun_lulus',
                'otp_code',
                'otp_expires_at',
                'password_changed_at',
                'banner_path',
                'bio',
                'location',
                'metadata',
                'website',
                'twitter',
                'linkedin',
                'github',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private static function normalizeUserType(string $userType): string
    {
        $slug = strtolower(trim($userType));

        return match ($slug) {
            'super_admin', 'super-admin' => 'admin',
            default => $slug,
        };
    }

    private static function displayRoleTitle(string $userType): string
    {
        return match ($userType) {
            'super_admin' => 'Super Admin',
            default => ucwords(str_replace(['-', '_'], ' ', $userType)),
        };
    }
};
