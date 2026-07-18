<script setup lang="ts">
import { ref } from "vue";
import { Link } from "@inertiajs/vue3";
import { 
  ArrowUpRight, 
  Calendar, 
  MapPin, 
  Copy, 
  Check, 
  ExternalLink, 
  X, 
  ChevronsRight, 
  Clock, 
  Users, 
  DollarSign,
  ShieldCheck,
  Video,
  Share2
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
  events: EventEntry[];
  title?: string;
  description?: string;
  showAllLink?: string;
}>();

const selectedEvent = ref<EventEntry | null>(null);
const copied = ref(false);
const isShareModalOpen = ref(false);
const shareEventTitle = ref("");
const shareEventUrl = ref("");
const isImagePreviewOpen = ref(false);
const previewImageUrl = ref("");

const openImagePreview = (url: string) => {
  previewImageUrl.value = url;
  isImagePreviewOpen.value = true;
};

const openEventDetails = (event: EventEntry) => {
  selectedEvent.value = event;
};

const closeEventDetails = () => {
  selectedEvent.value = null;
};

const triggerShare = (event: EventEntry) => {
  shareEventTitle.value = event.title;
  shareEventUrl.value = `${window.location.origin}/event/${event.slug}`;
  isShareModalOpen.value = true;
};

const handleCopyLinkDirect = () => {
  navigator.clipboard.writeText(shareEventUrl.value).then(() => {
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  });
};

const handleCopyLink = (slug: string) => {
  const url = `${window.location.origin}/event/${slug}`;
  navigator.clipboard.writeText(url).then(() => {
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  });
};

// Format left timeline date
const formatLeftDate = (dateStr: string) => {
  if (!dateStr) return { dayMonth: "", dayName: "" };
  const date = new Date(dateStr);
  const currentYear = new Date().getFullYear();
  const eventYear = date.getFullYear();
  
  let dayMonth = date.toLocaleDateString("id-ID", { day: "numeric", month: "short" });
  if (eventYear !== currentYear) {
    dayMonth += ` ${eventYear}`;
  }
  
  const dayName = date.toLocaleDateString("id-ID", { weekday: "long" });
  return { dayMonth, dayName };
};

// Format clock time
const formatTime = (timeStr: string) => {
  if (!timeStr) return "";
  const d = new Date(timeStr);
  return d.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" });
};

// Format time range
const formatTimeRange = (startStr: string, endStr?: string) => {
  if (!startStr) return "";
  const start = formatTime(startStr);
  if (endStr) {
    const end = formatTime(endStr);
    return `${start} - ${end} WIB`;
  }
  return `${start} WIB`;
};

// Format detail calendar date
const formatDetailDate = (dateStr: string) => {
  if (!dateStr) return "";
  const d = new Date(dateStr);
  return d.toLocaleDateString("id-ID", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric"
  });
};

const formatNumber = (num: number) => {
  if (num === undefined || num === null) return "0";
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};
</script>

