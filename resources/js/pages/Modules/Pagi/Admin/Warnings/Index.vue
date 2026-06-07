<script setup lang="ts">
import { router, useForm } from "@inertiajs/vue3";
import {
	CheckCircle2,
	Filter,
	RefreshCw,
	Search,
	ShieldAlert,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";

interface WarningItem {
	id: number;
	user: string;
	userHandle: string;
	userId: number;
	workId: number | null;
	workTitle: string | null;
	reason: string;
	severity: "low" | "medium" | "high";
	type: string;
	issuedBy: string;
	time: string;
	expiresAt: string;
	isActive: boolean;
}

const props = defineProps<{
	warnings?: WarningItem[];
}>();

const computedWarnings = computed(() => props.warnings ?? []);

// Search & filter states
const searchQuery = ref("");
const selectedStatus = ref("active");
const isLoading = ref(false);

// Filtered warnings
const filteredWarnings = computed(() => {
	return computedWarnings.value.filter((w) => {
		const matchSearch =
			w.user.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
			w.reason.toLowerCase().includes(searchQuery.value.toLowerCase());
		const matchStatus =
			selectedStatus.value === "all" ||
			(selectedStatus.value === "active" && w.isActive) ||
			(selectedStatus.value === "inactive" && !w.isActive);
		return matchSearch && matchStatus;
	});
});

// Severity Badge Color
const severityColor = {
	low: "bg-blue-50 text-blue-600 dark:bg-blue-950/20 dark:text-blue-400 border-blue-100 dark:border-blue-900/30",
	medium:
		"bg-amber-50 text-amber-600 dark:bg-amber-950/20 dark:text-amber-400 border-amber-100 dark:border-amber-900/30",
	high: "bg-rose-50 text-rose-600 dark:bg-rose-950/20 dark:text-rose-400 border-rose-100 dark:border-rose-900/30",
};

const revokeWarning = (warning: WarningItem) => {
	if (
		confirm(
			`Apakah Anda yakin ingin mencabut peringatan untuk mahasiswa ${warning.user}?`,
		)
	) {
		isLoading.value = true;
		router.post(
			`/pagi/admin/warnings/${warning.id}/revoke`,
			{},
			{
				onSuccess: () => {
					isLoading.value = false;
				},
				onError: () => {
					isLoading.value = false;
				},
			},
		);
	}
};
</script>

<template>
    <PagiAdminLayout title="Peringatan Aktif">
        
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Peringatan Aktif</h1>
            <p class="mt-0.5 text-[13px] text-slate-400 dark:text-zinc-500">Kelola riwayat surat peringatan resmi yang diterbitkan untuk akun mahasiswa</p>
        </div>

        <!-- Filters / Search -->
        <div class="mb-5 grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="md:col-span-2 relative flex items-center h-10 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm px-3">
                <Search class="h-4 w-4 text-slate-400 shrink-0 mr-2.5" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari nama mahasiswa atau alasan peringatan..."
                    class="w-full bg-transparent text-[13px] font-medium text-slate-700 dark:text-zinc-200 placeholder-slate-400 focus:outline-none"
                />
            </div>
            <div class="relative flex items-center h-10 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm px-2">
                <Filter class="h-3.5 w-3.5 text-slate-400 shrink-0 ml-1.5 mr-2" />
                <select v-model="selectedStatus" class="w-full bg-transparent text-[12px] font-semibold text-slate-600 dark:text-zinc-400 focus:outline-none">
                    <option value="active">Peringatan Aktif</option>
                    <option value="inactive">Dicabut / Kadaluarsa</option>
                    <option value="all">Semua Warnings</option>
                </select>
            </div>
        </div>

        <!-- Table / Cards list -->
        <div class="space-y-4">
            <!-- Mobile cards view (block md:hidden) -->
            <div class="block md:hidden space-y-4">
                <div v-for="w in filteredWarnings" :key="w.id" class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-4 space-y-3 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">{{ w.user }}</h3>
                            <p class="text-[10.5px] text-slate-400 dark:text-zinc-500">{{ w.userHandle }}</p>
                        </div>
                        <span :class="['inline-flex items-center rounded-full border px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider', severityColor[w.severity]]">
                            {{ w.severity }}
                        </span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-zinc-800/40 border border-slate-100 dark:border-zinc-800 text-[11.5px] text-slate-500 dark:text-zinc-400 font-medium leading-relaxed">
                        "{{ w.reason }}"
                        <p v-if="w.workTitle" class="mt-1.5 text-[10.5px] text-slate-400 dark:text-zinc-500 font-normal">
                            Terkait karya: <strong>{{ w.workTitle }}</strong>
                        </p>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-50 dark:border-zinc-800/40 pt-3">
                        <div class="text-[10.5px] text-slate-400 dark:text-zinc-500 space-y-0.5">
                            <p>Diterbitkan: {{ w.time }}</p>
                            <p>Berlaku s/d: {{ w.expiresAt }}</p>
                        </div>
                        <button
                            v-if="w.isActive"
                            @click="revokeWarning(w)"
                            class="rounded-lg border border-rose-200 dark:border-rose-900/50 bg-rose-50 dark:bg-rose-950/20 px-3 py-1 text-[11px] font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-100/50 transition-colors"
                        >
                            Cabut
                        </button>
                        <span v-else class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                            <CheckCircle2 class="h-3 w-3" /> Dicabut
                        </span>
                    </div>
                </div>
            </div>

            <!-- Desktop table (hidden md:block) -->
            <div class="hidden md:block rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 overflow-hidden shadow-sm">
                <table class="w-full min-w-[750px]">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-zinc-800">
                            <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Mahasiswa</th>
                            <th class="px-4 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Alasan Peringatan</th>
                            <th class="px-4 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Tingkat</th>
                            <th class="px-4 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Diterbitkan Oleh</th>
                            <th class="px-4 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Masa Berlaku</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-zinc-800/50">
                        <tr v-for="w in filteredWarnings" :key="w.id" class="hover:bg-slate-50 dark:hover:bg-zinc-800/20 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-[12.5px] font-semibold text-slate-800 dark:text-zinc-100">{{ w.user }}</p>
                                <p class="text-[11px] text-slate-400 dark:text-zinc-500">{{ w.userHandle }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <div class="max-w-[280px]">
                                    <p class="text-[12px] font-medium text-slate-700 dark:text-zinc-300 leading-tight">{{ w.reason }}</p>
                                    <p v-if="w.workTitle" class="text-[10.5px] text-slate-400 dark:text-zinc-500 mt-1 truncate">
                                        Karya: {{ w.workTitle }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span :class="['inline-flex items-center rounded-full border px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-wider', severityColor[w.severity]]">
                                    {{ w.severity }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-[12px] font-medium text-slate-700 dark:text-zinc-300">{{ w.issuedBy }}</p>
                                <p class="text-[10.5px] text-slate-400 dark:text-zinc-500">{{ w.time }}</p>
                            </td>
                            <td class="px-4 py-4 text-[11px] text-slate-500 dark:text-zinc-400">
                                {{ w.expiresAt }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    v-if="w.isActive"
                                    @click="revokeWarning(w)"
                                    class="rounded-lg border border-rose-200 dark:border-rose-900/50 bg-rose-50 dark:bg-rose-950/20 px-3 py-1.5 text-[11px] font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-100/50 transition-all shadow-sm"
                                >
                                    Revoke Warning
                                </button>
                                <span v-else class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center justify-end gap-1">
                                    <CheckCircle2 class="h-3 w-3" /> Dicabut
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="filteredWarnings.length === 0" class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-12 text-center">
                <div class="mx-auto h-12 w-12 rounded-full bg-slate-50 dark:bg-zinc-800 flex items-center justify-center mb-3">
                    <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-[14px] font-bold text-slate-800 dark:text-zinc-100">Peringatan tidak ditemukan</h3>
                <p class="text-[12px] text-slate-500 dark:text-zinc-400 mt-1">Belum ada catatan peringatan yang cocok dengan filter aktif Anda.</p>
            </div>
        </div>

    </PagiAdminLayout>
</template>
