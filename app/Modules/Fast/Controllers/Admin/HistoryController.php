<?php

namespace App\Modules\Fast\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use App\Models\SuratCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HistoryController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->has('status')
            ? $request->string('status')->toString()
            : 'pending';
        $categoryId = $request->integer('category_id');
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();

        $query = Surat::query()
            ->with(['pemohon:id,name,nim_nip', 'jenisSurat:id,nama,category_id'])
            ->where('type', 'surat_keluar')
            ->where('status', '!=', Surat::STATUS_FINISHED)
            ->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('nomor_surat', 'like', "%{$search}%")
                    ->orWhere('keperluan', 'like', "%{$search}%")
                    ->orWhereHas('pemohon', function ($pemohon) use ($search): void {
                        $pemohon->where('name', 'like', "%{$search}%")
                            ->orWhere('nim_nip', 'like', "%{$search}%");
                    });
            });
        }

        if ($status === 'pending') {
            $query->whereIn('status', [
                Surat::STATUS_PENDING,
                Surat::STATUS_VALIDATED_ADMIN,
            ]);
        } elseif (in_array($status, ['revisi', 'rejected'], true)) {
            $query->whereIn('status', [
                Surat::STATUS_REVISION_REQUESTED,
                Surat::STATUS_REJECTED_ADMIN,
                Surat::STATUS_REJECTED_APPROVER,
            ]);
        } elseif ($status !== '') {
            $query->where('status', $status);
        }

        if ($categoryId > 0) {
            $query->whereHas('jenisSurat', fn ($q) => $q->where('category_id', $categoryId));
        }

        if ($dateFrom !== '') {
            $query->whereDate('tanggal_pengajuan', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('tanggal_pengajuan', '<=', $dateTo);
        }

        $surats = $query->paginate(20)
            ->through(fn (Surat $surat): array => [
                'id' => $surat->id,
                'nomor_surat' => $surat->nomor_surat,
                'status' => $surat->status,
                'keperluan' => $surat->keperluan,
                'tanggal_pengajuan' => $surat->tanggal_pengajuan?->toISOString(),
                'tanggal_selesai' => $surat->tanggal_selesai?->toISOString(),
                'pemohon' => ['name' => $surat->pemohon?->name],
                'jenisSurat' => ['nama' => $surat->jenisSurat?->nama],
            ])
            ->withQueryString();

        return Inertia::render('admin/history/Index', [
            'surats' => $surats,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'category_id' => $categoryId > 0 ? (string) $categoryId : '',
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'categories' => SuratCategory::orderBy('urutan')->orderBy('nama')->get(['id', 'nama']),
        ]);
    }
}
