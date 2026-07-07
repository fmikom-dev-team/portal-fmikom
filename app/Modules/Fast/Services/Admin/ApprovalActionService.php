<?php

namespace App\Modules\Fast\Services\Admin;

use App\Models\Surat;
use App\Models\SuratApprovalFlow;
use App\Modules\Fast\Workflow\Approvals\FastApprovalWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApprovalActionService
{
    public function __construct(
        protected FastApprovalWorkflowService $workflow,
    ) {}

    public function approve(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $surat = Surat::query()->findOrFail($id);

        $this->workflow->approve(
            $surat,
            $this->resolveActorRole($request, $user),
            $user,
        );

        return back()->with('success', 'Pengajuan berhasil disetujui.');
    }

    /**
     * @param  array<int, Surat>  $surats
     */
    public function bulkApprove(Request $request, array $surats): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $role = $this->resolveActorRole($request, $user);
        $count = 0;

        DB::transaction(function () use ($surats, $role, $user, &$count): void {
            foreach ($surats as $surat) {
                $this->workflow->approve($surat, $role, $user);
                $count++;
            }
        });

        return back()->with(
            'success',
            $count > 0
                ? "{$count} pengajuan berhasil diproses sekaligus."
                : 'Tidak ada pengajuan yang diproses.',
        );
    }

    public function approveAdmin(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $surat = Surat::query()->findOrFail($id);

        $this->workflow->approve($surat, FastApprovalWorkflowService::ROLE_ADMIN, $user);

        return back()->with('success', 'Pengajuan berhasil divalidasi.');
    }

    /**
     * @param  array<int, Surat>  $surats
     */
    public function bulkApproveAdmin(Request $request, array $surats): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $count = 0;

        DB::transaction(function () use ($surats, $user, &$count): void {
            foreach ($surats as $surat) {
                $this->workflow->approve($surat, FastApprovalWorkflowService::ROLE_ADMIN, $user);
                $count++;
            }
        });

        return back()->with(
            'success',
            $count > 0
                ? "{$count} pengajuan berhasil divalidasi sekaligus."
                : 'Tidak ada pengajuan yang diproses.',
        );
    }

    public function saveNote(Request $request, int $id): RedirectResponse
    {
        $request->validate(['catatan' => ['required', 'string', 'max:1000']]);

        $user = $request->user();
        abort_if($user === null, 403);

        $surat = Surat::query()->findOrFail($id);

        $surat->approvalFlows()->create([
            'approver_id' => $user->id,
            'urutan' => 0,
            'role' => $this->resolveActorRole($request, $user),
            'status' => SuratApprovalFlow::STATUS_NOTE,
            'keterangan' => 'Catatan',
            'catatan' => $request->string('catatan')->toString(),
            'tanggal_aksi' => now(),
        ]);

        return back()->with('success', 'Catatan berhasil ditambahkan.');
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string'],
        ]);

        $user = $request->user();
        abort_if($user === null, 403);

        $surat = Surat::query()->findOrFail($id);

        $this->workflow->requestRevision(
            $surat,
            $this->resolveActorRole($request, $user),
            $user,
            $request->string('reason')->toString(),
        );

        return back()->with('success', 'Pengajuan berhasil dikembalikan untuk revisi.');
    }

    public function rejectAdmin(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string'],
        ]);

        $user = $request->user();
        abort_if($user === null, 403);

        $surat = Surat::query()->findOrFail($id);

        $this->workflow->rejectAdmin(
            $surat,
            $user,
            $request->string('reason')->toString(),
        );

        return back()->with('success', 'Pengajuan berhasil ditolak.');
    }

    public function finalReject(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string'],
        ]);

        $user = $request->user();
        abort_if($user === null, 403);

        $surat = Surat::query()->findOrFail($id);
        $normalizedRole = $this->resolveActorRole($request, $user);

        $this->workflow->finalReject(
            $surat,
            $normalizedRole,
            $user,
            $request->string('reason')->toString(),
        );

        return back()->with('success', 'Pengajuan berhasil ditolak.');
    }

    protected function normalizeRole(?string $slug, ?string $name): string
    {
        $normalizedSlug = Str::slug((string) $slug);
        $normalizedName = Str::slug((string) $name);

        if (Str::contains($normalizedSlug, 'kaprodi') || Str::contains($normalizedName, 'kaprodi')) {
            return FastApprovalWorkflowService::ROLE_KAPRODI;
        }

        return FastApprovalWorkflowService::ROLE_DEKAN;
    }

    protected function resolveActorRole(Request $request, $user): string
    {
        $resolvedRole = $request->attributes->get('resolved_role');

        if (is_string($resolvedRole) && filled($resolvedRole)) {
            return $this->normalizeRole($resolvedRole, $resolvedRole);
        }

        return $this->normalizeRole($user->userTypeSlug(), $user->roleDisplayName());
    }
}
