<?php

use App\Models\JenisSurat;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// LEGACY-ONLY: pembersihan data role spesifik fase awal yang sudah tidak dipakai.
// Menjaga histori migrasi tetap jelas sebelum modul lain digabung.
return new class extends Migration
{
    public function up(): void
    {
        $roleSlugs = ['kepala-lab', 'sekretaris-fakultas'];
        $jenisSuratSlugs = [
            'surat-tugas-laboratorium',
            'surat-undangan-fakultas',
        ];

        $roleIds = Role::query()
            ->whereIn('slug', $roleSlugs)
            ->pluck('id')
            ->all();

        if ($roleIds !== []) {
            DB::table('jenis_surats')
                ->whereIn('allowed_role_id', $roleIds)
                ->update(['allowed_role_id' => null]);

            DB::table('jenis_surats')
                ->whereIn('approval_role_id', $roleIds)
                ->update(['approval_role_id' => null]);
        }

        JenisSurat::query()
            ->whereIn('slug', $jenisSuratSlugs)
            ->delete();

        Role::query()
            ->whereIn('slug', $roleSlugs)
            ->delete();
    }

    public function down(): void
    {
        // Data surat yang sudah dihapus tidak dipulihkan otomatis.
        // Jika perlu, jalankan seeder resmi untuk mengembalikan data awal.
    }
};
