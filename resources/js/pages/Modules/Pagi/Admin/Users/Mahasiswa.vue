<script setup lang="ts">
import { computed, ref } from "vue";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";

const props = defineProps<{
	users?: Array<{
		id: number;
		name: string;
		handle: string;
		email: string;
		nim: string;
		prodi: string;
		status: "active" | "warning" | "suspended";
		karyaCount: number;
		joinDate: string;
	}>;
}>();

const searchQuery = ref("");
const filterStatus = ref("all");

const allUsers = computed(
	() =>
		props.users ?? [
			{
				id: 1,
				name: "Sarah Aulia",
				handle: "@sarah.ui",
				email: "sarah@student.fmikom.ac.id",
				nim: "2021010001",
				prodi: "DKV",
				status: "active",
				karyaCount: 24,
				joinDate: "12 Sep 2021",
			},
			{
				id: 2,
				name: "Naufal Dzaky",
				handle: "@naufal.dev",
				email: "naufal@student.fmikom.ac.id",
				nim: "2021010002",
				prodi: "TI",
				status: "active",
				karyaCount: 18,
				joinDate: "12 Sep 2021",
			},
			{
				id: 3,
				name: "Dimas Wirawan",
				handle: "@dimas.w",
				email: "dimas@student.fmikom.ac.id",
				nim: "2022010015",
				prodi: "DKV",
				status: "warning",
				karyaCount: 7,
				joinDate: "15 Sep 2022",
			},
			{
				id: 4,
				name: "Rizki Design",
				handle: "@rizki.design",
				email: "rizki@student.fmikom.ac.id",
				nim: "2022010030",
				prodi: "MM",
				status: "warning",
				karyaCount: 12,
				joinDate: "15 Sep 2022",
			},
			{
				id: 5,
				name: "Johan Triwibowo",
				handle: "@johan.3d",
				email: "johan@student.fmikom.ac.id",
				nim: "2020010045",
				prodi: "DKV",
				status: "active",
				karyaCount: 31,
				joinDate: "10 Sep 2020",
			},
			{
				id: 6,
				name: "Fitria Nur",
				handle: "@fitria.nur",
				email: "fitria@student.fmikom.ac.id",
				nim: "2023010008",
				prodi: "TI",
				status: "active",
				karyaCount: 5,
				joinDate: "14 Sep 2023",
			},
			{
				id: 7,
				name: "Bima Dev",
				handle: "@bima.dev",
				email: "bima@student.fmikom.ac.id",
				nim: "2021010089",
				prodi: "TI",
				status: "suspended",
				karyaCount: 0,
				joinDate: "12 Sep 2021",
			},
		],
);

