<script setup lang="ts">
import { computed, ref } from "vue";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";

const props = defineProps<{
	mitras?: Array<{
		id: number;
		name: string;
		email: string;
		pic: string;
		status: "active" | "warning" | "suspended";
		karyaCount: number;
		joinDate: string;
	}>;
}>();

const searchQuery = ref("");
const filterStatus = ref("all");

const allMitras = computed(
	() =>
		props.mitras ?? [
			{
				id: 1,
				name: "PT Telkom Indonesia",
				email: "hr@telkom.co.id",
				pic: "Budi Santoso",
				status: "active",
				karyaCount: 5,
				joinDate: "12 Sep 2021",
			},
			{
				id: 2,
				name: "Gojek Tokopedia (GoTo)",
				email: "partners@goto.com",
				pic: "Andi Wijaya",
				status: "active",
				karyaCount: 10,
				joinDate: "15 Oct 2022",
			},
			{
				id: 3,
				name: "Shopee Indonesia",
				email: "career@shopee.co.id",
				pic: "Sinta",
				status: "warning",
				karyaCount: 2,
				joinDate: "20 Nov 2022",
			},
			{
				id: 4,
				name: "Ruangguru",
				email: "info@ruangguru.com",
				pic: "Deni",
				status: "active",
				karyaCount: 8,
				joinDate: "10 Jan 2023",
			},
		],
);

const filteredMitras = computed(() => {
	return allMitras.value.filter((m) => {
		const matchesSearch =
			m.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
			m.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
			m.pic.toLowerCase().includes(searchQuery.value.toLowerCase());
		const matchesStatus =
			filterStatus.value === "all" || m.status === filterStatus.value;
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
</script>

<template>
    <PagiAdminLayout title="Mitra Perusahaan">
        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Manajemen Mitra</h1>
                <p class="mt-0.5 text-[13px] text-slate-400 dark:text-zinc-500">Kelola akun dan akses perusahaan mitra di modul PAGI</p>
            </div>
            <button class="rounded-xl bg-indigo-600 px-4 py-2 text-[12px] font-bold text-white hover:bg-indigo-700 transition-colors shadow-sm shadow-indigo-200 dark:shadow-none">
                + Tambah Mitra
            </button>
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
                            placeholder="Cari mitra..."
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
                <span class="text-[11px] text-slate-400 dark:text-zinc-600 font-medium">{{ filteredMitras.length }} mitra terdaftar</span>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px]">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-zinc-800">
                            <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Nama Perusahaan</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 hidden md:table-cell">PIC</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 hidden lg:table-cell">Bergabung</th>
                            <th class="px-5 py-3 text-right text-[10px] font-black uppercase tracking-widest text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-zinc-800/50">
                        <tr
                            v-for="m in filteredMitras"
                            :key="m.id"
                            class="group hover:bg-slate-50 dark:hover:bg-zinc-800/30 transition-colors"
                        >
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 shrink-0 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-[13px] font-black text-indigo-700 dark:text-indigo-300">
                                        {{ m.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-semibold text-slate-800 dark:text-zinc-100">{{ m.name }}</p>
                                        <p class="text-[11px] text-slate-400 dark:text-zinc-500">{{ m.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 hidden md:table-cell">
                                <span class="text-[12px] text-slate-600 dark:text-zinc-400">{{ m.pic }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-black', statusConfig[m.status]]">
                                    {{ statusLabel[m.status] }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 hidden lg:table-cell">
                                <span class="text-[12px] font-semibold text-slate-700 dark:text-zinc-300">{{ m.joinDate }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button class="rounded-lg border border-slate-200 dark:border-zinc-700 px-2.5 py-1.5 text-[11px] font-bold text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-colors">Detail</button>
                                    <button class="rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 px-2.5 py-1.5 text-[11px] font-bold text-amber-700 dark:text-amber-400 hover:bg-amber-100 transition-colors">Warn</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </PagiAdminLayout>
</template>
