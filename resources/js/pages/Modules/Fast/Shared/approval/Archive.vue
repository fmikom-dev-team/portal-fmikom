<script setup lang="ts">
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';
import {
    Search,
    FileText,
    XCircle,
    RefreshCcw,
    CheckCircle2,
    Clock3,
    Eye,
    ExternalLink,
    Calendar,
} from 'lucide-vue-next';
type DetailLampiran = {
    id: number;
    name: string;
    url: string;
    type?: string | null;
};
type SuratItem = {
    id: number;
    status: string;
    tanggal_pengajuan?: string | null;
    created_at?: string | null;
    pemohon?: { name?: string | null; nim?: string | null } | null;
    jenisSurat?: { id?: number | null; nama?: string | null } | null;
    nomor_surat?: string | null;
};
type PaginationLink = { url: string | null; label: string; active: boolean };
type PaginatedSurats = {
    data: SuratItem[];
    links: PaginationLink[];
    from?: number | null;
    to?: number | null;
    total: number;
};
type StatusOption = { value: string; label: string };
type CategoryOption = { id: number; nama: string };
const props = defineProps<{
    role: { name?: string | null; slug?: string | null };
    surats: PaginatedSurats;
    filters: { status?: string; search?: string; category_id?: string };
    statusOptions: StatusOption[];
    categories: CategoryOption[];
}>();
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const categoryId = ref(props.filters.category_id ?? '');
const toastMessage = ref('');
const normalizedRole = computed(() =>
    String(props.role.slug ?? props.role.name ?? '')
        .toLowerCase()
        .includes('kaprodi')
        ? 'kaprodi'
        : 'dekan',
);
const basePath = computed(() => `/${normalizedRole.value}`);
function applyFilters() {
    const params: Record<string, string> = {};
    if (search.value) params.search = search.value;
    if (status.value) params.status = status.value;
    if (categoryId.value) params.category_id = categoryId.value;
    router.get(`${basePath.value}/arsip`, params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}
function resetFilters() {
    search.value = '';
    status.value = '';
    categoryId.value = '';
    applyFilters();
}
function formatDate(date?: string | null) {
    if (!date) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date));
}
function statusLabel(s: string) {
    const lowered = String(s ?? '')
        .trim()
        .toLowerCase();
    if (lowered === 'approved_kaprodi') return 'Disetujui Kaprodi';
    if (lowered === 'approved_dekan') return 'Disetujui Dekan';
    if (lowered === 'revision_requested') return 'Revisi Diminta';
    if (lowered === 'rejected_approver') return 'Ditolak Final';
    if (lowered === 'finished') return 'Disetujui';
    return lowered;
}
function statusBadgeClass(s: string) {
    const lowered = String(s ?? '')
        .trim()
        .toLowerCase();
    if (
        lowered === 'approved_kaprodi' ||
        lowered === 'approved_dekan' ||
        lowered === 'finished'
    )
        return 'bg-sky-100 text-sky-700';
    if (lowered === 'revision_requested') return 'bg-amber-100 text-amber-700';
    if (lowered === 'rejected_approver') return 'bg-red-100 text-red-700';
    return 'bg-slate-100 text-slate-600';
}
function statusIcon(s: string) {
    const lowered = String(s ?? '')
        .trim()
        .toLowerCase();
    if (
        lowered === 'approved_kaprodi' ||
        lowered === 'approved_dekan' ||
        lowered === 'finished'
    )
        return CheckCircle2;
    if (lowered === 'revision_requested') return RefreshCcw;
    if (lowered === 'rejected_approver') return XCircle;
    return Clock3;
}
function statusColor(s: string) {
    const lowered = String(s ?? '')
        .trim()
        .toLowerCase();
    if (lowered === 'finished')
        return {
            bg: 'bg-blue-50',
            border: 'border-blue-200',
            text: 'text-blue-600',
            line: 'bg-blue-300',
        };
    if (lowered === 'rejected_approver')
        return {
            bg: 'bg-red-50',
            border: 'border-red-200',
            text: 'text-red-600',
            line: 'bg-red-300',
        };
    if (lowered === 'approved_kaprodi' || lowered === 'approved_dekan')
        return {
            bg: 'bg-sky-50',
            border: 'border-sky-200',
            text: 'text-sky-600',
            line: 'bg-sky-300',
        };
    if (lowered === 'revision_requested')
        return {
            bg: 'bg-amber-50',
            border: 'border-amber-200',
            text: 'text-amber-600',
            line: 'bg-amber-300',
        };
    return {
        bg: 'bg-slate-50',
        border: 'border-slate-200',
        text: 'text-slate-600',
        line: 'bg-slate-300',
    };
}
async function openDetail(id: number) {
    router.visit(`${basePath.value}/surat/${id}/detail?from=arsip`);
}
</script>
<template>
    <AdminLayout
        :title="`Arsip ${role.name || 'Approval'}`"
        subtitle="Riwayat pengajuan yang telah diproses"
        active-menu="approval.arsip"
        :breadcrumbs="[
            { label: 'Dashboard', href: `${basePath}/dashboard` },
            { label: 'Arsip' },
        ]"
    >
        <Head :title="`Arsip ${role.name || 'Approval'}`" />
        <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <div class="relative flex-1">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                    />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama atau NIM pemohon..."
                        class="h-11 w-full rounded-xl border border-slate-200 bg-white pr-4 pl-10 text-sm outline-none transition-colors focus:border-blue-400"
                        @keyup.enter="applyFilters"
                    />
                </div>
                <div class="w-full lg:w-56">
                    <select
                        v-model="categoryId"
                        class="h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-600 outline-none transition-colors focus:border-blue-400"
                    >
                        <option value="">Semua Kategori</option>
                        <option
                            v-for="category in categories"
                            :key="category.id"
                            :value="String(category.id)"
                        >
                            {{ category.nama }}
                        </option>
                    </select>
                </div>
                <button
                    type="button"
                    class="fast-btn fast-btn-primary h-11 px-5 text-sm"
                    @click="applyFilters"
                >
                    Terapkan
                </button>
                <button
                    v-if="search || status || categoryId"
                    type="button"
                    class="fast-btn fast-btn-outline h-11 px-5 text-sm font-medium text-slate-500"
                    @click="resetFilters"
                >
                    Reset
                </button>
            </div>
        </div>
        <!-- Filter pills -->
        <div class="mb-5 flex flex-wrap items-center gap-2">
            <button
                type="button"
                class="rounded-full border px-3 py-1.5 text-xs font-medium transition-colors"
                :class="
                    !status
                        ? 'border-blue-500 bg-blue-500 text-white shadow-sm'
                        : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300'
                "
                @click="
                    status = '';
                    applyFilters();
                "
            >
                Semua
            </button>
            <button
                type="button"
                class="rounded-full border px-3 py-1.5 text-xs font-medium transition-colors"
                :class="
                    status === 'approved'
                        ? 'border-blue-500 bg-blue-500 text-white shadow-sm'
                        : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300'
                "
                @click="
                    status = 'approved';
                    applyFilters();
                "
            >
                Disetujui
            </button>
            <button
                type="button"
                class="rounded-full border px-3 py-1.5 text-xs font-medium transition-colors"
                :class="
                    status === 'revision_requested'
                        ? 'border-amber-500 bg-amber-500 text-white shadow-sm'
                        : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300'
                "
                @click="
                    status = 'revision_requested';
                    applyFilters();
                "
            >
                Revisi
            </button>
            <button
                type="button"
                class="rounded-full border px-3 py-1.5 text-xs font-medium transition-colors"
                :class="
                    status === 'rejected_approver'
                        ? 'border-red-500 bg-red-500 text-white shadow-sm'
                        : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300'
                "
                @click="
                    status = 'rejected_approver';
                    applyFilters();
                "
            >
                Ditolak
            </button>
        </div>
        <!-- Timeline cards -->
        <div class="relative pl-8">
            <!-- Vertical line -->
            <div
                class="absolute top-3 bottom-3 left-[19px] w-px bg-slate-200"
            />
            <div
                v-if="surats.data.length === 0"
                class="flex flex-col items-center gap-2 py-12 text-center"
            >
                <Calendar class="size-8 text-slate-300" />
                <p class="text-sm text-slate-400">Belum ada data arsip.</p>
            </div>
            <div
                v-for="item in surats.data"
                :key="item.id"
                class="relative mb-4"
            >
                <!-- Timeline dot -->
                <div
                    class="absolute top-0 -left-8 z-10 grid size-10 place-items-center rounded-full border-2 bg-white"
                    :class="statusColor(item.status).border"
                >
                    <component
                        :is="statusIcon(item.status)"
                        :class="['size-5', statusColor(item.status).text]"
                    />
                </div>
                <!-- Card -->
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 transition-all hover:shadow-md"
                    :class="[
                    item.status === 'finished'
                            ? 'hover:border-sky-300'
                            : item.status === 'rejected_approver'
                              ? 'hover:border-red-300'
                              : item.status.startsWith('approved')
                                ? 'hover:border-sky-300'
                                : 'hover:border-amber-300',
                    ]"
                >
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                        <!-- Info -->
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-bold text-slate-900">
                                    {{ item.jenisSurat?.nama ?? '-' }}
                                </p>
                                <span
                                    class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    :class="statusBadgeClass(item.status)"
                                >
                                    {{ statusLabel(item.status) }}
                                </span>
                            </div>
                            <p
                                v-if="item.nomor_surat"
                                class="mt-1 font-mono text-[10px] text-slate-400"
                            >
                                {{ item.nomor_surat }}
                            </p>
                            <div
                                class="mt-3 flex items-center gap-3 text-[10px] text-slate-400"
                            >
                                <span class="flex items-center gap-1">
                                    <Calendar class="size-3" />
                                    {{
                                        formatDate(
                                            item.tanggal_pengajuan ||
                                                item.created_at,
                                        )
                                    }}
                                </span>
                                <span
                                    v-if="item.pemohon?.name"
                                    class="flex items-center gap-1"
                                >
                                    <FileText class="size-3" />
                                    {{ item.pemohon.name }}
                                </span>
                            </div>
                        </div>
                        <!-- Actions -->
                        <div class="flex shrink-0 items-start gap-2">
                            <button
                                type="button"
                                class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-[10px] font-medium text-slate-600 transition-colors hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600"
                                title="Lihat detail"
                                @click="openDetail(item.id)"
                            >
                                <Eye class="size-3" /> Detail
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Pagination -->
        <div
            v-if="surats.links.length > 3"
            class="mt-5 flex flex-wrap items-center gap-1.5 pl-8"
        >
            <Link
                v-for="link in surats.links"
                :key="link.label"
                :href="link.url ?? '#'"
                    class="fast-btn px-3 py-1.5 text-xs font-medium"
                :class="[
                    link.active
                        ? 'fast-btn-primary'
                        : 'fast-btn-outline',
                    !link.url ? 'pointer-events-none opacity-40' : '',
                ]"
                v-html="link.label"
            />
        </div>
        <!-- Toast -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-3 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-3 opacity-0"
        >
            <div
                v-if="toastMessage"
                class="fixed top-5 left-1/2 z-50 w-[calc(100%-2rem)] max-w-sm -translate-x-1/2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-blue-800 shadow-lg"
            >
                <div class="flex items-center gap-2.5">
                    <BadgeCheck class="size-5 shrink-0 text-blue-500" />
                    <p class="text-sm font-medium">{{ toastMessage }}</p>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
