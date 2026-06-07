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
		availableTabs?: Array<"takedown" | "warn" | "dismiss" | "notify">;
	}>(),
	{
		availableTabs: () => ["takedown", "warn", "dismiss", "notify"],
	},
);

const emit = defineEmits<{
	(e: "close"): void;
	(e: "success"): void;
}>();

// Tab state
const activeTab = ref<"takedown" | "warn" | "dismiss" | "notify">("takedown");

// Forms setup
const takedownForm = useForm({
	action: "hide",
	reason: "",
});

const warningForm = useForm({
	reason: "",
	content_id: null as number | null,
});

const dismissForm = useForm({
	action: "dismiss",
	reason: "Aman, laporan tidak terbukti melanggar panduan.",
});

const notifyForm = useForm({
	action: "warn" as "warn" | "takedown" | "message",
	message: "",
	work_id: null as number | null,
});

// Reset forms when item changes
watch(
	() => props.item,
	(newItem) => {
		if (newItem) {
			takedownForm.reason = "";
			takedownForm.action = "hide";

			warningForm.reason = "";
			warningForm.content_id = newItem.id;

			dismissForm.reason = "Aman, laporan tidak terbukti melanggar panduan.";

			notifyForm.message = "";
			notifyForm.action = "warn";
			notifyForm.work_id = newItem.id;
		}
	},
	{ immediate: true },
);

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

	if (activeTab.value === "takedown") {
		takedownForm.post(`/pagi/admin/content/work/${props.item.id}/moderate`, {
			onSuccess,
			onError,
		});
	} else if (activeTab.value === "warn") {
		warningForm.content_id = props.item.id;
		warningForm.post(`/pagi/admin/users/${props.item.userId}/warn`, {
			onSuccess,
			onError,
		});
	} else if (activeTab.value === "dismiss") {
		dismissForm.post(`/pagi/admin/content/work/${props.item.id}/moderate`, {
			onSuccess,
			onError,
		});
	} else if (activeTab.value === "notify") {
		notifyForm.work_id = props.item.id;
		notifyForm.post(`/pagi/admin/users/${props.item.userId}/notify`, {
			onSuccess,
			onError,
		});
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
        class="relative w-full max-h-[92vh] sm:max-h-[85vh] sm:max-w-2xl bg-white/85 dark:bg-zinc-900/90 backdrop-blur-xl sm:rounded-2xl border-t sm:border border-slate-200/60 dark:border-zinc-800/80 shadow-2xl overflow-hidden flex flex-col transform transition-all duration-300 translate-y-0 scale-100 animate-slide-up-mobile sm:animate-fade-scale"
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
            <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5 font-medium">Tinjau laporan karya dan tetapkan tindakan hukum/moderasi</p>
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
                <img v-if="item.thumbnail" :src="item.thumbnail" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
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
            <span class="text-[10px] font-black uppercase tracking-widest text-rose-600 dark:text-rose-400">Rincian Laporan</span>
            <div class="p-4 rounded-xl bg-rose-50/45 dark:bg-rose-950/5 border border-rose-100/40 dark:border-rose-900/10 space-y-3 shadow-inner">
              <div class="flex items-center justify-between flex-wrap gap-2">
                <span class="text-[11px] font-bold text-rose-600 dark:text-rose-400 bg-rose-100/50 dark:bg-rose-950/30 px-2.5 py-0.5 rounded-md border border-rose-100 dark:border-rose-900/20">
                  {{ item.reportReason }}
                </span>
                <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">
                  Dilaporkan oleh: <span class="text-slate-600 dark:text-zinc-300 font-semibold">{{ item.reporterHandle }}</span>
                </div>
              </div>
              <div class="relative bg-white/50 dark:bg-zinc-900/35 border border-rose-100/20 dark:border-zinc-800 p-3 rounded-lg">
                <p class="text-[12px] font-semibold text-slate-700 dark:text-zinc-300 leading-relaxed italic">
                  "{{ item.reportDescription }}"
                </p>
              </div>
              <div class="flex items-center justify-between text-[10px] text-slate-400 dark:text-zinc-500 font-medium pt-1">
                <span>Waktu laporan: {{ item.time }}</span>
                <span v-if="item.reportedBy">Pelapor: {{ item.reportedBy }}</span>
              </div>
            </div>
          </div>

          <!-- Section 3: Decision Form & Tabs -->
          <div class="pt-3 border-t border-slate-200/50 dark:border-zinc-800/60 space-y-3">
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-zinc-400">Keputusan Moderasi</span>

            <!-- Glassmorphic Tabs Selection -->
            <div class="flex items-center gap-1 bg-slate-100 dark:bg-zinc-800/60 p-1 rounded-xl shadow-inner border border-slate-200/20 dark:border-zinc-800/40">
              <button
                v-if="availableTabs.includes('takedown')"
                type="button"
                @click="activeTab = 'takedown'"
                :class="[
                  'flex-1 text-center py-2 text-[11.5px] font-bold rounded-lg transition-all duration-200',
                  activeTab === 'takedown'
                    ? 'bg-white dark:bg-zinc-900 text-slate-800 dark:text-white shadow-sm scale-102 font-black border border-slate-200/40 dark:border-zinc-800'
                    : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-200 hover:bg-slate-200/40 dark:hover:bg-zinc-800/20'
                ]"
              >
                Takedown
              </button>
              <button
                v-if="availableTabs.includes('warn')"
                type="button"
                @click="activeTab = 'warn'"
                :class="[
                  'flex-1 text-center py-2 text-[11.5px] font-bold rounded-lg transition-all duration-200',
                  activeTab === 'warn'
                    ? 'bg-white dark:bg-zinc-900 text-slate-800 dark:text-white shadow-sm scale-102 font-black border border-slate-200/40 dark:border-zinc-800'
                    : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-200 hover:bg-slate-200/40 dark:hover:bg-zinc-800/20'
                ]"
              >
                Peringatan
              </button>
              <button
                v-if="availableTabs.includes('dismiss')"
                type="button"
                @click="activeTab = 'dismiss'"
                :class="[
                  'flex-1 text-center py-2 text-[11.5px] font-bold rounded-lg transition-all duration-200',
                  activeTab === 'dismiss'
                    ? 'bg-white dark:bg-zinc-900 text-slate-800 dark:text-white shadow-sm scale-102 font-black border border-slate-200/40 dark:border-zinc-800'
                    : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-200 hover:bg-slate-200/40 dark:hover:bg-zinc-800/20'
                ]"
              >
                Abaikan
              </button>
              <button
                v-if="availableTabs.includes('notify')"
                type="button"
                @click="activeTab = 'notify'"
                :class="[
                  'flex-1 text-center py-2 text-[11.5px] font-bold rounded-lg transition-all duration-200',
                  activeTab === 'notify'
                    ? 'bg-white dark:bg-zinc-900 text-slate-800 dark:text-white shadow-sm scale-102 font-black border border-slate-200/40 dark:border-zinc-800'
                    : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-200 hover:bg-slate-200/40 dark:hover:bg-zinc-800/20'
                ]"
              >
                Notifikasi
              </button>
            </div>

            <!-- Tab Content: Takedown -->
            <div v-if="activeTab === 'takedown'" class="space-y-4 pt-1 animate-fade-in">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 mb-1.5 uppercase tracking-wide">Aksi Penurunan</label>
                  <select
                    v-model="takedownForm.action"
                    class="w-full h-10 rounded-xl border border-slate-200/80 dark:border-zinc-700/60 bg-white dark:bg-zinc-900 px-3 text-[12px] font-semibold text-slate-700 dark:text-zinc-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition-all"
                  >
                    <option value="hide">Sembunyikan (Draft)</option>
                    <option value="remove">Hapus Permanen</option>
                  </select>
                </div>
                <div class="flex items-end">
                  <p class="text-[10px] text-slate-400 dark:text-zinc-500 leading-relaxed font-medium">
                    * Sembunyikan akan menurunkan karya ke status 'hidden' agar mahasiswa dapat merevisi kontennya. Hapus permanen akan melabeli status 'removed'.
                  </p>
                </div>
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 mb-1.5 uppercase tracking-wide">Alasan Penurunan (Admin Note)</label>
                <textarea
                  v-model="takedownForm.reason"
                  rows="3"
                  placeholder="Masukkan alasan penjelasan detail penurunan agar penulis memahami pelanggaran pedoman..."
                  class="w-full rounded-xl border border-slate-200/85 dark:border-zinc-700/60 bg-white dark:bg-zinc-900 px-3.5 py-2.5 text-[12px] font-medium text-slate-700 dark:text-zinc-300 placeholder-slate-400/80 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition-all"
                />
              </div>
            </div>

            <!-- Tab Content: Peringatan -->
            <div v-if="activeTab === 'warn'" class="space-y-4 pt-1 animate-fade-in">
              <div class="p-3 bg-amber-50 dark:bg-amber-950/10 border border-amber-100 dark:border-amber-900/20 rounded-xl flex items-start gap-2.5 shadow-sm">
                <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="text-[11px] text-amber-700 dark:text-amber-400 leading-relaxed font-medium">
                  <strong>Peringatan Resmi:</strong> Menetapkan status peringatan di akun mahasiswa. Laporan akan ditandai 'actioned', dan pemberitahuan formal dikirim secara otomatis.
                </p>
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 mb-1.5 uppercase tracking-wide">Alasan Peringatan</label>
                <textarea
                  v-model="warningForm.reason"
                  rows="3"
                  placeholder="Jelaskan alasan pengiriman peringatan secara mendalam agar mahasiswa mematuhi aturan..."
                  class="w-full rounded-xl border border-slate-200/85 dark:border-zinc-700/60 bg-white dark:bg-zinc-900 px-3.5 py-2.5 text-[12px] font-medium text-slate-700 dark:text-zinc-300 placeholder-slate-400/80 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition-all"
                />
              </div>
            </div>

            <!-- Tab Content: Abaikan -->
            <div v-if="activeTab === 'dismiss'" class="space-y-4 pt-1 animate-fade-in">
              <div class="p-3 bg-emerald-50 dark:bg-emerald-950/10 border border-emerald-100 dark:border-emerald-900/20 rounded-xl flex items-start gap-2.5 shadow-sm">
                <svg class="h-4 w-4 text-emerald-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-[11px] text-emerald-700 dark:text-emerald-400 leading-relaxed font-medium">
                  Menyatakan laporan tidak berdasar. Status karya tetap aktif di galeri publik. Pelapor akan diinfokan bahwa karya tidak terbukti melanggar.
                </p>
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 mb-1.5 uppercase tracking-wide">Catatan Penolakan Laporan</label>
                <input
                  v-model="dismissForm.reason"
                  type="text"
                  placeholder="Alasan pengabaian laporan..."
                  class="w-full h-10 rounded-xl border border-slate-200/85 dark:border-zinc-700/60 bg-white dark:bg-zinc-900 px-3.5 text-[12px] font-medium text-slate-700 dark:text-zinc-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition-all"
                />
              </div>
            </div>

            <!-- Tab Content: Notifikasi -->
            <div v-if="activeTab === 'notify'" class="space-y-4 pt-1 animate-fade-in">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 mb-1.5 uppercase tracking-wide">Tipe Pesan</label>
                  <select
                    v-model="notifyForm.action"
                    class="w-full h-10 rounded-xl border border-slate-200/80 dark:border-zinc-700/60 bg-white dark:bg-zinc-900 px-3 text-[12px] font-semibold text-slate-700 dark:text-zinc-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition-all"
                  >
                    <option value="message">Pesan / Peringatan Ringan</option>
                    <option value="warn">Surat Peringatan Resmi (Akun Warning)</option>
                    <option value="takedown">Takedown Konten (Sembunyikan)</option>
                  </select>
                </div>
                <div class="flex items-end">
                  <p class="text-[10px] text-slate-400 dark:text-zinc-500 leading-relaxed font-medium">
                    Kirim notifikasi langsung ke dasbor pengguna terkait dengan instruksi detail (misal: "Harap perbaiki deskripsi karya agar tidak spamming").
                  </p>
                </div>
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 mb-1.5 uppercase tracking-wide">Pesan Notifikasi</label>
                <textarea
                  v-model="notifyForm.message"
                  rows="3"
                  placeholder="Tulis pesan atau alasan untuk dikirimkan langsung kepada pemilik karya..."
                  class="w-full rounded-xl border border-slate-200/85 dark:border-zinc-700/60 bg-white dark:bg-zinc-900 px-3.5 py-2.5 text-[12px] font-medium text-slate-700 dark:text-zinc-300 placeholder-slate-400/80 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition-all"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-slate-200/50 dark:border-zinc-800/60 bg-slate-50/70 dark:bg-zinc-800/30 shrink-0">
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
            :disabled="isProcessing || takedownForm.processing || warningForm.processing || dismissForm.processing || notifyForm.processing"
            class="rounded-xl bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white px-5 py-2 text-[12px] font-black shadow-md shadow-indigo-600/10 hover:shadow-indigo-600/20 disabled:opacity-50 transition-all flex items-center gap-1.5"
          >
            <svg
              v-if="isProcessing || takedownForm.processing || warningForm.processing || dismissForm.processing || notifyForm.processing"
              class="h-3.5 w-3.5 animate-spin"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            {{
              isProcessing || takedownForm.processing || warningForm.processing || dismissForm.processing || notifyForm.processing
                ? "Memproses..."
                : "Terapkan Keputusan"
            }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
@keyframes slide-up-mobile {
  from {
    transform: translateY(100%);
  }
  to {
    transform: translateY(0);
  }
}

@keyframes fade-scale {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.animate-slide-up-mobile {
  @media (max-width: 639px) {
    animation: slide-up-mobile 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  }
}

.animate-fade-scale {
  @media (min-width: 640px) {
    animation: fade-scale 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  }
}

.animate-fade-in {
  animation: fadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(2px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Custom scrollbar styling for seamless design */
.scrollbar-thin::-webkit-scrollbar {
  width: 5px;
}
.scrollbar-thin::-webkit-scrollbar-track {
  background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.2);
  border-radius: 9999px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.45);
}
</style>
