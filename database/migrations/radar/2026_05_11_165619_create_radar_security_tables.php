<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Radar Protections (Configuration for Rules)
        if (! Schema::hasTable('radar_protections')) {
            Schema::create('radar_protections', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique(); // e.g., bot_detection, brute_force, impossible_travel
                $table->string('name');
                $table->string('description')->nullable();
                $table->enum('status', ['Enabled', 'Logging', 'Disabled'])->default('Logging');
                $table->json('threshold_config')->nullable(); // e.g., max_attempts, time_window
                $table->boolean('auto_block')->default(false);
                $table->boolean('notify_admin')->default(false);
                $table->integer('sensitivity_level')->default(50); // 1-100
                $table->timestamps();
            });
        }

        // 2. Radar Devices (Fingerprinting)
        if (! Schema::hasTable('radar_devices')) {
            Schema::create('radar_devices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('device_fingerprint')->unique()->index();
                $table->string('ip_address', 45)->index();
                $table->string('user_agent')->nullable();
                $table->string('browser')->nullable();
                $table->string('os')->nullable();
                $table->string('country')->nullable();
                $table->string('city')->nullable();
                $table->json('geolocation')->nullable();
                $table->boolean('is_trusted')->default(false);
                $table->boolean('is_blocked')->default(false);
                $table->timestamp('last_seen_at')->useCurrent();
                $table->timestamps();

                $table->index(['user_id', 'is_trusted']);
            });
        }

        // 3. Radar Detections (Actual Threats Detected)
        if (! Schema::hasTable('radar_detections')) {
            Schema::create('radar_detections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('radar_protection_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('radar_device_id')->nullable()->constrained()->nullOnDelete();
                $table->string('detection_type')->index(); // e.g., brute_force
                $table->enum('severity', ['Low', 'Medium', 'High', 'Critical'])->default('Medium');
                $table->integer('risk_score')->default(0); // 0-100
                $table->enum('action_taken', ['Allowed', 'Challenged', 'Blocked', 'Logged'])->default('Logged');
                $table->string('ip_address', 45)->index();
                $table->json('metadata')->nullable(); // Request payload, headers, exact reason
                $table->text('resolution_note')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index(['created_at', 'detection_type']);
                $table->index(['ip_address', 'created_at']);
            });
        }

        // 4. Radar Blocked Items (Managed/Custom Lists)
        if (! Schema::hasTable('radar_blocked_items')) {
            Schema::create('radar_blocked_items', function (Blueprint $table) {
                $table->id();
                $table->enum('type', ['IP', 'Domain', 'Device', 'Email', 'UserAgent']);
                $table->string('value')->index(); // the actual IP, Domain, etc.
                $table->text('reason')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->unique(['type', 'value']);
            });
        }

        // 5. Radar Security Events (Audit Log of everything)
        if (! Schema::hasTable('radar_security_events')) {
            Schema::create('radar_security_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('event_type')->index(); // login_success, login_failed, password_reset
                $table->string('ip_address', 45)->nullable();
                $table->string('device_fingerprint')->nullable();
                $table->json('event_data')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('radar_security_events');
        Schema::dropIfExists('radar_blocked_items');
        Schema::dropIfExists('radar_detections');
        Schema::dropIfExists('radar_devices');
        Schema::dropIfExists('radar_protections');
    }
};
