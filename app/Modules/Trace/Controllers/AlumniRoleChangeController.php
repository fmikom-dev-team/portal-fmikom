<?php

namespace App\Modules\Trace\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProgramStudi;
use App\Models\Tracer\Kota;
use App\Models\Tracer\Provinsi;
use App\Modules\Trace\Services\AlumniRoleChangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AlumniRoleChangeController extends Controller
{
    public function __construct(
        protected AlumniRoleChangeService $roleChangeService,
    ) {}

    public function options(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'request' => $this->roleChangeService->requestFrom($user) ?: null,
            'programStudis' => ProgramStudi::query()->orderBy('nama')->get(['id', 'nama', 'kode']),
            'provinsis' => Provinsi::query()->orderBy('name')->get(['id', 'name']),
            'kotas' => Kota::query()->orderBy('name')->get(['id', 'name', 'provinsi_id']),
            'defaults' => [
                'program_studi_id' => $user->program_studi_id,
                'nomor_induk' => $user->nomor_induk,
                'no_telepon' => $user->no_telepon,
                'tahun_lulus' => $user->tahun_lulus,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->user_type === 'mahasiswa', 403, 'Hanya mahasiswa yang dapat mengajukan perubahan role ke alumni.');

        $validated = $request->validate([
            'tahun_lulus' => ['required', 'integer', 'min:1900', 'max:'.(date('Y') + 5)],
            'angkatan' => ['required', 'integer', 'min:1900', 'max:'.(date('Y') + 5)],
            'program_studi_id' => ['required', 'exists:program_studis,id'],
            'no_telepon' => ['nullable', 'string', 'max:25'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'provinsi_id' => ['nullable', 'exists:provinsi,id'],
            'kota_id' => ['nullable', 'exists:kota,id'],
            'alamat_rumah' => ['nullable', 'string'],
            'latitude_rumah' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude_rumah' => ['nullable', 'numeric', 'between:-180,180'],
            'bukti_kelulusan' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,image/jpeg,image/png', 'max:5120'],
        ]);

        $proof = $request->file('bukti_kelulusan');
        $validated['proof_path'] = $proof->store('trace/alumni-role-change-proofs', 'local');
        $validated['proof_original_name'] = $proof->getClientOriginalName();
        $validated['proof_mime'] = $proof->getClientMimeType();
        $validated['proof_size'] = $proof->getSize();
        unset($validated['bukti_kelulusan']);

        $this->roleChangeService->submit($user, $validated);

        return back()->with('status', 'Pengajuan perubahan role ke alumni berhasil dikirim. Admin akan meninjau data Anda.');
    }
}
