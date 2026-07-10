<?php

namespace App\Policies;

use App\Models\Surat;
use App\Models\User;
use App\Modules\Fast\Support\FastPermissionCatalog;

class FastSuratPolicy
{
    public function viewAny(User $user): bool
    {
        if ($this->isAdminRole($user)) {
            return FastPermissionCatalog::hasAny($user, [
                'fast.admin.dashboard.view',
                'fast.admin.queue.view',
                'fast.admin.history.view',
                'fast.admin.archive.view',
                'fast.admin.surat.view',
            ]);
        }

        if ($this->isApprovalRole($user)) {
            return FastPermissionCatalog::hasAny($user, [
                'fast.admin.dashboard.view',
                'fast.admin.queue.view',
                'fast.admin.history.view',
                'fast.admin.archive.view',
                'fast.admin.surat.view',
                'fast.approval.dashboard.view',
                'fast.approval.queue.view',
                'fast.approval.archive.view',
            ]);
        }

        if ($this->isPemohonRole($user)) {
            return FastPermissionCatalog::hasAny($user, [
                'fast.dashboard.view',
                'fast.submission.view',
            ]);
        }

        return false;
    }

    public function view(User $user, Surat $surat): bool
    {
        if ($this->isAdminRole($user)) {
            return FastPermissionCatalog::has($user, 'fast.admin.surat.view');
        }

        if ($this->isApprovalRole($user)) {
            return FastPermissionCatalog::hasAny($user, [
                'fast.approval.surat.view',
                'fast.admin.surat.view',
            ]);
        }

        if ($this->isPemohonRole($user)) {
            return FastPermissionCatalog::has($user, 'fast.submission.view')
                && (int) $surat->pemohon_id === (int) $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($this->isAdminRole($user)) {
            return FastPermissionCatalog::has($user, 'fast.admin.surat.create');
        }

        if ($this->isPemohonRole($user)) {
            return FastPermissionCatalog::has($user, 'fast.submission.create');
        }

        return false;
    }

    public function update(User $user, Surat $surat): bool
    {
        return $this->isAdminRole($user)
            && FastPermissionCatalog::has($user, 'fast.admin.surat.update')
            && $surat->canBeEditedByAdmin();
    }

    public function approve(User $user, Surat $surat): bool
    {
        if ($this->isAdminRole($user) && FastPermissionCatalog::has($user, 'fast.admin.surat.approve')) {
            return $surat->canBeApprovedByRole('admin');
        }

        $role = $this->approvalRole($user);

        return $this->isApprovalRole($user)
            && FastPermissionCatalog::has($user, 'fast.approval.surat.approve')
            && in_array($role, ['kaprodi', 'dekan'], true)
            && $surat->canBeApprovedByRole($role);
    }

    public function reject(User $user, Surat $surat): bool
    {
        if ($this->isAdminRole($user) && FastPermissionCatalog::has($user, 'fast.admin.surat.approve')) {
            return $surat->canBeRejectedByRole('admin');
        }

        $role = $this->approvalRole($user);

        return $this->isApprovalRole($user)
            && FastPermissionCatalog::has($user, 'fast.approval.surat.reject')
            && in_array($role, ['kaprodi', 'dekan'], true)
            && $surat->canBeRejectedByRole($role);
    }

    public function finalReject(User $user, Surat $surat): bool
    {
        $role = $this->approvalRole($user);

        return $this->isApprovalRole($user)
            && FastPermissionCatalog::has($user, 'fast.approval.surat.reject')
            && in_array($role, ['kaprodi', 'dekan'], true)
            && $surat->canBeFinalRejectedByRole($role);
    }

    public function cancel(User $user, Surat $surat): bool
    {
        return $this->isPemohonRole($user)
            && FastPermissionCatalog::has($user, 'fast.submission.cancel')
            && (int) $surat->pemohon_id === (int) $user->id
            && $surat->status === Surat::STATUS_PENDING;
    }

    public function previewAttachment(User $user, Surat $surat): bool
    {
        return $this->view($user, $surat);
    }

    public function download(User $user, Surat $surat): bool
    {
        if ($surat->canViewFinalDocumentPreview()) {
            if ($this->isAdminRole($user)) {
                return FastPermissionCatalog::hasAny($user, [
                    'fast.document.download',
                    'fast.admin.surat.view',
                ]);
            }

            if ($this->isApprovalRole($user)) {
                return FastPermissionCatalog::hasAny($user, [
                    'fast.document.download',
                    'fast.approval.surat.view',
                ]);
            }

            if ($this->isPemohonRole($user)) {
                return FastPermissionCatalog::hasAny($user, [
                    'fast.document.download',
                    'fast.submission.view',
                ]) && (int) $surat->pemohon_id === (int) $user->id;
            }

            return false;
        }

        return $this->view($user, $surat);
    }

    public function generate(User $user, Surat $surat): bool
    {
        return $this->isAdminRole($user)
            && FastPermissionCatalog::hasAny($user, [
                'fast.admin.surat.create',
                'fast.admin.surat.update',
            ]);
    }

    protected function approvalRole(User $user): ?string
    {
        $role = $this->roleSlug($user);

        return in_array($role, ['kaprodi', 'dekan', 'admin'], true) ? $role : null;
    }

    protected function roleSlug(User $user): string
    {
        return strtolower((string) ($user->getResolvedRoleSlug() ?? $user->getGlobalRoleSlug() ?? ''));
    }

    protected function isAdminRole(User $user): bool
    {
        return in_array($this->roleSlug($user), ['admin', 'super-admin'], true);
    }

    protected function isApprovalRole(User $user): bool
    {
        return in_array($this->roleSlug($user), ['kaprodi', 'dekan', 'super-admin'], true);
    }

    protected function isPemohonRole(User $user): bool
    {
        return in_array($this->roleSlug($user), ['mahasiswa', 'dosen', 'super-admin'], true);
    }
}
