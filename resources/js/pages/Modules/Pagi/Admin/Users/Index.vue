<script setup lang="ts">
import { Link, router, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { toast } from "vue-sonner";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";

const page = usePage();

interface UserItem {
	id: number;
	name: string;
	type: "mahasiswa" | "mitra" | "dosen" | "alumni" | string;
	handle: string | null;
	email: string;
	nim: string | null;
	prodi: string | null;
	pic: string | null;
	status: "active" | "warning" | "suspended";
	karyaCount: number;
	joinDate: string;
}

const props = defineProps<{
	users?:
		| {
				data: UserItem[];
				links: Array<{
					url: string | null;
					label: string;
					active: boolean;
				}>;
				meta: {
					current_page: number;
					from: number | null;
					last_page: number;
					per_page: number;
					to: number | null;
					total: number;
				};
		  }
		| UserItem[];
	filters?: {
		search?: string;
		type?: string;
		status?: string;
	};
}>();

const searchQuery = ref(props.filters?.search || "");
const filterType = ref(props.filters?.type || "all");
const filterStatus = ref(props.filters?.status || "all");

const isSearching = ref(false);

let searchTimeout: any = null;

const applyFilters = () => {
	isSearching.value = true;
	router.get(
		"/pagi/admin/users",
		{
			search: searchQuery.value || undefined,
			type: filterType.value !== "all" ? filterType.value : undefined,
			status: filterStatus.value !== "all" ? filterStatus.value : undefined,
		},
		{
			preserveState: true,
			replace: true,
			onFinish: () => {
				isSearching.value = false;
			},
		},
	);
};

watch(searchQuery, () => {
	clearTimeout(searchTimeout);
	searchTimeout = setTimeout(() => {
		applyFilters();
	}, 350);
});

watch([filterType, filterStatus], () => {
	applyFilters();
});

const isPaginated = computed(
	() => props.users && typeof props.users === "object" && "data" in props.users,
);

const userList = computed<UserItem[]>(() => {
	if (!props.users) return [];
	if (Array.isArray(props.users)) return props.users;
	return props.users.data || [];
});

const paginationMeta = computed(() => {
	if (isPaginated.value && !Array.isArray(props.users) && props.users?.meta) {
		return props.users.meta;
	}
	return {
		current_page: 1,
		from: 1,
		last_page: 1,
		per_page: userList.value.length,
		to: userList.value.length,
		total: userList.value.length,
	};
});

const paginationLinks = computed(() => {
	if (isPaginated.value && !Array.isArray(props.users) && props.users?.links) {
		return props.users.links;
	}
	return [];
});

const statusConfig: Record<string, string> = {
	active:
		"bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400",
	warning:
		"bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400",
	suspended: "bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400",
};

const statusLabel: Record<string, string> = {
	active: "Aktif",
	warning: "Peringatan",
	suspended: "Ditangguhkan",
};

const typeConfig: Record<string, string> = {
	mahasiswa:
		"bg-purple-50 text-purple-600 border border-purple-100 dark:bg-purple-950/20 dark:text-purple-400 dark:border-none",
	mitra:
		"bg-blue-50 text-blue-600 border border-blue-100 dark:bg-blue-950/20 dark:text-blue-400 dark:border-none",
	dosen:
		"bg-emerald-50 text-emerald-600 border border-emerald-100 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-none",
	alumni:
		"bg-amber-50 text-amber-600 border border-amber-100 dark:bg-amber-950/20 dark:text-amber-400 dark:border-none",
};

const typeLabel: Record<string, string> = {
	mahasiswa: "Mahasiswa",
	mitra: "Mitra Perusahaan",
	dosen: "Dosen",
	alumni: "Alumni",
};

const showWarnModal = ref(false);
const activeUser = ref<any>(null);

const warningForm = useForm({
	reason: "",
});

const handleWarn = (user: any) => {
	activeUser.value = user;
	warningForm.reason = "";
	showWarnModal.value = true;
};

const submitWarning = () => {
	if (!activeUser.value) return;

	warningForm.post(`/pagi/admin/users/${activeUser.value.id}/warn`, {
		onSuccess: () => {
			showWarnModal.value = false;
			toast.success(
				`Peringatan berhasil dikirim ke ${activeUser.value?.name}.`,
			);
			warningForm.reset();
		},
		onError: () => {
			toast.error("Gagal mengirim peringatan.");
		},
	});
};

const showStatusModal = ref(false);
const statusTargetUser = ref<UserItem | null>(null);

const statusForm = useForm({
	status: "active" as "active" | "warning" | "suspended",
	reason: "",
});

const openStatusModal = (
	user: UserItem,
	targetStatus?: "active" | "warning" | "suspended",
) => {
	statusTargetUser.value = user;
	statusForm.status = targetStatus || user.status;
	statusForm.reason = "";
	showStatusModal.value = true;
};

const submitStatusChange = () => {
	if (!statusTargetUser.value) return;

	const targetName = statusTargetUser.value.name;

	statusForm.post(`/pagi/admin/users/${statusTargetUser.value.id}/status`, {
		preserveScroll: true,
		onSuccess: () => {
			showStatusModal.value = false;
			toast.success(`Status akun ${targetName} berhasil diperbarui.`);
			statusForm.reset();
		},
		onError: (errors) => {
			const firstErr = Object.values(errors)[0];
			toast.error(
				typeof firstErr === "string"
					? firstErr
					: "Gagal memperbarui status pengguna.",
			);
		},
	});
};
</script>

<template>
    <PagiAdminLayout title="Manajemen Pengguna">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Manajemen Pengguna</h1>
                <p class="mt-0.5 text-[13px] text-slate-400 dark:text-zinc-500">Kelola akun dan akses mahasiswa serta mitra industri di modul PAGI</p>
            </div>
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 overflow-hidden animate-fade-in shadow-sm">
            <!-- Toolbar -->
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 px-5 py-4 border-b border-slate-100 dark:border-zinc-800">
                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-[240px]">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nama, NIM, email, PIC..."
                            class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 pl-9 pr-4 py-2 text-[12px] font-medium text-slate-700 dark:text-zinc-300 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        />
                    </div>

                    <!-- Type Filter -->
                    <select
                        v-model="filterType"
                        class="rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 px-3 py-2 text-[12px] font-semibold text-slate-600 dark:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all shrink-0"
                    >
                        <option value="all">Semua Tipe</option>
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="mitra">Mitra Perusahaan</option>
                        <option value="dosen">Dosen</option>
                        <option value="alumni">Alumni</option>
                    </select>

                    <!-- Status Filter -->
                    <select
                        v-model="filterStatus"
                        class="rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 px-3 py-2 text-[12px] font-semibold text-slate-600 dark:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all shrink-0"
                    >
                        <option value="all">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="warning">Peringatan</option>
                        <option value="suspended">Ditangguhkan</option>
                    </select>
                </div>
                <span class="text-[11px] text-slate-400 dark:text-zinc-500 font-extrabold self-end lg:self-auto">
                    Total <span class="text-slate-700 dark:text-zinc-300">{{ paginationMeta.total }}</span> pengguna
                </span>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <caption class="sr-only">Daftar Pengguna Modul PAGI</caption>
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-800/10">
                            <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Pengguna</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Tipe</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Identitas khusus</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Prodi / Email Kontak</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Karya</th>
                            <th class="px-5 py-3 text-right text-[10px] font-black uppercase tracking-widest text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <!-- Shimmer Skeleton Table Loading -->
                    <tbody v-if="isSearching" class="divide-y divide-slate-50 dark:divide-zinc-800/50">
                        <tr v-for="i in 5" :key="i" class="animate-pulse">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer shrink-0" />
                                    <div class="space-y-1.5 flex-1">
                                        <div class="h-3.5 w-32 rounded-md bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                                        <div class="h-2.5 w-24 rounded-md bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4"><div class="h-5 w-16 rounded-lg bg-slate-100 dark:bg-zinc-800 animate-shimmer" /></td>
                            <td class="px-4 py-4"><div class="h-3.5 w-24 rounded-md bg-slate-100 dark:bg-zinc-800 animate-shimmer" /></td>
                            <td class="px-4 py-4"><div class="h-3.5 w-28 rounded-md bg-slate-100 dark:bg-zinc-800 animate-shimmer" /></td>
                            <td class="px-4 py-4"><div class="h-5 w-16 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" /></td>
                            <td class="px-4 py-4"><div class="h-4 w-12 rounded-md bg-slate-100 dark:bg-zinc-800 animate-shimmer" /></td>
                            <td class="px-5 py-4 text-right"><div class="h-7 w-20 ml-auto rounded-lg bg-slate-100 dark:bg-zinc-800 animate-shimmer" /></td>
                        </tr>
                    </tbody>
                    <tbody v-else class="divide-y divide-slate-50 dark:divide-zinc-800/50">
                        <tr
                            v-for="u in userList"
                            :key="u.id"
                            class="group hover:bg-slate-50 dark:hover:bg-zinc-800/30 transition-colors"
                        >
                            <!-- Avatar & Nama -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div 
                                        :class="[
                                            'h-9 w-9 shrink-0 flex items-center justify-center text-[13px] font-black',
                                            u.type === 'mahasiswa' 
                                                ? 'rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' 
                                                : u.type === 'dosen'
                                                ? 'rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                                                : u.type === 'alumni'
                                                ? 'rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
                                                : 'rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300'
                                        ]"
                                    >
                                        {{ u.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-semibold text-slate-800 dark:text-zinc-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                            {{ u.name }}
                                        </p>
                                        <p class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">
                                            {{ u.type === 'mahasiswa' ? u.handle : u.email }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Tipe Pengguna -->
                            <td class="px-4 py-3.5">
                                <span 
                                    :class="[
                                        'inline-flex items-center rounded-lg px-2 py-0.5 text-[10px] font-bold tracking-tight',
                                        typeConfig[u.type] || 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400'
                                    ]"
                                >
                                    {{ typeLabel[u.type] || u.type }}
                                </span>
                            </td>

                            <!-- Identitas Khusus (NIM / PIC) -->
                            <td class="px-4 py-3.5">
                                <div class="text-[12px] text-slate-600 dark:text-zinc-400 font-medium">
                                    <span v-if="u.type === 'mahasiswa'" class="font-mono">{{ u.nim }}</span>
                                    <span v-else class="flex items-center gap-1">
                                        <span class="text-[10px] text-slate-400 uppercase tracking-widest font-black">PIC:</span>
                                        {{ u.pic }}
                                    </span>
                                </div>
                            </td>

                            <!-- Prodi / Email -->
                            <td class="px-4 py-3.5">
                                <span class="text-[12px] text-slate-600 dark:text-zinc-400 font-semibold">
                                    {{ u.type === 'mahasiswa' ? u.prodi : u.email }}
                                </span>
                            </td>

                            <!-- Status (Interactive Dropdown / Clickable Badge) -->
                            <td class="px-4 py-3.5">
                                <button
                                    @click="openStatusModal(u)"
                                    :class="[
                                        'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-black hover:opacity-80 transition-all cursor-pointer shadow-2xs group/btn',
                                        statusConfig[u.status]
                                    ]"
                                    title="Klik untuk mengubah status akun"
                                >
                                    <span>{{ statusLabel[u.status] }}</span>
                                    <svg class="h-2.5 w-2.5 opacity-60 group-hover/btn:translate-y-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </td>

                            <!-- Karya Count -->
                            <td class="px-4 py-3.5">
                                <div class="flex items-baseline gap-0.5">
                                    <span class="text-[13px] font-bold text-slate-800 dark:text-zinc-200">{{ u.karyaCount }}</span>
                                    <span class="text-[10px] text-slate-400 font-semibold">karya</span>
                                </div>
                            </td>

                            <!-- Aksi -->
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link 
                                        :href="`/pagi/profile/${u.id}`"
                                        class="rounded-lg border border-slate-200 dark:border-zinc-700 px-2.5 py-1.5 text-[11px] font-black text-slate-600 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors inline-block text-center"
                                    >
                                        Detail
                                    </Link>
                                    <button 
                                        @click="handleWarn(u)" 
                                        class="rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/20 px-2.5 py-1.5 text-[11px] font-black text-amber-700 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors"
                                    >
                                        Warn
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="userList.length === 0">
                            <td colspan="7" class="py-12 text-center text-slate-400 text-xs font-semibold">
                                Tidak ada pengguna yang ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar Footer -->
            <div v-if="paginationMeta.total > 0 && paginationLinks.length > 3" class="flex flex-col sm:flex-row items-center justify-between gap-4 px-5 py-3.5 border-t border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50">
                <p class="text-[12px] text-slate-500 dark:text-zinc-400 font-medium">
                    Menampilkan <span class="font-extrabold text-slate-700 dark:text-zinc-200">{{ paginationMeta.from || 0 }}</span> sampai <span class="font-extrabold text-slate-700 dark:text-zinc-200">{{ paginationMeta.to || 0 }}</span> dari <span class="font-extrabold text-slate-700 dark:text-zinc-200">{{ paginationMeta.total }}</span> pengguna
                </p>

                <div class="flex items-center gap-1 flex-wrap">
                    <template v-for="(link, idx) in paginationLinks" :key="idx">
                        <span
                            v-if="!link.url"
                            class="px-3 py-1.5 text-[11px] font-medium text-slate-300 dark:text-zinc-600 rounded-lg cursor-not-allowed"
                            v-html="link.label"
                        />
                        <Link
                            v-else
                            :href="link.url"
                            preserve-scroll
                            preserve-state
                            :class="[
                                'px-3 py-1.5 text-[11px] font-bold rounded-lg transition-all',
                                link.active
                                    ? 'bg-indigo-600 text-white shadow-sm font-black'
                                    : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-200/60 dark:hover:bg-zinc-800'
                            ]"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Status Change Modal -->
        <div v-if="showStatusModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/50 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-2xl bg-white dark:bg-zinc-900 p-6 border border-slate-100 dark:border-zinc-800 shadow-xl animate-scale-in">
                <h3 class="text-[15px] font-black text-slate-900 dark:text-white">Ubah Status Akun Pengguna</h3>
                <p class="mt-1 text-[12px] text-slate-400 dark:text-zinc-500">Kelola status akses dan keaktifan akun <strong>{{ statusTargetUser?.name }}</strong>.</p>

                <form @submit.prevent="submitStatusChange" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Pilih Status Baru</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                type="button"
                                @click="statusForm.status = 'active'"
                                :class="[
                                    'py-2 px-2.5 rounded-xl border text-[11px] font-black text-center transition-all',
                                    statusForm.status === 'active'
                                        ? 'border-emerald-500 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300 shadow-xs ring-2 ring-emerald-500/20'
                                        : 'border-slate-200 dark:border-zinc-700 text-slate-600 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800'
                                ]"
                            >
                                🟢 Aktif
                            </button>
                            <button
                                type="button"
                                @click="statusForm.status = 'warning'"
                                :class="[
                                    'py-2 px-2.5 rounded-xl border text-[11px] font-black text-center transition-all',
                                    statusForm.status === 'warning'
                                        ? 'border-amber-500 bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300 shadow-xs ring-2 ring-amber-500/20'
                                        : 'border-slate-200 dark:border-zinc-700 text-slate-600 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800'
                                ]"
                            >
                                🟠 Peringatan
                            </button>
                            <button
                                type="button"
                                @click="statusForm.status = 'suspended'"
                                :class="[
                                    'py-2 px-2.5 rounded-xl border text-[11px] font-black text-center transition-all',
                                    statusForm.status === 'suspended'
                                        ? 'border-rose-500 bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-300 shadow-xs ring-2 ring-rose-500/20'
                                        : 'border-slate-200 dark:border-zinc-700 text-slate-600 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800'
                                ]"
                            >
                                🔴 Suspend
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Alasan / Catatan Admin</label>
                        <textarea
                            v-model="statusForm.reason"
                            placeholder="Masukkan alasan perubahan status (opsional)..."
                            rows="3"
                            class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 p-3 text-[12px] font-semibold text-slate-800 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
                        ></textarea>
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="showStatusModal = false"
                            class="rounded-xl border border-slate-200 dark:border-zinc-700 px-4 py-2 text-[12px] font-bold text-slate-500 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="statusForm.processing"
                            class="rounded-xl bg-indigo-600 hover:bg-indigo-700 px-4 py-2 text-[12px] font-bold text-white transition-colors shadow-sm disabled:opacity-50"
                        >
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Warning Modal -->
        <div v-if="showWarnModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/50 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-2xl bg-white dark:bg-zinc-900 p-6 border border-slate-100 dark:border-zinc-800 shadow-xl animate-scale-in">
                <h3 class="text-[15px] font-black text-slate-900 dark:text-white">Kirim Peringatan</h3>
                <p class="mt-1 text-[12px] text-slate-400 dark:text-zinc-500">Berikan penjelasan alasan mengapa akun <strong>{{ activeUser?.name }}</strong> ini diperingatkan.</p>

                <form @submit.prevent="submitWarning" class="mt-4">
                    <textarea
                        v-model="warningForm.reason"
                        placeholder="Tulis alasan di sini..."
                        rows="4"
                        required
                        class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 p-3 text-[12px] font-semibold text-slate-800 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    ></textarea>

                    <div class="mt-4 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            @click="showWarnModal = false"
                            class="rounded-xl border border-slate-200 dark:border-zinc-700 px-4 py-2 text-[12px] font-bold text-slate-500 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="warningForm.processing"
                            class="rounded-xl bg-amber-600 hover:bg-amber-700 px-4 py-2 text-[12px] font-bold text-white transition-colors shadow-sm disabled:opacity-50"
                        >
                            Kirim Peringatan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PagiAdminLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.animate-scale-in {
    animation: scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes scaleIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
</style>

