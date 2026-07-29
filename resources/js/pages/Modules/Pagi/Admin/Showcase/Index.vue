<script setup lang="ts">
import { router, useForm } from "@inertiajs/vue3";
import { Bell, Check, Sparkles, Star } from "lucide-vue-next";
import { computed, ref } from "vue";
import FeatureShowcase, {
	type TabMedia,
} from "@/components/ui/FeatureShowcase.vue";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";

interface WorkItem {
	id: number;
	title: string;
	author: string;
	authorAvatar?: string;
	category: string;
	tags?: string[];
	thumbnail?: string;
	views: number;
	description: string;
	isComplete?: boolean;
	missingFields?: string[];
	createdAt?: string;
}

const props = defineProps<{
	allWorks: WorkItem[];
	showcaseConfig: {
		eyebrow: string;
		title: string;
		description: string;
		selectedWorkIds: number[];
	};
}>();

const form = useForm({
	eyebrow: props.showcaseConfig.eyebrow,
	title: props.showcaseConfig.title,
	description: props.showcaseConfig.description,
	selectedWorkIds: [...props.showcaseConfig.selectedWorkIds],
});

const isSelected = (id: number) => form.selectedWorkIds.includes(id);

const toggleWorkSelection = (id: number) => {
	if (isSelected(id)) {
		form.selectedWorkIds = form.selectedWorkIds.filter((wId) => wId !== id);
	} else {
		if (form.selectedWorkIds.length >= 5) return; // Recommended Max 5
		form.selectedWorkIds.push(id);
	}
};

const sendCompletenessRequest = (work: WorkItem) => {
	router.post(
		`/pagi/admin/showcase/request-completeness/${work.id}`,
		{},
		{ preserveScroll: true },
	);
};

// Preview tabs generated dynamically from selected works
const previewTabs = computed<TabMedia[]>(() => {
	return form.selectedWorkIds
		.map((id) => props.allWorks.find((w) => w.id === id))
		.filter((w): w is WorkItem => w !== undefined)
		.map((w) => ({
			value: `work-${w.id}`,
			label: w.title.length > 18 ? `${w.title.slice(0, 18)}...` : w.title,
			src:
				w.thumbnail ||
				"https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?w=800&q=80",
			alt: w.title,
			title: w.title,
			author: w.author,
			authorAvatar: w.authorAvatar,
			steps: [
				{
					id: "step-1",
					title: "💡 Konsep & Deskripsi Karya",
					text:
						w.description ||
						"Portofolio inovasi digital karya mahasiswa FMIKOM dengan standar antarmuka modern.",
				},
				{
					id: "step-2",
					title: "⚡ Teknologi & Tooling",
					text: `Diimplementasikan menggunakan: ${w.tags && w.tags.length > 0 ? w.tags.join(", ") : w.category}`,
				},
				{
					id: "step-3",
					title: "👥 Kreator & Kolaborator Karya",
					text: `Dirancang oleh ${w.author} (Dipublikasikan pada ${w.createdAt || "Terverifikasi"})`,
					authorName: w.author,
					authorAvatar: w.authorAvatar,
				},
			],
		}));
});

const submitSave = () => {
	form.post("/pagi/admin/showcase", {
		preserveScroll: true,
	});
};
</script>

