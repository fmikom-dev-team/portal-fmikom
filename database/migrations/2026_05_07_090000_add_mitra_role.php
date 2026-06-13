<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->updateOrInsert(
            ['slug' => 'mitra'],
            [
                'nama' => 'Pembimbing Lapangan Mitra',
                'deskripsi' => 'Akun pembimbing lapangan dari perusahaan mitra.',
            ],
        );
    }

    public function down(): void
    {
        DB::table('roles')->where('slug', 'mitra')->delete();
    }
};
