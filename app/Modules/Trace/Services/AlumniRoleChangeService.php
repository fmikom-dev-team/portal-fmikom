<?php

namespace App\Modules\Trace\Services;

use App\Models\Module;
use App\Models\Role;
use App\Models\Tracer\ProfilAlumni;
use App\Models\User;
use App\Models\UserModuleRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AlumniRoleChangeService
{
    public const METADATA_KEY = 'alumni_role_change_request';

    public function submit(User $user, array $data): void
    {
        $metadata = $user->metadata ?? [];
        $existing = $metadata[self::METADATA_KEY] ?? null;

        if (($existing['status'] ?? null) === 'pending') {
            abort(422, 'Pengajuan perubahan role Anda masih menunggu persetujuan.');
        }

        if (! empty($existing['data']['proof_path'])) {
            Storage::disk('local')->delete($existing['data']['proof_path']);
            Storage::disk('public')->delete($existing['data']['proof_path']);
        }

        $metadata[self::METADATA_KEY] = [
            'status' => 'pending',
            'submitted_at' => now()->toISOString(),
            'data' => $data,
        ];

        $user->forceFill(['metadata' => $metadata])->save();
    }

    public function approve(User $user, User $reviewer): void
    {
        $request = $this->requestFrom($user);

        abort_unless(($request['status'] ?? null) === 'pending', 422, 'Pengajuan tidak dalam status pending.');

        $data = $request['data'] ?? [];

        DB::transaction(function () use ($user, $reviewer, $data) {
            $user->forceFill([
                'user_type' => 'alumni',
                'program_studi_id' => $data['program_studi_id'] ?? $user->program_studi_id,
                'tahun_lulus' => $data['tahun_lulus'] ?? $user->tahun_lulus,
                'no_telepon' => $data['no_telepon'] ?? $user->no_telepon,
            ])->save();

            ProfilAlumni::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'angkatan' => $data['angkatan'],
                    'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                    'provinsi_id' => $data['provinsi_id'] ?? null,
                    'kota_id' => $data['kota_id'] ?? null,
                    'alamat_rumah' => $data['alamat_rumah'] ?? null,
                    'latitude_rumah' => $data['latitude_rumah'] ?? null,
                    'longitude_rumah' => $data['longitude_rumah'] ?? null,
                ],
            );

            $this->replaceStudentAccessWithAlumniAccess($user);

            $this->markReviewed($user, $reviewer, 'approved');
        });
    }

    public function reject(User $user, User $reviewer, ?string $reason = null): void
    {
        $request = $this->requestFrom($user);

        abort_unless(($request['status'] ?? null) === 'pending', 422, 'Pengajuan tidak dalam status pending.');

        $this->markReviewed($user, $reviewer, 'rejected', $reason);
    }

    public function requestFrom(User $user): array
    {
        return $user->metadata[self::METADATA_KEY] ?? [];
    }

    private function replaceStudentAccessWithAlumniAccess(User $user): void
    {
        UserModuleRole::query()
            ->where('user_id', $user->id)
            ->whereHas('role', fn ($query) => $query->where('slug', 'mahasiswa'))
            ->update(['is_active' => false]);

        $role = Role::query()->where('slug', 'alumni')->first();
        $modules = Module::query()->whereIn('code', ['TRACE', 'PAGI'])->get();

        if (! $role) {
            return;
        }

        foreach ($modules as $module) {
            UserModuleRole::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'module_id' => $module->id,
                    'role_id' => $role->id,
                ],
                ['is_active' => true],
            );
        }
    }

    private function markReviewed(User $user, User $reviewer, string $status, ?string $reason = null): void
    {
        $metadata = $user->metadata ?? [];
        $request = $metadata[self::METADATA_KEY] ?? [];
        $data = $request['data'] ?? [];

        if (! empty($data['proof_path'])) {
            Storage::disk('local')->delete($data['proof_path']);
            Storage::disk('public')->delete($data['proof_path']);

            $data['proof_deleted_at'] = now()->toISOString();
            unset($data['proof_path']);
        }

        $metadata[self::METADATA_KEY] = [
            ...$request,
            'status' => $status,
            'data' => $data,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now()->toISOString(),
            'rejection_reason' => $reason,
        ];

        $user->forceFill(['metadata' => $metadata])->save();
    }
}
