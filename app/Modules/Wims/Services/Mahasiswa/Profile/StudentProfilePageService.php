<?php

namespace App\Modules\Wims\Services\Mahasiswa\Profile;

use App\Models\User;
use App\Modules\Wims\Services\Mahasiswa\Period\StudentPeriodResolverService;
use App\Modules\Wims\Services\Shared\Portal\WimsModuleRoleService;

class StudentProfilePageService
{
    public function __construct(
        private readonly WimsModuleRoleService $wimsModuleRoleService,
        private readonly StudentPeriodResolverService $studentPeriodResolverService,
    ) {}

    public function build(User $user, ?int $selectedRegistrationId = null): array
    {
        $user->loadMissing('programStudi.fakultas');

        $registrations = $this->studentPeriodResolverService->resolveRegistrations($user->id, ['perusahaan.user', 'dosenPembimbing']);
        $selectedRegistration = $this->studentPeriodResolverService->resolveSelectedRegistrationFromCollection($registrations, $selectedRegistrationId);
        $periods = $this->studentPeriodResolverService->buildPeriodOptions($registrations, $selectedRegistration?->id);

        return [
            'selected_period_id' => $selectedRegistration?->id,
            'periods' => $periods,
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
            'registration' => $selectedRegistration ? [
                'id' => $selectedRegistration->id,
                'status' => $selectedRegistration->status,
                'company' => [
                    'proposal' => [
                        'name' => $selectedRegistration->perusahaan_diminati_nama,
                    ],
                    'final' => [
                        'id' => $selectedRegistration->perusahaan?->id,
                        'name' => $selectedRegistration->perusahaan?->nama,
                    ],
                ],
                'lecturer' => [
                    'id' => $selectedRegistration->dosenPembimbing?->id,
                    'name' => $selectedRegistration->dosenPembimbing?->name,
                    'role_context' => $selectedRegistration->dosenPembimbing
                        ? $this->wimsModuleRoleService->resolveContextRoleData($selectedRegistration->dosenPembimbing, 'dosen')
                        : null,
                ],
                'mentor' => [
                    'id' => $selectedRegistration->finalMentor()?->id,
                    'name' => $selectedRegistration->finalMentor()?->name ?? '-',
                    'role_context' => $selectedRegistration->finalMentor()
                        ? $this->wimsModuleRoleService->resolveContextRoleData($selectedRegistration->finalMentor(), 'mitra')
                        : null,
                ],
                'period_label' => $selectedRegistration->periodLabel(),
                'submitted_at' => $selectedRegistration->created_at?->translatedFormat('d M Y H:i'),
            ] : null,
        ];
    }
}
