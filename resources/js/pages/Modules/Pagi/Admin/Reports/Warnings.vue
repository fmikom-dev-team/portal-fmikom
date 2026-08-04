<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import {
	AlertTriangle,
	Archive,
	Calendar,
	CheckCircle,
	Clock,
	Eye,
	FileText,
	History,
	Info,
	Search,
	ShieldAlert,
	Sparkles,
	ThumbsDown,
	ThumbsUp,
	UserCheck,
	UserX,
	X,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import ConfirmDialogModal from "@/components/Admin/ui/ConfirmDialogModal.vue";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";

interface WarningHistoryItem {
	id: number;
	warningCount: number;
	reason: string;
	severity: string;
	type: string;
	isActive: boolean;
	workId?: number | null;
	workTitle?: string | null;
	workThumbnail?: string | null;
	issuerName?: string | null;
	time: string;
	expiresAt?: string | null;
	expiresAtHuman?: string | null;
	isExpired?: boolean;
}

interface UserWarningGroup {
	userId: number;
	user: string;
	userName: string;
	userHandle: string;
	userEmail?: string;
	userNim?: string | null;
	prodi?: string | null;
	activeWarningsCount: number;
	totalWarningsCount: number;
	maxAllowedWarnings: number;
	accountStatus: "active" | "warning" | "suspended" | "appealed";
	isSuspended: boolean;
	hasPendingAppeal: boolean;
	appealId?: number | null;
	appealReason?: string | null;
	appealTime?: string | null;
	nextExpiresAtHuman?: string | null;
	warningsHistory: WarningHistoryItem[];
}

const props = defineProps<{
	warningsList?: UserWarningGroup[];
	summary?: {
		spActiveUsers: number;
		suspendedUsers: number;
		pendingAppeals: number;
		archivedWarnings: number;
	};
}>();

const searchQuery = ref("");
const statusFilter = ref<"active_sp" | "suspended" | "appeals" | "archived">(
	"active_sp",
);

const selectedUser = ref<UserWarningGroup | null>(null);
const isTimelineModalOpen = ref(false);

const selectedAppealUser = ref<UserWarningGroup | null>(null);
const isAppealModalOpen = ref(false);

const selectedRevokeWarningId = ref<number | null>(null);
const isConfirmRevokeOpen = ref(false);
const isProcessing = ref(false);

const userWarningsList = computed(() => props.warningsList ?? []);

const filteredUsers = computed(() => {
	let list = userWarningsList.value;

	if (statusFilter.value === "active_sp") {
		list = list.filter((u) => u.activeWarningsCount > 0 && !u.isSuspended);
	} else if (statusFilter.value === "suspended") {
		list = list.filter((u) => u.isSuspended);
	} else if (statusFilter.value === "appeals") {
		list = list.filter((u) => u.hasPendingAppeal);
	} else if (statusFilter.value === "archived") {
		list = list.filter(
			(u) => u.activeWarningsCount === 0 && u.totalWarningsCount > 0,
		);
	}

	if (searchQuery.value.trim()) {
		const q = searchQuery.value.toLowerCase().trim();
		list = list.filter(
			(u) =>
				u.user.toLowerCase().includes(q) ||
				u.userHandle.toLowerCase().includes(q) ||
				u.userNim?.toLowerCase().includes(q) ||
				u.warningsHistory.some(
					(w) =>
						w.reason.toLowerCase().includes(q) ||
						w.workTitle?.toLowerCase().includes(q),
				),
		);
	}

	return list;
});

const openTimelineModal = (user: UserWarningGroup) => {
	selectedUser.value = user;
	isTimelineModalOpen.value = true;
};

const openAppealModal = (user: UserWarningGroup) => {
	selectedAppealUser.value = user;
	isAppealModalOpen.value = true;
};

const promptRevokeWarning = (warningId: number) => {
	selectedRevokeWarningId.value = warningId;
	isConfirmRevokeOpen.value = true;
};

const handleConfirmRevoke = () => {
	if (!selectedRevokeWarningId.value) return;
	isProcessing.value = true;

	router.post(
		`/pagi/admin/warnings/${selectedRevokeWarningId.value}/revoke`,
		{},
		{
			preserveScroll: true,
			onFinish: () => {
				isProcessing.value = false;
				isConfirmRevokeOpen.value = false;

				// Update local reactive state if detail modal is open
				if (selectedUser.value) {
					const item = selectedUser.value.warningsHistory.find(
						(w) => w.id === selectedRevokeWarningId.value,
					);
					if (item) {
						item.isActive = false;
					}
					selectedUser.value.activeWarningsCount = Math.max(
						0,
						selectedUser.value.activeWarningsCount - 1,
					);
					if (
						selectedUser.value.activeWarningsCount <
						selectedUser.value.maxAllowedWarnings
					) {
						selectedUser.value.isSuspended = false;
						selectedUser.value.accountStatus =
							selectedUser.value.activeWarningsCount > 0 ? "warning" : "active";
					}
				}
				selectedRevokeWarningId.value = null;
			},
		},
	);
};

const handleApproveAppeal = (appealId: number | null | undefined) => {
	if (!appealId) return;
	isProcessing.value = true;

	router.post(
		`/pagi/admin/appeals/${appealId}/approve`,
		{},
		{
			preserveScroll: true,
			onFinish: () => {
				isProcessing.value = false;
				isAppealModalOpen.value = false;
				selectedAppealUser.value = null;
			},
		},
	);
};

const handleRejectAppeal = (appealId: number | null | undefined) => {
	if (!appealId) return;
	isProcessing.value = true;

	router.post(
		`/pagi/admin/appeals/${appealId}/reject`,
		{},
		{
			preserveScroll: true,
			onFinish: () => {
				isProcessing.value = false;
				isAppealModalOpen.value = false;
				selectedAppealUser.value = null;
			},
		},
	);
};
</script>

<template>
    <PagiAdminLayout>
        <Head title="Pusat Moderasi Akun & SP - PAGI Portal" />

        <div class="space-y-6 pb-12">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-6 rounded-2xl shadow-sm">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Akun Peringatan (SP) & Moderasi</h1>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-black bg-orange-100 dark:bg-orange-950/60 text-orange-700 dark:text-orange-300">
                            <ShieldAlert class="w-3 h-3" /> User-Centric Moderation
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                        Kelola sanksi peringatan mahasiswa per akun, audit status pembatasan akses (*Restricted Mode*), dan proses permohonan banding.
                    </p>
                </div>
            </div>

            <!-- 4 Refocused KPI Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- KPI 1: Mahasiswa SP Aktif -->
                <div
                    @click="statusFilter = 'active_sp'"
                    class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-sm flex items-center justify-between cursor-pointer hover:border-orange-400 dark:hover:border-orange-800 transition-all hover:shadow-md group"
                >
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider group-hover:text-orange-600 transition-colors">Mahasiswa SP Aktif</p>
                        <p class="text-2xl font-black text-orange-600 dark:text-orange-400 mt-1">
                            {{ summary?.spActiveUsers ?? userWarningsList.filter(u => u.activeWarningsCount > 0 && !u.isSuspended).length }}
                        </p>
                        <p class="text-[10px] text-slate-400 dark:text-zinc-500 mt-0.5">SP 1 atau SP 2 Aktif</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-950/50 flex items-center justify-center text-orange-600 dark:text-orange-400 shrink-0 group-hover:scale-110 transition-transform">
                        <AlertTriangle class="w-5 h-5" />
                    </div>
                </div>

                <!-- KPI 2: Akun Dibatasi / Suspended -->
                <div
                    @click="statusFilter = 'suspended'"
                    class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-sm flex items-center justify-between cursor-pointer hover:border-red-400 dark:hover:border-red-800 transition-all hover:shadow-md group"
                >
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider group-hover:text-red-600 transition-colors">Akun Dibatasi (Suspended)</p>
                        <p class="text-2xl font-black text-red-600 dark:text-red-400 mt-1">
                            {{ summary?.suspendedUsers ?? userWarningsList.filter(u => u.isSuspended).length }}
                        </p>
                        <p class="text-[10px] text-slate-400 dark:text-zinc-500 mt-0.5">Restricted Access Mode</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/50 flex items-center justify-center text-red-600 dark:text-red-400 shrink-0 group-hover:scale-110 transition-transform">
                        <UserX class="w-5 h-5" />
                    </div>
                </div>

                <!-- KPI 3: Permohonan Banding -->
                <div
                    @click="statusFilter = 'appeals'"
                    class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-sm flex items-center justify-between cursor-pointer hover:border-indigo-400 dark:hover:border-indigo-800 transition-all hover:shadow-md group"
                >
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider group-hover:text-indigo-600 transition-colors">Permohonan Banding</p>
                        <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">
                            {{ summary?.pendingAppeals ?? userWarningsList.filter(u => u.hasPendingAppeal).length }}
                        </p>
                        <p class="text-[10px] text-slate-400 dark:text-zinc-500 mt-0.5">Butuh Peninjauan Admin</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0 group-hover:scale-110 transition-transform">
                        <FileText class="w-5 h-5" />
                    </div>
                </div>

                <!-- KPI 4: Arsip / SP Selesai -->
                <div
                    @click="statusFilter = 'archived'"
                    class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-sm flex items-center justify-between cursor-pointer hover:border-emerald-400 dark:hover:border-emerald-800 transition-all hover:shadow-md group"
                >
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider group-hover:text-emerald-600 transition-colors">Arsip / SP Selesai</p>
                        <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                            {{ summary?.archivedWarnings ?? userWarningsList.filter(u => u.activeWarningsCount === 0 && u.totalWarningsCount > 0).length }}
                        </p>
                        <p class="text-[10px] text-slate-400 dark:text-zinc-500 mt-0.5">Historical Log Permanen</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0 group-hover:scale-110 transition-transform">
                        <CheckCircle class="w-5 h-5" />
                    </div>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">
                <!-- Toolbar with Complete Tabs -->
                <div class="p-4 sm:p-6 border-b border-slate-100 dark:border-zinc-800 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <!-- Complete Status Filters Tabs -->
                    <div class="flex flex-wrap items-center gap-1.5 p-1 bg-slate-100 dark:bg-zinc-800 rounded-xl">
                        <button
                            @click="statusFilter = 'active_sp'"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-xs font-bold transition-all',
                                statusFilter === 'active_sp'
                                    ? 'bg-white dark:bg-zinc-900 text-orange-600 dark:text-orange-400 shadow-sm'
                                    : 'text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white'
                            ]"
                        >
                            SP Aktif (1-2 SP)
                        </button>
                        <button
                            @click="statusFilter = 'suspended'"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-xs font-bold transition-all',
                                statusFilter === 'suspended'
                                    ? 'bg-white dark:bg-zinc-900 text-red-600 dark:text-red-400 shadow-sm'
                                    : 'text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white'
                            ]"
                        >
                            Akun Dibatasi (Suspended)
                        </button>
                        <button
                            @click="statusFilter = 'appeals'"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-xs font-bold transition-all relative',
                                statusFilter === 'appeals'
                                    ? 'bg-white dark:bg-zinc-900 text-indigo-600 dark:text-indigo-400 shadow-sm'
                                    : 'text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white'
                            ]"
                        >
                            Permohonan Banding
                            <span v-if="userWarningsList.filter(u => u.hasPendingAppeal).length > 0" class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] bg-indigo-600 text-white font-bold">
                                {{ userWarningsList.filter(u => u.hasPendingAppeal).length }}
                            </span>
                        </button>
                        <button
                            @click="statusFilter = 'archived'"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-xs font-bold transition-all',
                                statusFilter === 'archived'
                                    ? 'bg-white dark:bg-zinc-900 text-emerald-600 dark:text-emerald-400 shadow-sm'
                                    : 'text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white'
                            ]"
                        >
                            Arsip SP Selesai
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full lg:w-72">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 dark:text-zinc-500" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nama, NIM, handle, atau alasan..."
                            class="w-full pl-9 pr-4 py-1.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>
                </div>

                <!-- User-Centric Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50">
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Pengguna Mahasiswa</th>
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Tingkat SP Aktif</th>
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Status Akses Akun</th>
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Status Banding</th>
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
                            <tr
                                v-for="u in filteredUsers"
                                :key="u.userId"
                                class="hover:bg-slate-50/80 dark:hover:bg-zinc-800/40 transition-colors"
                            >
                                <!-- Mahasiswa Info -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center font-bold text-slate-700 dark:text-zinc-300 text-xs shrink-0 border border-slate-200 dark:border-zinc-700">
                                            {{ u.user.substring(0, 2).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-white text-xs">{{ u.user }}</p>
                                            <p class="text-[11px] text-slate-500 dark:text-zinc-400">
                                                {{ u.userHandle }} <span v-if="u.userNim">• NIM: {{ u.userNim }}</span>
                                            </p>
                                            <p class="text-[10px] text-slate-400 dark:text-zinc-500">{{ u.prodi }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Tingkat SP Aktif -->
                                <td class="py-4 px-6">
                                    <div class="flex flex-col items-start gap-1">
                                        <span
                                            :class="[
                                                'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-black',
                                                u.activeWarningsCount >= u.maxAllowedWarnings
                                                    ? 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300'
                                                    : u.activeWarningsCount > 0
                                                    ? 'bg-orange-100 text-orange-700 dark:bg-orange-950/60 dark:text-orange-300'
                                                    : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'
                                            ]"
                                        >
                                            SP {{ u.activeWarningsCount }} / {{ u.maxAllowedWarnings }}
                                        </span>
                                        <span v-if="u.nextExpiresAtHuman" class="text-[10px] text-slate-400 dark:text-zinc-500">
                                            Expire terdekat: {{ u.nextExpiresAtHuman }}
                                        </span>
                                        <span v-else-if="u.activeWarningsCount === 0" class="text-[10px] text-slate-400 dark:text-zinc-500">
                                            Tidak ada SP aktif
                                        </span>
                                    </div>
                                </td>

                                <!-- Status Akses Akun -->
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <span
                                        v-if="u.isSuspended"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300 border border-red-200 dark:border-red-900/50"
                                    >
                                        <UserX class="w-3.5 h-3.5" /> Restricted (Suspended)
                                    </span>
                                    <span
                                        v-else-if="u.activeWarningsCount > 0"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-900/50"
                                    >
                                        <AlertTriangle class="w-3.5 h-3.5" /> Warning (Aktif)
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/50"
                                    >
                                        <CheckCircle class="w-3.5 h-3.5" /> Normal (Aktif)
                                    </span>
                                </td>

                                <!-- Status Banding -->
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <button
                                        v-if="u.hasPendingAppeal"
                                        @click="openAppealModal(u)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 hover:bg-indigo-200 transition-colors animate-pulse"
                                    >
                                        <FileText class="w-3.5 h-3.5" /> Ada Banding Pending
                                    </button>
                                    <span v-else class="text-[11px] font-medium text-slate-400 dark:text-zinc-500">
                                        Tidak Ada Banding
                                    </span>
                                </td>

                                <!-- Aksi -->
                                <td class="py-4 px-6 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="openTimelineModal(u)"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors"
                                        >
                                            <History class="w-3.5 h-3.5" /> Linimasa SP ({{ u.totalWarningsCount }})
                                        </button>

                                        <button
                                            v-if="u.hasPendingAppeal"
                                            @click="openAppealModal(u)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition-colors shadow-sm"
                                        >
                                            <Eye class="w-3.5 h-3.5" /> Tinjau Banding
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredUsers.length === 0">
                                <td colspan="5" class="py-12 text-center text-slate-400 dark:text-zinc-500">
                                    Tidak ada data mahasiswa berperingatan yang terdaftar.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Timeline SP Per Mahasiswa -->
        <div v-if="isTimelineModalOpen && selectedUser" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold">
                            <History class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base">Linimasa SP Mahasiswa</h3>
                            <p class="text-xs text-slate-500 dark:text-zinc-400">
                                {{ selectedUser.user }} ({{ selectedUser.userHandle }}) • {{ selectedUser.prodi }}
                            </p>
                        </div>
                    </div>
                    <button
                        @click="isTimelineModalOpen = false"
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Modal Body Timeline -->
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto text-xs">
                    <!-- Status Banner -->
                    <div class="p-3.5 rounded-xl border flex items-center justify-between" :class="selectedUser.isSuspended ? 'bg-red-50 dark:bg-red-950/30 border-red-200 dark:border-red-900/40 text-red-700 dark:text-red-300' : 'bg-slate-50 dark:bg-zinc-800/60 border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-zinc-300'">
                        <div>
                            <p class="font-bold text-xs uppercase tracking-wider">Status Akun Saat Ini</p>
                            <p class="text-sm font-black mt-0.5">
                                {{ selectedUser.isSuspended ? 'Restricted Access Mode (Suspended)' : (selectedUser.activeWarningsCount > 0 ? 'Dalam Peringatan Aktif' : 'Normal / Aktif') }}
                            </p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-white dark:bg-zinc-900 shadow-sm border border-slate-200 dark:border-zinc-700">
                            SP Aktif: {{ selectedUser.activeWarningsCount }} / {{ selectedUser.maxAllowedWarnings }}
                        </span>
                    </div>

                    <!-- Warnings List Timeline -->
                    <div v-if="selectedUser.warningsHistory.length > 0" class="space-y-3 relative before:absolute before:left-3.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-slate-200 dark:before:bg-zinc-800">
                        <div
                            v-for="item in selectedUser.warningsHistory"
                            :key="item.id"
                            class="relative pl-8 space-y-2"
                        >
                            <div
                                :class="[
                                    'absolute left-1.5 top-1.5 w-4 h-4 rounded-full border-2 bg-white dark:bg-zinc-900',
                                    item.isActive ? 'border-orange-500 ring-2 ring-orange-100 dark:ring-orange-950' : 'border-slate-300 dark:border-zinc-700'
                                ]"
                            />

                            <div class="p-4 rounded-xl border border-slate-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-sm space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-black bg-orange-100 text-orange-700 dark:bg-orange-950/60 dark:text-orange-300">
                                            Peringatan #{{ item.warningCount }}
                                        </span>
                                        <span
                                            :class="[
                                                'px-2 py-0.5 rounded text-[10px] font-semibold uppercase',
                                                item.severity === 'high' ? 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300'
                                            ]"
                                        >
                                            {{ item.severity }}
                                        </span>
                                    </div>

                                    <span v-if="item.isActive" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-50 text-orange-600 border border-orange-200">
                                        Aktif
                                    </span>
                                    <span v-else class="text-[10px] font-semibold text-slate-400">
                                        Non-Aktif / Dicabut
                                    </span>
                                </div>

                                <!-- Work Info if exists -->
                                <div v-if="item.workTitle" class="flex items-center gap-2.5 p-2 rounded-lg bg-slate-50 dark:bg-zinc-800/80 border border-slate-100 dark:border-zinc-700">
                                    <img
                                        v-if="item.workThumbnail"
                                        :src="item.workThumbnail"
                                        alt="Cover Karya"
                                        class="w-9 h-9 rounded object-cover shrink-0 border"
                                    />
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-slate-900 dark:text-white truncate">{{ item.workTitle }}</p>
                                        <p class="text-[10px] text-slate-400">ID Karya: #{{ item.workId }}</p>
                                    </div>
                                </div>

                                <!-- Reason -->
                                <p class="text-slate-800 dark:text-zinc-200 leading-relaxed font-normal">{{ item.reason }}</p>

                                <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1 border-t border-slate-100 dark:border-zinc-800">
                                    <span>Penerbit: {{ item.issuerName }} • {{ item.time }}</span>
                                    <span v-if="item.expiresAtHuman">Expire: {{ item.expiresAtHuman }}</span>
                                </div>

                                <!-- Revoke Button -->
                                <div v-if="item.isActive" class="pt-2 text-right">
                                    <button
                                        @click="promptRevokeWarning(item.id)"
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-amber-500 text-white text-[11px] font-bold hover:bg-amber-600 transition-colors shadow-sm"
                                    >
                                        <UserCheck class="w-3.5 h-3.5" /> Cabut Warning Ini
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center text-slate-400">
                        Belum ada riwayat surat peringatan yang terdaftar.
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-4 border-t border-slate-100 dark:border-zinc-800 text-right">
                    <button
                        @click="isTimelineModalOpen = false"
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 hover:bg-slate-200 transition-colors"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Tinjau Banding Akun -->
        <div v-if="isAppealModalOpen && selectedAppealUser" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                            <FileText class="w-4 h-4" />
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base">Permohonan Banding Akun</h3>
                            <p class="text-xs text-slate-400">ID Banding: #{{ selectedAppealUser.appealId }}</p>
                        </div>
                    </div>
                    <button
                        @click="isAppealModalOpen = false"
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="p-6 space-y-4 text-xs">
                    <!-- User Info -->
                    <div class="bg-slate-50 dark:bg-zinc-800/60 p-3.5 rounded-xl border border-slate-100 dark:border-zinc-800 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pemohon Banding</p>
                            <p class="text-sm font-bold text-slate-900 dark:text-white mt-0.5">{{ selectedAppealUser.user }}</p>
                            <p class="text-xs text-slate-500">{{ selectedAppealUser.userHandle }} • {{ selectedAppealUser.prodi }}</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">
                            PENDING REVIEW
                        </span>
                    </div>

                    <!-- Description of Appeal -->
                    <div class="p-4 bg-indigo-50/50 dark:bg-indigo-950/30 rounded-xl border border-indigo-100 dark:border-indigo-900/50 space-y-1.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Alasan Keberatan / Pesan Mahasiswa</p>
                        <p class="text-xs text-slate-800 dark:text-zinc-200 leading-relaxed font-medium">
                            "{{ selectedAppealUser.appealReason }}"
                        </p>
                        <p class="text-[10px] text-slate-400 text-right mt-1">Diajukan: {{ selectedAppealUser.appealTime }}</p>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-zinc-800/60 rounded-xl border text-[11px] text-slate-600 dark:text-zinc-400 space-y-1">
                        <p class="font-bold text-slate-800 dark:text-zinc-200">Dampak Keputusan Moderasi:</p>
                        <p>• <strong>Setujui Banding</strong>: SP Aktif dicabut, status akun otomatis dipulihkan dari Restricted Mode menjadi Normal Aktif.</p>
                        <p>• <strong>Tolak Banding</strong>: Permohonan ditolak, sanksi Restricted Mode tetap berlaku.</p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-slate-50/50 dark:bg-zinc-900/50 border-t border-slate-100 dark:border-zinc-800">
                    <button
                        @click="handleRejectAppeal(selectedAppealUser.appealId)"
                        :disabled="isProcessing"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-red-600 text-white text-xs font-bold hover:bg-red-700 transition-colors shadow-sm disabled:opacity-50"
                    >
                        <ThumbsDown class="w-4 h-4" /> Tolak Banding
                    </button>

                    <button
                        @click="handleApproveAppeal(selectedAppealUser.appealId)"
                        :disabled="isProcessing"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition-colors shadow-sm disabled:opacity-50"
                    >
                        <ThumbsUp class="w-4 h-4" /> Setujui Banding (Pulihkan Akun)
                    </button>
                </div>
            </div>
        </div>

        <!-- Confirm Revoke Dialog Modal -->
        <ConfirmDialogModal
            :is-open="isConfirmRevokeOpen"
            title="Cabut Surat Peringatan?"
            message="Apakah Anda yakin ingin mencabut SP ini? Jika total SP aktif mahasiswa berada di bawah batas ambang, akun mahasiswa otomatis diaktifkan kembali."
            confirm-text="Cabut Warning"
            variant="warning"
            :is-loading="isProcessing"
            @close="isConfirmRevokeOpen = false"
            @confirm="handleConfirmRevoke"
        />
    </PagiAdminLayout>
</template>
