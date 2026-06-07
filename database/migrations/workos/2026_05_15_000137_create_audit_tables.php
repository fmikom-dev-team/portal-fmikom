<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Audit Logs (Central Event Logs)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('event_type')->index();
            $table->string('severity')->default('info')->index(); // info, warning, critical
            $table->uuid('actor_id')->nullable()->index();
            $table->uuid('organization_id')->nullable()->index();
            
            // Polymorphic target (e.g., User, Post, Role)
            $table->nullableUuidMorphs('target');
            
            // Network & Device Info
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('user_agent')->nullable();
            $table->string('device_info')->nullable();
            
            // Request Context
            $table->string('request_method', 10)->nullable();
            $table->string('request_path')->nullable();
            $table->integer('response_status')->nullable();
            $table->string('correlation_id')->nullable()->index(); // For tracing distributed requests
            
            // Payload
            $table->json('metadata')->nullable(); // JSON schema for before/after changes, etc.
            
            $table->timestamp('created_at')->useCurrent()->index(); // Indexed for fast range queries
        });

        // 2. Security Incidents (High Severity Events)
        Schema::create('audit_security_incidents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('audit_log_id')->nullable()->constrained('audit_logs')->nullOnDelete();
            $table->string('incident_type')->index(); // e.g., brute_force, impossible_travel, unauthorized_access
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->index();
            $table->string('severity')->default('high');
            $table->json('details')->nullable();
            $table->string('mitigation_status')->default('open')->index(); // open, auto_blocked, resolved
            $table->timestamps();
        });

        // 3. API Requests Logging
        Schema::create('audit_api_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->uuid('token_id')->nullable()->index(); // Which API token was used
            $table->string('endpoint')->index();
            $table->string('method', 10);
            $table->integer('status_code')->index();
            $table->integer('response_time_ms')->nullable();
            $table->json('request_payload')->nullable();
            $table->string('ip_address', 45)->index();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        // 4. Exports Queue
        Schema::create('audit_exports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();
            $table->string('status')->default('pending')->index(); // pending, processing, completed, failed
            $table->string('format')->default('csv'); // csv, excel, json, pdf
            $table->json('filters')->nullable(); // The query filters used
            $table->string('file_path')->nullable(); // S3 or local path
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_exports');
        Schema::dropIfExists('audit_api_requests');
        Schema::dropIfExists('audit_security_incidents');
        Schema::dropIfExists('audit_logs');
    }
};
