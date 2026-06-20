<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Run assignDefaultModuleRoles for all existing users to ensure default roles (including PAGI for mitra) are assigned.
        foreach (User::all() as $user) {
            $user->assignDefaultModuleRoles();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed for role assignments
    }
};
