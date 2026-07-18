<script setup lang="ts">
import { ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import PublicNavbar from "@/components/Portal/PublicNavbar.vue";
import PublicFooter from "@/components/Portal/PublicFooter.vue";
import { 
  ArrowLeft, 
  Calendar, 
  MapPin, 
  Copy, 
  Check, 
  ArrowUpRight, 
  Clock, 
  ShieldCheck,
  Video,
  Share2,
  X,
  Coins,
  Users,
  UserCheck
} from "lucide-vue-next";
import { Button } from "@/components/ui/button";

export interface EventEntry {
  id: number;
  title: string;
  organizer?: string;
  slug: string;
  description: string;
  location?: string;
  start_time: string;
  end_time?: string;
  registration_link?: string;
  thumbnail?: string;
  is_paid: boolean;
  price: number;
  audience_type: string;
  is_quota_limited: boolean;
  quota?: number;
}

const props = defineProps<{
  event: EventEntry;
}>();

const copied = ref(false);
const isShareModalOpen = ref(false);
const isImagePreviewOpen = ref(false);
const previewImageUrl = ref("");

const openImagePreview = (url: string) => {
  previewImageUrl.value = url;
  isImagePreviewOpen.value = true;
};

const triggerShare = () => {
  isShareModalOpen.value = true;
};

const handleCopyLinkDirect = () => {
  navigator.clipboard.writeText(window.location.href).then(() => {
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  });
};

const getEventDayName = (dateStr: string) => {
  if (!dateStr) return "";
  const d = new Date(dateStr);
  return d.toLocaleDateString("id-ID", { weekday: "long" });
};

const getEventFormattedDate = (dateStr: string) => {
  if (!dateStr) return "";
  const d = new Date(dateStr);
  return d.toLocaleDateString("id-ID", {
    day: "numeric",
    month: "long",
    year: "numeric"
  });
};

const formatTime = (timeStr: string) => {
  if (!timeStr) return "";
  const d = new Date(timeStr);
  return d.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" });
};

const formatTimeRange = (startStr: string, endStr?: string) => {
  if (!startStr) return "";
  const start = formatTime(startStr);
  if (endStr) {
    const end = formatTime(endStr);
    return `${start} - ${end} WIB`;
  }
  return `${start} WIB`;
};

const formatNumber = (num: number) => {
  if (num === undefined || num === null) return "0";
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};
</script>

<template>
  <Head>
    <title>{{ event.title }} - Portal FMIKOM</title>
    <meta name="description" content="Detail lengkap kegiatan dan event di Fakultas Matematika dan Ilmu Komputer." />
  </Head>

  <div class="min-h-screen bg-[#F9FAFB] dark:bg-slate-950 font-sans antialiased text-slate-900 dark:text-slate-100 transition-colors duration-300">
    <!-- Navbar -->
    <PublicNavbar />

    <!-- Main Section -->
    <main class="py-10 md:py-16">
      <div class="container mx-auto px-4 max-w-5xl">
        
        <!-- Back Navigation Link -->
        <div class="mb-8">
          <Link 
            href="/event"
            class="inline-flex items-center gap-2 text-xs font-black text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors uppercase tracking-wider"
          >
            <ArrowLeft class="w-4 h-4" />
            Kembali ke Daftar Event
          </Link>
        </div>

        <!-- Event Grid Layout -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
          
          <!-- LEFT COLUMN (Cover image & Host Details) -->
          <div class="md:col-span-5 space-y-6">
            <!-- Cover image card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800/80 rounded-xl overflow-hidden p-3 shadow-xs">
              <div v-if="event.thumbnail" class="w-full rounded-lg overflow-hidden aspect-video bg-slate-50 dark:bg-slate-950 cursor-zoom-in hover:opacity-90 transition-opacity" @click="openImagePreview(event.thumbnail)">
                <img 
                  :src="event.thumbnail" 
                  :alt="event.title" 
                  class="w-full h-full object-cover"
                />
              </div>
              <div v-else class="w-full rounded-lg overflow-hidden aspect-video bg-slate-100 dark:bg-slate-955 flex flex-col items-center justify-center text-slate-350 dark:text-slate-650">
                <Calendar class="w-12 h-12 opacity-30 mb-2" />
                <span class="text-[10px] font-black uppercase tracking-wider">No Cover Poster</span>
              </div>
            </div>

            <!-- Host block -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800/80 rounded-xl p-5 space-y-3.5 shadow-xs">
              <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Diselenggarakan Oleh</h4>
              <div class="flex items-center gap-3">
                <div v-if="event.organizer_logo" class="w-9 h-9 rounded-lg overflow-hidden border border-slate-200/60 dark:border-slate-800 shrink-0 bg-slate-50 flex items-center justify-center">
                  <img :src="event.organizer_logo" class="w-full h-full object-cover" />
                </div>
                <div v-else class="w-9 h-9 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 flex items-center justify-center text-sm font-black uppercase shrink-0">
                  {{ (event.organizer || 'FMIKOM').substring(0, 1) }}
                </div>
                <div>
                  <h5 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 leading-none">
                    {{ event.organizer || 'FMIKOM' }}
                  </h5>
                  <p class="text-[10px] text-slate-450 font-bold uppercase tracking-wider mt-1">Penyelenggara Acara</p>
                </div>
              </div>
            </div>

            <!-- Share & Copy actions -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800/80 rounded-xl p-5 shadow-xs flex items-center justify-between">
              <span class="text-xs font-bold text-slate-500">Bagikan acara ini</span>
              <button 
                @click="triggerShare" 
                class="flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold bg-slate-50 hover:bg-slate-100 dark:bg-slate-850 dark:hover:bg-slate-800 border border-slate-200/50 dark:border-slate-700/85 text-slate-700 dark:text-slate-300 transition-all cursor-pointer"
              >
                <Share2 class="w-4 h-4" />
                <span>Bagikan</span>
              </button>
            </div>
          </div>

          <!-- RIGHT COLUMN (Event Metadata details) -->
          <div class="md:col-span-7 space-y-6">
            
            <!-- Main Header Info -->
            <div class="space-y-4">
              <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white leading-tight">
                {{ event.title }}
              </h1>
            </div>

            <!-- Stacked Metadata Items (Luma style) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl p-6 shadow-xs space-y-5">
              
              <!-- 1. Waktu Acara Block -->
              <div class="flex gap-4 items-start">
                <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex flex-col items-center justify-center border border-blue-100/20 shrink-0">
                  <span class="text-[9px] font-black leading-none uppercase">{{ new Date(event.start_time).toLocaleDateString('id-ID', { month: 'short' }) }}</span>
                  <span class="text-base font-black leading-none mt-0.5">{{ new Date(event.start_time).getDate() }}</span>
                </div>
                <div class="flex-1 min-w-0">
                  <span class="text-[9px] text-slate-400 dark:text-slate-550 font-black uppercase tracking-wider block">Waktu Acara</span>
                  <h4 class="text-xs font-black text-slate-800 dark:text-slate-200 mt-0.5">
                    {{ getEventDayName(event.start_time) }}, {{ getEventFormattedDate(event.start_time) }}
                  </h4>
                  <p class="text-xs text-slate-405 dark:text-slate-500 font-semibold mt-0.5">
                    {{ formatTimeRange(event.start_time, event.end_time) }}
                  </p>
                </div>
              </div>

              <!-- 2. Tempat Pelaksanaan Block -->
              <div class="flex gap-4 items-start pt-4 border-t border-slate-100 dark:border-slate-850">
                <div class="w-10 h-10 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-150 dark:border-slate-700/80 text-slate-500 dark:text-slate-400 flex items-center justify-center shrink-0">
                  <MapPin v-if="!event.location?.toLowerCase().includes('zoom')" class="w-5 h-5" />
                  <Video v-else class="w-5 h-5 text-blue-500" />
                </div>
                <div class="flex-1 min-w-0">
                  <span class="text-[9px] text-slate-400 dark:text-slate-555 font-black uppercase tracking-wider block">Tempat Pelaksanaan</span>
                  <h4 class="text-xs font-black text-slate-800 dark:text-slate-200 mt-0.5">
                    {{ event.location || "Online" }}
                  </h4>
                </div>
              </div>

              <!-- 3. Biaya Masuk Block -->
              <div class="flex gap-4 items-start pt-4 border-t border-slate-100 dark:border-slate-850">
                <div class="w-10 h-10 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-150 dark:border-slate-700/80 text-slate-500 dark:text-slate-400 flex items-center justify-center shrink-0">
                  <Coins class="w-5 h-5 text-amber-500" />
                </div>
                <div class="flex-1 min-w-0">
                  <span class="text-[9px] text-slate-400 dark:text-slate-555 font-black uppercase tracking-wider block">Biaya Masuk</span>
                  <h4 class="text-xs font-black text-slate-800 dark:text-slate-200 mt-0.5">
                    {{ event.is_paid ? 'Rp ' + formatNumber(event.price) : 'Gratis' }}
                  </h4>
                </div>
              </div>

              <!-- 4. Target Peserta Block -->
              <div class="flex gap-4 items-start pt-4 border-t border-slate-100 dark:border-slate-850">
                <div class="w-10 h-10 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-150 dark:border-slate-700/80 text-slate-500 dark:text-slate-400 flex items-center justify-center shrink-0">
                  <Users class="w-5 h-5 text-blue-500" />
                </div>
                <div class="flex-1 min-w-0">
                  <span class="text-[9px] text-slate-400 dark:text-slate-555 font-black uppercase tracking-wider block">Target Peserta</span>
                  <h4 class="text-xs font-black text-slate-800 dark:text-slate-200 mt-0.5">
                    {{ event.audience_type === 'khusus' ? 'Civitas FMIKOM' : 'Umum / Publik' }}
                  </h4>
                </div>
              </div>

              <!-- 5. Kuota Kursi Block -->
              <div class="flex gap-4 items-start pt-4 border-t border-slate-100 dark:border-slate-850">
                <div class="w-10 h-10 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-150 dark:border-slate-700/80 text-slate-500 dark:text-slate-400 flex items-center justify-center shrink-0">
                  <UserCheck class="w-5 h-5 text-indigo-500" />
                </div>
                <div class="flex-1 min-w-0">
                  <span class="text-[9px] text-slate-400 dark:text-slate-555 font-black uppercase tracking-wider block">Kuota Kursi</span>
                  <h4 class="text-xs font-black text-slate-800 dark:text-slate-200 mt-0.5">
                    {{ event.is_quota_limited ? event.quota + ' Orang' : 'Tidak Terbatas' }}
                  </h4>
                </div>
              </div>

            </div>

            <!-- Registration Action Ticket -->
            <div class="p-5 rounded-xl bg-blue-50/20 dark:bg-blue-900/5 border border-blue-150/45 dark:border-blue-900/20 space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-xs font-black text-blue-700 dark:text-blue-400 uppercase tracking-wider flex items-center gap-1.5">
                  <ShieldCheck class="w-4 h-4 text-emerald-500" />
                  Pendaftaran Terbuka
                </span>
              </div>

              <!-- Action Register Button -->
              <div v-if="event.registration_link">
                <Button 
                  variant="default" 
                  class="w-full py-4 text-xs font-black rounded-xl bg-blue-600 hover:bg-blue-750 text-white transition-all shadow-md shadow-blue-500/10 cursor-pointer flex items-center justify-center gap-2"
                  as-child
                >
                  <a :href="event.registration_link" target="_blank" rel="noreferrer">
                    Daftar Sekarang
                    <ArrowUpRight class="w-4 h-4" />
                  </a>
                </Button>
              </div>
            </div>

            <!-- About / Content Detail description -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl p-6 md:p-8 space-y-4 shadow-xs">
              <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider pb-3 border-b border-slate-50 dark:border-slate-850">Tentang Acara</h3>
              <div 
                class="text-xs md:text-sm leading-relaxed text-slate-650 dark:text-slate-400 prose dark:prose-invert max-w-none font-medium"
                v-html="event.description"
              />
            </div>

          </div>

        </div>

      </div>
    </main>

    <!-- Footer -->
    <PublicFooter />

    <!-- SHARE DIALOG MODAL -->
    <Transition name="fade">
      <div 
        v-if="isShareModalOpen" 
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-[200] flex items-center justify-center p-4" 
        @click.self="isShareModalOpen = false"
      >
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-850 shadow-2xl w-full max-w-sm overflow-hidden p-6 space-y-4 animate-fade-in">
          <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Bagikan Acara</h3>
            <button @click="isShareModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer">
              <X class="w-4 h-4" />
            </button>
          </div>
          
          <div class="grid grid-cols-4 gap-3 py-1">
            <!-- WhatsApp -->
            <a :href="`https://api.whatsapp.com/send?text=${encodeURIComponent(event.title + ' - ' + window.location.href)}`" target="_blank" class="flex flex-col items-center gap-1.5 hover:opacity-85 transition-opacity">
              <div class="w-11 h-11 rounded-lg bg-emerald-500 text-white flex items-center justify-center shadow-xs">
                <svg class="w-5.5 h-5.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.003 5.324 5.328 0 12.008 0c3.233.001 6.271 1.258 8.56 3.548 2.289 2.29 3.543 5.328 3.542 8.566-.003 6.678-5.33 12.006-12.013 12.006-2.004-.001-3.98-.496-5.739-1.442L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.42 9.863-9.864.001-2.63-1.023-5.102-2.884-6.964-1.861-1.862-4.331-2.885-6.963-2.886-5.438 0-9.863 4.42-9.866 9.865-.001 1.838.5 3.626 1.452 5.228l-.953 3.483 3.573-.938zm9.802-5.753c-.267-.134-1.583-.781-1.829-.871-.247-.09-.427-.134-.607.134-.18.267-.697.871-.853 1.05-.157.18-.314.202-.581.068-1.079-.54-1.879-.933-2.62-2.203-.197-.34-.197-.549-.033-.713.148-.148.329-.381.493-.571.164-.19.22-.326.329-.54.11-.214.055-.405-.027-.54-.082-.135-.607-1.462-.832-2.003-.22-.527-.442-.455-.607-.463-.157-.008-.337-.008-.517-.008-.18 0-.472.068-.72.337-.247.267-.944.922-.944 2.249 0 1.327.966 2.607 1.1 2.787.135.18 1.902 2.905 4.609 4.072.644.278 1.147.444 1.54.569.647.206 1.237.177 1.703.107.519-.078 1.583-.647 1.808-1.27.225-.623.225-1.157.157-1.27-.068-.113-.247-.202-.517-.337z"/></svg>
              </div>
              <span class="text-[10px] font-bold text-slate-500">WhatsApp</span>
            </a>
            <!-- Telegram -->
            <a :href="`https://t.me/share/url?url=${encodeURIComponent(window.location.href)}&text=${encodeURIComponent(event.title)}`" target="_blank" class="flex flex-col items-center gap-1.5 hover:opacity-85 transition-opacity">
              <div class="w-11 h-11 rounded-lg bg-sky-500 text-white flex items-center justify-center shadow-xs">
                <svg class="w-5.5 h-5.5 fill-current" viewBox="0 0 24 24"><path d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.47-.52-.17L7.62 13.3 3.53 12c-.89-.28-.9-.89.19-1.33L19.74 4.3c.74-.27 1.38.17 1.14 1.16l-2.73 12.87c-.2 1-.8 1.25-1.63.78l-4.17-3.07-2.01 1.94c-.22.22-.4.4-.82.4z"/></svg>
              </div>
              <span class="text-[10px] font-bold text-slate-500">Telegram</span>
            </a>
            <!-- Twitter/X -->
            <a :href="`https://twitter.com/intent/tweet?text=${encodeURIComponent(event.title)}&url=${encodeURIComponent(window.location.href)}`" target="_blank" class="flex flex-col items-center gap-1.5 hover:opacity-85 transition-opacity">
              <div class="w-11 h-11 rounded-lg bg-black text-white flex items-center justify-center shadow-xs">
                <svg class="w-4.5 h-4.5 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
              </div>
              <span class="text-[10px] font-bold text-slate-500">Twitter / X</span>
            </a>
            <!-- Facebook -->
            <a :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.href)}`" target="_blank" class="flex flex-col items-center gap-1.5 hover:opacity-85 transition-opacity">
              <div class="w-11 h-11 rounded-lg bg-blue-600 text-white flex items-center justify-center shadow-xs">
                <svg class="w-5.5 h-5.5 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
              </div>
              <span class="text-[10px] font-bold text-slate-500">Facebook</span>
            </a>
          </div>
          
          <!-- Copy input field -->
          <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800">
            <label class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Tautan Acara</label>
            <div class="flex items-center gap-2 p-1.5 bg-slate-50 dark:bg-slate-955 rounded-lg border border-slate-200/60 dark:border-slate-800">
              <input readonly :value="window.location.href" class="text-xs bg-transparent border-none outline-none focus:ring-0 flex-1 px-1 truncate text-slate-600 dark:text-slate-450 select-all" />
              <button @click="handleCopyLinkDirect" class="px-2.5 py-1.5 rounded bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-200/50 dark:border-slate-700 hover:bg-slate-100 text-[10px] font-black cursor-pointer">
                {{ copied ? 'Tersalin' : 'Salin' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- FULLSCREEN LIGHTBOX IMAGE PREVIEW -->
    <Transition name="fade">
      <div 
        v-if="isImagePreviewOpen" 
        class="fixed inset-0 bg-slate-950/90 backdrop-blur-md z-[210] flex items-center justify-center p-4" 
        @click="isImagePreviewOpen = false"
      >
        <button 
          @click="isImagePreviewOpen = false" 
          class="absolute top-6 right-6 text-white/70 hover:text-white transition-colors p-2 hover:bg-white/10 rounded-full cursor-pointer"
        >
          <X class="w-6 h-6" />
        </button>
        <img 
          :src="previewImageUrl" 
          class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl border border-white/10 select-none animate-fade-in" 
        />
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
.animate-fade-in {
  animation: fadeIn 0.2s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(4px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
