<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('auth_mfa')) {
            Schema::create('auth_mfa', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('type')->default('totp');
                $table->text('secret');
                $table->boolean('is_active')->default(false);
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('auth_backup_codes')) {
            Schema::create('auth_backup_codes', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('code_hash');
                $table->boolean('is_used')->default(false);
                $table->timestamp('used_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('auth_passkeys')) {
            Schema::create('auth_passkeys', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('credential_id')->unique();
                $table->text('public_key');
                $table->string('name')->nullable();
                $table->string('aaguid')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_passkeys');
        Schema::dropIfExists('auth_backup_codes');
        Schema::dropIfExists('auth_mfa');
    }
};