const filteredUsers = computed(() => {
	return allUsers.value.filter((u) => {
		const matchesSearch =
			u.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
			u.nim.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
			u.handle.toLowerCase().includes(searchQuery.value.toLowerCase());
		const matchesStatus =
			filterStatus.value === "all" || u.status === filterStatus.value;
		return matchesSearch && matchesStatus;
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

import { useForm } from "@inertiajs/vue3";

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
    <PagiAdminLayout title="Mahasiswa">
        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Manajemen Mahasiswa</h1>
                <p class="mt-0.5 text-[13px] text-slate-400 dark:text-zinc-500">Kelola akun dan akses mahasiswa di modul PAGI</p>
            </div>
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 overflow-hidden">

            <!-- Toolbar -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 px-5 py-4 border-b border-slate-100 dark:border-zinc-800">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <div class="relative flex-1 sm:flex-none">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari mahasiswa..."
                            class="w-full sm:w-[240px] rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 pl-9 pr-4 py-2 text-[12px] font-medium text-slate-700 dark:text-zinc-300 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        />
                    </div>
                    <select
                        v-model="filterStatus"
                        class="rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 px-3 py-2 text-[12px] font-medium text-slate-600 dark:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="all">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="warning">Peringatan</option>
                        <option value="suspended">Ditangguhkan</option>
                    </select>
                </div>
                <span class="text-[11px] text-slate-400 dark:text-zinc-600 font-medium">{{ filteredUsers.length }} mahasiswa terdaftar</span>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px]">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-zinc-800">
                            <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Mahasiswa</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 hidden md:table-cell">NIM</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 hidden md:table-cell">Prodi</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 hidden lg:table-cell">Karya</th>
                            <th class="px-5 py-3 text-right text-[10px] font-black uppercase tracking-widest text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-zinc-800/50">
                        <tr
                            v-for="u in filteredUsers"
                            :key="u.id"
                            class="group hover:bg-slate-50 dark:hover:bg-zinc-800/30 transition-colors"
                        >
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 shrink-0 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-[13px] font-black text-indigo-700 dark:text-indigo-300">
                                        {{ u.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-semibold text-slate-800 dark:text-zinc-100">{{ u.name }}</p>
                                        <p class="text-[11px] text-slate-400 dark:text-zinc-500">{{ u.handle }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 hidden md:table-cell">
                                <span class="font-mono text-[12px] text-slate-500 dark:text-zinc-500">{{ u.nim }}</span>
                            </td>
                            <td class="px-4 py-3.5 hidden md:table-cell">
                                <span class="text-[12px] text-slate-600 dark:text-zinc-400">{{ u.prodi }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-black', statusConfig[u.status]]">
                                    {{ statusLabel[u.status] }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 hidden lg:table-cell">
                                <span class="text-[12px] font-semibold text-slate-700 dark:text-zinc-300">{{ u.karyaCount }}</span>
                                <span class="text-[11px] text-slate-400 ml-1">karya</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button class="rounded-lg border border-slate-200 dark:border-zinc-700 px-2.5 py-1.5 text-[11px] font-bold text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-colors">Detail</button>
                                    <button @click="handleWarn(u)" class="rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 px-2.5 py-1.5 text-[11px] font-bold text-amber-700 dark:text-amber-400 hover:bg-amber-100 transition-colors">Warn</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Warn Student Modal -->
        <div v-if="showWarnModal && activeUser" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-all animate-fade-in">
            <div class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl border border-slate-100 dark:border-zinc-800 shadow-xl overflow-hidden animate-slide-up flex flex-col text-left">
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-zinc-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800 dark:text-zinc-100">Kirim Peringatan Resmi</h2>
                        <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5">Mahasiswa: {{ activeUser.name }} ({{ activeUser.nim }})</p>
                    </div>
                    <button @click="showWarnModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300">
                        <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Form Body -->
                <div class="p-6 space-y-4">
                    <div class="p-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/30 rounded-xl">
                        <p class="text-[11px] text-amber-700 dark:text-amber-400 leading-relaxed font-medium">
                            Karya atau aktivitas mahasiswa ini terdeteksi melanggar aturan portal. Peringatan akan dikirimkan dan mengubah status akunnya menjadi Peringatan.
                        </p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 mb-1">Alasan Peringatan</label>
                        <textarea 
                            v-model="warningForm.reason" 
                            rows="4" 
                            placeholder="Tuliskan pelanggaran secara spesifik (misal: spam portofolio, mencantumkan hak cipta orang lain tanpa lisensi)..."
                            class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-[12px] font-medium text-slate-700 dark:text-zinc-300 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                        />
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-slate-100 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-800/40">
                    <button 
                        @click="showWarnModal = false" 
                        class="rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-4 py-2 text-[12px] font-bold text-slate-600 dark:text-zinc-400 hover:bg-slate-50 transition-colors"
                    >
                        Batal
                    </button>
                    <button 
                        @click="submitWarning"
                        :disabled="warningForm.processing || !warningForm.reason"
                        class="rounded-xl bg-indigo-600 px-4 py-2 text-[12px] font-bold text-white hover:bg-indigo-700 disabled:opacity-50 transition-colors shadow-sm"
                    >
                        {{ warningForm.processing ? 'Mengirim...' : 'Kirim Peringatan' }}
                    </button>
                </div>
            </div>
        </div>

    </PagiAdminLayout>
</template>
