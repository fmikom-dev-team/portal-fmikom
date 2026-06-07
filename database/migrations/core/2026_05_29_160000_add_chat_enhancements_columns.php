<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable()->after('remember_token');
        });

        Schema::table('pagi_messages', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('receiver_id')->constrained('pagi_messages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pagi_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_seen_at');
        });
    }
};
