<?php

// app/Http/Controllers/Admin/QrManageController.php

namespace App\Modules\Fast\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use App\Models\SuratQrCode;
use App\Modules\Fast\Services\Shared\SuratHistoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QrManageController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();

        // Ambil dari tabel surat_qr_codes jika ada, fallback ke surats.qr_token
        $query = Surat::query()
            ->select(['id', 'jenis_surat_id', 'pemohon_id', 'subject_user_id', 'type', 'nomor_surat', 'status', 'qr_token', 'created_at'])
            ->with([
                'pemohon:id,name,nomor_induk',
                'subjectUser:id,name,nomor_induk',
                'jenisSurat:id,nama',
                'qrCode',
            ])
            ->whereNotNull('qr_token')
            ->latest();
        // $query = Surat::query()
        //         ->select(['id', 'jenis_surat_id', 'pemohon_id', 'nomor_surat', 'status', 'qr_token', 'created_at'])
        //         ->with(['pemohon:id,name,nomor_induk', 'jenisSurat:id,nama', 'qrCode:id,surat_id,status,revoked_at'])
        //         ->whereNotNull('qr_token')
        //         ->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('nomor_surat', 'like', "%{$search}%")
                    ->orWhere(function ($typeQuery) use ($search): void {
                        $typeQuery
                            ->where('type', 'pengajuan')
                            ->whereHas('pemohon', function ($userQuery) use ($search): void {
                                $userQuery
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('nomor_induk', 'like', "%{$search}%");
                            });
                    })
                    ->orWhere(function ($typeQuery) use ($search): void {
                        $typeQuery
                            ->where('type', 'surat_keluar')
                            ->whereHas('subjectUser', function ($userQuery) use ($search): void {
                                $userQuery
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('nomor_induk', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if ($status !== '') {
            $query->where(function ($q) use ($status) {
                if ($status === SuratQrCode::STATUS_ACTIVE) {
                    $q->whereDoesntHave('qrCode')
                        ->orWhereHas('qrCode', fn ($qr) => $qr->where('status', SuratQrCode::STATUS_ACTIVE));

                    return;
                }

                $q->whereHas('qrCode', fn ($qr) => $qr->where('status', $status));
            });
        }

        $surats = $query->paginate(15)
            ->through(fn (Surat $s) => [
                'id' => $s->id,
                'type' => $s->type,
                'nomor_surat' => $s->nomor_surat,
                'status' => $s->status,
                'qr_token' => $s->qr_token,
                'created_at' => $s->created_at?->toISOString(),
                'subject' => $s->serializeSubjectIdentity(),
                'jenisSurat' => ['nama' => $s->jenisSurat?->nama],
                // Cek dari tabel surat_qr_codes
                'qr_status' => $s->qrCode?->status ?? 'active',
                'qr_revoked_at' => $s->qrCode?->revoked_at?->toISOString(),
            ])
            ->withQueryString();

        return Inertia::render('admin/qr/Index', [
            'surats' => $surats,
            'filters' => compact('search', 'status'),
        ]);
    }

    public function revoke(Request $request, int $suratId): RedirectResponse
    {
        $request->validate([
            'alasan' => 'nullable|string|max:255',
        ]);

        $surat = Surat::findOrFail($suratId);

        // Update di tabel surat_qr_codes jika ada
        $qrCode = SuratQrCode::where('surat_id', $suratId)
            ->where('status', 'active')
            ->first();

        if ($qrCode) {
            $qrCode->revoke(auth()->id(), $request->alasan ?? '');
        }

        // Catat history
        SuratHistoryService::qrRevoked($suratId, $request->alasan ?? 'Dicabut oleh admin');

        return back()->with('success', 'QR Code berhasil dicabut.');
    }
}
