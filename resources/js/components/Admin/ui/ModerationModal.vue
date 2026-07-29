<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";

interface ModerationItem {
	id: number;
	title: string;
	author: string;
	authorHandle: string;
	type: "Laporan" | "Karya Baru" | "Komentar";
	reportedBy?: string;
	time: string;
	status: "active" | "warning" | "hidden" | "removed" | "pending";
	thumbnail?: string | null;
	userId: number;
	description: string;
	category: string;
	reportReason: string;
	reportDescription: string;
	reporterHandle: string;
}

const props = withDefaults(
	defineProps<{
		show: boolean;
		item: ModerationItem | null;
		availableTabs?: Array<"dismiss" | "takedown" | "warn">;
	}>(),
	{
		availableTabs: () => ["dismiss", "takedown", "warn"],
	},
);

const emit = defineEmits<{
	(e: "close"): void;
	(e: "success"): void;
}>();

// Tab state: 3 main decision actions
const activeTab = ref<"dismiss" | "takedown" | "warn">("dismiss");

// Integrated Checkbox states
const notifyUserOnTakedown = ref(true);
const alsoHideContentOnWarn = ref(true);

// Forms setup
const dismissForm = useForm({
	action: "dismiss",
	reason: "Aman, laporan tidak terbukti melanggar panduan.",
});

const takedownForm = useForm({
	action: "hide",
	reason: "",
});

const warningForm = useForm({
	reason: "",
	content_id: null as number | null,
});

// Reset forms when item changes
watch(
	() => props.item,
	(newItem) => {
		if (newItem) {
			dismissForm.reason = "Aman, laporan tidak terbukti melanggar panduan.";

			takedownForm.reason = "";
			takedownForm.action = "hide";

			warningForm.reason = "";
			warningForm.content_id = newItem.id;
		}
	},
	{ immediate: true },
);

// Preset Templates for quick admin selection
const dismissPresets = [
	"Aman, laporan tidak terbukti melanggar panduan.",
	"Konten bersifat artistik dan sesuai etika galeri kampus.",
	"Laporan tidak memiliki bukti pendukung yang cukup.",
];

const takedownPresets = [
	"Konten Mengandung Indikasi Spam / Judol",
	"Pelanggaran Hak Cipta & Plagiarisme Karya",
	"Gambar / Visual Tidak Pantas / SARA",
	"Informasi Palsu / Misinformasi Akademik",
];

const warnPresets = [
	"Pelanggaran Pedoman Komunitas PAGI Portal",
	"Penggunaan Bahasa / Kata Tidak Pantas",
	"Konten Menyesatkan / Plagiarisme Berulang",
	"Pengulangan Spamming Portofolio & Komentar",
];

const applyPreset = (formType: "dismiss" | "takedown" | "warn", text: string) => {
	if (formType === "dismiss") dismissForm.reason = text;
	if (formType === "takedown") takedownForm.reason = text;
	if (formType === "warn") warningForm.reason = text;
};

// Reset default active tab if not in available tabs
watch(
	() => props.show,
	(isShowing) => {
		if (isShowing && props.availableTabs.length > 0) {
			if (!props.availableTabs.includes(activeTab.value)) {
				activeTab.value = props.availableTabs[0];
			}
		}
	},
);

const isProcessing = ref(false);

const submitDecision = () => {
	if (!props.item) return;
	isProcessing.value = true;

	const onSuccess = () => {
		isProcessing.value = false;
		emit("success");
		emit("close");
	};

	const onError = () => {
		isProcessing.value = false;
	};

	const targetUserId = props.item.userId || 1;

	if (activeTab.value === "dismiss") {
		dismissForm.post(`/pagi/admin/content/work/${props.item.id}/moderate`, {
			onSuccess,
			onError,
		});
	} else if (activeTab.value === "takedown") {
		takedownForm.post(`/pagi/admin/content/work/${props.item.id}/moderate`, {
			onSuccess,
			onError,
		});
	} else if (activeTab.value === "warn") {
		warningForm.content_id = props.item.id;

		if (alsoHideContentOnWarn.value) {
			// First hide content, then issue official warning
			takedownForm.action = "hide";
			takedownForm.reason = warningForm.reason || "Takedown otomatis terkait Surat Peringatan (SP)";
			takedownForm.post(`/pagi/admin/content/work/${props.item.id}/moderate`, {
				preserveScroll: true,
				onSuccess: () => {
					warningForm.post(`/pagi/admin/users/${targetUserId}/warn`, {
						onSuccess,
						onError,
					});
				},
				onError,
			});
		} else {
			warningForm.post(`/pagi/admin/users/${targetUserId}/warn`, {
				onSuccess,
				onError,
			});
		}
	}
};
</script>