<template>
    <PagiAdminLayout title="Karya Terbaik">
        <!-- Header -->
        <div class="mb-6 flex items-start justify-between gap-3 flex-wrap">
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    <Sparkles class="h-5 w-5 text-indigo-500" />
                    Karya Terbaik
                </h1>
                <p class="mt-0.5 text-[13px] text-slate-400 dark:text-zinc-500">
                    Pilih dan kelola karya unggulan mahasiswa yang ditayangkan di Halaman Utama Portal FMIKOM
                </p>
            </div>
            <button
                @click="submitSave"
                :disabled="form.processing"
                class="rounded-xl bg-indigo-600 hover:bg-indigo-700 px-5 py-2.5 text-[12.5px] font-bold text-white shadow-lg shadow-indigo-500/20 transition-all disabled:opacity-50 flex items-center gap-2 cursor-pointer"
            >
                <Sparkles class="h-4 w-4" />
                <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Karya Terbaik' }}</span>
            </button>
        </div>

        <!-- Section 1: Form Config -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-1 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-5 space-y-4 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 dark:text-zinc-100 pb-2 border-b border-slate-100 dark:border-zinc-800">
                    Konfigurasi Teks Showcase
                </h3>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">Eyebrow (Label Atas)</label>
                    <input
                        v-model="form.eyebrow"
                        type="text"
                        placeholder="Contoh: Inovasi Mahasiswa FMIKOM"
                        class="w-full h-10 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 text-[12px] font-semibold text-slate-700 dark:text-zinc-200 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                    />
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">Judul Utama Showcase</label>
                    <input
                        v-model="form.title"
                        type="text"
                        placeholder="Contoh: Portofolio Unggulan Berstandar Industri"
                        class="w-full h-10 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 text-[12px] font-semibold text-slate-700 dark:text-zinc-200 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                    />
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">Deskripsi Narasi</label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        placeholder="Deskripsi singkat seputar karya mahasiswa..."
                        class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-[12px] font-medium text-slate-700 dark:text-zinc-200 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none resize-none"
                    />
                </div>
            </div>

            <!-- Section 2: Work Selector Grid -->
            <div class="lg:col-span-2 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-5 space-y-4 shadow-sm">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-zinc-800">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-zinc-100">
                            Pilih Karya Unggulan (Dipilih {{ form.selectedWorkIds.length }} dari maks 5)
                        </h3>
                        <p class="text-[11px] text-slate-400">Klik card karya untuk menambah atau mengurangi dari Halaman Utama</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[380px] overflow-y-auto pr-1">
                    <div
                        v-for="work in allWorks"
                        :key="work.id"
                        @click="toggleWorkSelection(work.id)"
                        :class="[
                            'p-3 rounded-xl border transition-all cursor-pointer flex items-start gap-3 relative overflow-hidden',
                            isSelected(work.id)
                                ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20 ring-2 ring-indigo-500/30'
                                : 'border-slate-100 dark:border-zinc-800 hover:border-slate-200 dark:hover:border-zinc-700 bg-white dark:bg-zinc-900'
                        ]"
                    >
                        <div class="h-12 w-14 rounded-lg bg-slate-100 dark:bg-zinc-800 overflow-hidden shrink-0 border border-slate-200/50 dark:border-zinc-700/50 mt-0.5">
                            <img v-if="work.thumbnail" :src="work.thumbnail" :alt="work.title" class="h-full w-full object-cover" />
                            <div v-else class="h-full w-full flex items-center justify-center text-slate-400">
                                <Star class="h-4 w-4" />
                            </div>
                        </div>
                        <div class="min-w-0 flex-1 space-y-1">
                            <h4 class="text-[12.5px] font-bold text-slate-800 dark:text-zinc-100 truncate">{{ work.title }}</h4>
                            <p class="text-[11px] text-slate-400 dark:text-zinc-500 truncate font-semibold">{{ work.author }}</p>
                            
                            <!-- Completeness Status Badge -->
                            <div class="flex items-center gap-1.5 flex-wrap pt-0.5">
                                <span
                                    class="inline-flex items-center text-[9.5px] font-extrabold px-1.5 py-0.5 rounded-md"
                                    :class="work.isComplete
                                        ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-900/40'
                                        : 'bg-amber-50 text-amber-600 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200/60 dark:border-amber-900/40'"
                                >
                                    {{ work.isComplete ? '✓ Lengkap' : '⚠️ Perlu Disesuaikan' }}
                                </span>

                                <button
                                    v-if="!work.isComplete"
                                    @click.stop="sendCompletenessRequest(work)"
                                    class="inline-flex items-center gap-1 text-[9.5px] font-extrabold px-1.5 py-0.5 rounded-md bg-indigo-50 hover:bg-indigo-100 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 transition-colors border border-indigo-200/60 dark:border-indigo-800/40"
                                    title="Kirim notifikasi ke mahasiswa agar melengkapi foto sampul/deskripsi"
                                >
                                    <Bell class="h-2.5 w-2.5" />
                                    <span>Minta Lengkapi</span>
                                </button>
                            </div>
                        </div>
                        <div v-if="isSelected(work.id)" class="h-6 w-6 rounded-full bg-indigo-600 text-white flex items-center justify-center shrink-0 mt-0.5">
                            <Check class="h-3.5 w-3.5 stroke-[3]" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Real-time Feature Showcase Live Preview -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Pratinjau Langsung (Live Preview Showcase)</span>
                <span class="text-[11px] text-slate-400">Pratinjau tampilan interaktif yang akan muncul di Halaman Depan Portal</span>
            </div>

            <FeatureShowcase
                :eyebrow="form.eyebrow"
                :title="form.title"
                :description="form.description"
                :tabs="previewTabs"
                :panelMinHeight="640"
            />
        </div>
    </PagiAdminLayout>
</template>
