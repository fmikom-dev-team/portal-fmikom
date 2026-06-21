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

        $defaults = [
            [
                'key' => 'font_family_all',
                'label' => 'Font Semua Bagian',
                'value' => 'Times New Roman',
                'tipe' => 'text',
            ],
            [
                'key' => 'font_family_kop',
                'label' => 'Font Kop',
                'value' => 'Times New Roman',
                'tipe' => 'text',
            ],
            [
                'key' => 'font_family_body',
                'label' => 'Font Isi Surat',
                'value' => 'Times New Roman',
                'tipe' => 'text',
            ],
            [
                'key' => 'font_family_footer',
                'label' => 'Font Footer',
                'value' => 'Times New Roman',
                'tipe' => 'text',
            ],
        ];

        foreach ($defaults as $setting) {
            $existing = DB::table('template_global_settings')
                ->where('key', $setting['key'])
                ->first();

            if ($existing === null) {
                DB::table('template_global_settings')->insert([
                    'key' => $setting['key'],
                    'label' => $setting['label'],
                    'tipe' => $setting['tipe'],
                    'value' => $setting['value'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                continue;
            }

            $updates = [];

            if (blank($existing->label ?? null)) {
                $updates['label'] = $setting['label'];
            }
            if (blank($existing->tipe ?? null)) {
                $updates['tipe'] = $setting['tipe'];
            }
            if (blank($existing->value ?? null)) {
                $updates['value'] = $setting['value'];
            }

            if (! empty($updates)) {
                $updates['updated_at'] = now();

                DB::table('template_global_settings')
                    ->where('key', $setting['key'])
                    ->update($updates);
            }
        }
    }

    public function down(): void
    {
        // Keep the settings to avoid losing admin configuration.
    }
};
