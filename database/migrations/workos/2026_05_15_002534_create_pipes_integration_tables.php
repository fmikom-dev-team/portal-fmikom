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
        // 1. Providers Catalog & Configuration
        Schema::create('pipe_providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique(); // e.g., google, slack, github
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('oauth_type')->default('oauth2'); // oauth2, oauth1, api_key
            $table->string('client_id')->nullable(); // Encrypted at rest or in env
            $table->text('client_secret')->nullable(); // Encrypted at rest
            $table->string('auth_url')->nullable();
            $table->string('token_url')->nullable();
            $table->string('api_base_url')->nullable();
            $table->string('status')->default('disabled'); // enabled, disabled, maintenance
            $table->json('supported_features')->nullable(); // JSON array of features (e.g., ["sync", "webhooks"])
            $table->json('settings')->nullable(); // Provider specific configurations
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Provider Scopes Definitions
        Schema::create('pipe_provider_scopes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('provider_id')->constrained('pipe_providers')->cascadeOnDelete();
            $table->string('name'); // e.g., 'user.read', 'repo'
            $table->text('description')->nullable();
            $table->string('group')->nullable(); // Scope grouping for UI
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });

        // 3. User/Org Connections to Providers
        Schema::create('pipe_connections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained('modules')->nullOnDelete(); // Assuming 'modules' is the org table here
            $table->foreignUuid('provider_id')->constrained('pipe_providers')->cascadeOnDelete();
            $table->string('connection_name')->nullable();
            $table->string('external_account_id')->nullable(); // User ID on the external provider
            $table->string('external_account_email')->nullable();
            $table->string('status')->default('connected'); // connected, disconnected, expired, error
            $table->string('health_status')->default('healthy'); // healthy, degraded, failing
            $table->timestamp('last_sync_at')->nullable();
            $table->json('granted_scopes')->nullable(); // Array of scopes authorized
            $table->json('settings')->nullable(); // Connection specific settings
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Encrypted Tokens per Connection
        Schema::create('pipe_connection_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('connection_id')->constrained('pipe_connections')->cascadeOnDelete();
            $table->text('access_token'); // Encrypted
            $table->text('refresh_token')->nullable(); // Encrypted
            $table->timestamp('expires_at')->nullable();
            $table->string('token_type')->default('Bearer');
            $table->timestamps();
        });

        // 5. Sync Activity & Logging
        Schema::create('pipe_sync_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('connection_id')->constrained('pipe_connections')->cascadeOnDelete();
            $table->string('sync_type'); // incremental, full
            $table->string('status'); // started, completed, failed
            $table->unsignedInteger('records_processed')->default(0);
            $table->unsignedInteger('records_failed')->default(0);
            $table->text('error_message')->nullable();
            $table->json('sync_checkpoint')->nullable(); // For resuming syncs
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('latency_ms')->nullable();
            $table->timestamps();
        });

        // 6. External Webhooks Configuration
        Schema::create('pipe_webhooks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('connection_id')->constrained('pipe_connections')->cascadeOnDelete();
            $table->string('endpoint_url');
            $table->string('signing_secret')->nullable(); // Encrypted
            $table->json('events'); // Array of events to subscribe to
            $table->string('status')->default('active'); // active, disabled, failing
            $table->timestamps();
        });

        // 7. Workflow Automations
        Schema::create('pipe_workflows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('organization_id')->nullable()->constrained('modules')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('draft'); // draft, active, disabled
            $table->json('config')->nullable(); // Workflow visual builder JSON
            $table->timestamps();
            $table->softDeletes();
        });

        // 8. Workflow Triggers
        Schema::create('pipe_triggers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workflow_id')->constrained('pipe_workflows')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->nullable()->constrained('pipe_providers')->nullOnDelete();
            $table->string('trigger_type'); // e.g., 'webhook_received', 'sync_completed'
            $table->json('conditions')->nullable(); // Rules for trigger
            $table->timestamps();
        });

        // 9. Workflow Actions
        Schema::create('pipe_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workflow_id')->constrained('pipe_workflows')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->nullable()->constrained('pipe_providers')->nullOnDelete();
            $table->string('action_type'); // e.g., 'create_record', 'send_notification'
            $table->unsignedInteger('step_order')->default(0);
            $table->json('payload_mapping')->nullable(); // Field mapping rules
            $table->string('on_failure')->default('stop'); // stop, continue, retry
            $table->timestamps();
        });

        // 10. Provider Health Indicators
        Schema::create('pipe_provider_health', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('provider_id')->constrained('pipe_providers')->cascadeOnDelete();
            $table->string('status'); // operational, degraded, outage
            $table->integer('average_latency_ms')->default(0);
            $table->decimal('error_rate_percent', 5, 2)->default(0);
            $table->timestamp('last_checked_at')->useCurrent();
            $table->timestamps();
        });

        // 11. Monitoring Metrics (Time Series Data Simulation)
        Schema::create('pipe_monitoring_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('provider_id')->nullable()->constrained('pipe_providers')->nullOnDelete();
            $table->foreignUuid('connection_id')->nullable()->constrained('pipe_connections')->nullOnDelete();
            $table->string('metric_name'); // e.g., 'api_request', 'rate_limit_hit'
            $table->decimal('value', 15, 4);
            $table->json('tags')->nullable(); // Additional dimensions
            $table->timestamp('timestamp')->useCurrent();
            
            $table->index(['metric_name', 'timestamp']);
            $table->index(['provider_id', 'timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pipe_monitoring_metrics');
        Schema::dropIfExists('pipe_provider_health');
        Schema::dropIfExists('pipe_actions');
        Schema::dropIfExists('pipe_triggers');
        Schema::dropIfExists('pipe_workflows');
        Schema::dropIfExists('pipe_webhooks');
        Schema::dropIfExists('pipe_sync_logs');
        Schema::dropIfExists('pipe_connection_tokens');
        Schema::dropIfExists('pipe_connections');
        Schema::dropIfExists('pipe_provider_scopes');
        Schema::dropIfExists('pipe_providers');
    }
};
