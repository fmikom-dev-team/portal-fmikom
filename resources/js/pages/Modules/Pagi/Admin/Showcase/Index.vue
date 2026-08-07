<script setup lang="ts">
import { router, useForm } from "@inertiajs/vue3";
import {
	Bell,
	Check,
	Image as ImageIcon,
	Plus,
	Search,
	X,
} from "lucide-vue-next";
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

const searchQuery = ref("");

const isSelected = (id: number) => form.selectedWorkIds.includes(id);

const selectedWorksList = computed(() => {
	return form.selectedWorkIds
		.map((id) => props.allWorks.find((w) => w.id === id))
		.filter((w): w is WorkItem => w !== undefined);
});

const filteredCatalogWorks = computed(() => {
	if (!searchQuery.value.trim()) return props.allWorks;
	const q = searchQuery.value.toLowerCase().trim();
	return props.allWorks.filter(
		(w) =>
			w.title.toLowerCase().includes(q) ||
			w.author.toLowerCase().includes(q) ||
			w.category.toLowerCase().includes(q),
	);
});

const unpinWork = (id: number) => {
	form.selectedWorkIds = form.selectedWorkIds.filter((wId) => wId !== id);
};

const pinWork = (id: number) => {
	if (isSelected(id)) return;
	if (form.selectedWorkIds.length >= 5) return;
	form.selectedWorkIds.push(id);
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
				<h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
					Karya Terbaik
				</h1>
				<p class="mt-0.5 text-[13px] text-slate-400 dark:text-zinc-500">
					Pilih dan kelola karya unggulan mahasiswa yang ditayangkan di Halaman Utama Portal FMIKOM
				</p>
			</div>
			<button
				@click="submitSave"
				:disabled="form.processing"
				class="rounded-xl bg-indigo-600 hover:bg-indigo-700 px-5 py-2.5 text-[12.5px] font-bold text-white shadow-lg shadow-indigo-500/20 transition-all disabled:opacity-50 flex items-center gap-2 cursor-pointer border-none"
			>
				<span>{{ form.processing ? 'Menyimpan...' : 'Simpan Karya Terbaik' }}</span>
			</button>
		</div>

		<!-- Grid Layout Top: Config Form & Active Pinned Showcase -->
		<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
			<!-- Config Form (4 cols) -->
			<div class="lg:col-span-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-5 space-y-4 shadow-sm">
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

			<!-- Active Pinned Showcase List (8 cols) -->
			<div class="lg:col-span-8 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-5 space-y-4 shadow-sm">
				<div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-zinc-800 flex-wrap gap-2">
					<div>
						<h3 class="text-sm font-bold text-slate-800 dark:text-zinc-100 flex items-center gap-2">
							<span>Karya Terpilih saat Ini</span>
							<span class="px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-[11px] font-black border border-indigo-100 dark:border-indigo-900/50">
								{{ form.selectedWorkIds.length }} / 5 Slot Terisi
							</span>
						</h3>
						<p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5">
							Aksi <span class="font-bold text-slate-600 dark:text-zinc-400">"Lepas"</span> hanya mengeluarkan karya dari Halaman Utama Portal, dan tidak menghapus karya asli mahasiswa.
						</p>
					</div>
				</div>

				<!-- Selected Works List -->
				<div v-if="selectedWorksList.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[300px] overflow-y-auto pr-1">
					<div
						v-for="work in selectedWorksList"
						:key="work.id"
						class="p-3 rounded-xl border border-indigo-500/60 bg-indigo-50/40 dark:bg-indigo-950/20 flex items-start justify-between gap-3 relative overflow-hidden transition-all shadow-xs"
					>
						<div class="flex items-start gap-3 min-w-0 flex-1">
							<div class="h-12 w-14 rounded-lg bg-slate-100 dark:bg-zinc-800 overflow-hidden shrink-0 border border-slate-200/50 dark:border-zinc-700/50 mt-0.5">
								<img v-if="work.thumbnail" :src="work.thumbnail" :alt="work.title" class="h-full w-full object-cover" />
								<div v-else class="h-full w-full flex items-center justify-center text-slate-400">
									<ImageIcon class="h-4 w-4" />
								</div>
							</div>
							<div class="min-w-0 flex-1 space-y-1">
								<h4 class="text-[12.5px] font-bold text-slate-900 dark:text-zinc-100 truncate">{{ work.title }}</h4>
								<p class="text-[11px] text-slate-500 dark:text-zinc-400 truncate font-semibold">{{ work.author }}</p>
								<div class="flex items-center gap-1.5 flex-wrap pt-0.5">
									<span
										class="inline-flex items-center text-[9.5px] font-extrabold px-1.5 py-0.5 rounded-md"
										:class="work.isComplete
											? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-900/40'
											: 'bg-amber-50 text-amber-600 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200/60 dark:border-amber-900/40'"
									>
										{{ work.isComplete ? '✓ Lengkap' : '⚠️ Perlu Disesuaikan' }}
									</span>
								</div>
							</div>
						</div>

						<!-- Unpin Button -->
						<button
							@click="unpinWork(work.id)"
							class="shrink-0 px-2.5 py-1 text-[11px] font-bold text-red-600 hover:text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-lg border border-red-200 dark:border-red-900/50 transition-all flex items-center gap-1 cursor-pointer bg-transparent"
							title="Lepaskan karya ini dari Showcase Halaman Utama"
						>
							<X class="w-3.5 h-3.5" />
							<span>Lepas</span>
						</button>
					</div>
				</div>

				<!-- Empty State if no works selected -->
				<div v-else class="flex flex-col items-center justify-center py-10 text-center border-2 border-dashed border-slate-200 dark:border-zinc-800 rounded-xl bg-slate-50/50 dark:bg-zinc-900/30">
					<ImageIcon class="h-8 w-8 text-slate-300 dark:text-zinc-600 mb-2" />
					<p class="text-xs font-bold text-slate-700 dark:text-zinc-300">Belum ada karya terpilih</p>
					<p class="text-[11px] text-slate-400 dark:text-zinc-500 max-w-sm mt-0.5">
						Pilih karya dari katalog di bawah untuk menambahkannya ke Halaman Utama Portal.
					</p>
				</div>
			</div>
		</div>

		<!-- Section 3: Catalog All Works (Pilih untuk Ditambahkan) -->
		<div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-5 space-y-4 shadow-sm mb-8">
			<div class="flex items-center justify-between gap-4 flex-wrap pb-3 border-b border-slate-100 dark:border-zinc-800">
				<div>
					<h3 class="text-sm font-bold text-slate-800 dark:text-zinc-100">
						Katalog Semua Karya Mahasiswa
					</h3>
					<p class="text-[11px] text-slate-400 dark:text-zinc-500">Pilih karya untuk ditambahkan ke daftar Karya Terbaik di Halaman Utama</p>
				</div>

				<!-- Search Input -->
				<div class="relative w-full sm:w-64">
					<Search class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-3" />
					<input
						v-model="searchQuery"
						type="text"
						placeholder="Cari karya atau mahasiswa..."
						class="w-full h-9 pl-8 pr-3 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-[11.5px] font-semibold text-slate-700 dark:text-zinc-200 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
					/>
				</div>
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 max-h-[380px] overflow-y-auto pr-1">
				<div
					v-for="work in filteredCatalogWorks"
					:key="work.id"
					:class="[
						'p-3 rounded-xl border transition-all flex items-start justify-between gap-3 relative overflow-hidden',
						isSelected(work.id)
							? 'border-indigo-500 bg-indigo-50/30 dark:bg-indigo-950/20'
							: 'border-slate-150 dark:border-zinc-800 hover:border-slate-300 dark:hover:border-zinc-700 bg-white dark:bg-zinc-900'
					]"
				>
					<div class="flex items-start gap-3 min-w-0 flex-1">
						<div class="h-12 w-14 rounded-lg bg-slate-100 dark:bg-zinc-800 overflow-hidden shrink-0 border border-slate-200/50 dark:border-zinc-700/50 mt-0.5">
							<img v-if="work.thumbnail" :src="work.thumbnail" :alt="work.title" class="h-full w-full object-cover" />
							<div v-else class="h-full w-full flex items-center justify-center text-slate-400">
								<ImageIcon class="h-4 w-4" />
							</div>
						</div>
						<div class="min-w-0 flex-1 space-y-1">
							<h4 class="text-[12.5px] font-bold text-slate-800 dark:text-zinc-100 truncate">{{ work.title }}</h4>
							<p class="text-[11px] text-slate-400 dark:text-zinc-500 truncate font-semibold">{{ work.author }}</p>
							
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
									class="inline-flex items-center gap-1 text-[9.5px] font-extrabold px-1.5 py-0.5 rounded-md bg-indigo-50 hover:bg-indigo-100 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 transition-colors border border-indigo-200/60 dark:border-indigo-800/40 cursor-pointer"
									title="Kirim notifikasi ke mahasiswa agar melengkapi foto sampul/deskripsi"
								>
									<Bell class="h-2.5 w-2.5" />
									<span>Minta Lengkapi</span>
								</button>
							</div>
						</div>
					</div>

					<!-- Selection Status / Action Button -->
					<div class="shrink-0">
						<button
							v-if="isSelected(work.id)"
							@click="unpinWork(work.id)"
							class="px-2 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 text-[10.5px] font-bold flex items-center gap-1 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all cursor-pointer group"
							title="Klik untuk melepaskan dari Showcase"
						>
							<Check class="w-3 h-3 group-hover:hidden" />
							<X class="w-3 h-3 hidden group-hover:inline" />
							<span class="group-hover:hidden">Terpilih</span>
							<span class="hidden group-hover:inline">Lepas</span>
						</button>
						<button
							v-else
							@click="pinWork(work.id)"
							:disabled="form.selectedWorkIds.length >= 5"
							class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-200 dark:disabled:bg-zinc-800 disabled:text-slate-400 text-white text-[10.5px] font-bold flex items-center gap-1 transition-all cursor-pointer disabled:cursor-not-allowed border-none shadow-xs"
						>
							<Plus class="w-3 h-3" />
							<span>Pin Karya</span>
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Section 4: Real-time Feature Showcase Live Preview -->
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
