<?php

namespace App\Modules\Wims\Services\Admin;

use App\Models\Magang\PendaftaranMagang;
use App\Modules\Wims\Services\Shared\Portal\WimsModuleRoleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Response;

class AdminRegistrationPageService
{
    public function __construct(
        private readonly WimsModuleRoleService $wimsModuleRoleService,
    ) {
    }

    public function build(Request $request): array
    {
        $status = (string) $request->string('status', 'all');
        $search = trim((string) $request->string('search', ''));
        $visibleStatuses = ['pending', 'approved', 'revisi', 'rejected', 'aktif'];

        $query = PendaftaranMagang::with(['mahasiswa', 'perusahaan'])
            ->whereIn('status', $visibleStatuses)
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'revisi' THEN 1 ELSE 2 END")
            ->latest('id');

        if ($status !== '' && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->whereHas('mahasiswa', function ($mahasiswaQuery) use ($search): void {
                    $mahasiswaQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nomor_induk', 'like', "%{$search}%");
                })
                    ->orWhereHas('perusahaan', function ($perusahaanQuery) use ($search): void {
                        $perusahaanQuery->where('nama', 'like', "%{$search}%");
                    })
                    ->orWhere('perusahaan_diminati_nama', 'like', "%{$search}%")
                    ->orWhere('perusahaan_diminati_alamat', 'like', "%{$search}%");
            });
        }

        $registrations = $query
            ->paginate(10)
            ->withQueryString();

        $this->wimsModuleRoleService->preloadContextRoles(
            $registrations->getCollection()->pluck('mahasiswa')->filter()->all(),
        );

        $registrations->through(fn (PendaftaranMagang $pendaftaran) => [
                'id' => $pendaftaran->id,
                'student' => [
                    'id' => $pendaftaran->mahasiswa?->id,
                    'name' => $pendaftaran->mahasiswa?->name,
                    'email' => $pendaftaran->mahasiswa?->email,
                    'identity' => $pendaftaran->mahasiswa?->nomor_induk ?: $pendaftaran->mahasiswa?->nim_nip,
                    'role_context' => $pendaftaran->mahasiswa
                        ? $this->wimsModuleRoleService->resolveContextRoleData($pendaftaran->mahasiswa, 'mahasiswa')
                        : null,
                ],
                'company' => [
                    'proposal' => [
                        'name' => $pendaftaran->perusahaan_diminati_nama,
                        'address' => $pendaftaran->perusahaan_diminati_alamat,
                    ],
                    'final' => [
                        'id' => $pendaftaran->perusahaan?->id,
                        'name' => $pendaftaran->perusahaan?->nama,
                    ],
                ],
                'application_note' => $pendaftaran->catatan_pengajuan,
                'revision_note' => $pendaftaran->catatan_revisi_admin,
                'tanggal_mulai' => $this->formatDate($pendaftaran->tanggal_mulai),
                'tanggal_selesai' => $this->formatDate($pendaftaran->tanggal_selesai),
                'status' => $pendaftaran->status,
                'dosen_pembimbing_id' => $pendaftaran->dosen_pembimbing_id,
            ]);

        return [
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
            'summary' => [
                'all' => PendaftaranMagang::whereIn('status', $visibleStatuses)->count(),
                'pending' => PendaftaranMagang::where('status', 'pending')->count(),
                'approved' => PendaftaranMagang::where('status', 'approved')->count(),
                'revisi' => PendaftaranMagang::where('status', 'revisi')->count(),
                'rejected' => PendaftaranMagang::where('status', 'rejected')->count(),
                'aktif' => PendaftaranMagang::where('status', 'aktif')->count(),
            ],
            'registrations' => $registrations,
        ];
    }

    private function formatDate(mixed $date): ?string
    {
        if (blank($date)) {
            return null;
        }

        if ($date instanceof Carbon) {
            return $date->translatedFormat('d M Y');
        }

        return Carbon::parse($date)->translatedFormat('d M Y');
    }
}
