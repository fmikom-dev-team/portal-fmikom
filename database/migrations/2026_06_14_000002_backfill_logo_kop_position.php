<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('template_global_settings')) {
            return;
        }

        $existing = DB::table('template_global_settings')
            ->where('key', 'logo_kop_position')
            ->first();

        if ($existing === null) {
            DB::table('template_global_settings')->insert([
                'key' => 'logo_kop_position',
                'label' => 'Posisi Logo Kop',
                'tipe' => 'text',
                'value' => 'top',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return;
        }

        $updates = [];

        if (blank($existing->label ?? null)) {
            $updates['label'] = 'Posisi Logo Kop';
        }

        if (blank($existing->tipe ?? null)) {
            $updates['tipe'] = 'text';
        }

        if (blank($existing->value ?? null)) {
            $updates['value'] = 'top';
        }

        if (! empty($updates)) {
            $updates['updated_at'] = now();

            DB::table('template_global_settings')
                ->where('key', 'logo_kop_position')
                ->update($updates);
        }
    }

    public function down(): void
    {
        // Keep the setting to avoid losing admin configuration.
    }
};
