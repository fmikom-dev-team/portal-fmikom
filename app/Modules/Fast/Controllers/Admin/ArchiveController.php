<?php

// app/Http/Controllers/Admin/ArchiveController.php

namespace App\Modules\Fast\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use App\Models\SuratCategory;
use App\Modules\Fast\Support\FastUserIdentitySearch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArchiveController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Surat::class);

        $search = $request->string('search')->trim()->toString();
        $categoryId = $request->integer('category_id');
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();

        $query = Surat::query()
            ->with([
                'pemohon:id,name,nomor_induk',
                'subjectUser:id,name,nomor_induk',
                'jenisSurat:id,nama,category_id',
                'jenisSurat.category:id,nama',
                'validatedByAdmin:id,name',
                'approvedBy:id,name',
                'approvalFlows.approver:id,name',
            ])
            ->whereIn('type', ['pengajuan', 'surat_keluar'])
            ->where('status', 'finished')
            ->whereNotNull('generated_file_path')
            ->latest('tanggal_selesai');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('nomor_surat', 'like', "%{$search}%")
                    ->orWhere('keperluan', 'like', "%{$search}%")
                    ->orWhereHas('jenisSurat', function ($jenisQuery) use ($search): void {
                        $jenisQuery
                            ->where('nama', 'like', "%{$search}%")
                            ->orWhereHas('category', function ($categoryQuery) use ($search): void {
                                $categoryQuery->where('nama', 'like', "%{$search}%");
                            });
                    })
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
                    })
                    ->orWhereHas('approvedBy', function ($userQuery) use ($search): void {
                        FastUserIdentitySearch::apply($userQuery, $search);
                    })
                    ->orWhereHas('validatedByAdmin', function ($userQuery) use ($search): void {
                        FastUserIdentitySearch::apply($userQuery, $search);
                    })
                    ->orWhereHas('approvalFlows.approver', function ($userQuery) use ($search): void {
                        FastUserIdentitySearch::apply($userQuery, $search);
                    });
            });
        }

        if ($categoryId > 0) {
            $query->whereHas('jenisSurat', fn ($q) => $q->where('category_id', $categoryId));
        }

        if ($dateFrom !== '') {
            $query->whereDate('tanggal_selesai', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('tanggal_selesai', '<=', $dateTo);
        }

        $surats = $query->paginate(15)
            ->through(function (Surat $s): array {
                $latestFinalApproval = $s->approvalFlows
                    ->where('status', 'approved')
                    ->whereIn('role', ['kaprodi', 'dekan'])
                    ->sortByDesc('urutan')
                    ->first();

                return [
                    'id' => $s->id,
                    'type' => $s->type,
                    'nomor_surat' => $s->nomor_surat,
                    'keperluan' => $s->keperluan,
                    'tanggal_selesai' => $s->tanggal_selesai?->toISOString(),
                    'generated_file_path' => $s->generated_file_path,
                    'subject' => $s->serializeSubjectIdentity(),
                    'letter_mode' => $s->resolvedLetterMode(),
                    'letter_mode_label' => $s->letterModeLabel(),
                    'is_institution' => $s->resolvedLetterMode() === 'institution',
                    'jenisSurat' => [
                        'nama' => $s->jenisSurat?->nama,
                        'category' => ['nama' => $s->jenisSurat?->category?->nama],
                    ],
                    'validator' => [
                        'name' => $s->approvedBy->name
                            ?? $latestFinalApproval?->approver->name
                            ?? $s->validatedByAdmin?->name,
                    ],
                    'download_url' => $s->generated_file_path
                        ? route('documents.surat.pdf', $s->id, absolute: false)
                        : null,
                ];
            })
            ->withQueryString();

        return Inertia::render('Modules/Fast/Admin/archive/Index', [
            'surats' => $surats,
            'filters' => [
                'search' => $search,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'category_id' => $categoryId > 0 ? (string) $categoryId : '',
            ],
            'categories' => SuratCategory::orderBy('urutan')->orderBy('nama')->get(['id', 'nama']),
        ]);
    }
}
