<script setup lang="ts">
import { ref } from "vue";
import { Head } from "@inertiajs/vue3";
import PublicFooter from "@/components/Portal/PublicFooter.vue";
import PublicNavbar from "@/components/Portal/PublicNavbar.vue";
import EventTimeline, { EventEntry } from "@/components/Portal/EventTimeline.vue";

defineProps<{
  activeEvents: EventEntry[];
  pastEvents: EventEntry[];
}>();

const activeTab = ref("aktif");
</script>

<template>
  <Head>
    <title>Agenda Event & Kegiatan - Portal FMIKOM</title>
    <meta name="description" content="Jelajahi berbagai agenda kegiatan, seminar, webinar, dan event akademik terbaru di lingkungan FMIKOM." />
  </Head>

  <div class="min-h-screen bg-white dark:bg-slate-950 font-sans antialiased text-slate-900 dark:text-slate-100 transition-colors duration-300">
    <!-- Navigation -->
    <PublicNavbar />

    <!-- Main Content -->
    <main class="pt-8">
      
      <!-- Tabs switcher -->
      <div class="flex justify-center mt-8">
        <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-900 rounded-2xl border border-slate-200/50 dark:border-slate-800/80 shadow-xs">
          <button 
            @click="activeTab = 'aktif'"
            :class="[
              'px-5 py-2.5 rounded-xl text-xs font-black transition-all flex items-center gap-2 cursor-pointer',
              activeTab === 'aktif' 
                ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-xs border border-slate-200/10' 
                : 'text-slate-500 dark:text-slate-450 hover:text-slate-700'
            ]"
          >
            Event Aktif / Mendatang
            <span class="px-2 py-0.5 rounded-md text-[10px] bg-slate-200/60 dark:bg-slate-800 text-slate-650 dark:text-slate-450 font-extrabold">
              {{ activeEvents.length }}
            </span>
          </button>
          <button 
            @click="activeTab = 'selesai'"
            :class="[
              'px-5 py-2.5 rounded-xl text-xs font-black transition-all flex items-center gap-2 cursor-pointer',
              activeTab === 'selesai' 
                ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-xs border border-slate-200/10' 
                : 'text-slate-500 dark:text-slate-450 hover:text-slate-700'
            ]"
          >
            Event Selesai / Lampau
            <span class="px-2 py-0.5 rounded-md text-[10px] bg-slate-200/60 dark:bg-slate-800 text-slate-650 dark:text-slate-450 font-extrabold">
              {{ pastEvents.length }}
            </span>
          </button>
        </div>
      </div>

      <!-- Active Events Timeline -->
      <div v-if="activeTab === 'aktif'" class="animate-fade-in">
        <EventTimeline 
          :events="activeEvents" 
          title="Timeline Event Aktif" 
          description="Jangan lewatkan berbagai agenda seminar, webinar, workshop, kompetisi, dan kegiatan fakultas terbaru yang akan datang."
        />
      </div>

      <!-- Past Events Timeline -->
      <div v-else class="animate-fade-in">
        <EventTimeline 
          :events="pastEvents" 
          title="Event Selesai / Lampau" 
          description="Arsip dan daftar dokumentasi berbagai kegiatan dan event yang telah selesai diselenggarakan di lingkungan FMIKOM."
        />
      </div>

    </main>

    <!-- Footer -->
    <PublicFooter />
  </div>
</template>