<template>
  <Teleport to="body">
    <!-- Backdrop & Wrapper -->
    <div
      v-if="show"
      class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-950/40 backdrop-blur-md transition-all duration-300"
      @click.self="emit('close')"
    >
      <!-- Modal Content Card -->
      <div
        v-if="item"
        class="relative w-full max-h-[92vh] sm:max-h-[85vh] sm:max-w-2xl bg-white/90 dark:bg-zinc-900/90 backdrop-blur-xl sm:rounded-2xl border-t sm:border border-slate-200/60 dark:border-zinc-800/80 shadow-2xl overflow-hidden flex flex-col transform transition-all duration-300 translate-y-0 scale-100 animate-slide-up-mobile sm:animate-fade-scale"
      >
        <!-- Mobile drag indicator -->
        <div class="flex sm:hidden justify-center py-2.5 shrink-0">
          <div class="w-12 h-1 rounded-full bg-slate-300 dark:bg-zinc-700"></div>
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200/50 dark:border-zinc-800/60 shrink-0">
          <div>
            <h2 class="text-[15px] font-black tracking-tight text-slate-800 dark:text-zinc-100 flex items-center gap-2">
              <span class="inline-block w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
              Detail Peninjauan Moderasi
            </h2>
            <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5 font-medium">Tinjau laporan karya dan tetapkan tindakan keputusan moderasi</p>
          </div>
          <button
            @click="emit('close')"
            class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800/50 p-1.5 rounded-xl transition-all"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Content Area -->
        <div class="p-6 overflow-y-auto space-y-5 flex-1 scrollbar-thin text-left">
          <!-- Section 1: Karya Info -->
          <div class="space-y-2">
            <span class="text-[10px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Informasi Karya</span>
            <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-50/70 dark:bg-zinc-950/20 border border-slate-100 dark:border-zinc-800/80 shadow-inner">
              <!-- Cover Thumbnail -->
              <div class="h-16 w-16 shrink-0 rounded-xl bg-slate-100 dark:bg-zinc-800 overflow-hidden border border-slate-200/50 dark:border-zinc-700/50 shadow-sm relative group">
                <img v-if="item.thumbnail" :src="item.thumbnail" :alt="item.title || 'Pratinjau konten'" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
                <div v-else class="h-full w-full flex items-center justify-center bg-slate-100 dark:bg-zinc-800">
                  <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159" />
                  </svg>
                </div>
              </div>
              <!-- Meta Info -->
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 mb-1">
                  <span class="inline-block text-[9px] font-black bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 px-2 py-0.5 rounded-md border border-indigo-100 dark:border-indigo-900/30">
                    {{ item.category }}
                  </span>
                  <span class="inline-block text-[9px] font-black bg-slate-100 dark:bg-zinc-800 text-slate-500 dark:text-zinc-400 px-2 py-0.5 rounded-md">
                    ID #{{ item.id }}
                  </span>
                </div>
                <h3 class="text-[13px] font-extrabold text-slate-800 dark:text-zinc-100 truncate tracking-tight">{{ item.title }}</h3>
                <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5 line-clamp-2 leading-relaxed">{{ item.description }}</p>
                <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-2 font-medium">
                  Pembuat: <span class="text-slate-700 dark:text-zinc-300 font-bold hover:underline cursor-pointer">{{ item.author }}</span> 
                  <span class="text-slate-400 dark:text-zinc-600 font-normal ml-1">{{ item.authorHandle }}</span>
                </p>
              </div>
            </div>
          </div>

          <!-- Section 2: Report details (if Laporan/Report) -->
          <div v-if="item.reportReason" class="space-y-2">
            <div class="flex items-center justify-between">
              <span class="text-[10px] font-black uppercase tracking-widest text-rose-600 dark:text-rose-400">Rincian Laporan</span>
              <span
                :class="[
                  'px-2.5 py-0.5 text-[9.5px] font-black rounded-md border',
                  ((item as any).sourceType === 'ai_flag' || item.reporterHandle === '@system.sentinel' || item.reportDescription?.includes('[Auto Moderasi AI]'))
                    ? 'bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800'
                    : 'bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800'
                ]"
              >
                {{ ((item as any).sourceType === 'ai_flag' || item.reporterHandle === '@system.sentinel' || item.reportDescription?.includes('[Auto Moderasi AI]')) ? '🤖 Automasi AI Sentinel' : '👥 Laporan Pengguna' }}
              </span>
            </div>
            <div class="p-4 rounded-xl bg-rose-50/45 dark:bg-rose-950/5 border border-rose-100/40 dark:border-rose-900/10 space-y-3 shadow-inner">
              <div class="flex items-center justify-between flex-wrap gap-2">
                <span class="text-[11px] font-bold text-rose-600 dark:text-rose-400 bg-rose-100/50 dark:bg-rose-950/30 px-2.5 py-0.5 rounded-md border border-rose-100 dark:border-rose-900/20">
                  {{ item.reportReason }}
                </span>
                <div class="text-[11px] text-slate-500 dark:text-zinc-400 font-medium">
                  Sumber / Pelapor: 
                  <span v-if="(item as any).sourceType === 'ai_flag' || item.reporterHandle === '@system.sentinel' || item.reportDescription?.includes('[Auto Moderasi AI]')" class="text-indigo-600 dark:text-indigo-400 font-extrabold">
                    🤖 {{ item.reportedBy || 'System Sentinel AI' }}
                  </span>
                  <span v-else class="text-slate-700 dark:text-zinc-200 font-semibold">
                    {{ item.reportedBy || item.reporterHandle }}
                  </span>
                </div>
              </div>
              <div class="relative bg-white/50 dark:bg-zinc-900/35 border border-rose-100/20 dark:border-zinc-800 p-3 rounded-lg">
                <p class="text-[12px] font-semibold text-slate-700 dark:text-zinc-300 leading-relaxed italic">
                  "{{ item.reportDescription }}"
                </p>
              </div>
              <div class="flex items-center justify-between text-[10px] text-slate-400 dark:text-zinc-500 font-medium pt-1">
                <span>Waktu laporan: {{ item.time }}</span>
                <span v-if="item.reporterHandle && item.reporterHandle !== '@system.sentinel'">Handle: {{ item.reporterHandle }}</span>
              </div>
            </div>
          </div>

          <!-- Section 3: Decision Form & 3 Unified Actions -->
          <div class="pt-3 border-t border-slate-200/50 dark:border-zinc-800/60 space-y-3">
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-zinc-400">Pilihan Keputusan Moderasi</span>

            <!-- Simplified 3 Action Tabs -->
            <div class="flex items-center gap-1.5 bg-slate-100 dark:bg-zinc-800/60 p-1.5 rounded-xl shadow-inner border border-slate-200/20 dark:border-zinc-800/40">
              <button
                v-if="availableTabs.includes('dismiss')"
                type="button"
                @click="activeTab = 'dismiss'"
                :class="[
                  'flex-1 text-center py-2 text-[11.5px] font-bold rounded-lg transition-all duration-200',
                  activeTab === 'dismiss'
                    ? 'bg-emerald-600 text-white shadow-md scale-102 font-black'
                    : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-200 hover:bg-slate-200/40 dark:hover:bg-zinc-800/20'
                ]"
              >
                1. Abaikan Laporan
              </button>
              <button
                v-if="availableTabs.includes('takedown')"
                type="button"
                @click="activeTab = 'takedown'"
                :class="[
                  'flex-1 text-center py-2 text-[11.5px] font-bold rounded-lg transition-all duration-200',
                  activeTab === 'takedown'
                    ? 'bg-rose-600 text-white shadow-md scale-102 font-black'
                    : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-200 hover:bg-slate-200/40 dark:hover:bg-zinc-800/20'
                ]"
              >
                2. Takedown Karya
              </button>
              <button
                v-if="availableTabs.includes('warn')"
                type="button"
                @click="activeTab = 'warn'"
                :class="[
                  'flex-1 text-center py-2 text-[11.5px] font-bold rounded-lg transition-all duration-200',
                  activeTab === 'warn'
                    ? 'bg-amber-500 text-white shadow-md scale-102 font-black'
                    : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-200 hover:bg-slate-200/40 dark:hover:bg-zinc-800/20'
                ]"
              >
                3. Beri Peringatan (SP)
              </button>
            </div>

            <!-- Tab Content 1: Abaikan Laporan (Dismiss) -->
            <div v-if="activeTab === 'dismiss'" class="space-y-4 pt-1 animate-fade-in">
              <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 rounded-xl flex items-start gap-2.5 shadow-sm">
                <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-[11px] text-emerald-700 dark:text-emerald-300 leading-relaxed font-medium">
                  <strong>Persetujuan Konten:</strong> Menyatakan laporan tidak berdasar atau aman. Status karya tetap aktif di galeri publik.
                </p>
              </div>
              <div>
                <div class="flex items-center justify-between mb-1.5 flex-wrap gap-1">
                  <label class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wide">Catatan Penolakan Laporan</label>
                  <span class="text-[10px] text-slate-400 dark:text-zinc-500">Pilih cepat:</span>
                </div>
                <!-- Preset Chips -->
                <div class="flex flex-wrap gap-1.5 mb-2">
                  <button
                    v-for="preset in dismissPresets"
                    :key="preset"
                    type="button"
                    @click="applyPreset('dismiss', preset)"
                    class="text-[10px] font-semibold px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-900/30 transition-all text-left"
                  >
                    + {{ preset }}
                  </button>
                </div>
                <input
                  v-model="dismissForm.reason"
                  type="text"
                  placeholder="Alasan pengabaian laporan..."
                  class="w-full h-10 rounded-xl border border-slate-200/85 dark:border-zinc-700/60 bg-white dark:bg-zinc-900 px-3.5 text-[12px] font-medium text-slate-700 dark:text-zinc-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition-all"
                />
              </div>
            </div>

            <!-- Tab Content 2: Takedown Karya -->
            <div v-if="activeTab === 'takedown'" class="space-y-4 pt-1 animate-fade-in">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 mb-1.5 uppercase tracking-wide">Aksi Penurunan</label>
                  <select
                    v-model="takedownForm.action"
                    class="w-full h-10 rounded-xl border border-slate-200/80 dark:border-zinc-700/60 bg-white dark:bg-zinc-900 px-3 text-[12px] font-semibold text-slate-700 dark:text-zinc-300 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 focus:outline-none transition-all"
                  >
                    <option value="hide">Sembunyikan (Draft untuk Revisi)</option>
                    <option value="remove">Hapus Permanen dari Platform</option>
                  </select>
                </div>
                <div class="flex items-center">
                  <label class="inline-flex items-center gap-2 cursor-pointer pt-4">
                    <input
                      type="checkbox"
                      v-model="notifyUserOnTakedown"
                      class="rounded border-slate-300 text-rose-600 focus:ring-rose-500 w-4 h-4"
                    />
                    <span class="text-[11px] font-semibold text-slate-700 dark:text-zinc-300">Kirim Notifikasi Penjelasan ke Pembuat</span>
                  </label>
                </div>
              </div>

              <div>
                <div class="flex items-center justify-between mb-1.5 flex-wrap gap-1">
                  <label class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wide">Alasan Penurunan Karya</label>
                  <span class="text-[10px] text-slate-400 dark:text-zinc-500">Pilih cepat:</span>
                </div>
                <!-- Preset Chips -->
                <div class="flex flex-wrap gap-1.5 mb-2">
                  <button
                    v-for="preset in takedownPresets"
                    :key="preset"
                    type="button"
                    @click="applyPreset('takedown', preset)"
                    class="text-[10px] font-semibold px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-300 border border-rose-100 dark:border-rose-900/30 transition-all text-left"
                  >
                    + {{ preset }}
                  </button>
                </div>
                <textarea
                  v-model="takedownForm.reason"
                  rows="3"
                  placeholder="Masukkan alasan penjelasan detail penurunan agar penulis memahami pelanggaran pedoman..."
                  class="w-full rounded-xl border border-slate-200/85 dark:border-zinc-700/60 bg-white dark:bg-zinc-900 px-3.5 py-2.5 text-[12px] font-medium text-slate-700 dark:text-zinc-300 placeholder-slate-400/80 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 focus:outline-none transition-all"
                />
              </div>
            </div>

            <!-- Tab Content 3: Beri Peringatan (SP) -->
            <div v-if="activeTab === 'warn'" class="space-y-4 pt-1 animate-fade-in">
              <div class="p-3.5 bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/30 rounded-xl flex items-start gap-2.5 shadow-sm">
                <svg class="h-4 w-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="text-[11px] text-amber-700 dark:text-amber-300 leading-relaxed font-medium space-y-1">
                  <p><strong>Peringatan Resmi (Surat Peringatan):</strong> Menerbitkan 1 SP ke akun mahasiswa. Jika akumulasi SP mencapai 3, akun akan otomatis masuk status pembatasan (*Restricted Mode*).</p>
                </div>
              </div>

              <!-- Integrated Option Checkbox -->
              <div class="p-3 bg-slate-50 dark:bg-zinc-800/60 rounded-xl border border-slate-200/60 dark:border-zinc-700/60">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="alsoHideContentOnWarn"
                    class="rounded border-slate-300 text-amber-600 focus:ring-amber-500 w-4 h-4"
                  />
                  <span class="text-[11.5px] font-bold text-slate-800 dark:text-zinc-200">Sekaligus Sembunyikan Karya Yang Melanggar (1-Klik Action)</span>
                </label>
              </div>

              <div>
                <div class="flex items-center justify-between mb-1.5 flex-wrap gap-1">
                  <label class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wide">Alasan Peringatan Resmi</label>
                  <span class="text-[10px] text-slate-400 dark:text-zinc-500">Pilih cepat:</span>
                </div>
                <!-- Preset Chips -->
                <div class="flex flex-wrap gap-1.5 mb-2">
                  <button
                    v-for="preset in warnPresets"
                    :key="preset"
                    type="button"
                    @click="applyPreset('warn', preset)"
                    class="text-[10px] font-semibold px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/40 dark:hover:bg-amber-900/60 text-amber-700 dark:text-amber-300 border border-amber-100 dark:border-amber-900/30 transition-all text-left"
                  >
                    + {{ preset }}
                  </button>
                </div>
                <textarea
                  v-model="warningForm.reason"
                  rows="3"
                  placeholder="Jelaskan alasan pengiriman peringatan secara mendalam agar mahasiswa mematuhi aturan..."
                  class="w-full rounded-xl border border-slate-200/85 dark:border-zinc-700/60 bg-white dark:bg-zinc-900 px-3.5 py-2.5 text-[12px] font-medium text-slate-700 dark:text-zinc-300 placeholder-slate-400/80 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 focus:outline-none transition-all"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-between px-6 py-4 border-t border-slate-200/50 dark:border-zinc-800/60 bg-slate-50/70 dark:bg-zinc-800/30 shrink-0">
          <button
            type="button"
            @click="emit('close')"
            class="rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-4.5 py-2 text-[12px] font-bold text-slate-600 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800 hover:text-slate-800 dark:hover:text-zinc-200 shadow-sm transition-all"
          >
            Batal
          </button>
          <button
            type="button"
            @click="submitDecision"
            :disabled="isProcessing"
            :class="[
              'rounded-xl px-5 py-2 text-[12px] font-bold text-white shadow-md transition-all disabled:opacity-50',
              activeTab === 'dismiss' ? 'bg-emerald-600 hover:bg-emerald-700' :
              activeTab === 'takedown' ? 'bg-rose-600 hover:bg-rose-700' :
              'bg-amber-600 hover:bg-amber-700'
            ]"
          >
            {{ isProcessing ? 'Memproses...' : 'Terapkan Keputusan' }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
