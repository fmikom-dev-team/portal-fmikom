<?php
// app/Http/Controllers/Admin/LetterIndexController.php

namespace App\Modules\Fast\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuratCategory;
use App\Models\Surat;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LetterIndexController extends Controller
{
    public function index(Request $request): Response
    {
        $search       = $request->string('search')->trim()->toString();
        $status = $request->has('status')
            ? $request->string('status')->toString()
            : Surat::STATUS_PENDING;
        $categoryId   = $request->integer('category_id');
        $baseStatuses = [
            Surat::STATUS_PENDING,
            Surat::STATUS_VALIDATED_ADMIN,
            Surat::STATUS_REVISION_REQUESTED,
            Surat::STATUS_REJECTED_ADMIN,
        ];

        $baseQuery = Surat::query()
            ->with(['pemohon', 'jenisSurat.category', 'dataEntries'])
            ->where('type', 'pengajuan')
            ->whereIn('status', $baseStatuses)
            ->latest();

        $query = clone $baseQuery;

        if ($status === 'pending') {
            $query->where('status', Surat::STATUS_PENDING);
        } elseif ($status === 'rejected_admin') {
            $query->where('status', Surat::STATUS_REJECTED_ADMIN);
        } elseif ($status === 'all') {
            // show all statuses in the current base scope
        } elseif ($status !== '') {
            $query->whereRaw('1 = 0');
        }

        if ($categoryId > 0) {
            $query->whereHas('jenisSurat', function ($jenisQuery) use ($categoryId): void {
                $jenisQuery->where('category_id', $categoryId);
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->whereHas('pemohon', function ($pemohon) use ($search): void {
                    $pemohon->where('name', 'like', "%{$search}%")
                        ->orWhere('nim_nip', 'like', "%{$search}%");
                });
            });
        }

        $surats = $query->paginate(15)
            ->through(fn (Surat $surat) => [
                'id'               => $surat->id,
                'nomor_surat'      => $surat->nomor_surat,
                'status'           => $surat->status,
                'can_approve'      => $surat->canBeValidatedByAdmin(),
                'revision_label'   => $surat->status === Surat::STATUS_REVISION_REQUESTED
                    ? match ($surat->finalApprovalRoleSlug()) {
                        'kaprodi' => 'Dikembalikan Kaprodi',
                        'dekan' => 'Dikembalikan Dekan',
                        default => 'Dikembalikan untuk Revisi',
                    }
                    : null,
                'can_edit'         => $surat->canBeEditedByAdmin(),
                'keperluan'        => $surat->keperluan,
                'tanggal_pengajuan' => $surat->tanggal_pengajuan?->toISOString(),
                'tanggal_selesai'  => $surat->tanggal_selesai?->toISOString(),
                'created_at'       => $surat->created_at?->toISOString(),
                'pemohon' => [
                    'name' => $surat->pemohon?->name,
                    'nim'  => $surat->pemohon?->nim_nip ?? $surat->pemohon?->nomor_induk,
                ],
                'jenisSurat' => [
                    'id'       => $surat->jenisSurat?->id,
                    'nama'     => $surat->jenisSurat?->nama,
                    'category' => ['nama' => $surat->jenisSurat?->category?->nama],
                ],
            ])
            ->withQueryString();

        return Inertia::render('admin/letters/Index', [
            'surats'     => $surats,
            'filters'    => [
                'search' => $search,
                'status' => $status,
                'category_id' => $categoryId > 0 ? (string) $categoryId : '',
            ],
            'categories' => SuratCategory::query()
                ->orderBy('urutan')
                ->orderBy('nama')
                ->get(['id', 'nama'])
                ->map(fn (SuratCategory $category): array => [
                    'id' => $category->id,
                    'nama' => $category->nama,
                ])
                ->values(),
            'summary'    => [
                'total'    => (clone $baseQuery)->count(),
                'pending'  => (clone $baseQuery)->where('status', Surat::STATUS_PENDING)->count(),
                'rejected' => (clone $baseQuery)->where('status', Surat::STATUS_REJECTED_ADMIN)->count(),
            ],
        ]);
    }
}
