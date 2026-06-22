<?php

namespace App\Modules\Wims\Services\Mahasiswa\Profile;

use App\Models\Magang\PendaftaranMagang;
use App\Models\User;
use App\Modules\Wims\Services\Shared\Portal\WimsModuleRoleService;

class StudentProfilePageService
{
    public function __construct(
        private readonly WimsModuleRoleService $wimsModuleRoleService,
    ) {}

    public function build(User $user): array
    {
        $user->loadMissing('programStudi.fakultas');

        $latestRegistration = PendaftaranMagang::query()
            ->with(['perusahaan.user', 'dosenPembimbing'])
            ->forMahasiswa($user->id)
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->first();

        return [
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'nim_nip' => $user->nim_nip,
                'nomor_induk' => $user->nomor_induk,
                'phone' => $user->no_telepon,
                'tanggal_lahir' => $user->tanggal_lahir?->toDateString(),
                'tanggal_lahir_label' => $user->tanggal_lahir?->translatedFormat('d M Y'),
                'bio' => $user->bio,
                'website' => $user->website,
                'linkedin' => $user->linkedin,
                'photo_url' => $user->photoUrl(),
                'program_studi' => $user->programStudi?->nama,
                'program_studi_code' => $user->programStudi?->kode,
                'fakultas' => $user->programStudi?->fakultas?->nama,
                'role' => $this->wimsModuleRoleService->resolveContextRoleLabel($user, 'mahasiswa') ?? 'Mahasiswa',
                'status_approval' => $user->status_approval,
                'is_active' => (bool) $user->is_active,
            ],
            'registration' => $latestRegistration ? [
                'status' => $latestRegistration->status,
                'company' => [
                    'proposal' => [
                        'name' => $latestRegistration->perusahaan_diminati_nama,
                    ],
                    'final' => [
                        'id' => $latestRegistration->perusahaan?->id,
                        'name' => $latestRegistration->perusahaan?->nama,
                    ],
                ],
                'lecturer' => [
                    'id' => $latestRegistration->dosenPembimbing?->id,
                    'name' => $latestRegistration->dosenPembimbing?->name,
                    'role_context' => $latestRegistration->dosenPembimbing
                        ? $this->wimsModuleRoleService->resolveContextRoleData($latestRegistration->dosenPembimbing, 'dosen')
                        : null,
                ],
                'mentor' => [
                    'id' => $latestRegistration->perusahaan?->user?->id,
                    'name' => $latestRegistration->perusahaan?->user?->name ?? '-',
                    'role_context' => $latestRegistration->perusahaan?->user
                        ? $this->wimsModuleRoleService->resolveContextRoleData($latestRegistration->perusahaan->user, 'mitra')
                        : null,
                ],
                'period_label' => $latestRegistration->tanggal_mulai && $latestRegistration->tanggal_selesai
                    ? $latestRegistration->tanggal_mulai->translatedFormat('d M Y')
                        .' - '
                        .$latestRegistration->tanggal_selesai->translatedFormat('d M Y')
                    : null,
                'submitted_at' => $latestRegistration->created_at?->translatedFormat('d M Y H:i'),
            ] : null,
        ];
    }
}
