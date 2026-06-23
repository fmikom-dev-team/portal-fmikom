<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add reviewer_note and reviewed_at to job_applycants (if not already present)
        Schema::table('job_applycants', function (Blueprint $table) {
            if (! Schema::hasColumn('job_applycants', 'reviewer_note')) {
                $table->text('reviewer_note')->nullable()->after('status');
            }
            if (! Schema::hasColumn('job_applycants', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewer_note');
            }
        });

        // 2. Add indexes to job_applycants (ignore if already exist)
        try {
            Schema::table('job_applycants', function (Blueprint $table) {
                $table->index('job_id');
                $table->index('alumni_id');
                $table->index('status');
                $table->unique(['job_id', 'alumni_id']);
            });
        } catch (Exception $e) {
            // Indexes may already exist, safe to ignore
        }

        // 3. Change applied_at from string to timestamp
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE job_applycants MODIFY applied_at TIMESTAMP NULL');
        }

        // 4. Add rejection_reason and rejected_at to jobs_listings
        Schema::table('jobs_listings', function (Blueprint $table) {
            if (! Schema::hasColumn('jobs_listings', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }
            if (! Schema::hasColumn('jobs_listings', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejection_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse 4: Drop rejection_reason and rejected_at from jobs_listings
        Schema::table('jobs_listings', function (Blueprint $table) {
            $table->dropColumn(['rejection_reason', 'rejected_at']);
        });

        // Reverse 3: Revert applied_at back to string
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE job_applycants MODIFY applied_at VARCHAR(255) NULL');
        }

        // Reverse 2: Drop indexes from job_applycants
        Schema::table('job_applycants', function (Blueprint $table) {
            $table->dropUnique(['job_id', 'alumni_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['alumni_id']);
            $table->dropIndex(['job_id']);
        });

        // Reverse 1: Drop reviewer_note and reviewed_at from job_applycants
        Schema::table('job_applycants', function (Blueprint $table) {
            $table->dropColumn(['reviewer_note', 'reviewed_at']);
        });
    }
};
