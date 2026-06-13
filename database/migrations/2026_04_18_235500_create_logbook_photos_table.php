<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logbook_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logbook_id')->constrained('logbook_magangs')->cascadeOnDelete();
            $table->string('file_path');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbook_photos');
    }
};
