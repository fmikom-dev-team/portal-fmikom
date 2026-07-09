<?php

// app/Http/Controllers/Admin/QrManageController.php

namespace App\Modules\Fast\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use App\Models\SuratQrCode;
use App\Modules\Fast\Support\FastUserIdentitySearch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QrManageController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Surat::class);

        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();

        $query = Surat::query()
            ->select(['id', 'jenis_surat_id', 'pemohon_id', 'type', 'nomor_surat', 'status', 'qr_token', 'created_at'])
            ->with([
                'pemohon:id,name,nomor_induk',
                'subjectUser:id,name,nomor_induk',
                'jenisSurat:id,nama',
                'qrCode',
            ])
            ->whereNotNull('qr_token')
            ->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('nomor_surat', 'like', "%{$search}%")
                    ->orWhere(function ($typeQuery) use ($search): void {
                        $typeQuery
                            ->where('type', 'pengajuan')
                            ->whereHas('pemohon', function ($userQuery) use ($search): void {
                                FastUserIdentitySearch::apply($userQuery, $search);
                            });
                    })
                    ->orWhere(function ($typeQuery) use ($search): void {
                        $typeQuery
                            ->where('type', 'surat_keluar')
                            ->whereHas('subjectUser', function ($userQuery) use ($search): void {
                                FastUserIdentitySearch::apply($userQuery, $search);
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
                'letter_mode' => $s->resolvedLetterMode(),
                'letter_mode_label' => $s->letterModeLabel(),
                'is_institution' => $s->resolvedLetterMode() === 'institution',
                'jenisSurat' => ['nama' => $s->jenisSurat?->nama],
                'qr_status' => $s->qrCode?->status ?? 'active',
            ])
            ->withQueryString();

        return Inertia::render('admin/qr/Index', [
            'surats' => $surats,
            'filters' => compact('search', 'status'),
        ]);
    }
}