<template>
  <section class="py-12 md:py-20 bg-[#F9FAFB] dark:bg-slate-950 transition-colors duration-300">
    <div class="container mx-auto px-4 max-w-4xl">
      
      <!-- Section Title Header -->
      <div class="text-center mb-12 md:mb-16">
        <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white mb-4">
          {{ title || "Timeline Event & Kegiatan" }}
        </h2>
        <p class="text-slate-500 dark:text-slate-400 max-w-xl mx-auto text-sm md:text-base leading-relaxed font-medium">
          {{ description || "Jangan lewatkan berbagai kegiatan akademik, webinar, workshop, dan agenda seru lainnya di FMIKOM." }}
        </p>
      </div>

      <!-- Empty state -->
      <div v-if="events.length === 0" class="text-center text-slate-400 py-16 border border-dashed border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900">
        <Calendar class="w-12 h-12 mx-auto mb-3 opacity-40 text-slate-400 dark:text-slate-650" />
        <p class="text-xs font-bold uppercase tracking-wider">Belum ada agenda event terdekat.</p>
      </div>

      <!-- Luma-style Timeline layout -->
      <div v-else class="relative pl-0 md:pl-8 space-y-6">
        
        <!-- Vertical timeline line (Desktop only) -->
        <div class="absolute left-[135px] top-6 bottom-6 w-[1px] bg-slate-200 dark:bg-slate-800 hidden md:block" aria-hidden="true"></div>

        <div 
          v-for="event in events" 
          :key="event.id"
          class="relative flex flex-col md:flex-row gap-2 md:gap-8 items-start animate-fade-in"
        >
          
          <!-- Timeline Date Column -->
          <div class="flex md:flex-col items-baseline md:items-end w-full md:w-[100px] shrink-0 text-left md:text-right gap-2 md:gap-0.5 pt-1.5 md:pt-4">
            <span class="text-base font-black text-slate-800 dark:text-slate-200">
              {{ formatLeftDate(event.start_time).dayMonth }}
            </span>
            <span class="text-xs text-slate-400 dark:text-slate-550 font-bold uppercase tracking-wider">
              {{ formatLeftDate(event.start_time).dayName }}
            </span>
          </div>

          <!-- Timeline dot connector (Desktop only) -->
          <div class="absolute left-[131px] top-5 w-[9px] h-[9px] bg-slate-300 dark:bg-slate-700 rounded-full border-2 border-slate-50 dark:border-slate-950 hidden md:block z-10" aria-hidden="true"></div>

          <!-- Timeline Event Card -->
          <div 
            @click="openEventDetails(event)"
            class="w-full bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800/80 rounded-xl overflow-hidden hover:shadow-md transition-all duration-300 hover:border-slate-200 dark:hover:border-slate-750 cursor-pointer group flex items-stretch justify-between"
          >
            
            <!-- Left content inside card -->
            <div class="flex-1 min-w-0 space-y-2 p-5">
              
              <!-- Time -->
              <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 block">
                {{ formatTimeRange(event.start_time, event.end_time) }}
              </span>

              <!-- Event Title -->
              <h3 class="text-base md:text-md font-black text-slate-900 dark:text-white leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200 truncate">
                {{ event.title }}
              </h3>

              <!-- Host / Organization -->
              <div class="flex items-center gap-1.5 text-xs text-slate-550 dark:text-slate-400 font-bold">
                <div v-if="event.organizer_logo" class="w-4.5 h-4.5 rounded-md overflow-hidden border border-slate-200/60 dark:border-slate-800 shrink-0 bg-slate-50 flex items-center justify-center">
                  <img :src="event.organizer_logo" class="w-full h-full object-cover" />
                </div>
                <div v-else class="w-4.5 h-4.5 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 flex items-center justify-center text-[9px] font-black shrink-0 uppercase">
                  {{ (event.organizer || 'FMIKOM').substring(0, 1) }}
                </div>
                <span class="truncate">Oleh {{ event.organizer || 'FMIKOM' }}</span>
              </div>

              <!-- Location -->
              <div class="flex items-center gap-1.5 text-xs text-slate-400 dark:text-slate-500 font-semibold">
                <MapPin v-if="!event.location?.toLowerCase().includes('zoom')" class="w-3.5 h-3.5 shrink-0" />
                <Video v-else class="w-3.5 h-3.5 shrink-0 text-blue-500" />
                <span class="truncate">{{ event.location || "Online" }}</span>
              </div>

              <!-- Badges -->
              <div class="flex flex-wrap gap-1.5 pt-1">
                <span :class="[
                  'text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md border',
                  event.is_paid 
                    ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/25' 
                    : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/25'
                ]">
                  {{ event.is_paid ? 'Berbayar' : 'Gratis' }}
                </span>
                <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md border bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/25">
                  {{ event.audience_type === 'khusus' ? 'Khusus FMIKOM' : 'Umum' }}
                </span>
              </div>

            </div>

            <!-- Right Cover image inside card — full height, flush to right edge -->
            <div v-if="event.thumbnail" class="w-32 sm:w-40 shrink-0 self-stretch bg-slate-100 dark:bg-slate-800">
              <img 
                :src="event.thumbnail" 
                :alt="event.title" 
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                loading="lazy"
              />
            </div>

          </div>

        </div>

      </div>

      <!-- Show All Link Button -->
      <div v-if="showAllLink && events.length > 0" class="flex justify-center mt-12 md:mt-16">
        <Button 
          variant="outline" 
          size="lg"
          class="group bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-blue-600 hover:text-blue-600 font-bold transition-all duration-300 rounded-xl px-6 py-3.5 shadow-sm hover:shadow-md cursor-pointer flex items-center gap-2"
          as-child
        >
          <a :href="showAllLink">
            Lihat Semua Event
            <ArrowUpRight class="w-4 h-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
          </a>
        </Button>
      </div>

    </div>
  </section>

  <!-- SIDEBAR DRAWER OVERLAY -->
  <Transition name="fade">
    <div 
      v-if="selectedEvent" 
      class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-[100]" 
      @click="closeEventDetails"
    ></div>
  </Transition>

  <!-- SIDEBAR DRAWER (Sliding from Right) -->
  <Transition name="slide-over">
    <div 
      v-if="selectedEvent" 
      class="fixed top-2 bottom-2 right-2 w-[calc(100%-16px)] sm:w-[550px] bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl shadow-2xl z-[101] flex flex-col overflow-hidden"
    >
      
      <!-- Drawer Header Bar -->
      <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/30 dark:bg-slate-900/50">
        <button 
          @click="closeEventDetails" 
          class="w-9 h-9 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-slate-800 dark:hover:text-white transition-colors cursor-pointer"
          title="Tutup"
        >
          <ChevronsRight class="w-5 h-5" />
        </button>

        <div class="flex items-center gap-2">
          <!-- Copy/Share Link Button -->
          <button 
            @click="triggerShare(selectedEvent)" 
            class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/50 dark:border-slate-700/80 transition-all cursor-pointer"
          >
            <Share2 class="w-3.5 h-3.5" />
            <span>Bagikan</span>
          </button>

          <!-- Dedicated Page Link -->
          <Link 
            :href="`/event/${selectedEvent.slug}`" 
            class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-slate-50 dark:bg-slate-850 hover:bg-slate-100 dark:hover:bg-slate-850 hover:text-blue-600 dark:hover:text-blue-400 text-slate-700 dark:text-slate-300 border border-slate-200/50 dark:border-slate-700/80 transition-all cursor-pointer"
          >
            <span>Halaman Acara</span>
            <ExternalLink class="w-3.5 h-3.5" />
          </Link>
        </div>
      </div>

      <!-- Drawer Content Body (Scrollable) -->
      <div class="flex-1 overflow-y-auto p-6 space-y-5">
        
        <!-- Cover Image -->
        <div v-if="selectedEvent.thumbnail" class="w-full rounded-xl overflow-hidden border border-slate-100 dark:border-slate-800/80 aspect-video bg-slate-50 dark:bg-slate-950 cursor-zoom-in hover:opacity-90 transition-opacity" @click="openImagePreview(selectedEvent.thumbnail)">
          <img 
            :src="selectedEvent.thumbnail" 
            :alt="selectedEvent.title" 
            class="w-full h-full object-cover"
          />
        </div>

        <!-- Title & Organizer -->
        <div class="space-y-2.5">
          <h2 class="text-lg md:text-xl font-black text-slate-900 dark:text-white leading-snug">
            {{ selectedEvent.title }}
          </h2>
          
          <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
            <div v-if="selectedEvent.organizer_logo" class="w-5 h-5 rounded-md overflow-hidden border border-slate-200/60 dark:border-slate-800 shrink-0 bg-slate-50 flex items-center justify-center">
              <img :src="selectedEvent.organizer_logo" class="w-full h-full object-cover" />
            </div>
            <div v-else class="w-5 h-5 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 flex items-center justify-center text-[9px] font-black uppercase shrink-0">
              {{ (selectedEvent.organizer || 'FMIKOM').substring(0, 1) }}
            </div>
            <span>Diselenggarakan oleh {{ selectedEvent.organizer || 'FMIKOM' }}</span>
          </div>
        </div>

        <!-- Date & Time Info Card -->
        <div class="flex items-center gap-4 p-4 rounded-xl border border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/30">
          <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex flex-col items-center justify-center border border-blue-100/30 shrink-0">
            <span class="text-[9px] font-black leading-none uppercase">{{ new Date(selectedEvent.start_time).toLocaleDateString('id-ID', { month: 'short' }) }}</span>
            <span class="text-base font-black leading-none mt-0.5">{{ new Date(selectedEvent.start_time).getDate() }}</span>
          </div>
          <div class="flex-1 min-w-0">
            <span class="text-[9px] text-slate-400 dark:text-slate-550 font-black uppercase tracking-wider block">Waktu Acara</span>
            <h4 class="text-xs font-black text-slate-800 dark:text-slate-200 mt-0.5">
              {{ formatDetailDate(selectedEvent.start_time) }}
            </h4>
            <p class="text-xs text-slate-405 dark:text-slate-500 font-semibold mt-0.5">
              {{ formatTimeRange(selectedEvent.start_time, selectedEvent.end_time) }}
            </p>
          </div>
        </div>

        <!-- Location Info Card -->
        <div class="flex items-center gap-4 p-4 rounded-xl border border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/30">
          <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center shrink-0">
            <MapPin v-if="!selectedEvent.location?.toLowerCase().includes('zoom')" class="w-4 h-4" />
            <Video v-else class="w-4 h-4 text-blue-500" />
          </div>
          <div class="flex-1 min-w-0">
            <span class="text-[9px] text-slate-400 dark:text-slate-555 font-black uppercase tracking-wider block">Tempat Pelaksanaan</span>
            <h4 class="text-xs font-black text-slate-800 dark:text-slate-200 mt-0.5 truncate">
              {{ selectedEvent.location || "Online" }}
            </h4>
          </div>
        </div>

        <!-- Join Box Container -->
        <div class="p-5 rounded-xl border border-slate-150 dark:border-slate-800 bg-[#F9FAFB] dark:bg-slate-900/45 shadow-xs space-y-4">
          <!-- Badges inside Box -->
          <div class="grid grid-cols-3 gap-2 text-center">
            <div class="space-y-1">
              <span class="text-[10px] text-slate-400 dark:text-slate-500 font-extrabold uppercase tracking-wider block">Biaya Masuk</span>
              <span class="text-xs font-black text-slate-900 dark:text-white block">
                {{ selectedEvent.is_paid ? 'Rp ' + formatNumber(selectedEvent.price) : 'Gratis' }}
              </span>
            </div>

            <div class="space-y-1 border-x border-slate-200/60 dark:border-slate-800/80 px-1">
              <span class="text-[10px] text-slate-400 dark:text-slate-500 font-extrabold uppercase tracking-wider block">Target Peserta</span>
              <span class="text-xs font-black text-slate-900 dark:text-white block">
                {{ selectedEvent.audience_type === 'khusus' ? 'FMIKOM' : 'Umum' }}
              </span>
            </div>

            <div class="space-y-1">
              <span class="text-[10px] text-slate-400 dark:text-slate-500 font-extrabold uppercase tracking-wider block">Kuota Kursi</span>
              <span class="text-xs font-black text-slate-900 dark:text-white block">
                {{ selectedEvent.is_quota_limited ? selectedEvent.quota + ' Orang' : 'Tak Terbatas' }}
              </span>
            </div>
          </div>

          <!-- Registration Button -->
          <div v-if="selectedEvent.registration_link" class="pt-3 border-t border-slate-200/50 dark:border-slate-800/80">
            <Button 
              variant="default" 
              class="w-full py-4 text-xs font-black rounded-xl bg-blue-600 hover:bg-blue-750 text-white transition-all shadow-md shadow-blue-500/10 cursor-pointer flex items-center justify-center gap-2"
              as-child
            >
              <a :href="selectedEvent.registration_link" target="_blank" rel="noreferrer">
                Daftar Sekarang
                <ArrowUpRight class="w-4 h-4" />
              </a>
            </Button>
          </div>
        </div>

        <!-- Description (About event) -->
        <div class="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-800">
          <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Tentang Acara</h3>
          <div 
            class="text-xs md:text-sm leading-relaxed text-slate-650 dark:text-slate-400 prose dark:prose-invert max-w-none font-medium"
            v-html="selectedEvent.description"
          />
        </div>

      </div>

    </div>
  </Transition>

  <!-- SHARE POPUP DIALOG MODAL -->
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
          <a :href="`https://api.whatsapp.com/send?text=${encodeURIComponent(shareEventTitle + ' - ' + shareEventUrl)}`" target="_blank" class="flex flex-col items-center gap-1.5 hover:opacity-85 transition-opacity">
            <div class="w-11 h-11 rounded-lg bg-emerald-500 text-white flex items-center justify-center shadow-xs">
              <svg class="w-5.5 h-5.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.003 5.324 5.328 0 12.008 0c3.233.001 6.271 1.258 8.56 3.548 2.289 2.29 3.543 5.328 3.542 8.566-.003 6.678-5.33 12.006-12.013 12.006-2.004-.001-3.98-.496-5.739-1.442L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.42 9.863-9.864.001-2.63-1.023-5.102-2.884-6.964-1.861-1.862-4.331-2.885-6.963-2.886-5.438 0-9.863 4.42-9.866 9.865-.001 1.838.5 3.626 1.452 5.228l-.953 3.483 3.573-.938zm9.802-5.753c-.267-.134-1.583-.781-1.829-.871-.247-.09-.427-.134-.607.134-.18.267-.697.871-.853 1.05-.157.18-.314.202-.581.068-1.079-.54-1.879-.933-2.62-2.203-.197-.34-.197-.549-.033-.713.148-.148.329-.381.493-.571.164-.19.22-.326.329-.54.11-.214.055-.405-.027-.54-.082-.135-.607-1.462-.832-2.003-.22-.527-.442-.455-.607-.463-.157-.008-.337-.008-.517-.008-.18 0-.472.068-.72.337-.247.267-.944.922-.944 2.249 0 1.327.966 2.607 1.1 2.787.135.18 1.902 2.905 4.609 4.072.644.278 1.147.444 1.54.569.647.206 1.237.177 1.703.107.519-.078 1.583-.647 1.808-1.27.225-.623.225-1.157.157-1.27-.068-.113-.247-.202-.517-.337z"/></svg>
            </div>
            <span class="text-[10px] font-bold text-slate-500">WhatsApp</span>
          </a>
          <!-- Telegram -->
          <a :href="`https://t.me/share/url?url=${encodeURIComponent(shareEventUrl)}&text=${encodeURIComponent(shareEventTitle)}`" target="_blank" class="flex flex-col items-center gap-1.5 hover:opacity-85 transition-opacity">
            <div class="w-11 h-11 rounded-lg bg-sky-500 text-white flex items-center justify-center shadow-xs">
              <svg class="w-5.5 h-5.5 fill-current" viewBox="0 0 24 24"><path d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.47-.52-.17L7.62 13.3 3.53 12c-.89-.28-.9-.89.19-1.33L19.74 4.3c.74-.27 1.38.17 1.14 1.16l-2.73 12.87c-.2 1-.8 1.25-1.63.78l-4.17-3.07-2.01 1.94c-.22.22-.4.4-.82.4z"/></svg>
            </div>
            <span class="text-[10px] font-bold text-slate-500">Telegram</span>
          </a>
          <!-- Twitter/X -->
          <a :href="`https://twitter.com/intent/tweet?text=${encodeURIComponent(shareEventTitle)}&url=${encodeURIComponent(shareEventUrl)}`" target="_blank" class="flex flex-col items-center gap-1.5 hover:opacity-85 transition-opacity">
            <div class="w-11 h-11 rounded-lg bg-black text-white flex items-center justify-center shadow-xs">
              <svg class="w-4.5 h-4.5 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </div>
            <span class="text-[10px] font-bold text-slate-500">Twitter / X</span>
          </a>
          <!-- Facebook -->
          <a :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareEventUrl)}`" target="_blank" class="flex flex-col items-center gap-1.5 hover:opacity-85 transition-opacity">
            <div class="w-11 h-11 rounded-lg bg-blue-600 text-white flex items-center justify-center shadow-xs">
              <svg class="w-5.5 h-5.5 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </div>
            <span class="text-[10px] font-bold text-slate-500">Facebook</span>
          </a>
        </div>
        
        <!-- Copy input field -->
        <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800">
          <label class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Tautan Acara</label>
          <div class="flex items-center gap-2 p-1.5 bg-slate-50 dark:bg-slate-950 rounded-lg border border-slate-200/60 dark:border-slate-800">
            <input readonly :value="shareEventUrl" class="text-xs bg-transparent border-none outline-none focus:ring-0 flex-1 px-1 truncate text-slate-600 dark:text-slate-450 select-all" />
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
</template>

<style scoped>
/* Custom animations & transitions */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.slide-over-enter-active, .slide-over-leave-active {
  transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-over-enter-from, .slide-over-leave-to {
  transform: translateX(100%);
}

.animate-fade-in {
  animation: fadeIn 0.25s ease-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(4px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
