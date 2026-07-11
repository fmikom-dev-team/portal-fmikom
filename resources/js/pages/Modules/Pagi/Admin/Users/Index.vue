<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";

const props = defineProps<{
	users?: Array<{
		id: number;
		name: string;
		type: "mahasiswa" | "mitra";
		handle: string | null;
		email: string;
		nim: string | null;
		prodi: string | null;
		pic: string | null;
		status: "active" | "warning" | "suspended";
		karyaCount: number;
		joinDate: string;
	}>;
}>();

const searchQuery = ref("");
const filterType = ref("all");
const filterStatus = ref("all");

const allUsers = computed(
	() =>
		props.users ?? [
			{
				id: 1,
				name: "Sarah Aulia",
				type: "mahasiswa" as const,
				handle: "@sarah.ui",
				email: "sarah@student.fmikom.ac.id",
				nim: "2021010001",
				prodi: "Informatika",
				pic: null,
				status: "active" as const,
				karyaCount: 24,
				joinDate: "12 Sep 2021",
			},
			{
				id: 2,
				name: "Naufal Dzaky",
				type: "mahasiswa" as const,
				handle: "@naufal.dev",
				email: "naufal@student.fmikom.ac.id",
				nim: "2021010002",
				prodi: "Informatika",
				pic: null,
				status: "active" as const,
				karyaCount: 18,
				joinDate: "12 Sep 2021",
			},
			{
				id: 3,
				name: "Dimas Wirawan",
				type: "mahasiswa" as const,
				handle: "@dimas.w",
				email: "dimas@student.fmikom.ac.id",
				nim: "2022010015",
				prodi: "Sistem Informasi",
				pic: null,
				status: "warning" as const,
				karyaCount: 7,
				joinDate: "15 Sep 2022",
			},
			{
				id: 101,
				name: "PT Telkom Indonesia",
				type: "mitra" as const,
				handle: null,
				email: "hr@telkom.co.id",
				nim: null,
				prodi: null,
				pic: "Budi Santoso",
				status: "active" as const,
				karyaCount: 5,
				joinDate: "12 Sep 2021",
			},
		],
);

const filteredUsers = computed(() => {
	return allUsers.value.filter((u) => {
		const searchLower = searchQuery.value.toLowerCase();
		const matchesSearch =
			u.name.toLowerCase().includes(searchLower) ||
			u.email.toLowerCase().includes(searchLower) ||
			(u.nim && u.nim.toLowerCase().includes(searchLower)) ||
			(u.handle && u.handle.toLowerCase().includes(searchLower)) ||
			(u.pic && u.pic.toLowerCase().includes(searchLower));

		const matchesType =
			filterType.value === "all" || u.type === filterType.value;

		const matchesStatus =
			filterStatus.value === "all" || u.status === filterStatus.value;

		return matchesSearch && matchesType && matchesStatus;
	});
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
			warningForm.reset();
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

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 overflow-hidden animate-fade-in">
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
                        class="rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 px-3 py-2 text-[12px] font-semibold text-slate-600 dark:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
                    >
                        <option value="all">Semua Tipe</option>
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="mitra">Mitra Perusahaan</option>
                    </select>

                    <!-- Status Filter -->
                    <select
                        v-model="filterStatus"
                        class="rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 px-3 py-2 text-[12px] font-semibold text-slate-600 dark:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
                    >
                        <option value="all">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="warning">Peringatan</option>
                        <option value="suspended">Ditangguhkan</option>
                    </select>
                </div>
                <span class="text-[11px] text-slate-400 dark:text-zinc-600 font-bold self-end lg:self-auto">
                    {{ filteredUsers.length }} pengguna ditemukan
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
                    <tbody class="divide-y divide-slate-50 dark:divide-zinc-800/50">
                        <tr
                            v-for="u in filteredUsers"
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
                                        u.type === 'mahasiswa' 
                                            ? 'bg-purple-50 text-purple-600 border border-purple-100 dark:bg-purple-950/20 dark:text-purple-400 dark:border-none' 
                                            : 'bg-blue-50 text-blue-600 border border-blue-100 dark:bg-blue-950/20 dark:text-blue-400 dark:border-none'
                                    ]"
                                >
                                    {{ u.type === 'mahasiswa' ? 'Mahasiswa' : 'Mitra Perusahaan' }}
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

                            <!-- Status -->
                            <td class="px-4 py-3.5">
                                <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-black', statusConfig[u.status]]">
                                    {{ statusLabel[u.status] }}
                                </span>
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
                                    <button class="rounded-lg border border-slate-200 dark:border-zinc-700 px-2.5 py-1.5 text-[11px] font-black text-slate-600 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors">
                                        Detail
                                    </button>
                                    <button 
                                        @click="handleWarn(u)" 
                                        class="rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/20 px-2.5 py-1.5 text-[11px] font-black text-amber-700 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors"
                                    >
                                        Warn
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
