<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('job_applycants', 'job_applicants');
    }

    public function down(): void
    {
        Schema::rename('job_applicants', 'job_applycants');
    }
};
