<?php

namespace App\Modules\Fast\Services\Admin;

use App\Models\Surat;
use App\Models\SuratApprovalFlow;
use App\Modules\Fast\Workflow\Approvals\FastApprovalWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApprovalActionService
{
    public function __construct(
        protected FastApprovalWorkflowService $workflow,
    ) {
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $surat = Surat::query()->findOrFail($id);

        $this->workflow->approve(
            $surat,
            $this->normalizeRole($user->userTypeSlug(), $user->roleDisplayName()),
            $user,
        );

        return back()->with('success', 'Pengajuan berhasil disetujui.');
    }

    public function approveAdmin(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $surat = Surat::query()->findOrFail($id);

        $this->workflow->approve($surat, FastApprovalWorkflowService::ROLE_ADMIN, $user);

        return back()->with('success', 'Pengajuan berhasil divalidasi.');
    }

    public function saveNote(Request $request, int $id): RedirectResponse
    {
        $request->validate(['catatan' => ['required', 'string', 'max:1000']]);

        $user = $request->user();
        abort_if($user === null, 403);

        $surat = Surat::query()->findOrFail($id);

        $surat->approvalFlows()->create([
            'approver_id'  => $user->id,
            'urutan'       => 0,
            'role'         => $this->normalizeRole($user->userTypeSlug(), $user->roleDisplayName()),
            'status'       => SuratApprovalFlow::STATUS_NOTE,
            'keterangan'   => 'Catatan',
            'catatan'      => $request->string('catatan')->toString(),
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
            $this->normalizeRole($user->userTypeSlug(), $user->roleDisplayName()),
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
        $normalizedRole = $this->normalizeRole($user->userTypeSlug(), $user->roleDisplayName());

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
}
