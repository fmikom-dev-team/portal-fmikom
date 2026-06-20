export type FastProgressApprovalRole = {
    slug?: string | null;
    nama?: string | null;
} | null | undefined;

export type FastProgressItem = {
    status: string;
    approvalRole?: FastProgressApprovalRole;
    requiresFinalApproval?: boolean;
};

export type ProgressStep = {
    key: string;
    short: string;
};

function approvalRoleSlug(item: FastProgressItem): string | null {
    return (item.approvalRole?.slug ?? '')
        .toString()
        .trim()
        .toLowerCase()
        .replace(/\s+/g, '_') || null;
}

export function approvalRoleLabel(item: FastProgressItem): string {
    const slug = approvalRoleSlug(item);
    if (slug === 'kaprodi') return 'Kaprodi';
    if (slug === 'dekan') return 'Dekan';
    return item.approvalRole?.nama ?? 'Approval';
}

export function hasFinalApproval(item: FastProgressItem): boolean {
    return !!item.requiresFinalApproval && approvalRoleSlug(item) !== null;
}

export function progressSteps(item: FastProgressItem): ProgressStep[] {
    if (!hasFinalApproval(item)) {
        return [
            { key: 'pending', short: 'Diajukan' },
            { key: 'validated_admin', short: 'Validasi Admin' },
            { key: 'finished', short: 'Selesai' },
        ];
    }

    return [
        { key: 'pending', short: 'Diajukan' },
        { key: 'validated_admin', short: 'Validasi Admin' },
        { key: 'final_approval', short: approvalRoleLabel(item) },
        { key: 'finished', short: 'Selesai' },
    ];
}

export function getProgressStepIndex(item: FastProgressItem): number {
    if (item.status === 'cancelled') return 0;
    if (item.status === 'pending') return 0;
    if (item.status === 'validated_admin') return 1;
    if (item.status === 'revision_requested') return 1;
    if (item.status === 'approved_kaprodi' || item.status === 'approved_dekan')
        return hasFinalApproval(item) ? 2 : 2;
    if (item.status === 'finished') return hasFinalApproval(item) ? 3 : 2;
    if (item.status === 'rejected_admin') return 1;
    if (item.status === 'rejected_approver') return hasFinalApproval(item) ? 2 : 2;
    return 0;
}

export function getProgressPercent(item: FastProgressItem): number {
    const steps = progressSteps(item).length;
    const current = getProgressStepIndex(item);
    if (current < 0) return 0;
    return Math.min(100, Math.round(((current + 1) / steps) * 100));
}

export function isProgressStepFilled(
    item: FastProgressItem,
    stepIndex: number,
): boolean {
    const current = getProgressStepIndex(item);
    const total = progressSteps(item).length;
    return current > stepIndex || (current === stepIndex && stepIndex === total - 1);
}

export function isProgressStepActive(
    item: FastProgressItem,
    stepIndex: number,
): boolean {
    const current = getProgressStepIndex(item);
    const total = progressSteps(item).length;
    return current === stepIndex && stepIndex !== total - 1;
}
