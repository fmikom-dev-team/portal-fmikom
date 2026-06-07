<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pagi_username', 30)->nullable()->unique()->after('instagram')
                ->comment('Username unik untuk modul PAGI — digunakan untuk pencarian dan identitas komentar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['pagi_username']);
            $table->dropColumn('pagi_username');
        });
    }
};
