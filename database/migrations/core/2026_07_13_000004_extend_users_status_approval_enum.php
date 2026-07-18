<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Extend users.status_approval enum with new lifecycle states.
 *
 * PostgreSQL: Adding values to ENUM requires ALTER TYPE.
 * MySQL: ALTER TABLE MODIFY COLUMN with new enum values.
 *
 * New states added:
 *   - otp_sent     : Activation OTP/email has been sent
 *   - otp_verified : OTP verified, awaiting password creation
 *   - activated    : Full lifecycle complete, account is active
 *   - suspended    : Admin-suspended account
 *   - expired      : Activation token expired without action
 *
 * ALSO: Changes DEFAULT from 'approved' → 'pending'
 * This ensures new users are NOT auto-approved.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL: Tambah nilai ke existing enum type
            $enumValues = [
                'otp_sent',
                'otp_verified',
                'activated',
                'suspended',
                'expired',
            ];

            foreach ($enumValues as $value) {
                // Idempotent: skip if value already exists
                DB::statement("DO \$\$
                    BEGIN
                        IF NOT EXISTS (
                            SELECT 1 FROM pg_enum
                            WHERE enumlabel = '{$value}'
                            AND enumtypid = (
                                SELECT oid FROM pg_type WHERE typname = 'status_approval'
                            )
                        ) THEN
                            ALTER TYPE status_approval ADD VALUE '{$value}';
                        END IF;
                    END
                \$\$;");
            }

            // Note: Changing DEFAULT di PostgreSQL is straightforward
            DB::statement("ALTER TABLE users ALTER COLUMN status_approval SET DEFAULT 'pending'");

        } elseif ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE users
                MODIFY COLUMN status_approval
                ENUM('pending','approved','rejected','otp_sent','otp_verified','activated','suspended','expired')
                NOT NULL DEFAULT 'pending'
            ");
        } else {
            // SQLite (testing): Not strict about ENUM, just change default
            Schema::table('users', function (Blueprint $table) {
                $table->string('status_approval', 30)->default('pending')->change();
            });
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            // Revert to original 3-value enum with 'approved' default
            DB::statement("ALTER TABLE users
                MODIFY COLUMN status_approval
                ENUM('pending','approved','rejected')
                NOT NULL DEFAULT 'approved'
            ");
        } elseif ($driver === 'pgsql') {
            // PostgreSQL does not support removing enum values without recreating the type
            // Change default back to 'approved' at minimum
            DB::statement("ALTER TABLE users ALTER COLUMN status_approval SET DEFAULT 'approved'");
        }
    }
};
